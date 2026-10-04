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

            if (! $frontFile || $frontFile->getError() === UPLOAD_ERR_NO_FILE) {
                return $response->setStatusCode(400)->setJSON([
                    'ok'    => false,
                    'error' => 'Please pick the front photo of the ID first.',
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

            $frontText = $this->readIdText($apiKey, $frontFile);
            if ($frontText === null) {
                return $response->setStatusCode(502)->setJSON([
                    'ok'    => false,
                    'error' => 'The OCR service could not read the front image. Try a clearer photo.',
                ]);
            }

            $combinedText = $frontText;
            if ($hasBack) {
                $backText = $this->readIdText($apiKey, $backFile);
                if ($backText !== null) {
                    $combinedText .= "\n" . $backText;
                }
            }

            $nameMatch = $fullName !== '' ? $this->matchesName($combinedText, $fullName, $firstName, $middleName, $lastName) : null;
            $dobMatch  = $dob !== '' ? $this->matchesDob($combinedText, $dob) : null;

            $verified = ($nameMatch === true) && ($dobMatch !== false);

            return $response->setJSON([
                'ok'         => true,
                'verified'   => $verified,
                'name_match' => $nameMatch,
                'dob_match'  => $dobMatch,
                'reason'     => $this->explain($verified, $nameMatch, $dobMatch, $fullName, $dob),
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

    private function readIdText(string $apiKey, UploadedFile $file): ?string
    {
        $path = $file->getTempName();
        if (! is_file($path)) {
            return null;
        }

        $mime      = (string) $file->getMimeType() ?: 'application/octet-stream';
        $filename  = $file->getClientName() ?: ('upload.' . ($file->getExtension() ?: 'jpg'));
        $isPdf     = strtolower($mime) === 'application/pdf';

        $post = [
            'apikey'                 => $apiKey,
            'language'               => 'eng',
            'isOverlayRequired'      => 'false',
            'detectOrientation'      => 'true',
            'scale'                  => 'true',
            'OCREngine'              => '2',
            'filetype'               => $isPdf ? 'PDF' : strtoupper($file->getExtension() ?: 'JPG'),
            'file'                   => new \CURLFile($path, $mime, $filename),
        ];

        $ch = curl_init(self::OCR_ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $raw   = curl_exec($ch);
        $errNo = curl_errno($ch);
        curl_close($ch);

        if ($errNo !== 0 || ! is_string($raw) || $raw === '') {
            log_message('warning', 'OCR request failed (curl errno ' . $errNo . ' error=' . curl_strerror($errNo) . ').');
            return null;
        }

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            log_message('warning', 'OCR response was not JSON: ' . substr($raw, 0, 200));
            return null;
        }
        if (! empty($data['IsErroredOnProcessing'])) {
            $err = is_array($data['ErrorMessage'] ?? null) ? implode(' ', $data['ErrorMessage']) : (string) ($data['ErrorMessage'] ?? '');
            log_message('warning', 'OCR service returned an error: ' . $err);
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
     * First and last names must appear in full. A middle name may appear
     * in full or as its initial, so an ID printed "JUAN S DELA CRUZ" matches
     * a census middle name of "SANTOS".
     */
    private function matchesName(string $text, string $fullName, string $firstName = '', string $middleName = '', string $lastName = ''): bool
    {
        $normalizedText = $this->normalize($text);
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

        foreach (array_merge($firstTokens, $lastTokens) as $token) {
            if (strlen($token) > 1 && ! $this->wordPresent($normalizedText, $token)) {
                return false;
            }
        }

        foreach ($middleTokens as $token) {
            if ($token === '') {
                continue;
            }
            if ($this->wordPresent($normalizedText, $token)) {
                continue;
            }
            if (strlen($token) > 1 && $this->wordPresent($normalizedText, substr($token, 0, 1))) {
                continue;
            }

            return false;
        }

        return $firstTokens !== [] || $middleTokens !== [] || $lastTokens !== [];
    }

    /**
     * Accept the census birthdate when the ID prints it as numbers or as a
     * month name, in either day/month or month/day order.
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

        return $this->numericDatePresent($flat, $month, $day, $year)
            || $this->wordDatePresent($flat, $month, $day, $year);
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

        foreach ([
            '(?<!\d)' . $monthPart . '\s+' . $dayPart . '\s+' . $yearPart . '(?!\d)',
            '(?<!\d)' . $dayPart . '\s+' . $monthPart . '\s+' . $yearPart . '(?!\d)',
            '(?<!\d)' . $yearPart . '\s+' . $monthPart . '\s+' . $dayPart . '(?!\d)',
            '(?<!\d)' . $monthPart . '\s+' . $dayPart . '\s+' . $shortYear . '(?!\d)',
            '(?<!\d)' . $dayPart . '\s+' . $monthPart . '\s+' . $shortYear . '(?!\d)',
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
            1  => ['january', 'enero', 'jan'],
            2  => ['february', 'pebrero', 'feb'],
            3  => ['march', 'marso', 'mar'],
            4  => ['april', 'abril', 'apr'],
            5  => ['mayo', 'may'],
            6  => ['hunyo', 'june', 'jun'],
            7  => ['hulyo', 'july', 'jul'],
            8  => ['agosto', 'august', 'aug'],
            9  => ['setyembre', 'september', 'sept', 'sep'],
            10 => ['oktubre', 'october', 'oct'],
            11 => ['nobyembre', 'november', 'nov'],
            12 => ['disyembre', 'december', 'dec'],
        ];
        $monthPart = '(?<![a-z])(?:' . implode('|', $names[$month] ?? []) . ')(?![a-z])';
        $dayPart   = '(?<!\d)0*' . $day . '(?:st|nd|rd|th)?';
        $yearPart  = '(?:' . $year . '|(?<!\d)' . sprintf('%02d', $year % 100) . '(?!\d))';
        $gap       = '\s*';

        foreach ([
            $monthPart . $gap . '(?:of\s+)?' . $dayPart . $gap . '(?:of\s+)?' . $yearPart,
            $dayPart . $gap . '(?:of\s+)?' . $monthPart . $gap . '(?:of\s+)?' . $yearPart,
            '(?<!\d)' . $year . '(?!\d)' . $gap . $monthPart . $gap . $dayPart,
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
        $value = preg_replace('/[^A-Z0-9\s]/u', ' ', $value) ?? '';
        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function explain(bool $verified, ?bool $nameMatch, ?bool $dobMatch, string $name, string $dob): string
    {
        if ($verified) {
            $bits = ['name matches the uploaded ID'];
            if ($dobMatch === true) {
                $bits[] = 'date of birth matches';
            }
            return 'Verified: ' . implode(' and ', $bits) . '.';
        }

        if ($name === '') {
            return 'Type the full name in personal information first, then re-run the check.';
        }
        if ($nameMatch === false && $dobMatch === false && $dob !== '') {
            return 'The name and date of birth on the ID do not match what was typed.';
        }
        if ($nameMatch === false) {
            return 'The name on the ID does not match the personal information.';
        }
        if ($dobMatch === false) {
            return 'The date of birth on the ID does not match the personal information.';
        }

        return 'Could not confirm a match. Try a clearer photo of the front and back.';
    }
}
