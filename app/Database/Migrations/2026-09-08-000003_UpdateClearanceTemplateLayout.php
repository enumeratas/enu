<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateClearanceTemplateLayout extends Migration
{
    public function up()
    {
        $html = <<<'HTML'
<div class="bc-wrap" id="printable-doc">
  <div class="bc-page">
    <div class="bc-top-box">
      <div class="bc-header-row">
        <img src="/bacolod.png" class="bc-seal" alt="Bacolod Seal">
        <div class="bc-header-center">
          <p>{{country}}</p><p>{{region}}</p><p>{{province}}</p><p>{{municipality}}</p>
          <p><strong>{{barangay_name}}</strong></p><p class="bc-oOo">-oOo-</p>
        </div>
        <img src="/Picture1.png" class="bc-seal" alt="Barangay Seal">
      </div>
      <div class="bc-office-bar"><strong>{{office_header}}</strong></div>
    </div>
    <div class="bc-body-box">
      <div class="bc-watermark"><img src="/bacolod.png" alt="watermark"></div>
      <div class="bc-doc-title">BARANGAY CLEARANCE</div>
      <div class="bc-body-text">
        <p><strong>TO WHOM IT MAY CONCERN,</strong></p>
        <p class="bc-indent">This is to certify that <span class="bc-line bc-line-name"><strong>{{recipient_name}}</strong></span>, <strong>a legal age</strong>, <strong>{{recipient_civil_status}}</strong> and a bonafide resident of <span class="bc-line bc-line-address"><strong>{{recipient_address}}</strong></span>.</p>
        <p class="bc-indent">He/She possessed good moral character, trustworthy, a law-abiding Filipino Citizen and cooperative to all undertakings for the progress of the community.</p>
        <p class="bc-indent">This Barangay Clearance is being issued upon the request of the above-named person for <strong>{{purpose}}</strong> and for whatever legal purposes it may serve.</p>
        <p class="bc-indent">Given this <span class="bc-line bc-line-date"><strong>{{issued_day}}</strong></span> day of <span class="bc-line bc-line-month"><strong>{{issued_month}}, {{issued_year}}</strong></span> at <strong>{{full_address}}, Philippines.</strong></p>
      </div>
      <div class="bc-sig-section">
        <div class="bc-sig-left"><div class="bc-sig-line"></div><div class="bc-sig-sub">(Signature of Applicant)</div></div>
        <div class="bc-sig-right">
          <p class="bc-approved-by">Approved by:</p>
          <p class="bc-captain-name" style="padding-top:12mm;"><strong>{{captain_name}}</strong></p>
          <p class="bc-captain-title">{{captain_title}}</p>
        </div>
      </div>
      <div class="bc-footer-info">
        <p>CTC No.&nbsp; : _______________</p>
        <p>Issued at : <strong><u>{{full_address}}</u></strong></p>
        <p>Issued on: <strong><u>{{issued_month}} {{issued_day}}, {{issued_year}}</u></strong></p>
        <div class="bc-photo-row"><div class="bc-photo-box"></div><div class="bc-photo-box"></div></div>
        <p>OR. No.&nbsp;&nbsp; : _______________</p>
        <p>Issued at : <strong><u>{{full_address}}</u></strong></p>
        <p>Issued on: <strong><u>{{issued_month}} {{issued_day}}, {{issued_year}}</u></strong></p>
      </div>
    </div>
  </div>
</div>
HTML;

        $this->db->table('document_templates')
            ->where('template_key', 'clearance')
            ->update(['html' => $html, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function down()
    {
        // Keep the improved document layout when rolling back other migrations.
    }
}
