<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHouseholdMemberRequests extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('household_member_requests')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'request_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'household_no' => [
                'type'       => 'CHAR',
                'constraint' => 5,
            ],
            'member_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'destination_household_no' => [
                'type'       => 'CHAR',
                'constraint' => 5,
                'null'       => true,
            ],
            'payload' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],
            'requested_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'reviewed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'reviewed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('household_no');
        $this->forge->addKey('status');
        $this->forge->createTable('household_member_requests', true);
    }

    public function down()
    {
        $this->forge->dropTable('household_member_requests', true);
    }
}
