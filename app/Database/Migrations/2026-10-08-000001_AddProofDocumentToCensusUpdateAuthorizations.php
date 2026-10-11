<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProofDocumentToCensusUpdateAuthorizations extends Migration
{
    public function up()
    {
        // Safe add — only if column not already present
        if (! $this->db->fieldExists('proof_document_path', 'census_update_authorizations')) {
            $this->forge->addColumn('census_update_authorizations', [
                'proof_document_path' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'notes',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('proof_document_path', 'census_update_authorizations')) {
            $this->forge->dropColumn('census_update_authorizations', 'proof_document_path');
        }
    }
}
