<?php

namespace App\Models;

use CodeIgniter\Model;

class SkProgramModel extends Model
{
    protected $table         = 'sk_programs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'name',
        'category',
        'description',
        'requirements',
        'notify_residents',
        'start_date',
        'end_date',
        'conducted_date',
        'min_age',
        'max_age',
        'venue',
        'target_participants',
        'actual_participants',
        'budget',
        'status',
        'created_by',
    ];

    /** Counts grouped by status. */
    public function statusCounts(): array
    {
        $rows = $this->db->table('sk_programs')
            ->select('status, COUNT(*) AS cnt')
            ->groupBy('status')
            ->get()->getResultArray();

        $map = ['Upcoming' => 0, 'Active' => 0, 'Completed' => 0, 'Cancelled' => 0, 'total' => 0];
        foreach ($rows as $r) {
            $map[$r['status']] = (int) $r['cnt'];
            $map['total']     += (int) $r['cnt'];
        }
        return $map;
    }

    /**
     * Return programs visible to residents (Active or Upcoming).
     */
    public function getVisibleToResidents(): array
    {
        $this->syncDateStatuses();
        return $this->whereIn('status', ['Active', 'Upcoming'])
            ->orderBy('start_date', 'DESC')
            ->findAll();
    }

    /**
     * Get registration count for a program.
     */
    public function getRegistrationCount(int $programId): int
    {
        return (int) $this->db->table('sk_program_registrations')
            ->where('program_id', $programId)
            ->countAllResults();
    }

    /**
     * Check if a user is registered for a program.
     */
    public function isRegistered(int $programId, int $userId): ?array
    {
        return $this->db->table('sk_program_registrations')
            ->where('program_id', $programId)
            ->where('user_id', $userId)
            ->get()->getRowArray();
    }

    /**
     * Parse requirements string into an array.
     *
     * @return string[]
     */
    public static function parseRequirements(?string $req): array
    {
        if (empty($req)) return [];
        return array_filter(array_map('trim', explode(',', $req)));
    }

    /** Keep non-cancelled program status aligned with the conducted date. */
    public function syncDateStatuses(): void
    {
        $today = date('Y-m-d');
        foreach ($this->whereIn('status', ['Upcoming', 'Active', 'Completed'])->findAll() as $program) {
            $conductedDate = $program['conducted_date'] ?? $program['end_date'] ?? $program['start_date'] ?? null;
            if (! $conductedDate) {
                continue;
            }

            $newStatus = $conductedDate < $today
                ? 'Completed'
                : ($conductedDate === $today ? 'Active' : 'Upcoming');
            if ($newStatus !== $program['status']) {
                $this->update((int) $program['id'], ['status' => $newStatus]);
            }
        }
    }
}
