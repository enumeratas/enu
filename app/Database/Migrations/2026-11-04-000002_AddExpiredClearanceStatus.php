<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExpiredClearanceStatus extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE clearance_requests MODIFY status ENUM('pending', 'approved', 'rejected', 'released', 'expired') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        $this->db->query("UPDATE clearance_requests SET status = 'rejected' WHERE status = 'expired'");
        $this->db->query("ALTER TABLE clearance_requests MODIFY status ENUM('pending', 'approved', 'rejected', 'released') NOT NULL DEFAULT 'pending'");
    }
}
