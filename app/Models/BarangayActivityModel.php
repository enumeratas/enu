<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangayActivityModel extends Model
{
    protected $table         = 'barangay_activities';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'title',
        'category',
        'description',
        'requirements',
        'notify_residents',
        'activity_date',
        'start_date',
        'end_date',
        'conducted_date',
        'min_age',
        'max_age',
        'target_participants',
        'start_time',
        'end_time',
        'venue',
        'banner_path',
        'status',
        'created_by',
    ];

    public const CATEGORIES = ['Sports', 'Livelihood', 'Health', 'Education', 'Environment', 'Cultural', 'Other'];

    public const REQUIREMENT_OPTIONS = [
        'DOCUMENT: Barangay ID',
        'DOCUMENT: School ID',
        'DOCUMENT: Medical Certificate',
        'PHOTO: 2x2 ID Picture',
        'PHOTO: Full-body Picture',
    ];

    /** Posted activities, newest date first, with the official who posted them. */
    public function visibleToResidents(int $limit = 0): array
    {
        $builder = $this->listBuilder()->whereIn('a.status', ['Upcoming', 'Active', 'Posted']);
        if ($limit > 0) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    /** Every activity for the secretary and captain management page. */
    public function forOfficials(): array
    {
        return $this->listBuilder()->get()->getResultArray();
    }

    private function listBuilder()
    {
        return $this->db->table($this->table . ' a')
            ->select('a.*, u.first_name, u.last_name, u.role AS poster_role')
            ->join('users u', 'u.id = a.created_by', 'left')
            ->orderBy('a.activity_date', 'DESC')
            ->orderBy('a.start_time', 'DESC')
            ->orderBy('a.id', 'DESC');
    }

    /** @return string[] */
    public static function parseRequirements(?string $requirements): array
    {
        if ($requirements === null || trim($requirements) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $requirements))));
    }

    public function registrationCount(int $activityId): int
    {
        if (! $this->db->tableExists('barangay_activity_registrations')) {
            return 0;
        }

        return (int) $this->db->table('barangay_activity_registrations')
            ->where('activity_id', $activityId)
            ->countAllResults();
    }

    public function registrationFor(int $activityId, int $userId): ?array
    {
        if (! $this->db->tableExists('barangay_activity_registrations')) {
            return null;
        }

        $row = $this->db->table('barangay_activity_registrations')
            ->where('activity_id', $activityId)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function syncDateStatuses(): void
    {
        if (! $this->db->fieldExists('conducted_date', $this->table)) {
            return;
        }

        $today = date('Y-m-d');
        foreach ($this->findAll() as $row) {
            if (($row['status'] ?? '') === 'Cancelled') {
                continue;
            }

            $date = $row['conducted_date'] ?? $row['activity_date'] ?? null;
            if (! $date) {
                continue;
            }

            $status = $date < $today ? 'Completed' : ($date === $today ? 'Active' : 'Upcoming');
            if ($status !== ($row['status'] ?? '')) {
                $this->update((int) $row['id'], ['status' => $status]);
            }
        }
    }
}
