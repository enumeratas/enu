<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRejectionReasonToSkProgramRegistrations extends Migration
{
    public function up()
    {
        $this->forge->addColumn('sk_program_registrations', [
            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'notes',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('sk_program_registrations', 'rejection_reason');
    }
}
