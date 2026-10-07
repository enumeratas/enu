<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table      = 'notifications';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'user_id',
        'type',
        'title',
        'body',
        'link',
        'read_at',
    ];

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Get notifications for a user.
     * Residents include broadcasts; staff see only rows addressed to them.
     */
    public function getForUser(int $userId, bool $includeBroadcasts = true): array
    {
        $builder = $this->db->table('notifications');

        if ($includeBroadcasts) {
            $builder->groupStart()
                ->where('user_id', $userId)
                ->orWhere('user_id IS NULL')
                ->groupEnd();
        } else {
            $builder->where('user_id', $userId);
        }

        return $builder
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();
    }

    /**
     * Count unread notifications for a user.
     */
    public function countUnread(int $userId, bool $includeBroadcasts = true): int
    {
        $builder = $this->db->table('notifications')
            ->where('read_at IS NULL');

        if ($includeBroadcasts) {
            $builder->groupStart()
                ->where('user_id', $userId)
                ->orWhere('user_id IS NULL')
                ->groupEnd();
        } else {
            $builder->where('user_id', $userId);
        }

        return (int) $builder->countAllResults();
    }

    /**
     * Mark one notification read for a given user.
     * Broadcasts (user_id IS NULL) get a personal read record — we handle this
     * by inserting a per-user copy only when creating notifications, so we can
     * simply update by id + user_id / null.
     */
    public function markReadById(int $notifId, int $userId, bool $includeBroadcasts = true): void
    {
        $builder = $this->db->table('notifications')
            ->where('id', $notifId);

        if ($includeBroadcasts) {
            $builder->groupStart()
                ->where('user_id', $userId)
                ->orWhere('user_id IS NULL')
                ->groupEnd();
        } else {
            $builder->where('user_id', $userId);
        }

        $builder->update(['read_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Mark all unread notifications read for a user.
     */
    public function markAllRead(int $userId, bool $includeBroadcasts = true): void
    {
        $builder = $this->db->table('notifications')
            ->where('read_at IS NULL');

        if ($includeBroadcasts) {
            $builder->groupStart()
                ->where('user_id', $userId)
                ->orWhere('user_id IS NULL')
                ->groupEnd();
        } else {
            $builder->where('user_id', $userId);
        }

        $builder->update(['read_at' => date('Y-m-d H:i:s')]);
    }

    public static function includesBroadcastsForRole(?string $role): bool
    {
        return strtolower(trim((string) $role)) === 'resident';
    }

    /**
     * Push a notification for a specific user.
     */
    public static function push(int $userId, string $type, string $title, string $body = '', string $link = ''): void
    {
        if ($userId <= 0) {
            return;
        }

        $model = new self();
        $model->insert([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body ?: null,
            'link'    => $link ?: null,
            'read_at' => null,
        ]);
    }

    /**
     * Notify every user with the given role, skipping rejected/inactive accounts
     * and optionally the person who just performed the action.
     */
    public static function pushToRole(
        string $role,
        string $type,
        string $title,
        string $body = '',
        string $link = '',
        ?int $exceptUserId = null
    ): int {
        $role = strtolower(trim($role));
        if ($role === '') {
            return 0;
        }

        $rows = \Config\Database::connect()->table('users')
            ->select('id, status')
            ->where('role', $role)
            ->get()
            ->getResultArray();

        $sent = 0;
        foreach ($rows as $row) {
            $userId = (int) ($row['id'] ?? 0);
            $status = strtolower(trim((string) ($row['status'] ?? '')));
            if ($userId <= 0 || ($exceptUserId !== null && $userId === $exceptUserId)) {
                continue;
            }
            if (in_array($status, ['rejected', 'inactive', 'disabled', 'blocked'], true)) {
                continue;
            }

            self::push($userId, $type, $title, $body, $link);
            $sent++;
        }

        if ($sent === 0) {
            log_message('warning', 'No ' . $role . ' account received notification: ' . $title);
        }

        return $sent;
    }

    /**
     * Push a broadcast notification to all residents (user_id = NULL).
     */
    public static function broadcast(string $type, string $title, string $body = '', string $link = ''): void
    {
        $model = new self();
        $model->insert([
            'user_id' => null,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body ?: null,
            'link'    => $link ?: null,
            'read_at' => null,
        ]);
    }
}
