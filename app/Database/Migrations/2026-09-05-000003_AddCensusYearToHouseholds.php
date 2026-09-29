<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add census_year column to households table to enable year-based filtering
 * and retrieval of historical census data.
 */
class AddCensusYearToHouseholds extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('households', [
            'census_year' => [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => false,
                'default'    => (int) date('Y'),
                'after'      => 'recorded_date',
            ],
        ]);

        // For existing rows, set census_year to YEAR(recorded_date)
        // If recorded_date is NULL, use current year
        $this->db->query(
            "UPDATE households
             SET census_year = IFNULL(YEAR(recorded_date), YEAR(NOW()))
             WHERE census_year IS NULL OR census_year = 0"
        );

        // Add index for faster year-based queries
        $this->forge->addKey('census_year', false, false, 'idx_households_census_year');
        $this->forge->processIndexes('households');
    }

    public function down(): void
    {
        $this->forge->dropKey('households', 'idx_households_census_year');
        $this->forge->dropColumn('households', 'census_year');
    }
}
