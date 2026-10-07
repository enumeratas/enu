<?php

namespace App\Models;

use CodeIgniter\Model;

class CensusUpdateAuthorizationModel extends Model
{
    protected $table          = 'census_update_authorizations';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'household_no',
        'token',
        'status',
        'expires_at',
        'sent_at',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'reviewed_by',
        'notes',
    ];

    public function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function createForUser(int $userId, ?string $householdNo, int $days = 7, ?string $expiresAt = null): array
    {
        $token = $this->generateToken();
        if ($expiresAt === null || $expiresAt === '') {
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . max(1, $days) . ' days'));
        }

        $this->insert([
            'user_id' => $userId,
            'household_no' => $householdNo,
            'token' => $token,
            'status' => 'pending',
            'expires_at' => $expiresAt,
            'sent_at' => null,
            'submitted_at' => null,
            'approved_at' => null,
            'rejected_at' => null,
            'reviewed_by' => null,
            'notes' => null,
        ], false);

        return $this->where('token', $token)->first();
    }

    public function findValidByToken(string $token): ?array
    {
        return $this->where('token', $token)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->whereIn('status', ['pending', 'sent', 'submitted', 'approved'])
            ->first();
    }

    public function getPendingByUser(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->whereIn('status', ['pending', 'sent', 'submitted'])
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /** @return list<array<string, mixed>> */
    public function listForReview(array $statuses = ['submitted']): array
    {
        $builder = $this->db->table($this->table . ' a')
            ->select('a.*, u.first_name, u.last_name, u.role AS user_role')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->orderBy('a.submitted_at', 'DESC')
            ->orderBy('a.id', 'DESC');

        if ($statuses !== []) {
            $builder->whereIn('a.status', $statuses);
        }

        return $builder->get()->getResultArray();
    }
}
