<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillReleasedClearanceSnapshots extends Migration
{
    public function up()
    {
        $settings = $this->db->table('barangay_settings')
            ->select('setting_value')
            ->where('setting_key', 'captain_name')
            ->get()->getRowArray();
        $captainName = trim((string) ($settings['setting_value'] ?? '')) ?: 'PUNONG BARANGAY';

        $this->db->query(
            'UPDATE clearance_requests
             SET issued_captain_name = ?,
                 issued_date = COALESCE(DATE(processed_at), DATE(updated_at), DATE(created_at))
             WHERE status = "released"
               AND (issued_captain_name IS NULL OR issued_captain_name = "")',
            [$captainName]
        );
    }

    public function down()
    {
        // Existing released records must remain immutable; do not erase snapshots.
    }
}
