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
            $fullName  = trim((string) $this->request->getPost('full_name'));
            $dob       = trim((string) $this->request->getPost('date_of_birth'));

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

            $nameMatch = $fullName !== '' ? $this->matchesName($combinedText, $fullName) : null;
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
     * Case-insensitive check that every alphabetic word in the typed name
     * appears in the OCR text. Middle initials and one-letter tokens are
     * treated as optional so "JUAN S DELA CRUZ" still matches "JUAN DELA CRUZ".
     */
    private function matchesName(string $text, string $fullName): bool
    {
        $normalizedText = $this->normalize($text);
        $tokens         = array_values(array_filter(preg_split('/\s+/', $this->normalize($fullName)) ?: [], static fn($t) => $t !== ''));
        if ($tokens === []) {
            return false;
        }

        $required = array_filter($tokens, static fn($t) => strlen($t) > 1);
        $required = $required === [] ? $tokens : $required;

        foreach ($required as $token) {
            if (! preg_match('/\b' . preg_quote($token, '/') . '\b/u', $normalizedText)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Accepts the DOB in a few common formats the ID might use.
     * Returns true when any variant appears in the OCR text.
     */
    private function matchesDob(string $text, string $dob): bool
    {
        $stamp = strtotime($dob);
        if ($stamp === false) {
            return false;
        }

        $needles = [
            date('Y-m-d', $stamp),
            date('Y/m/d', $stamp),
            date('m/d/Y', $stamp),
            date('m-d-Y', $stamp),
            date('d/m/Y', $stamp),
            date('d-m-Y', $stamp),
            date('F j, Y', $stamp),
            date('F j Y', $stamp),
            date('j F Y', $stamp),
            date('M j, Y', $stamp),
            date('M j Y', $stamp),
            date('d M Y', $stamp),
        ];

        $haystack = strtolower(preg_replace('/\s+/', ' ', $text));
        foreach ($needles as $needle) {
            $needle = strtolower(preg_replace('/\s+/', ' ', $needle));
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
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
