<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBannerToBarangayActivities extends Migration
{
    public function up()
    {
        $this->forge->addColumn('barangay_activities', [
            'banner_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'venue',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('barangay_activities', 'banner_path');
    }
}
