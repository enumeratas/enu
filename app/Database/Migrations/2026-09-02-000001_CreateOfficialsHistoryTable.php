<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOfficialsHistoryTable extends Migration
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
                'null'       => false,
            ],
            'full_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'The official role held (captain, secretary, sk)',
            ],
            'event' => [
                'type'       => 'ENUM',
                'constraint' => ['appointed', 'revoked'],
                'null'       => false,
            ],
            'changed_by_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User ID of the secretary/captain who made the change',
            ],
            'changed_by_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('role');
        $this->forge->addKey('created_at');
        $this->forge->createTable('officials_history');

        // Back-fill a record for every currently active official
        // so history starts from their account creation date at minimum
        $now = date('Y-m-d H:i:s');
        $db  = \Config\Database::connect();

        $officials = $db->table('users')
            ->whereIn('role', ['captain', 'secretary', 'sk', 'council'])
            ->where('status', 'active')
            ->get()->getResultArray();

        foreach ($officials as $off) {
            if ($off['username'] === 'secretary_admin') {
                continue; // skip default seeded account
            }
            $db->table('officials_history')->insert([
                'user_id'         => $off['id'],
                'full_name'       => trim($off['first_name'] . ' ' . $off['last_name']),
                'role'            => $off['role'],
                'event'           => 'appointed',
                'changed_by_id'   => null,
                'changed_by_name' => 'System (back-fill)',
                'notes'           => 'Back-filled on migration. Actual appointment date may be earlier.',
                'created_at'      => $off['created_at'] ?? $now,
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('officials_history', true);
    }
}
