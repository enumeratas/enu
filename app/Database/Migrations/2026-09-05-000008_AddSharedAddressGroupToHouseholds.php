<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds shared dwelling identification columns to households.
 *
 * shared_address_group — a short auto-generated key (e.g. "SHR-12345") that
 *   groups multiple household records that share the same physical address.
 *   NULL when the household is not Shared.
 *
 * family_number — ordinal position of this family within the shared address
 *   (1 = first family registered, 2 = second, etc.). 1 when not Shared.
 */
class AddSharedAddressGroupToHouseholds extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('households', [
            'shared_address_group' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'num_families',
            ],
            'family_number' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 1,
                'after'      => 'shared_address_group',
            ],
        ]);

        // Index for fast group lookups
        $this->forge->addKey('shared_address_group', false, false, 'idx_shared_address_group');
        $this->forge->processIndexes('households');
    }

    public function down(): void
    {
        $this->forge->dropKey('households', 'idx_shared_address_group');
        $this->forge->dropColumn('households', ['shared_address_group', 'family_number']);
    }
}
