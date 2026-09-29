<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCensusUpdateAuthorizationTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],
            'household_no' => [
                'type' => 'VARCHAR',
                'constraint' => '5',
                'null' => true,
            ],
            'token' => [
                'type' => 'VARCHAR',
                'constraint' => '64',
                'null' => false,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'sent', 'submitted', 'approved', 'rejected', 'expired'],
                'default' => 'pending',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'rejected_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'reviewed_by' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'notes' => [
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('token');
        $this->forge->addKey('user_id');
        $this->forge->addKey('household_no');
        $this->forge->createTable('census_update_authorizations', true);
    }

    public function down()
    {
        $this->forge->dropTable('census_update_authorizations', true);
    }
}
