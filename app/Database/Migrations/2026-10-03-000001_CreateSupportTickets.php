<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSupportTickets extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('support_tickets')) {
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
                'target_role' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 160,
                ],
                'concern' => [
                    'type' => 'TEXT',
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'pending',
                ],
                'conversation_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'reviewed_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'reviewed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addKey('user_id');
            $this->forge->addKey(['target_role', 'status']);
            $this->forge->createTable('support_tickets');
        }

        if ($this->db->tableExists('chat_conversations')) {
            $fields = $this->db->getFieldNames('chat_conversations');
            if (is_array($fields) && ! in_array('assigned_role', $fields, true)) {
                $this->forge->addColumn('chat_conversations', [
                    'assigned_role' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 20,
                        'null'       => true,
                        'after'      => 'assigned_staff_id',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('support_tickets')) {
            $this->forge->dropTable('support_tickets');
        }

        if ($this->db->tableExists('chat_conversations')) {
            $fields = $this->db->getFieldNames('chat_conversations');
            if (is_array($fields) && in_array('assigned_role', $fields, true)) {
                $this->forge->dropColumn('chat_conversations', 'assigned_role');
            }
        }
    }
}
