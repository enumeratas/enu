<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConcernProgressTracking extends Migration
{
    public function up()
    {
        // Expand the status ENUM on concern_submissions
        $this->db->query("ALTER TABLE concern_submissions MODIFY status ENUM('pending','approved','processing','ongoing','resolved','dismissed') NOT NULL DEFAULT 'pending'");

        // Create the concern_progress tracking table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => false,
                'auto_increment' => true,
            ],
            'concern_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'processing', 'ongoing', 'resolved', 'dismissed'],
                'null'       => false,
            ],
            'note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'proof_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 300,
                'null'       => true,
            ],
            'created_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
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
        $this->forge->addKey('concern_id');
        $this->forge->createTable('concern_progress', true);
    }

    public function down()
    {
        $this->forge->dropTable('concern_progress', true);
        $this->db->query("ALTER TABLE concern_submissions MODIFY status ENUM('pending','approved','resolved','dismissed') NOT NULL DEFAULT 'pending'");
    }
}
