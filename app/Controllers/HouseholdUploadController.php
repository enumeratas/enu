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

        if (preg_match('#(?:^|/)(ids|ownership|avatars|sk_profiles|sk_programs|blotter_evidence|barangay_activities)/(.+)$#i', $value, $matches)) {
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
            return $this->response->setStatusCode(404);
        }

        $localPaths = [FCPATH . $path, WRITEPATH . $path];
        foreach ($localPaths as $localPath) {
            if (is_file($localPath)) {
                $mimeType = mime_content_type($localPath) ?: 'application/octet-stream';
                return $this->response
                    ->setContentType($mimeType)
                    ->setBody((string) file_get_contents($localPath));
            }
        }

        $storage = new HouseholdUploadStorage();
        $object = $storage->download($path);
        if ($object !== null) {
            return $this->response
                ->setContentType($object['mime'])
                ->setBody($object['body']);
        }

        $signedUrl = $storage->signedUrl($path);
        return $signedUrl === null
            ? $this->response->setStatusCode(404)
            : redirect()->to($signedUrl);
    }
}
