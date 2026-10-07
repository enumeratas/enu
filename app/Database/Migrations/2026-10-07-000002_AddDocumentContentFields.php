<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDocumentContentFields extends Migration
{
    public function up()
    {
        $this->addTextColumn('document_templates', 'content', 'html');
        $this->addTextColumn('clearance_requests', 'document_content', 'issued_document_snapshot');
    }

    public function down()
    {
        $this->dropColumnIfExists('document_templates', 'content');
        $this->dropColumnIfExists('clearance_requests', 'document_content');
    }

    private function addTextColumn(string $table, string $column, string $after): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        $this->db->resetDataCache();
        $fields = $this->db->getFieldNames($table);
        if (in_array($column, $fields, true)) {
            return;
        }

        $this->forge->addColumn($table, [
            $column => [
                'type' => 'TEXT',
                'null' => true,
                'after' => $after,
            ],
        ]);
    }

    private function dropColumnIfExists(string $table, string $column): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        $this->db->resetDataCache();
        if (in_array($column, $this->db->getFieldNames($table), true)) {
            $this->forge->dropColumn($table, $column);
        }
    }
}
