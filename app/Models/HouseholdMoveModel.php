<?php

namespace App\Models;

use CodeIgniter\Model;

class HouseholdMoveModel extends Model
{
    protected $table         = 'household_moves';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'source_household_no',
        'destination_type',
        'destination_household_no',
        'includes_head',
        'member_ids',
        'replacement_head_member_id',
        'move_reason',
        'notes',
        'new_zone',
        'new_address',
        'status',
        'summary',
        'requested_by',
        'processed_by',
        'processed_at',
        'rejection_reason',
    ];

    /** Every move with requester and processor names, newest first. */
    public function listAll(?string $status = null, ?string $search = null): array
    {
        $builder = $this->db->table($this->table . ' mv')
            ->select('mv.*, ru.first_name AS req_first, ru.last_name AS req_last, pu.first_name AS proc_first, pu.last_name AS proc_last, sh.last_name AS src_last_name, sh.first_name AS src_first_name, dh.last_name AS dst_last_name, dh.first_name AS dst_first_name')
            ->join('users ru', 'ru.id = mv.requested_by', 'left')
            ->join('users pu', 'pu.id = mv.processed_by', 'left')
            ->join('households sh', 'sh.household_no = mv.source_household_no', 'left')
            ->join('households dh', 'dh.household_no = mv.destination_household_no', 'left')
            ->orderBy('mv.created_at', 'DESC')
            ->orderBy('mv.id', 'DESC');

        if ($status !== null && $status !== '') {
            $builder->where('mv.status', $status);
        }

        if ($search !== null && $search !== '') {
            $builder->groupStart()
                ->like('mv.source_household_no', $search)
                ->orLike('mv.destination_household_no', $search)
                ->orLike('sh.last_name', $search)
                ->orLike('sh.first_name', $search)
                ->orLike('dh.last_name', $search)
                ->orLike('dh.first_name', $search)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function countByStatus(): array
    {
        $rows = $this->db->table($this->table)
            ->select('status, COUNT(*) AS c')
            ->groupBy('status')
            ->get()
            ->getResultArray();
        $out = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
        foreach ($rows as $row) {
            $out[strtolower((string) $row['status'])] = (int) $row['c'];
        }
        return $out;
    }
}
