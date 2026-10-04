<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDesignatedHeadToHouseholdMoves extends Migration
{
    public function up()
    {
        $fields = $this->db->getFieldNames('household_moves');
        $columns = [];
        if (! in_array('designated_head_type', $fields, true)) {
            $columns['designated_head_type'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'replacement_head_member_id',
            ];
        }
        if (! in_array('designated_head_member_id', $fields, true)) {
            $columns['designated_head_member_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'designated_head_type',
            ];
        }
        if (! in_array('designated_head_name', $fields, true)) {
            $columns['designated_head_name'] = [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
                'after'      => 'designated_head_member_id',
            ];
        }
        if ($columns !== []) {
            $this->forge->addColumn('household_moves', $columns);
        }
    }

    public function down()
    {
        $fields = $this->db->getFieldNames('household_moves');
        foreach (['designated_head_name', 'designated_head_member_id', 'designated_head_type'] as $column) {
            if (in_array($column, $fields, true)) {
                $this->forge->dropColumn('household_moves', $column);
            }
        }
    }
}
