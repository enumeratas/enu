<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationDismissals extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'item_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'item_ref' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'dismissed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['user_id', 'item_type', 'item_ref']);
        $this->forge->addKey('user_id');
        $this->forge->createTable('notification_dismissals');
    }

    public function down()
    {
        $this->forge->dropTable('notification_dismissals');
    }
}
