<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddYearOfDeathColumns extends Migration
{
    public function up(): void
    {
        // Earlier migrations in the same run cache column lists. Forge does not
        // refresh that cache, so fieldExists() would miss columns just added.
        $this->db->resetDataCache();

        // Add year_of_death to households if missing
        if (! $this->db->fieldExists('year_of_death', 'households')) {
            $this->forge->addColumn('households', [
                'year_of_death' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'is_deceased',
                ],
            ]);
        }

        $this->db->resetDataCache();

        // Add year_of_death to household_members if missing
        if (! $this->db->fieldExists('year_of_death', 'household_members')) {
            $this->forge->addColumn('household_members', [
                'year_of_death' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'is_deceased',
                ],
            ]);
        }
    }

    public function down(): void
    {
        // AddDeceasedFieldsToHouseholds creates these columns and drops them.
        // This migration only backfills a missing column, and it runs later, so
        // dropping here would remove the column before that migration rolls back.
    }
}
