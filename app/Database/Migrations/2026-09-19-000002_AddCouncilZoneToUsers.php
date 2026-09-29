<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCouncilZoneToUsers extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();

        if (! in_array('council_zone', $this->db->getFieldNames('users'), true)) {
            $this->forge->addColumn('users', [
                'council_zone' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                    'after'      => 'role',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();

        if (in_array('council_zone', $this->db->getFieldNames('users'), true)) {
            $this->forge->dropColumn('users', 'council_zone');
        }
    }
}
