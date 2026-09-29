<?php

namespace App\Controllers;

use App\Models\DocumentTemplateModel;
use App\Models\BarangaySettingsModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class DocumentTemplateController extends BaseController
{
    protected DocumentTemplateModel  $templateModel;
    protected BarangaySettingsModel  $settingsModel;

    public function __construct()
    {
        $this->templateModel = new DocumentTemplateModel();
        $this->settingsModel = new BarangaySettingsModel();
    }

    /**
     * Build the barangay settings array with captain_name always resolved
     * live from the users table (never from the static stored value).
     */
    private function getSettings(): array
    {
        // getAll() already overrides captain_name with the live value
        return $this->settingsModel->getAll();
    }

    // ── Document Templates ────────────────────────────────────────────────────

    /**
     * Insert any missing template rows that were added after the initial migration.
     * This is idempotent — safe to call on every page load (uses INSERT IGNORE pattern).
     */
    private function seedMissingTemplates(): void
    {
        if (! $this->templateModel->tableExists()) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $missing = [
            'first_time_job_seeker' => [
                'template_key' => 'first_time_job_seeker',
                'name'         => 'First Time Job Seeker Certificate',
                'fields'       => json_encode([
                    ['name' => 'applicant_name',    'label' => 'Applicant Name', 'type' => 'text',     'value' => 'Juan Dela Cruz'],
                    ['name' => 'applicant_age',     'label' => 'Age',            'type' => 'text',     'value' => '18'],
                    ['name' => 'applicant_address', 'label' => 'Address',        'type' => 'text',     'value' => 'Zone 1, Barangay Bacolod, Bato, Camarines Sur'],
                    ['name' => 'issued_day',        'label' => 'Issued Day',     'type' => 'text',     'value' => '1st'],
                    ['name' => 'issued_month',      'label' => 'Issued Month',   'type' => 'text',     'value' => 'January, 2026'],
                    ['name' => 'issued_year',       'label' => 'Issued Year',    'type' => 'text',     'value' => '2026'],
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
            'other_document' => [
                'template_key' => 'other_document',
                'name'         => 'Other Document',
                'fields'       => json_encode([
                    ['name' => 'recipient_name',    'label' => 'Recipient Name',  'type' => 'text',     'value' => 'Juan Dela Cruz'],
                    ['name' => 'recipient_address', 'label' => 'Address',         'type' => 'text',     'value' => 'Barangay Bacolod, Bato, Camarines Sur'],
                    ['name' => 'document_title',    'label' => 'Document Title',  'type' => 'text',     'value' => 'BARANGAY CERTIFICATION'],
                    ['name' => 'body_text',         'label' => 'Body Text',       'type' => 'textarea', 'value' => 'This is to certify that the above-named person is known to this office.'],
                    ['name' => 'purpose',           'label' => 'Purpose',         'type' => 'text',     'value' => 'whatever legal purpose it may serve'],
                    ['name' => 'issued_day',        'label' => 'Issued Day',      'type' => 'text',     'value' => '1st'],
                    ['name' => 'issued_month',      'label' => 'Issued Month',    'type' => 'text',     'value' => 'January, 2026'],
                    ['name' => 'issued_year',       'label' => 'Issued Year',     'type' => 'text',     'value' => '2026'],
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

        $db = \Config\Database::connect();
        foreach ($missing as $key => $data) {
            $exists = $db->table('document_templates')
                ->where('template_key', $key)
                ->countAllResults();
            if (! $exists) {
                $db->table('document_templates')->insert($data);
            }
        }
    }

    public function edit(string $key)
    {
        $template = $this->templateModel->getTemplate($key);
        if (! $template) {
            throw new PageNotFoundException('Template not found');
        }

        return view('dashboard/secretary/document_templates_edit', [
            'template'         => $template,
            'barangaySettings' => $this->getSettings(),
        ]);
    }

    public function update(string $key)
    {
        $template = $this->templateModel->getTemplate($key);
        if (! $template) {
            return redirect()->back()->with('error', 'Template not found');
        }

        $fields = $template['fields'];
        foreach ($fields as &$field) {
            if (! empty($field['name'])) {
                $posted = $this->request->getPost($field['name']);
                if ($posted !== null) {
                    $field['value'] = $posted;
                }
            }
        }
        unset($field);

        $newHtml = $this->request->getPost('html');
        $updateData = [
            'fields' => json_encode($fields, JSON_UNESCAPED_UNICODE),
            'html'   => $newHtml,
        ];

        if (! $this->templateModel->update($template['id'], $updateData)) {
            return redirect()->back()->with('error', 'Unable to save template changes');
        }

        return redirect()->to(site_url(session_role() . '/clearance/templates/edit/' . $key))
            ->with('success', 'Template updated successfully');
    }

    // ── Barangay Settings ─────────────────────────────────────────────────────

    /**
     * Show the barangay information settings page.
     */
    public function barangaySettings()
    {
        return view('dashboard/secretary/barangay_settings', [
            'settingsGrouped'  => $this->settingsModel->getAllGrouped(),
            'barangaySettings' => $this->getSettings(),
        ]);
    }

    /**
     * Show the report front-page editor.
     */
    public function reportFrontPageSettings()
    {
        return view('dashboard/secretary/report_front_page', [
            'reportFrontPage' => $this->settingsModel->getReportFrontPageSettings(),
            'role'           => 'secretary',
        ]);
    }

    /**
     * Save values for the report front page cover.
     */
    public function saveReportFrontPageSettings()
    {
        if (! $this->settingsModel->tableExists()) {
            return redirect()->back()->with('error', 'Please run database migrations first.');
        }

        $post = $this->request->getPost();
        unset($post[csrf_token()]);

        $this->settingsModel->saveReportFrontPageData($post);

        return redirect()->to(site_url(session_role() . '/reports/front-page'))
            ->with('success', 'Report front page information updated successfully.');
    }

    /**
     * Save updated barangay settings.
     */
    public function saveBarangaySettings()
    {
        if (! $this->settingsModel->tableExists()) {
            return redirect()->back()->with('error', 'Please run database migrations first.');
        }

        $post = $this->request->getPost();
        // Strip CSRF and non-setting fields
        unset($post[csrf_token()]);

        // Only save known keys that exist in the DB
        $this->settingsModel->saveAll($post);

        return redirect()->to(site_url(session_role() . '/barangay-settings'))
            ->with('success', 'Barangay information updated successfully.');
    }
}
