<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Audit table that tracks every change to a household's ownership classification.
 * Each row represents one change event (whether pending, approved, or rejected).
 */
class CreateHouseholdOwnershipChangesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'household_no' => [
                'type'       => 'CHAR',
                'constraint' => 5,
                'null'       => false,
            ],
            'changed_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,   // null = system
            ],
            'old_ownership' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
            ],
            'new_ownership' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => false,
            ],
            'old_num_families' => [
                'type'    => 'TINYINT',
                'null'    => true,
            ],
            'new_num_families' => [
                'type'    => 'TINYINT',
                'null'    => true,
            ],
            'document_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // pending | approved | rejected | auto_approved (no ownership change, just other fields)
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected', 'auto_approved'],
                'default'    => 'pending',
            ],
            'reviewed_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'reviewed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'review_note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('household_no');
        $this->forge->addKey('status');
        $this->forge->createTable('household_ownership_changes');
    }

    public function down(): void
    {
        $this->forge->dropTable('household_ownership_changes');
    }
}
