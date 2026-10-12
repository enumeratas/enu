<?php

namespace App\Controllers;

use App\Libraries\HouseholdUploadStorage;

class HouseholdUploadController extends BaseController
{
    public static function normalizeUploadPath(?string $path): string
    {
        $value = trim((string) $path);
        if ($value === '') {
            return '';
        }

        $value = str_replace('\\', '/', $value);
        $value = rawurldecode($value);
        $value = preg_replace('#^https?://[^/]+#i', '', $value);
        $value = ltrim($value, '/');

        if (preg_match('#(?:^|/)uploads/(.+)$#i', $value, $matches)) {
            return 'uploads/' . ltrim($matches[1], '/');
        }

        if (preg_match('#(?:^|/)(public/)?uploads/(.+)$#i', $value, $matches)) {
            return 'uploads/' . ltrim($matches[2], '/');
        }

        if (preg_match('#(?:^|/)(ids|ownership|avatars|sk_profiles|sk_programs|blotter_evidence|barangay_activities|census_proof)/(.+)$#i', $value, $matches)) {
            return 'uploads/' . $matches[1] . '/' . ltrim($matches[2], '/');
        }

        if (str_contains($value, 'uploads/')) {
            $pos = strrpos(strtolower($value), 'uploads/');
            if ($pos !== false) {
                return 'uploads/' . ltrim(substr($value, $pos + strlen('uploads/')), '/');
            }
        }

        return 'uploads/' . ltrim($value, '/');
    }

    public function showFile(string $path = '')
    {
        return $this->show(self::normalizeUploadPath($path));
    }

    public function show(string $path = '')
    {
        $path = self::normalizeUploadPath($path);
        if ($path === '' || str_contains($path, '..') || ! str_starts_with($path, 'uploads/')) {
            return $this->fileMissing();
        }

        $filename = basename($path) ?: 'document';
        $localPaths = [FCPATH . $path, WRITEPATH . $path];
        foreach ($localPaths as $localPath) {
            if (is_file($localPath)) {
                $detected = mime_content_type($localPath);
                $mimeType = (is_string($detected) && $detected !== '' && $detected !== 'application/octet-stream')
                    ? $detected
                    : $this->mimeFromName($filename);

                return $this->inlineFile((string) file_get_contents($localPath), $mimeType, $filename);
            }
        }

        $storage = new HouseholdUploadStorage();
        $object = $storage->download($path);
        if ($object !== null) {
            $mimeType = (string) ($object['mime'] ?? '');
            if ($mimeType === '' || $mimeType === 'application/octet-stream') {
                $mimeType = $this->mimeFromName($filename);
            }

            return $this->inlineFile((string) $object['body'], $mimeType, $filename);
        }

        $signedUrl = $storage->signedUrl($path);
        if ($signedUrl !== null) {
            return redirect()->to($signedUrl);
        }

        return $this->fileMissing();
    }

    private function inlineFile(string $body, string $mimeType, string $filename)
    {
        $safeName = str_replace(['"', "\r", "\n"], '', $filename);

        return $this->response
            ->setContentType($mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . $safeName . '"')
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody($body);
    }

    private function mimeFromName(string $filename): string
    {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'pdf'         => 'application/pdf',
            default       => 'application/octet-stream',
        };
    }

    private function fileMissing()
    {
        return $this->response
            ->setStatusCode(404)
            ->setContentType('text/html')
            ->setBody('<!doctype html><html><body style="font-family:Arial,sans-serif;padding:32px;">That file is not available.</body></html>');
    }
}
