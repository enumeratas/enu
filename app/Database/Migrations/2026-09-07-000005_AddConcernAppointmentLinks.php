<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConcernAppointmentLinks extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('concern_submissions', [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
            'schedule_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'appointment_time',
            ],
        ]);

        $this->forge->addKey('user_id', false, false, 'idx_concern_user_id');
        $this->forge->addKey('schedule_id', false, false, 'idx_concern_schedule_id');
        $this->forge->processIndexes('concern_submissions');

        $this->forge->addColumn('schedules', [
            'concern_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'blotter_id',
            ],
        ]);

        $this->forge->addKey('concern_id', false, false, 'idx_schedule_concern_id');
        $this->forge->processIndexes('schedules');
    }

    public function down(): void
    {
        $this->forge->dropKey('concern_submissions', 'idx_concern_user_id');
        $this->forge->dropKey('concern_submissions', 'idx_concern_schedule_id');
        $this->forge->dropColumn('concern_submissions', ['user_id', 'schedule_id']);
        $this->forge->dropKey('schedules', 'idx_schedule_concern_id');
        $this->forge->dropColumn('schedules', 'concern_id');
    }
}
