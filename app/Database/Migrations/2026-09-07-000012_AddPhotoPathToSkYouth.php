<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoPathToSkYouth extends Migration
{
    public function up()
    {
        $this->forge->addColumn('sk_youth', [
            'photo_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'user_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('sk_youth', 'photo_path');
    }
}
