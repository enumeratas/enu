<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillConcernAppointmentLinks extends Migration
{
    public function up(): void
    {
        $db = $this->db;
        $concerns = $db->table('concern_submissions')
            ->where('appointment_date IS NOT NULL')
            ->get()->getResultArray();

        foreach ($concerns as $concern) {
            $user = $db->table('users')
                ->select('id')
                ->where('email', $concern['email'])
                ->where('role', 'resident')
                ->get()->getRowArray();

            $updates = [];
            if ($user) {
                $updates['user_id'] = (int) $user['id'];
            }

            $schedule = $db->table('schedules')
                ->where('concern_id', (int) $concern['id'])
                ->orGroupStart()
                    ->where('title', 'Concern Appointment: ' . ($concern['subject'] ?: 'Barangay Concern'))
                    ->where('event_date', $concern['appointment_date'])
                    ->where('start_time', $concern['appointment_time'])
                ->groupEnd()
                ->orderBy('id', 'ASC')
                ->get()->getRowArray();

            if ($schedule) {
                $updates['schedule_id'] = (int) $schedule['id'];
                if (empty($schedule['concern_id'])) {
                    $db->table('schedules')->where('id', $schedule['id'])->update(['concern_id' => $concern['id']]);
                }
            }

            if ($updates) {
                $db->table('concern_submissions')->where('id', $concern['id'])->update($updates);
            }
        }
    }

    public function down(): void
    {
        // Backfilled links are intentionally retained when this migration is rolled back.
    }
}
