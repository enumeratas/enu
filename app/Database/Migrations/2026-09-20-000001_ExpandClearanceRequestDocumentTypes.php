<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Stores every document type offered by Config\ClearanceDocuments.
 */
class ExpandClearanceRequestDocumentTypes extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('clearance_requests', [
            'document_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'default'    => 'Barangay Clearance',
                'comment'    => 'Document type selected by the resident or secretary',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('clearance_requests', [
            'document_type' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'Barangay Clearance',
                    'Certificate of Residency',
                    'Certificate of Indigency',
                ],
                'default' => 'Barangay Clearance',
            ],
        ]);
    }
}
