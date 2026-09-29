<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAgeAndConductedDateToSkPrograms extends Migration
{
    public function up()
    {
        $this->forge->addColumn('sk_programs', [
            'conducted_date' => [
                'type'  => 'DATE',
                'null'  => true,
                'after' => 'end_date',
            ],
            'min_age' => [
                'type'    => 'TINYINT',
                'unsigned' => true,
                'null'    => true,
                'default' => null,
                'after'   => 'conducted_date',
            ],
            'max_age' => [
                'type'    => 'TINYINT',
                'unsigned' => true,
                'null'    => true,
                'default' => null,
                'after'   => 'min_age',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('sk_programs', ['conducted_date', 'min_age', 'max_age']);
    }
}
