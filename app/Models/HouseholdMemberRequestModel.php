<?php

namespace App\Models;

use CodeIgniter\Model;

class HouseholdMemberRequestModel extends Model
{
    protected $table         = 'household_member_requests';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'request_type',
        'household_no',
        'member_id',
        'destination_household_no',
        'payload',
        'reason',
        'status',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    /** @return list<array<string, mixed>> */
    public function listPending(): array
    {
        return $this->listByStatus('pending');
    }

    /** @return list<array<string, mixed>> */
    public function listByStatus(?string $status = null): array
    {
        $builder = $this->db->table($this->table . ' r')
            ->select('r.*, u.first_name AS req_first, u.last_name AS req_last, m.first_name AS mem_first, m.last_name AS mem_last, m.relationship AS mem_relationship')
            ->join('users u', 'u.id = r.requested_by', 'left')
            ->join('household_members m', 'm.id = r.member_id', 'left')
            ->orderBy('r.created_at', 'DESC')
            ->orderBy('r.id', 'DESC');

        if ($status !== null && $status !== '') {
            $builder->where('r.status', $status);
        }

        return $builder->get()->getResultArray();
    }
}
