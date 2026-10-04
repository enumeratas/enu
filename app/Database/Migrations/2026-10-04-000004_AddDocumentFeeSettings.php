<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\ClearanceDocuments;

class AddDocumentFeeSettings extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('barangay_settings')) {
            return;
        }

        $labels = [
            'clearance_fee' => 'Barangay Clearance',
            'residency_fee' => 'Certificate of Residency',
            'indigency_fee' => 'Certificate of Indigency',
        ];
        foreach ($labels as $key => $label) {
            $this->db->table('barangay_settings')
                ->where('setting_key', $key)
                ->update(['label' => $label, 'group' => 'fees']);
        }

        foreach (ClearanceDocuments::FEES as $meta) {
            $exists = $this->db->table('barangay_settings')
                ->where('setting_key', $meta['key'])
                ->countAllResults();
            if ($exists > 0) {
                continue;
            }
            $this->db->table('barangay_settings')->insert([
                'setting_key'   => $meta['key'],
                'setting_value' => $meta['default'],
                'label'         => $meta['label'],
                'group'         => 'fees',
                'sort_order'    => $meta['sort'],
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('barangay_settings')) {
            return;
        }

        $this->db->table('barangay_settings')
            ->whereIn('setting_key', [
                'good_moral_fee',
                'job_seeker_fee',
                'solo_parent_fee',
                'business_permit_fee',
                'medical_fee',
                'philhealth_fee',
                'scholarship_fee',
                'employment_fee',
                'other_document_fee',
            ])
            ->delete();
    }
}
