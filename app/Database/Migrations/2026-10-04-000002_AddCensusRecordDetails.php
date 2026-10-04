<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCensusRecordDetails extends Migration
{
    public function up()
    {
        $households = $this->db->getFieldNames('households');
        $householdColumns = [];
        if (! in_array('record_status', $households, true)) {
            $householdColumns['record_status'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'complete',
                'after'      => 'approval_status',
            ];
        }
        if (! in_array('supporting_doc_path', $households, true)) {
            $householdColumns['supporting_doc_path'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'ownership_document_path',
            ];
        }
        if (! in_array('linked_household_no', $households, true)) {
            $householdColumns['linked_household_no'] = [
                'type'       => 'VARCHAR',
                'constraint' => 5,
                'null'       => true,
                'after'      => 'family_number',
            ];
        }
        if ($householdColumns !== []) {
            $this->forge->addColumn('households', $householdColumns);
        }

        $members = $this->db->getFieldNames('household_members');
        $memberColumns = [];
        if (! in_array('supporting_doc_path', $members, true)) {
            $memberColumns['supporting_doc_path'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'id_senior_path',
            ];
        }
        if (! in_array('work_detail', $members, true)) {
            $memberColumns['work_detail'] = [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'after'      => 'occupation',
            ];
        }
        if ($memberColumns !== []) {
            $this->forge->addColumn('household_members', $memberColumns);
        }
    }

    public function down()
    {
        $households = $this->db->getFieldNames('households');
        foreach (['record_status', 'supporting_doc_path', 'linked_household_no'] as $column) {
            if (in_array($column, $households, true)) {
                $this->forge->dropColumn('households', $column);
            }
        }

        $members = $this->db->getFieldNames('household_members');
        foreach (['supporting_doc_path', 'work_detail'] as $column) {
            if (in_array($column, $members, true)) {
                $this->forge->dropColumn('household_members', $column);
            }
        }
    }
}
