<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveDuplicateConcernAppointments extends Migration
{
    public function up(): void
    {
        $db = $this->db;
        $concerns = $db->table('concern_submissions')->get()->getResultArray();

        foreach ($concerns as $concern) {
            $title = 'Concern Appointment: ' . ($concern['subject'] ?: 'Barangay Concern');
            $description = 'Concern for ' . ($concern['full_name'] ?: 'Resident') . '.';
            $events = $db->table('schedules')
                ->where('title', $title)
                ->where('description', $description)
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();

            if (count($events) < 1) {
                continue;
            }

            $keep = null;
            foreach ($events as $event) {
                if (($event['event_date'] ?? null) === ($concern['appointment_date'] ?? null)) {
                    $keep = $event;
                    break;
                }
            }
            $keep ??= $events[0];

            $db->table('schedules')
                ->where('title', $title)
                ->where('description', $description)
                ->where('id !=', $keep['id'])
                ->delete();
            $db->table('schedules')->where('id', $keep['id'])->update(['concern_id' => $concern['id']]);
            $db->table('concern_submissions')->where('id', $concern['id'])->update(['schedule_id' => $keep['id']]);
        }

        $this->forge->addUniqueKey('concern_id', 'uq_schedule_concern_id');
        $this->forge->processIndexes('schedules');
    }

    public function down(): void
    {
        $this->forge->dropKey('schedules', 'uq_schedule_concern_id');
    }
}
