<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCouncilRoleToUsers extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE users MODIFY role ENUM('captain', 'secretary', 'sk', 'council', 'resident') NOT NULL DEFAULT 'resident'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE users MODIFY role ENUM('captain', 'secretary', 'sk', 'resident') NOT NULL DEFAULT 'resident'");
    }
}
