<?php

namespace App\Libraries;

use Google\Cloud\Storage\StorageClient;

class HouseholdUploadStorage
{
    /** @var list<array{0:string,1:string}> */
    private static array $queued = [];

    private static bool $flushRegistered = false;

    /**
     * Keep the local file immediately so census save is not blocked by GCS.
     * Remote upload is queued and flushed after the HTTP response.
     */
    public function store(string $localPath, string $objectPath): bool
    {
        if (! is_file($localPath)) {
            return false;
        }

        $this->queueRemote($localPath, $objectPath);

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

    public static function flushQueued(): void
    {
        $items = self::$queued;
        self::$queued = [];
        if ($items === []) {
            return;
        }

        try {
            $storage = new self();
            $bucket = $storage->bucket();
            if ($bucket === null) {
                return;
            }

            foreach ($items as [$localPath, $objectPath]) {
                if (! is_file($localPath)) {
                    continue;
                }
                try {
                    $handle = fopen($localPath, 'r');
                    if ($handle === false) {
                        continue;
                    }
                    $bucket->upload($handle, [
                        'name' => ltrim($objectPath, '/'),
                        'metadata' => ['cacheControl' => 'private, max-age=3600'],
                    ]);
                } catch (\Throwable $e) {
                    log_message('error', 'Deferred household upload failed: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Household upload flush failed: ' . $e->getMessage());
        }
    }

    private function queueRemote(string $localPath, string $objectPath): void
    {
        if ($this->bucket() === null) {
            return;
        }

        self::$queued[] = [$localPath, $objectPath];
        if (! self::$flushRegistered) {
            self::$flushRegistered = true;
            register_shutdown_function([self::class, 'flushQueued']);
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
