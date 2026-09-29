<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIdToSkYouth extends Migration
{
    public function up()
    {
        $this->forge->addColumn('sk_youth', [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        $this->db->query('ALTER TABLE sk_youth ADD INDEX idx_sk_youth_user_id (user_id)');
        $this->db->query('ALTER TABLE sk_youth ADD CONSTRAINT fk_sk_youth_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE sk_youth DROP FOREIGN KEY fk_sk_youth_user');
        $this->db->query('ALTER TABLE sk_youth DROP INDEX idx_sk_youth_user_id');
        $this->forge->dropColumn('sk_youth', 'user_id');
    }
}
