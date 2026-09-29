<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAttachmentsToSkProgramRegistrations extends Migration
{
    public function up()
    {
        $this->forge->addColumn('sk_program_registrations', [
            'attachments' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'requirements_submitted',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('sk_program_registrations', 'attachments');
    }
}
