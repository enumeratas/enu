<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHouseholdMovesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'source_household_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 5,
            ],
            'destination_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 10, // 'new' or 'existing'
            ],
            'destination_household_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 5,
                'null'       => true,
            ],
            'includes_head' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'member_ids' => [
                'type' => 'TEXT',
                'null' => true, // JSON array of household_members.id
            ],
            'replacement_head_member_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'move_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'new_zone' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
            ],
            'new_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 12,
                'default'    => 'pending', // pending, approved, rejected
            ],
            'summary' => [
                'type' => 'TEXT',
                'null' => true, // human-readable audit line
            ],
            'requested_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'processed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('source_household_no');
        $this->forge->addKey('destination_household_no');
        $this->forge->addKey('status');
        $this->forge->createTable('household_moves');
    }

    public function down()
    {
        $this->forge->dropTable('household_moves');
    }
}
