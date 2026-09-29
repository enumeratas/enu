<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBlotterHearingNarratives extends Migration
{
    public function up()
    {
        $this->forge->addColumn('blotter_reports', [
            'hearing_complainant_narrative' => ['type' => 'TEXT', 'null' => true, 'after' => 'hearing_notes'],
            'hearing_respondent_narrative'  => ['type' => 'TEXT', 'null' => true, 'after' => 'hearing_complainant_narrative'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('blotter_reports', [
            'hearing_complainant_narrative',
            'hearing_respondent_narrative',
        ]);
    }
}
