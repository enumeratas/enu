<?php

namespace App\Models;

use CodeIgniter\Model;

class ClearanceRequestModel extends Model
{
    protected $table         = 'clearance_requests';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'user_id',
        'household_no',
        'for_member',
        'member_relationship',
        'document_type',
        'purpose',
        'notes',
        'status',
        'remarks',
        'processed_by',
        'processed_at',
        'est_release_date',
        'issued_captain_name',
        'issued_date',
        'issued_document_snapshot',
        'document_content',
    ];

    /**
     * Get all requests for a specific resident user.
     */
    public function getByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /** True when this resident already has a pending or approved request for the same person and document. */
    public function hasOpenRequest(int $userId, string $documentType, string $forMember): bool
    {
        $target = strtolower(trim($forMember));
        $rows = $this->where('user_id', $userId)
            ->where('document_type', $documentType)
            ->whereIn('status', ['pending', 'approved'])
            ->findAll();

        foreach ($rows as $row) {
            if (strtolower(trim((string) ($row['for_member'] ?? ''))) === $target) {
                return true;
            }
        }

        return false;
    }

    /**
     * Block a second insert for the same person and document.
     * Returns the lock name when this request may be saved, false when one already exists, null when the lock is busy.
     */
    public function claimOpenSlot(int $userId, string $documentType, string $forMember): string|false|null
    {
        $name = 'clr' . substr(hash('sha256', $userId . '|' . strtolower($documentType) . '|' . strtolower(trim($forMember))), 0, 50);
        $row = $this->db->query('SELECT GET_LOCK(?, 8) AS locked', [$name])->getRowArray();
        if ((int) ($row['locked'] ?? 0) !== 1) {
            return null;
        }
        try {
            $alreadyFiled = $this->hasOpenRequest($userId, $documentType, $forMember)
                || $this->hasJustSubmitted($userId, $documentType, $forMember);
        } catch (\Throwable $error) {
            $this->db->query('SELECT RELEASE_LOCK(?)', [$name]);
            throw $error;
        }
        if ($alreadyFiled) {
            $this->db->query('SELECT RELEASE_LOCK(?)', [$name]);

            return false;
        }

        return $name;
    }

    public function releaseOpenSlot(string $name): void
    {
        if ($name === '') {
            return;
        }
        $this->db->query('SELECT RELEASE_LOCK(?)', [$name]);
    }

    private function hasJustSubmitted(int $userId, string $documentType, string $forMember): bool
    {
        $target = strtolower(trim($forMember));
        $rows = $this->where('user_id', $userId)
            ->where('document_type', $documentType)
            ->where('created_at >=', date('Y-m-d H:i:s', time() - 15))
            ->findAll();

        foreach ($rows as $row) {
            if (strtolower(trim((string) ($row['for_member'] ?? ''))) === $target) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all pending requests (for captain/secretary view).
     */
    public function getPending(): array
    {
        return $this->where('status', 'pending')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
