<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPwdFieldsToHouseholdMembers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('household_members', [
            'is_pwd' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'educational_attainment',
            ],
            'pwd_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'default'    => null,
                'after'      => 'is_pwd',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('household_members', ['is_pwd', 'pwd_type']);
    }
}
