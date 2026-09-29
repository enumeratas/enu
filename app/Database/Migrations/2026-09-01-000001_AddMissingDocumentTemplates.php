<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingDocumentTemplates extends Migration
{
    public function up()
    {
        $now       = date('Y-m-d H:i:s');
        $templates = [
            [
                'template_key' => 'first_time_job_seeker',
                'name'         => 'First Time Job Seeker Certificate',
                'fields'       => json_encode([
                    ['name' => 'applicant_name',    'label' => 'Applicant Name',    'type' => 'text', 'value' => 'Juan Dela Cruz'],
                    ['name' => 'applicant_age',     'label' => 'Age',               'type' => 'text', 'value' => '18'],
                    ['name' => 'applicant_address', 'label' => 'Address',           'type' => 'text', 'value' => 'Zone 1, Barangay Bacolod, Bato, Camarines Sur'],
                    ['name' => 'issued_day',        'label' => 'Issued Day',        'type' => 'text', 'value' => '1st'],
                    ['name' => 'issued_month',      'label' => 'Issued Month',      'type' => 'text', 'value' => 'January, 2026'],
                    ['name' => 'issued_year',       'label' => 'Issued Year',       'type' => 'text', 'value' => '2026'],
                ], JSON_UNESCAPED_UNICODE),
                'html' => '<div class="bc-wrap" id="printable-doc">
  <div class="bc-page">
    <div class="bc-top-box">
      <div class="bc-header-row">
        <img src="/bacolod.png" class="bc-seal" alt="Bacolod Seal">
        <div class="bc-header-center">
          <p>Republic of the Philippines</p>
          <p>Region V</p>
          <p>Province of Camarines Sur</p>
          <p>Municipality of Bato</p>
          <p><strong>BARANGAY BACOLOD</strong></p>
          <p class="bc-oOo">-oOo-</p>
        </div>
        <img src="/bacolod.png" class="bc-seal" alt="Bacolod Seal">
      </div>
      <div class="bc-office-bar">OFFICE OF THE PUNONG BARANGAY</div>
    </div>
    <div class="bc-body-box">
      <div class="bc-watermark"><img src="/bacolod.png" alt="watermark"></div>
      <div class="bc-doc-title">FIRST TIME JOB SEEKER CERTIFICATE</div>
      <div class="bc-doc-subtitle" style="text-align:center;font-size:11pt;color:#555;margin-bottom:12pt;">(Pursuant to Republic Act No. 11261)</div>
      <div class="bc-body-text">
        <p><strong>TO WHOM IT MAY CONCERN,</strong></p>
        <p class="bc-indent">This is to certify that <strong>{{applicant_name}}</strong>, <strong>{{applicant_age}}</strong> years old, a bonafide resident of <strong>{{applicant_address}}</strong>, is a first-time jobseeker as defined under Republic Act No. 11261 (First Time Jobseekers Assistance Act).</p>
        <p class="bc-indent">This certification is issued to exempt the applicant from payment of fees for government documents and transactions in connection with employment.</p>
        <p class="bc-indent">Issued this <strong>{{issued_day}}</strong> day of <strong>{{issued_month}}</strong>, <strong>{{issued_year}}</strong> at Barangay Bacolod, Bato, Camarines Sur.</p>
      </div>
      <div class="bc-sig-section" style="justify-content:flex-end;">
        <div style="text-align:center;">
          <p class="bc-approved-by">Attested by:</p>
          <p class="bc-captain-name">{{captain_name}}</p>
          <p class="bc-captain-title">Punong Barangay</p>
        </div>
      </div>
    </div>
  </div>
</div>',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_key' => 'other_document',
                'name'         => 'Other Document',
                'fields'       => json_encode([
                    ['name' => 'recipient_name',    'label' => 'Recipient Name',    'type' => 'text',     'value' => 'Juan Dela Cruz'],
                    ['name' => 'recipient_address', 'label' => 'Address',           'type' => 'text',     'value' => 'Barangay Bacolod, Bato, Camarines Sur'],
                    ['name' => 'document_title',    'label' => 'Document Title',    'type' => 'text',     'value' => 'BARANGAY CERTIFICATION'],
                    ['name' => 'body_text',         'label' => 'Body Text',         'type' => 'textarea', 'value' => 'This is to certify that the above-named person is known to this office.'],
                    ['name' => 'purpose',           'label' => 'Purpose',           'type' => 'text',     'value' => 'whatever legal purpose it may serve'],
                    ['name' => 'issued_day',        'label' => 'Issued Day',        'type' => 'text',     'value' => '1st'],
                    ['name' => 'issued_month',      'label' => 'Issued Month',      'type' => 'text',     'value' => 'January, 2026'],
                    ['name' => 'issued_year',       'label' => 'Issued Year',       'type' => 'text',     'value' => '2026'],
                ], JSON_UNESCAPED_UNICODE),
                'html' => '<div class="bc-wrap" id="printable-doc">
  <div class="bc-page">
    <div class="bc-top-box">
      <div class="bc-header-row">
        <img src="/bacolod.png" class="bc-seal" alt="Bacolod Seal">
        <div class="bc-header-center">
          <p>Republic of the Philippines</p>
          <p>Region V</p>
          <p>Province of Camarines Sur</p>
          <p>Municipality of Bato</p>
          <p><strong>BARANGAY BACOLOD</strong></p>
          <p class="bc-oOo">-oOo-</p>
        </div>
        <img src="/bacolod.png" class="bc-seal" alt="Bacolod Seal">
      </div>
      <div class="bc-office-bar">OFFICE OF THE PUNONG BARANGAY</div>
    </div>
    <div class="bc-body-box">
      <div class="bc-watermark"><img src="/bacolod.png" alt="watermark"></div>
      <div class="bc-doc-title">{{document_title}}</div>
      <div class="bc-body-text">
        <p><strong>TO WHOM IT MAY CONCERN,</strong></p>
        <p class="bc-indent">{{body_text}}</p>
        <p class="bc-indent">This is issued for <strong>{{purpose}}</strong>.</p>
        <p class="bc-indent">Issued this <strong>{{issued_day}}</strong> day of <strong>{{issued_month}}</strong>, <strong>{{issued_year}}</strong> at Barangay Bacolod, Bato, Camarines Sur.</p>
      </div>
      <div class="bc-sig-section" style="justify-content:flex-end;">
        <div style="text-align:center;">
          <p class="bc-approved-by">Attested by:</p>
          <p class="bc-captain-name">{{captain_name}}</p>
          <p class="bc-captain-title">Punong Barangay</p>
        </div>
      </div>
    </div>
  </div>
</div>',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($templates as $t) {
            // Only insert if the key doesn't already exist
            $exists = $this->db->table('document_templates')
                ->where('template_key', $t['template_key'])
                ->countAllResults();
            if (! $exists) {
                $this->db->table('document_templates')->insert($t);
            }
        }
    }

    public function down()
    {
        $this->db->table('document_templates')
            ->whereIn('template_key', ['first_time_job_seeker', 'other_document'])
            ->delete();
    }
}
