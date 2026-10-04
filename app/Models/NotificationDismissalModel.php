<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationDismissalModel extends Model
{
    protected $table         = 'notification_dismissals';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'user_id',
        'item_type',
        'item_ref',
        'dismissed_at',
    ];

    public static function key(string $type, string $ref): string
    {
        return strtolower(trim($type)) . ':' . trim($ref);
    }

    public function dismiss(int $userId, string $type, string $ref): void
    {
        if ($userId <= 0 || trim($type) === '' || trim($ref) === '') {
            return;
        }

        $builder = $this->db->table($this->table);
        $exists = $builder
            ->where('user_id', $userId)
            ->where('item_type', $type)
            ->where('item_ref', $ref)
            ->countAllResults();

        if ($exists > 0) {
            return;
        }

        $this->db->table($this->table)->insert([
            'user_id'      => $userId,
            'item_type'    => $type,
            'item_ref'     => $ref,
            'dismissed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param list<array{type:string, ref_id:string|int}> $items
     */
    public function dismissMany(int $userId, array $items): void
    {
        foreach ($items as $item) {
            $this->dismiss(
                $userId,
                (string) ($item['type'] ?? ''),
                (string) ($item['ref_id'] ?? '')
            );
        }
    }

    /**
     * @return array<string, true>
     */
    public function getDismissedKeys(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $rows = $this->db->table($this->table)
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        $keys = [];

        foreach ($rows as $row) {
            $keys[self::key((string) ($row['item_type'] ?? ''), (string) ($row['item_ref'] ?? ''))] = true;
        }

        return $keys;
    }
}
