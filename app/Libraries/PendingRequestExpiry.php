<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Closes secretary queues that were left pending past their processing window.
 * Clearance and concern requests use 30 days. Census update links use the deadline already stored on the authorization.
 */
class PendingRequestExpiry
{
    public const CLEARANCE_DAYS = 30;

    public static function sweep(): void
    {
        try {
            $db = \Config\Database::connect();
        } catch (\Throwable $e) {
            log_message('error', 'Pending request expiry could not open the database: ' . $e->getMessage());

            return;
        }

        if (! $db instanceof BaseConnection) {
            return;
        }

        try {
            self::expireClearances($db);
        } catch (\Throwable $e) {
            log_message('error', 'Clearance expiry failed: ' . $e->getMessage());
        }

        try {
            self::expireCensusAuthorizations($db);
        } catch (\Throwable $e) {
            log_message('error', 'Census authorization expiry failed: ' . $e->getMessage());
        }

        try {
            self::expireConcerns($db);
        } catch (\Throwable $e) {
            log_message('error', 'Concern expiry failed: ' . $e->getMessage());
        }
    }

    private static function expireClearances(BaseConnection $db): void
    {
        if (! $db->tableExists('clearance_requests')) {
            return;
        }

        $cutoff = date('Y-m-d H:i:s', time() - (self::CLEARANCE_DAYS * 86400));
        $rows = $db->table('clearance_requests cr')
            ->select('cr.id, cr.user_id, cr.document_type, u.role')
            ->join('users u', 'u.id = cr.user_id', 'left')
            ->where('cr.status', 'pending')
            ->where('cr.created_at <', $cutoff)
            ->get()
            ->getResultArray();

        if ($rows === []) {
            return;
        }

        $ids = array_map(static fn(array $row): int => (int) $row['id'], $rows);
        $now = date('Y-m-d H:i:s');
        $message = 'This request was invalidated because it was not processed within ' . self::CLEARANCE_DAYS . ' days.';
        $db->table('clearance_requests')
            ->whereIn('id', $ids)
            ->set('status', 'expired')
            ->set('processed_at', $now)
            ->set('updated_at', $now)
            ->set(
                'remarks',
                'CASE WHEN remarks IS NULL OR remarks = ' . $db->escape('') . ' THEN ' . $db->escape($message) . ' ELSE remarks END',
                false
            )
            ->update();

        if (! $db->tableExists('notifications')) {
            return;
        }

        $notices = [];
        foreach ($rows as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            if ($userId <= 0) {
                continue;
            }
            $role = strtolower((string) ($row['role'] ?? 'resident'));
            if (! in_array($role, ['resident', 'captain', 'council', 'sk', 'secretary'], true)) {
                $role = 'resident';
            }
            $document = trim((string) ($row['document_type'] ?? 'document'));
            $notices[] = [
                'user_id'    => $userId,
                'type'       => 'clearance_expired',
                'title'      => 'Request expired — ' . $document,
                'body'       => 'Your ' . $document . ' request was invalidated because it was not processed within ' . self::CLEARANCE_DAYS . ' days. You may file a new request.',
                'link'       => '/' . $role . '/clearance',
                'read_at'    => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($notices !== []) {
            $db->table('notifications')->insertBatch($notices);
        }
    }

    private static function expireCensusAuthorizations(BaseConnection $db): void
    {
        if (! $db->tableExists('census_update_authorizations')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $db->table('census_update_authorizations')
            ->whereIn('status', ['pending', 'sent'])
            ->where('expires_at <', $now)
            ->set('status', 'expired')
            ->set('updated_at', $now)
            ->update();

        $reviewCutoff = date('Y-m-d H:i:s', time() - (self::CLEARANCE_DAYS * 86400));
        $db->table('census_update_authorizations')
            ->where('status', 'submitted')
            ->where('submitted_at <', $reviewCutoff)
            ->set('status', 'expired')
            ->set('updated_at', $now)
            ->update();
    }

    private static function expireConcerns(BaseConnection $db): void
    {
        if (! $db->tableExists('concern_submissions')) {
            return;
        }

        $cutoff = date('Y-m-d H:i:s', time() - (self::CLEARANCE_DAYS * 86400));
        $now = date('Y-m-d H:i:s');
        $message = 'This request was closed because it was not processed within ' . self::CLEARANCE_DAYS . ' days.';
        $db->table('concern_submissions')
            ->where('status', 'pending')
            ->where('created_at <', $cutoff)
            ->set('status', 'dismissed')
            ->set('updated_at', $now)
            ->set(
                'notes',
                'CASE WHEN notes IS NULL OR notes = ' . $db->escape('') . ' THEN ' . $db->escape($message) . ' ELSE notes END',
                false
            )
            ->update();
    }
}
