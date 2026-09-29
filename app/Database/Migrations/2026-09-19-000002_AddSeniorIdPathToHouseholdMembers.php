<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSeniorIdPathToHouseholdMembers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('household_members', [
            'id_senior_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'id_pwd_path',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('household_members', 'id_senior_path');
    }
}
