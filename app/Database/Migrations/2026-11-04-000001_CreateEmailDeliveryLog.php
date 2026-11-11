<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Migration;

class CreateEmailDeliveryLog extends Migration
{
    public function up()
    {
        $db = $this->db;
        if (! $db instanceof BaseConnection) {
            return;
        }

        if (! $db->tableExists('email_delivery_log')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'dedupe_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 191,
                ],
                'recipient' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 190,
                ],
                'subject' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'pending',
                ],
                'error_message' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('dedupe_key');
            $this->forge->addKey('status');
            $this->forge->createTable('email_delivery_log');
        }

        foreach (['barangay_activities', 'sk_programs'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }
            $fields = $db->getFieldNames($table);
            $add = [];
            if (! in_array('start_time', $fields, true)) {
                $add['start_time'] = ['type' => 'TIME', 'null' => true];
            }
            if (! in_array('end_time', $fields, true)) {
                $add['end_time'] = ['type' => 'TIME', 'null' => true];
            }
            if ($add !== []) {
                $this->forge->addColumn($table, $add);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('email_delivery_log', true);
    }
}
