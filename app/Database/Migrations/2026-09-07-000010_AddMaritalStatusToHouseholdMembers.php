<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMaritalStatusToHouseholdMembers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('household_members', [
            'marital_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Single', 'Married', 'Widowed', 'Separated', 'Annulled'],
                'default'    => 'Single',
                'after'      => 'gender',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('household_members', 'marital_status');
    }
}
