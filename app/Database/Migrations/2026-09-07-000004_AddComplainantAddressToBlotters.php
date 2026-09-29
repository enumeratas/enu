<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddComplainantAddressToBlotters extends Migration
{
    public function up()
    {
        $this->forge->addColumn('blotter_reports', [
            'complainant_address' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'complainant_contact'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('blotter_reports', 'complainant_address');
    }
}
