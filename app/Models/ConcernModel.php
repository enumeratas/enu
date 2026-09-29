<?php

namespace App\Models;

use CodeIgniter\Model;

class ConcernModel extends Model
{
    protected $table         = 'concern_submissions';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'user_id',
        'full_name',
        'email',
        'contact_number',
        'category',
        'subject',
        'message',
        'appointment_date',
        'appointment_time',
        'schedule_id',
        'status',
        'notes',
    ];
}
