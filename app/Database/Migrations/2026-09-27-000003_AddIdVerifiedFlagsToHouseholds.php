<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdVerifiedFlagsToHouseholds extends Migration
{
    private array $columns = [
        'id_4ps_verified'          => 'id_4ps_path',
        'id_pwd_verified'          => 'id_pwd_path',
        'id_senior_verified'       => 'id_senior_path',
        'id_solo_parent_verified'  => 'id_solo_parent_path',
    ];

    public function up()
    {
        foreach ($this->columns as $column => $after) {
            $this->forge->addColumn('households', [
                $column => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'null'       => false,
                    'default'    => 0,
                    'after'      => $after,
                ],
            ]);
        }
    }

    public function down()
    {
        foreach (array_keys($this->columns) as $column) {
            $this->forge->dropColumn('households', $column);
        }
    }
}
