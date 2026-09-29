<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdUploadFields extends Migration
{
    public function up(): void
    {
        // ── Household head ID uploads (4Ps, PWD, Senior, Solo Parent) ────
        $this->forge->addColumn('households', [
            'id_4ps_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'is_4ps',
            ],
            'id_pwd_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'pwd_type',
            ],
            'id_senior_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'is_senior_citizen',
            ],
            'id_solo_parent_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'is_solo_parent',
            ],
        ]);

        // ── Household member PWD ID upload ─────────────────────────────────
        $this->forge->addColumn('household_members', [
            'id_pwd_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'pwd_type',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('households', [
            'id_4ps_path',
            'id_pwd_path',
            'id_senior_path',
            'id_solo_parent_path',
        ]);
        $this->forge->dropColumn('household_members', ['id_pwd_path']);
    }
}
