<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDocumentSnapshotToClearanceRequests extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();

        if (! in_array('issued_document_snapshot', $this->db->getFieldNames('clearance_requests'), true)) {
            $this->forge->addColumn('clearance_requests', [
                'issued_document_snapshot' => [
                    'type'    => 'TEXT',
                    'null'    => true,
                    'default' => null,
                    'comment' => 'Immutable document data captured when the request is released',
                    'after'   => 'issued_date',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();

        if (in_array('issued_document_snapshot', $this->db->getFieldNames('clearance_requests'), true)) {
            $this->forge->dropColumn('clearance_requests', 'issued_document_snapshot');
        }
    }
}
