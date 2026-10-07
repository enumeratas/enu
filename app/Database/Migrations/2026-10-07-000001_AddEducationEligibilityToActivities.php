<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEducationEligibilityToActivities extends Migration
{
    public function up()
    {
        $this->addEligibilityColumns('barangay_activities');
        $this->addEligibilityColumns('sk_programs');
    }

    public function down()
    {
        $this->dropEligibilityColumns('barangay_activities');
        $this->dropEligibilityColumns('sk_programs');
    }

    private function addEligibilityColumns(string $table): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        $this->db->resetDataCache();
        $fields = $this->db->getFieldNames($table);
        $add    = [];

        if (! in_array('eligibility_groups', $fields, true)) {
            $add['eligibility_groups'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'max_age',
            ];
        }
        if (! in_array('eligibility_other', $fields, true)) {
            $add['eligibility_other'] = [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'after'      => 'eligibility_groups',
            ];
        }

        if ($add !== []) {
            $this->forge->addColumn($table, $add);
        }
    }

    private function dropEligibilityColumns(string $table): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        $this->db->resetDataCache();
        $fields = $this->db->getFieldNames($table);
        $drop   = [];
        foreach (['eligibility_groups', 'eligibility_other'] as $column) {
            if (in_array($column, $fields, true)) {
                $drop[] = $column;
            }
        }
        if ($drop !== []) {
            $this->forge->dropColumn($table, $drop);
        }
    }
}
