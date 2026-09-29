<?php

namespace App\Libraries;

use Google\Cloud\Storage\StorageClient;

class HouseholdUploadStorage
{
    public function store(string $localPath, string $objectPath): bool
    {
        $bucket = $this->bucket();
        if ($bucket === null || ! is_file($localPath)) {
            return false;
        }

        $bucket->upload(fopen($localPath, 'r'), [
            'name' => ltrim($objectPath, '/'),
            'metadata' => ['cacheControl' => 'private, max-age=3600'],
        ]);

        return true;
    }

    public function signedUrl(string $objectPath): ?string
    {
        $bucket = $this->bucket();
        if ($bucket === null) {
            return null;
        }

        try {
            $object = $bucket->object(ltrim($objectPath, '/'));
            if (! $object->exists()) {
                log_message('warning', 'Household upload object not found: ' . ltrim($objectPath, '/'));
                return null;
            }

            return $object->signedUrl(
                new \DateTimeImmutable('+15 minutes')
            );
        } catch (\Throwable $e) {
            log_message('error', 'Unable to sign household upload URL: ' . $e->getMessage());
            return null;
        }
    }

    /** Read a private object directly when signed URL generation is unavailable. */
    public function download(string $objectPath): ?array
    {
        $bucket = $this->bucket();
        if ($bucket === null) {
            return null;
        }

        try {
            $object = $bucket->object(ltrim($objectPath, '/'));
            if (! $object->exists()) {
                return null;
            }

            $info = $object->info();
            return [
                'body' => $object->downloadAsString(),
                'mime' => $info['contentType'] ?? 'application/octet-stream',
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Unable to download household upload: ' . $e->getMessage());
            return null;
        }
    }

    private function bucket(): ?\Google\Cloud\Storage\Bucket
    {
        $bucketName = trim((string) (getenv('GCS_UPLOAD_BUCKET') ?: ''));
        if ($bucketName === '') {
            $projectId = trim((string) (getenv('GOOGLE_CLOUD_PROJECT') ?: getenv('GCLOUD_PROJECT')));
            $bucketName = $projectId !== '' ? $projectId . '.appspot.com' : '';
        }
        if ($bucketName === '') {
            return null;
        }

        try {
            return (new StorageClient())->bucket($bucketName);
        } catch (\Throwable $e) {
            log_message('error', 'Unable to connect to household upload bucket: ' . $e->getMessage());
            return null;
        }
    }
}
