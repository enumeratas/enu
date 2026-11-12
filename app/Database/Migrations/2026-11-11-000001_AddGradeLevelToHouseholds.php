<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Lets the household head store a grade or year level, the same way members do.
 */
class AddGradeLevelToHouseholds extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        $fields = $this->db->getFieldNames('households');
        if (! in_array('grade_level', $fields, true)) {
            $this->forge->addColumn('households', [
                'grade_level' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 80,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'occupation',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();
        $fields = $this->db->getFieldNames('households');
        if (in_array('grade_level', $fields, true)) {
            $this->forge->dropColumn('households', 'grade_level');
        }
    }
}
