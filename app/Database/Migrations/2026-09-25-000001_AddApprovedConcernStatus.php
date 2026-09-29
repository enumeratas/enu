<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApprovedConcernStatus extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE concern_submissions MODIFY status ENUM('pending', 'approved', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE concern_submissions MODIFY status ENUM('pending', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending'");
    }
}
