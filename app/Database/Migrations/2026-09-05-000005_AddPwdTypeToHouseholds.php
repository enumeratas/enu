<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPwdTypeToHouseholds extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('households', [
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
        $this->forge->dropColumn('households', 'pwd_type');
    }
}
