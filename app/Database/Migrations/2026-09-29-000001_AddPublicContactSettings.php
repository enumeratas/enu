<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicContactSettings extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('barangay_settings')) {
            return;
        }

        $rows = [
            ['public_address', 'Barangay Bacolod, Bato, Camarines Sur, Philippines', 'Public Address', 210],
            ['public_phone', '+63 (054) 000-0000', 'Public Phone', 211],
            ['public_email', 'barangaybacolod@bato.gov.ph', 'Public Email', 212],
            ['public_hours', 'Mon – Fri: 8:00 AM – 5:00 PM', 'Office Hours', 213],
            ['public_facebook', '', 'Facebook Link', 214],
            ['public_twitter', '', 'Twitter Link', 215],
        ];

        foreach ($rows as [$key, $value, $label, $sort]) {
            $exists = $this->db->table('barangay_settings')
                ->where('setting_key', $key)
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('barangay_settings')->insert([
                    'setting_key'   => $key,
                    'setting_value' => $value,
                    'label'         => $label,
                    'group'         => 'website',
                    'sort_order'    => $sort,
                ]);
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
                'public_address',
                'public_phone',
                'public_email',
                'public_hours',
                'public_facebook',
                'public_twitter',
            ])
            ->delete();
    }
}
