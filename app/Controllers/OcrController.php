<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * OCR-backed ID verification helper.
 *
 * Uses the ocr.space Free API to read the text from an uploaded ID
 * (front and optional back photo/PDF) and compares it against the
 * personal info the secretary or captain typed for the resident.
 *
 * Optimized to handle:
 * - Upside-down and rotated IDs (tries multiple orientations)
 * - Common OCR character confusions (0/O, 1/I/L, 5/S, etc.)
 * - Fuzzy name matching with similarity threshold
 * - Various date formats used in Philippine IDs
 *
 * The endpoint returns JSON only; the census form calls it from the
 * browser after both ID files are picked and the name/DOB fields have
 * values.
 */
class OcrController extends BaseController
{
    private const OCR_ENDPOINT       = 'https://api.ocr.space/parse/image';
    private const MAX_UPLOAD_BYTES   = 5 * 1024 * 1024;
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
        'application/pdf',
    ];

    // Minimum similarity percentage for fuzzy name matching (0-100)
    private const NAME_SIMILARITY_THRESHOLD = 75;

    public function verifyId()
    {
        $response = $this->response->setContentType('application/json');

        try {
            log_message('info', 'OCR verifyId called');

            if (! function_exists('curl_init')) {
                return $response->setStatusCode(503)->setJSON([
                    'ok'    => false,
                    'error' => 'The PHP curl extension is not enabled on this server.',
                ]);
            }

            $apiKey = trim((string) env('OCR_SPACE_API_KEY', ''));
            if ($apiKey === '') {
                log_message('warning', 'OCR verifyId: API key missing from environment.');
                return $response->setStatusCode(503)->setJSON([
                    'ok'    => false,
                    'error' => 'OCR service is not configured (missing OCR_SPACE_API_KEY).',
                ]);
            }

            $frontFile = $this->request->getFile('id_front');
            $backFile  = $this->request->getFile('id_back');
            $fullName   = trim((string) $this->request->getPost('full_name'));
            $firstName  = trim((string) $this->request->getPost('first_name'));
            $middleName = trim((string) $this->request->getPost('middle_name'));
            $lastName   = trim((string) $this->request->getPost('last_name'));
            $dob        = trim((string) $this->request->getPost('date_of_birth'));
            $documentType = trim((string) $this->request->getPost('document_type'));
            $isBirthOrId = $documentType === 'birth_or_id';

            if (! $frontFile || $frontFile->getError() === UPLOAD_ERR_NO_FILE) {
                return $response->setStatusCode(400)->setJSON([
                    'ok'    => false,
                    'error' => $isBirthOrId
                        ? 'Please pick the ID or birth certificate first.'
                        : 'Please pick the front photo of the ID first.',
                ]);
            }

            $frontError = $this->validateUpload($frontFile);
            if ($frontError !== null) {
                return $response->setStatusCode(400)->setJSON([
                    'ok'    => false,
                    'error' => 'Front image: ' . $frontError,
                ]);
            }

            $hasBack = $backFile && $backFile->getError() !== UPLOAD_ERR_NO_FILE;
            if ($hasBack) {
                $backError = $this->validateUpload($backFile);
                if ($backError !== null) {
                    return $response->setStatusCode(400)->setJSON([
                        'ok'    => false,
                        'error' => 'Back image: ' . $backError,
                    ]);
                }
            }

            // Read text from the front image, trying multiple orientations if needed
            $frontText = $this->readIdTextWithRotations($apiKey, $frontFile, $isBirthOrId);
            if ($frontText === null) {
                return $response->setStatusCode(502)->setJSON([
                    'ok'    => false,
                    'error' => $isBirthOrId
                        ? 'The OCR service could not read the ID or birth certificate. Try a clearer photo or a higher-resolution scan.'
                        : 'The OCR service could not read the front image. Try a clearer photo.',
                ]);
            }

            $combinedText = $frontText;
            if ($hasBack) {
                $backText = $this->readIdTextWithRotations($apiKey, $backFile, $isBirthOrId);
                if ($backText !== null) {
                    $combinedText .= "\n" . $backText;
                }
            }

            log_message('debug', 'OCR combined text: ' . substr($combinedText, 0, 500));

            $nameMatch = $fullName !== '' ? $this->matchesName($combinedText, $fullName, $firstName, $middleName, $lastName) : null;
            $dobMatch  = $dob !== '' ? $this->matchesDob($combinedText, $dob) : null;

            // Birth certificates often print the date under labels; require DOB
            // when one was typed so the check stays meaningful for that document.
            $verified = ($nameMatch === true) && ($dobMatch !== false);
            if ($isBirthOrId && $dob !== '') {
                $verified = ($nameMatch === true) && ($dobMatch === true);
            }

            return $response->setJSON([
                'ok'         => true,
                'verified'   => $verified,
                'name_match' => $nameMatch,
                'dob_match'  => $dobMatch,
                'reason'     => $this->explain($verified, $nameMatch, $dobMatch, $fullName, $dob, $isBirthOrId),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'OCR verifyId fatal: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return $response->setStatusCode(500)->setJSON([
                'ok'    => false,
                'error' => 'OCR failed: ' . $e->getMessage(),
            ]);
        }
    }

    private function validateUpload(UploadedFile $file): ?string
    {
        if (! $file->isValid()) {
            return 'the file could not be uploaded (' . $file->getErrorString() . ').';
        }
        if ($file->getSize() > self::MAX_UPLOAD_BYTES) {
            return 'must be 5 MB or smaller.';
        }
        $mime = strtolower((string) $file->getMimeType());
        if (! in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            return 'must be a JPG, PNG, WEBP, or PDF file.';
        }

        return null;
    }

    /**
     * Try to read ID text, rotating the image if initial attempt yields poor results.
     * This handles upside-down and sideways IDs.
     */
    private function readIdTextWithRotations(string $apiKey, UploadedFile $file, bool $optimizeBirthCert = false): ?string
    {
        $path = $file->getTempName();
        if (! is_file($path)) {
            return null;
        }

        $mime = strtolower((string) $file->getMimeType());
        $isPdf = $mime === 'application/pdf';

        // First try: original orientation with OCR's auto-detect
        $originalText = $this->readIdText($apiKey, $file, $optimizeBirthCert);
        $originalLength = strlen(preg_replace('/\s+/', '', $originalText ?? '') ?? '');

        // If we got enough text (name + date typically needs ~20 chars), use it
        if ($originalLength >= 20) {
            return $originalText;
        }

        // For PDFs, we can't rotate easily, so return what we have
        if ($isPdf) {
            return $originalText;
        }

        // Try rotating the image to different orientations
        $rotations = [180, 90, 270]; // 180° (upside down), 90° and 270° (sideways)
        $bestText = $originalText ?? '';
        $bestLength = $originalLength;

        foreach ($rotations as $degrees) {
            $rotatedPath = $this->rotateImage($path, $degrees, $mime);
            if ($rotatedPath === null) {
                continue;
            }

            try {
                // Create a temporary UploadedFile-like object for the rotated image
                $rotatedText = $this->readIdTextFromPath($apiKey, $rotatedPath, $mime, $optimizeBirthCert);
                $rotatedLength = strlen(preg_replace('/\s+/', '', $rotatedText ?? '') ?? '');

                if ($rotatedLength > $bestLength) {
                    $bestText = $rotatedText;
                    $bestLength = $rotatedLength;
                    log_message('debug', "OCR: Better result at {$degrees}° rotation ({$rotatedLength} chars)");
                }

                // If we got good text, stop trying more rotations
                if ($bestLength >= 30) {
                    break;
                }
            } finally {
                @unlink($rotatedPath);
            }
        }

        return $bestText === '' ? null : $bestText;
    }

    /**
     * Rotate an image file and save to a temp file.
     */
    private function rotateImage(string $path, int $degrees, string $mime): ?string
    {
        if (! function_exists('imagecreatefromjpeg')) {
            return null; // GD not available
        }

        try {
            $source = match ($mime) {
                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
                'image/png' => @imagecreatefrompng($path),
                'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
                default => false,
            };

            if ($source === false) {
                return null;
            }

            $rotated = imagerotate($source, -$degrees, 0); // Negative because imagerotate is counter-clockwise
            imagedestroy($source);

            if ($rotated === false) {
                return null;
            }

            $tempPath = sys_get_temp_dir() . '/ocr_rotated_' . uniqid() . '.jpg';

            // Always save as JPEG for consistency
            $saved = imagejpeg($rotated, $tempPath, 95);
            imagedestroy($rotated);

            return $saved ? $tempPath : null;
        } catch (\Throwable $e) {
            log_message('warning', 'Image rotation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Read text from a file path (for rotated images).
     */
    private function readIdTextFromPath(string $apiKey, string $path, string $originalMime, bool $optimizeBirthCert = false): ?string
    {
        $engines = ['2', '1'];
        $best = '';

        foreach ($engines as $engine) {
            $text = $this->callOcrSpaceFromPath($apiKey, $path, $engine, $optimizeBirthCert);
            if ($text === null) {
                continue;
            }
            if (strlen($text) > strlen($best)) {
                $best = $text;
            }
            if (strlen(preg_replace('/\s+/', '', $best) ?? '') >= 24) {
                break;
            }
        }

        return $best === '' ? null : $best;
    }

    private function readIdText(string $apiKey, UploadedFile $file, bool $optimizeBirthCert = false): ?string
    {
        $path = $file->getTempName();
        if (! is_file($path)) {
            return null;
        }

        // Engine 2 first for printed IDs/certificates; fall back to Engine 1
        // when the first pass returns little or no text.
        $engines = ['2', '1'];
        $best = '';

        foreach ($engines as $engine) {
            $text = $this->callOcrSpace($apiKey, $file, $path, $engine, $optimizeBirthCert);
            if ($text === null) {
                continue;
            }
            if (strlen($text) > strlen($best)) {
                $best = $text;
            }
            // Enough text to match a name and date — stop early.
            if (strlen(preg_replace('/\s+/', '', $best) ?? '') >= 24) {
                break;
            }
        }

        return $best === '' ? null : $best;
    }

    private function callOcrSpaceFromPath(
        string $apiKey,
        string $path,
        string $engine,
        bool $optimizeBirthCert
    ): ?string {
        $post = [
            'apikey'            => $apiKey,
            'language'          => 'eng',
            'isOverlayRequired' => 'false',
            'detectOrientation' => 'true',
            'scale'             => 'true',
            'isTable'           => $optimizeBirthCert ? 'true' : 'false',
            'OCREngine'         => $engine,
            'filetype'          => 'JPG',
            'file'              => new \CURLFile($path, 'image/jpeg', 'rotated.jpg'),
        ];

        $ch = curl_init(self::OCR_ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $raw   = curl_exec($ch);
        $errNo = curl_errno($ch);
        curl_close($ch);

        if ($errNo !== 0 || ! is_string($raw) || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        if (! is_array($data) || ! empty($data['IsErroredOnProcessing'])) {
            return null;
        }

        $chunks = [];
        foreach ((array) ($data['ParsedResults'] ?? []) as $result) {
            $text = trim((string) ($result['ParsedText'] ?? ''));
            if ($text !== '') {
                $chunks[] = $text;
            }
        }

        return $chunks === [] ? '' : implode("\n", $chunks);
    }

    private function callOcrSpace(
        string $apiKey,
        UploadedFile $file,
        string $path,
        string $engine,
        bool $optimizeBirthCert
    ): ?string {
        $mime     = (string) $file->getMimeType() ?: 'application/octet-stream';
        $filename = $file->getClientName() ?: ('upload.' . ($file->getExtension() ?: 'jpg'));
        $isPdf    = strtolower($mime) === 'application/pdf';
        $ext      = strtoupper($file->getExtension() ?: 'JPG');

        $post = [
            'apikey'            => $apiKey,
            'language'          => 'eng',
            'isOverlayRequired' => 'false',
            'detectOrientation' => 'true',
            'scale'             => 'true',
            'isTable'           => $optimizeBirthCert ? 'true' : 'false',
            'OCREngine'         => $engine,
            'filetype'          => $isPdf ? 'PDF' : $ext,
            'file'              => new \CURLFile($path, $mime, $filename),
        ];

        $ch = curl_init(self::OCR_ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $raw   = curl_exec($ch);
        $errNo = curl_errno($ch);
        curl_close($ch);

        if ($errNo !== 0 || ! is_string($raw) || $raw === '') {
            log_message('warning', 'OCR request failed (engine ' . $engine . ', curl errno ' . $errNo . ').');

            return null;
        }

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            log_message('warning', 'OCR response was not JSON: ' . substr($raw, 0, 200));

            return null;
        }
        if (! empty($data['IsErroredOnProcessing'])) {
            $err = is_array($data['ErrorMessage'] ?? null)
                ? implode(' ', $data['ErrorMessage'])
                : (string) ($data['ErrorMessage'] ?? '');
            log_message('warning', 'OCR service returned an error (engine ' . $engine . '): ' . $err);

            return null;
        }

        $chunks = [];
        foreach ((array) ($data['ParsedResults'] ?? []) as $result) {
            $text = trim((string) ($result['ParsedText'] ?? ''));
            if ($text !== '') {
                $chunks[] = $text;
            }
        }

        return $chunks === [] ? '' : implode("\n", $chunks);
    }

    /**
     * Match name with fuzzy matching and OCR error correction.
     * First and last names must appear (exact or fuzzy match).
     * Middle name may appear in full, as initial, or be skipped on IDs.
     */
    private function matchesName(string $text, string $fullName, string $firstName = '', string $middleName = '', string $lastName = ''): bool
    {
        $normalizedText = $this->normalize($text);
        // Apply OCR error corrections to the text
        $correctedText = $this->correctOcrErrors($normalizedText);
        // Compact form catches OCR that drops spaces between name parts.
        $compactText    = preg_replace('/\s+/', '', $normalizedText) ?? '';
        $compactCorrected = preg_replace('/\s+/', '', $correctedText) ?? '';

        $firstTokens    = $this->nameTokens($firstName);
        $middleTokens   = $this->nameTokens($middleName);
        $lastTokens     = $this->nameTokens($lastName);

        if ($firstTokens === [] && $lastTokens === []) {
            $tokens = $this->nameTokens($fullName);
            if ($tokens === []) {
                return false;
            }
            $firstTokens = [array_shift($tokens)];
            if ($tokens !== []) {
                $lastTokens = [array_pop($tokens)];
                $middleTokens = $tokens;
            }
        }

        // Check first and last name tokens (required)
        foreach (array_merge($firstTokens, $lastTokens) as $token) {
            if (strlen($token) <= 1) {
                continue;
            }

            // Try exact match first
            if ($this->wordPresent($normalizedText, $token) || str_contains($compactText, $token)) {
                continue;
            }

            // Try with OCR corrections
            if ($this->wordPresent($correctedText, $token) || str_contains($compactCorrected, $token)) {
                continue;
            }

            // Try fuzzy matching
            if ($this->fuzzyWordPresent($normalizedText, $token)) {
                continue;
            }

            // Try with common OCR variations of the token itself
            $tokenVariations = $this->generateOcrVariations($token);
            $found = false;
            foreach ($tokenVariations as $variation) {
                if ($this->wordPresent($normalizedText, $variation) || str_contains($compactText, $variation)) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                continue;
            }

            return false;
        }

        // Check middle name tokens (optional - may appear as initial or not at all)
        foreach ($middleTokens as $token) {
            if ($token === '') {
                continue;
            }

            // Full match
            if ($this->wordPresent($normalizedText, $token) || str_contains($compactText, $token)) {
                continue;
            }

            // With OCR corrections
            if ($this->wordPresent($correctedText, $token) || str_contains($compactCorrected, $token)) {
                continue;
            }

            // Initial only (e.g., "S" for "SANTOS")
            if (strlen($token) > 1 && $this->wordPresent($normalizedText, substr($token, 0, 1))) {
                continue;
            }

            // Fuzzy match
            if ($this->fuzzyWordPresent($normalizedText, $token)) {
                continue;
            }

            // Middle name often omitted on IDs - don't fail if not found
            // Just log it for debugging
            log_message('debug', 'OCR: Middle name token "' . $token . '" not found in text');
        }

        return $firstTokens !== [] || $middleTokens !== [] || $lastTokens !== [];
    }

    /**
     * Correct common OCR misreads in the scanned text.
     */
    private function correctOcrErrors(string $text): string
    {
        // Common OCR character confusions
        $corrections = [
            '0' => 'O',
            '1' => 'I',
            '5' => 'S',
            '8' => 'B',
            '6' => 'G',
            '@' => 'A',
            '4' => 'A',
        ];

        // Only apply corrections in word contexts (not in numbers)
        $words = preg_split('/\s+/', $text) ?: [];
        $corrected = [];

        foreach ($words as $word) {
            // If word looks like a date or number, keep it as is
            if (preg_match('/^\d+$/', $word) || preg_match('/^\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}$/', $word)) {
                $corrected[] = $word;
                continue;
            }

            // Apply corrections to mixed alphanumeric words
            $fixed = $word;
            foreach ($corrections as $digit => $letter) {
                // Only replace digits that appear within letter sequences
                $fixed = preg_replace('/(?<=[A-Z])' . $digit . '(?=[A-Z])/', $letter, $fixed) ?? $fixed;
                $fixed = preg_replace('/(?<=^)' . $digit . '(?=[A-Z]{2,})/', $letter, $fixed) ?? $fixed;
                $fixed = preg_replace('/(?<=[A-Z]{2,})' . $digit . '$/', $letter, $fixed) ?? $fixed;
            }
            $corrected[] = $fixed;
        }

        return implode(' ', $corrected);
    }

    /**
     * Generate possible OCR misread variations of a name token.
     */
    private function generateOcrVariations(string $token): array
    {
        $variations = [$token];

        // Common letter-to-digit confusions
        $swaps = [
            'O' => '0',
            'I' => '1',
            'L' => '1',
            'S' => '5',
            'B' => '8',
            'G' => '6',
            'A' => '4',
            'Z' => '2',
            'E' => '3',
        ];

        // Generate variations by swapping each character
        for ($i = 0; $i < strlen($token); $i++) {
            $char = $token[$i];
            if (isset($swaps[$char])) {
                $variant = substr($token, 0, $i) . $swaps[$char] . substr($token, $i + 1);
                $variations[] = $variant;
            }
            // Reverse mapping too
            $reverseKey = array_search($char, $swaps, true);
            if ($reverseKey !== false) {
                $variant = substr($token, 0, $i) . $reverseKey . substr($token, $i + 1);
                $variations[] = $variant;
            }
        }

        return array_unique($variations);
    }

    /**
     * Check if a word is present using fuzzy matching (Levenshtein distance).
     */
    private function fuzzyWordPresent(string $text, string $token): bool
    {
        if (strlen($token) < 3) {
            return false; // Too short for fuzzy matching
        }

        $words = preg_split('/\s+/', $text) ?: [];
        $maxDistance = max(1, (int) floor(strlen($token) * 0.25)); // Allow ~25% error

        foreach ($words as $word) {
            if (strlen($word) < 2) {
                continue;
            }

            // Check Levenshtein distance
            $distance = levenshtein($token, $word);
            if ($distance <= $maxDistance) {
                return true;
            }

            // Also check if token is a substring (handles cases where OCR added extra chars)
            if (strlen($word) > strlen($token) && str_contains($word, $token)) {
                return true;
            }

            // Check similar_text percentage
            similar_text($token, $word, $percent);
            if ($percent >= self::NAME_SIMILARITY_THRESHOLD) {
                return true;
            }
        }

        return false;
    }

    /**
     * Accept the census birthdate when the ID prints it as numbers or as a
     * month name, in either day/month or month/day order.
     * Also handles common date format variations on Philippine IDs.
     */
    private function matchesDob(string $text, string $dob): bool
    {
        $stamp = strtotime($dob);
        if ($stamp === false) {
            return false;
        }

        $month = (int) date('n', $stamp);
        $day   = (int) date('j', $stamp);
        $year  = (int) date('Y', $stamp);
        $flat  = $this->flattenDateText($text);

        // Also try with OCR corrections applied
        $correctedFlat = $this->flattenDateText($this->correctOcrErrorsForDates($text));

        return $this->numericDatePresent($flat, $month, $day, $year)
            || $this->numericDatePresent($correctedFlat, $month, $day, $year)
            || $this->wordDatePresent($flat, $month, $day, $year)
            || $this->wordDatePresent($correctedFlat, $month, $day, $year)
            || $this->philippineDateFormats($flat, $month, $day, $year)
            || $this->philippineDateFormats($correctedFlat, $month, $day, $year);
    }

    /**
     * Correct OCR errors specifically in date contexts.
     */
    private function correctOcrErrorsForDates(string $text): string
    {
        // In date contexts, O is often misread as 0 (correct), but we need
        // to handle month names where 0 should be O
        $text = preg_replace('/\b0ct(?:ober)?\b/i', 'Oct', $text) ?? $text;
        $text = preg_replace('/\bN0v(?:ember)?\b/i', 'Nov', $text) ?? $text;
        $text = preg_replace('/\bSept\b/i', 'Sept', $text) ?? $text;

        return $text;
    }

    /**
     * Check for Philippine-specific date formats commonly found on IDs.
     */
    private function philippineDateFormats(string $text, int $month, int $day, int $year): bool
    {
        $mm = sprintf('%02d', $month);
        $dd = sprintf('%02d', $day);
        $shortYear = sprintf('%02d', $year % 100);

        // Philippine ID formats: MM-DD-YYYY, DD-MM-YYYY with various separators
        $separators = ['\\s*[-\\/.]\\s*', '\\s+'];

        foreach ($separators as $sep) {
            $patterns = [
                // MM-DD-YYYY
                '(?<!\d)' . $mm . $sep . $dd . $sep . $year . '(?!\d)',
                // DD-MM-YYYY
                '(?<!\d)' . $dd . $sep . $mm . $sep . $year . '(?!\d)',
                // YYYY-MM-DD (ISO format)
                '(?<!\d)' . $year . $sep . $mm . $sep . $dd . '(?!\d)',
                // With short year
                '(?<!\d)' . $mm . $sep . $dd . $sep . $shortYear . '(?!\d)',
                '(?<!\d)' . $dd . $sep . $mm . $sep . $shortYear . '(?!\d)',
                // Without leading zeros
                '(?<!\d)' . $month . $sep . $day . $sep . $year . '(?!\d)',
                '(?<!\d)' . $day . $sep . $month . $sep . $year . '(?!\d)',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match('/' . $pattern . '/', $text)) {
                    return true;
                }
            }
        }

        // Check for date written as continuous digits (common OCR artifact)
        // MMDDYYYY or DDMMYYYY
        $continuous = [
            '(?<!\d)' . $mm . $dd . $year . '(?!\d)',
            '(?<!\d)' . $dd . $mm . $year . '(?!\d)',
            '(?<!\d)' . $year . $mm . $dd . '(?!\d)',
        ];

        foreach ($continuous as $pattern) {
            if (preg_match('/' . $pattern . '/', $text)) {
                return true;
            }
        }

        return false;
    }

    private function flattenDateText(string $text): string
    {
        $text = strtolower($text);
        // Correct a scanned O or I only when it sits inside a month word.
        // A year glued to the month, such as APR1990, must keep its digits.
        $text = preg_replace('/(?<=[a-z])0(?=[a-z])/', 'o', $text) ?? $text;
        $text = preg_replace('/(?<=[a-z])1(?=[a-z])/', 'i', $text) ?? $text;
        $text = preg_replace('/(?<![a-z0-9])0(?=[a-z]{2,})/', 'o', $text) ?? $text;
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/', ' ', $text) ?? '');
    }

    private function numericDatePresent(string $text, int $month, int $day, int $year): bool
    {
        $monthPart = '0*' . $month;
        $dayPart   = '0*' . $day;
        $yearPart  = (string) $year;
        $shortYear = sprintf('%02d', $year % 100);
        // Zero-padded forms common on PSA birth certificates / ID cards.
        $mm = sprintf('%02d', $month);
        $dd = sprintf('%02d', $day);

        foreach ([
            '(?<!\d)' . $monthPart . '\s+' . $dayPart . '\s+' . $yearPart . '(?!\d)',
            '(?<!\d)' . $dayPart . '\s+' . $monthPart . '\s+' . $yearPart . '(?!\d)',
            '(?<!\d)' . $yearPart . '\s+' . $monthPart . '\s+' . $dayPart . '(?!\d)',
            '(?<!\d)' . $monthPart . '\s+' . $dayPart . '\s+' . $shortYear . '(?!\d)',
            '(?<!\d)' . $dayPart . '\s+' . $monthPart . '\s+' . $shortYear . '(?!\d)',
            // Compact / glued OCR output (no spaces between parts).
            '(?<!\d)' . $mm . $dd . $yearPart . '(?!\d)',
            '(?<!\d)' . $dd . $mm . $yearPart . '(?!\d)',
            '(?<!\d)' . $yearPart . $mm . $dd . '(?!\d)',
            // Additional patterns with single digits
            '(?<!\d)' . $month . '\s+' . $day . '\s+' . $yearPart . '(?!\d)',
            '(?<!\d)' . $day . '\s+' . $month . '\s+' . $yearPart . '(?!\d)',
        ] as $pattern) {
            if (preg_match('/' . $pattern . '/', $text)) {
                return true;
            }
        }

        return false;
    }

    private function wordDatePresent(string $text, int $month, int $day, int $year): bool
    {
        $names = [
            1  => ['january', 'enero', 'jan', 'ene'],
            2  => ['february', 'pebrero', 'feb', 'peb'],
            3  => ['march', 'marso', 'mar'],
            4  => ['april', 'abril', 'apr', 'abr'],
            5  => ['mayo', 'may'],
            6  => ['hunyo', 'june', 'jun'],
            7  => ['hulyo', 'july', 'jul'],
            8  => ['agosto', 'august', 'aug'],
            9  => ['setyembre', 'september', 'sept', 'sep', 'set'],
            10 => ['oktubre', 'october', 'oct', 'okt'],
            11 => ['nobyembre', 'november', 'nov', 'nob'],
            12 => ['disyembre', 'december', 'dec', 'dis'],
        ];
        $monthPart = '(?<![a-z])(?:' . implode('|', $names[$month] ?? []) . ')(?![a-z])';
        $dayPart   = '(?<!\d)0*' . $day . '(?:st|nd|rd|th)?';
        $yearPart  = '(?:' . $year . '|(?<!\d)' . sprintf('%02d', $year % 100) . '(?!\d))';
        $gap       = '[\s,]*';

        foreach ([
            $monthPart . $gap . '(?:of\s+)?' . $dayPart . $gap . '(?:of\s+)?' . $yearPart,
            $dayPart . $gap . '(?:of\s+)?' . $monthPart . $gap . '(?:of\s+)?' . $yearPart,
            '(?<!\d)' . $year . '(?!\d)' . $gap . $monthPart . $gap . $dayPart,
            // Day without leading zero
            $monthPart . $gap . $day . $gap . $yearPart,
        ] as $pattern) {
            if (preg_match('/' . $pattern . '/', $text)) {
                return true;
            }
        }

        return false;
    }

    private function nameTokens(string $value): array
    {
        $tokens = preg_split('/\s+/', $this->normalize($value)) ?: [];

        return array_values(array_filter($tokens, static fn ($token) => $token !== ''));
    }

    private function wordPresent(string $text, string $token): bool
    {
        return (bool) preg_match('/\b' . preg_quote($token, '/') . '\b/u', $text);
    }

    private function normalize(string $value): string
    {
        $value = strtoupper($value);
        // Keep Ñ for Filipino names but normalize other special characters
        $value = str_replace(['Ñ', 'ñ'], 'N', $value);
        $value = preg_replace('/[^A-Z0-9\s]/u', ' ', $value) ?? '';
        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function explain(
        bool $verified,
        ?bool $nameMatch,
        ?bool $dobMatch,
        string $name,
        string $dob,
        bool $isBirthOrId = false
    ): string {
        $doc = $isBirthOrId ? 'ID or birth certificate' : 'ID';

        if ($verified) {
            $bits = ['name matches the uploaded ' . $doc];
            if ($dobMatch === true) {
                $bits[] = 'date of birth matches';
            }
            return 'Verified: ' . implode(' and ', $bits) . '.';
        }

        if ($name === '') {
            return 'Type the full name in personal information first, then re-run the check.';
        }
        if ($nameMatch === false && $dobMatch === false && $dob !== '') {
            return 'The name and date of birth on the ' . $doc . ' do not match what was typed. Check for typos or try a clearer photo.';
        }
        if ($nameMatch === false) {
            return 'The name on the ' . $doc . ' does not match the personal information. Check spelling and try again.';
        }
        if ($dobMatch === false) {
            return 'The date of birth on the ' . $doc . ' does not match the personal information. Verify the date format.';
        }

        return $isBirthOrId
            ? 'Could not confirm a match. Try a clearer photo or scan of the ID or birth certificate.'
            : 'Could not confirm a match. Try a clearer photo of the front and back.';
    }
}
