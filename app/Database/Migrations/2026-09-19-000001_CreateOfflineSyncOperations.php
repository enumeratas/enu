<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOfflineSyncOperations extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'operation_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'operation' => ['type' => 'VARCHAR', 'constraint' => 80],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('operation_id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('offline_sync_operations');
    }

    public function down()
    {
        $this->forge->dropTable('offline_sync_operations', true);
    }
}
