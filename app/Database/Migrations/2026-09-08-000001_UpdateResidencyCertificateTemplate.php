<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateResidencyCertificateTemplate extends Migration
{
    public function up()
    {
    $fields = json_encode([
      ['name' => 'resident_name',    'label' => 'Resident Name',  'type' => 'text', 'value' => 'MR. RODOLFO RELANO AND MRS. PATRICIA ELPEDES RELANO'],
      ['name' => 'civil_status',     'label' => 'Civil Status',   'type' => 'text', 'value' => 'Married'],
      ['name' => 'resident_address', 'label' => 'Address',        'type' => 'text', 'value' => 'Zone 3 Barangay Bacolod, Bato, Camarines Sur'],
      ['name' => 'purpose',          'label' => 'Purpose',         'type' => 'text', 'value' => 'reference'],
      ['name' => 'issued_day',       'label' => 'Issued Day',     'type' => 'text', 'value' => '16th'],
      ['name' => 'issued_month',     'label' => 'Issued Month',   'type' => 'text', 'value' => 'April'],
      ['name' => 'issued_year',      'label' => 'Issued Year',    'type' => 'text', 'value' => '2026'],
    ], JSON_UNESCAPED_UNICODE);

        $html = <<<'HTML'
<div class="bc-wrap" id="printable-doc">
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
        <img src="/Picture1.png" class="bc-seal" alt="Barangay Seal">
      </div>
      <div class="bc-office-bar">OFFICE OF THE PUNONG BARANGAY</div>
    </div>
    <div class="bc-body-box">
      <div class="bc-watermark"><img src="/bacolod.png" alt="watermark"></div>
      <div class="bc-doc-title">BARANGAY CERTIFICATION</div>
      <div class="bc-body-text">
        <p><strong>TO WHOM IT MAY CONCERN:</strong></p>
        <p class="bc-indent">This is to certify that <strong>{{resident_name}}</strong>, both of legal age, {{civil_status}} Filipino and a Bonafide resident of <strong>{{resident_address}}.</strong></p>
        <p class="bc-indent">This further certifies that according to the records, the above-mentioned name was living in the same household together at the address stated above.</p>
        <p class="bc-indent">This certification is issued upon the request of the interested party as <strong>{{purpose}}</strong> and for whatever legal intent this may serve.</p>
        <p class="bc-indent">Issued this <strong>{{issued_day}}</strong> day of <strong>{{issued_month}}, {{issued_year}}</strong> at <strong>{{resident_address}}. Philippines.</strong></p>
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
</div>
HTML;

        $this->db->table('document_templates')
            ->where('template_key', 'residency')
          ->update([
            'fields'     => $fields,
            'html'       => $html,
            'updated_at' => date('Y-m-d H:i:s'),
          ]);
    }

    public function down()
    {
        // The previous template remains available in migration history.
    }
}
