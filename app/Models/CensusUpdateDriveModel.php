<?php

namespace App\Models;

use CodeIgniter\Model;

class CensusUpdateDriveModel extends Model
{
    protected $table         = 'census_update_drives';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'title',
        'message',
        'deadline',
        'notified_count',
        'created_by',
    ];

    public function listAll(): array
    {
        return $this->db->table($this->table . ' d')
            ->select('d.*, u.first_name, u.last_name')
            ->join('users u', 'u.id = d.created_by', 'left')
            ->orderBy('d.deadline', 'DESC')
            ->orderBy('d.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function currentOpen(): ?array
    {
        $row = $this->where('deadline >=', date('Y-m-d'))
            ->orderBy('deadline', 'ASC')
            ->orderBy('id', 'DESC')
            ->first();

        return $row ?: null;
    }
}
