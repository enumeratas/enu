<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeceasedAccountStatus extends Migration
{
    public function up()
    {
        $this->db->query(
            "ALTER TABLE `users`
             MODIFY `status` ENUM('unverified','pending','active','rejected','deceased')
             NOT NULL DEFAULT 'unverified'"
        );

        if ($this->db->tableExists('barangay_settings')) {
            $exists = $this->db->table('barangay_settings')
                ->where('setting_key', 'deceased_account_action')
                ->countAllResults();
            if ($exists === 0) {
                $this->db->table('barangay_settings')->insert([
                    'setting_key'   => 'deceased_account_action',
                    'setting_value' => 'block',
                    'label'         => 'Deceased account action',
                    'group'         => 'accounts',
                    'sort_order'    => 90,
                ]);
            }
        }
    }

    public function down()
    {
        $this->db->query(
            "UPDATE `users` SET `status` = 'rejected' WHERE `status` = 'deceased'"
        );
        $this->db->query(
            "ALTER TABLE `users`
             MODIFY `status` ENUM('unverified','pending','active','rejected')
             NOT NULL DEFAULT 'unverified'"
        );
        if ($this->db->tableExists('barangay_settings')) {
            $this->db->table('barangay_settings')
                ->where('setting_key', 'deceased_account_action')
                ->delete();
        }
    }
}
