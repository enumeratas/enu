<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBlotterResidentLinksAndNarratives extends Migration
{
    public function up()
    {
        $this->forge->addColumn('blotter_reports', [
            'complainant_narrative' => ['type' => 'TEXT', 'null' => true, 'after' => 'complainant_contact'],
            'respondent_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'respondent_name'],
            'respondent_narrative' => ['type' => 'TEXT', 'null' => true, 'after' => 'respondent_address'],
        ]);
        $this->forge->addKey('respondent_user_id');
    }

    public function down()
    {
        $this->forge->dropColumn('blotter_reports', [
            'complainant_narrative',
            'respondent_user_id',
            'respondent_narrative',
        ]);
    }
}