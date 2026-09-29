<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddResidencyStartYear extends Migration
{
    public function up()
    {
        $this->forge->addColumn('households', [
            'residency_start_year' => [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'years_of_residency',
            ],
        ]);

        $this->db->query(
            'UPDATE households SET residency_start_year = YEAR(CURDATE()) - years_of_residency WHERE residency_start_year IS NULL'
        );
    }

    public function down()
    {
        $this->forge->dropColumn('households', 'residency_start_year');
    }
}
