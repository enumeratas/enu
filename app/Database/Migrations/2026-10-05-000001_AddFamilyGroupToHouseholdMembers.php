<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFamilyGroupToHouseholdMembers extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('family_group', 'household_members')) {
            $this->forge->addColumn('household_members', [
                'family_group' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 1,
                    'null'       => false,
                    'after'      => 'household_no',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('family_group', 'household_members')) {
            $this->forge->dropColumn('household_members', 'family_group');
        }
    }
}
