<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeceasedFieldsToHouseholds extends Migration
{
    public function up(): void
    {
        // Household head
        $this->forge->addColumn('households', [
            'is_deceased' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'null'    => false,
                'default' => 0,
                'after'   => 'recorded_date',
            ],
            'year_of_death' => [
                'type'    => 'SMALLINT',
                'constraint' => 4,
                'unsigned' => true,
                'null'    => true,
                'default' => null,
                'after'   => 'is_deceased',
            ],
        ]);

        // Household members
        $this->forge->addColumn('household_members', [
            'is_deceased' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'null'    => false,
                'default' => 0,
                'after'   => 'pwd_type',
            ],
            'year_of_death' => [
                'type'    => 'SMALLINT',
                'constraint' => 4,
                'unsigned' => true,
                'null'    => true,
                'default' => null,
                'after'   => 'is_deceased',
            ],
        ]);
    }

    public function down(): void
    {
        foreach (['households', 'household_members'] as $table) {
            foreach (['year_of_death', 'is_deceased'] as $column) {
                $this->db->resetDataCache();

                if ($this->db->fieldExists($column, $table)) {
                    $this->forge->dropColumn($table, $column);
                }
            }
        }
    }
}
