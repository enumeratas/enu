<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEvidencePhotosToBlotterReports extends Migration
{
    public function up()
    {
        $this->forge->addColumn('blotter_reports', [
            'evidence_photos' => [
                'type'    => 'JSON',
                'null'    => true,
                'default' => null,
                'after'   => 'narrative',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('blotter_reports', 'evidence_photos');
    }
}
