<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOwnershipProofToHouseholds extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('households', [
            'ownership_document_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'house_ownership',
            ],
            'ownership_notes' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'ownership_document_path',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('households', ['ownership_document_path', 'ownership_notes']);
    }
}
