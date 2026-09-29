<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFamilyTypeToHouseholds extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();

        if (! in_array('family_type', $this->db->getFieldNames('households'), true)) {
            $this->forge->addColumn('households', [
                'family_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 40,
                    'null'       => true,
                    'after'      => 'family_number',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();

        if (in_array('family_type', $this->db->getFieldNames('households'), true)) {
            $this->forge->dropColumn('households', 'family_type');
        }
    }
}
