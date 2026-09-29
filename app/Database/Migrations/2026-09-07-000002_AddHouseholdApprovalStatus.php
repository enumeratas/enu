<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHouseholdApprovalStatus extends Migration
{
    public function up()
    {
        $this->forge->addColumn('households', [
            'approval_status' => [
                'type'       => 'ENUM',
                'constraint' => ['approved', 'pending', 'rejected'],
                'default'    => 'approved',
                'after'      => 'recorded_date',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('households', 'approval_status');
    }
}
