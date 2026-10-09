<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBarangayTransferFields extends Migration
{
    public function up()
    {
        $fields  = $this->db->getFieldNames('household_moves');
        $columns = [];

        if (! in_array('destination_barangay', $fields, true)) {
            $columns['destination_barangay'] = [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'after'      => 'notes',
            ];
        }

        if (! in_array('destination_barangay_email', $fields, true)) {
            $columns['destination_barangay_email'] = [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
                'after'      => 'destination_barangay',
            ];
        }

        if (! in_array('transfer_confirmation_sent_at', $fields, true)) {
            $columns['transfer_confirmation_sent_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'destination_barangay_email',
            ];
        }

        if (! in_array('transfer_confirmed_at', $fields, true)) {
            $columns['transfer_confirmed_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'transfer_confirmation_sent_at',
            ];
        }

        if (! in_array('is_barangay_transfer', $fields, true)) {
            $columns['is_barangay_transfer'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'transfer_confirmed_at',
            ];
        }

        if ($columns !== []) {
            $this->forge->addColumn('household_moves', $columns);
        }
    }

    public function down()
    {
        $fields = $this->db->getFieldNames('household_moves');
        foreach (['is_barangay_transfer', 'transfer_confirmed_at', 'transfer_confirmation_sent_at', 'destination_barangay_email', 'destination_barangay'] as $column) {
            if (in_array($column, $fields, true)) {
                $this->forge->dropColumn('household_moves', $column);
            }
        }
    }
}
