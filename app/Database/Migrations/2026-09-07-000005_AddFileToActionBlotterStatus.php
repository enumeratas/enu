<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFileToActionBlotterStatus extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE blotter_reports MODIFY status ENUM('pending', 'under_investigation', 'file_to_action', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE blotter_reports MODIFY status ENUM('pending', 'under_investigation', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending'");
    }
}
