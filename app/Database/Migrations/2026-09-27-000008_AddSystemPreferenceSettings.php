<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSystemPreferenceSettings extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('barangay_settings')) {
            return;
        }

        $rows = [
            [
                'setting_key'   => 'email_notifications',
                'setting_value' => '1',
                'label'         => 'Email Notifications',
                'group'         => 'system',
                'sort_order'    => 200,
            ],
            [
                'setting_key'   => 'auto_approve_clearances',
                'setting_value' => '0',
                'label'         => 'Auto-approve Clearances',
                'group'         => 'system',
                'sort_order'    => 201,
            ],
            [
                'setting_key'   => 'account_approval_alerts',
                'setting_value' => '1',
                'label'         => 'Account Approval Alerts',
                'group'         => 'system',
                'sort_order'    => 202,
            ],
        ];

        foreach ($rows as $row) {
            $exists = $this->db->table('barangay_settings')
                ->where('setting_key', $row['setting_key'])
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('barangay_settings')->insert($row);
            }
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('barangay_settings')) {
            return;
        }

        $this->db->table('barangay_settings')
            ->whereIn('setting_key', [
                'email_notifications',
                'auto_approve_clearances',
                'account_approval_alerts',
            ])
            ->delete();
    }
}
