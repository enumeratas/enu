<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentTemplateModel extends Model
{
    protected $table      = 'document_templates';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['template_key', 'name', 'fields', 'html'];
    protected $useTimestamps = true;

    public function tableExists(): bool
    {
        return $this->db !== null && $this->db->tableExists($this->table);
    }

    protected function normalizeRows(array $rows): array
    {
        foreach ($rows as &$row) {
            if (! is_array($row)) {
                continue;
            }
            $row['fields'] = json_decode($row['fields'] ?? '[]', true) ?: [];
        }

        return $rows;
    }

    public function getDefaultTemplates(): array
    {
        return [
            'clearance' => [
                'id' => 0,
                'template_key' => 'clearance',
                'name' => 'Barangay Clearance',
                'fields' => [],
                'html' => '<div class="doc-template"><div class="doc-header"><h2>Barangay Clearance</h2></div><p>Template data is missing because the document_templates table has not been created yet.</p></div>',
            ],
            'residency' => [
                'id' => 0,
                'template_key' => 'residency',
                'name' => 'Barangay Certification',
                'fields' => [],
                'html' => '<div class="doc-template"><div class="doc-header"><h2>Barangay Certification</h2></div><p>Template data is missing because the document_templates table has not been created yet.</p></div>',
            ],
        ];
    }

    public function getTemplate(string $key): ?array
    {
        if (! $this->tableExists()) {
            return $this->getDefaultTemplates()[$key] ?? null;
        }

        $row = $this->where('template_key', $key)->first();
        if (! $row) {
            return null;
        }

        $row['fields'] = json_decode($row['fields'] ?? '[]', true) ?: [];
        return $row;
    }

    public function getTemplatesIndexedByKey(): array
    {
        if (! $this->tableExists()) {
            return $this->getDefaultTemplates();
        }

        $this->ensureSeeded();

        $rows = $this->normalizeRows($this->orderBy('id')->findAll());
        return array_column($rows, null, 'template_key');
    }

    public function getAllTemplates(): array
    {
        if (! $this->tableExists()) {
            return array_values($this->getDefaultTemplates());
        }

        $this->ensureSeeded();

        return $this->normalizeRows($this->orderBy('id')->findAll());
    }

    /**
     * Insert any missing template rows that were added after the initial migration.
     * Idempotent — safe to call on every request.
     */
    private function ensureSeeded(): void
    {
        $now = date('Y-m-d H:i:s');

        $missing = [
            'first_time_job_seeker' => [
                'template_key' => 'first_time_job_seeker',
                'name'         => 'First Time Job Seeker Certificate',
                'fields'       => json_encode([
                    ['name' => 'applicant_name',    'label' => 'Applicant Name', 'type' => 'text', 'value' => 'Juan Dela Cruz'],
                    ['name' => 'applicant_age',     'label' => 'Age',            'type' => 'text', 'value' => '18'],
                    ['name' => 'applicant_address', 'label' => 'Address',        'type' => 'text', 'value' => 'Zone 1, Barangay Bacolod, Bato, Camarines Sur'],
                    ['name' => 'issued_day',        'label' => 'Issued Day',     'type' => 'text', 'value' => '1st'],
                    ['name' => 'issued_month',      'label' => 'Issued Month',   'type' => 'text', 'value' => 'January, 2026'],
                    ['name' => 'issued_year',       'label' => 'Issued Year',    'type' => 'text', 'value' => '2026'],
                ], JSON_UNESCAPED_UNICODE),
                'html'       => '<div class="bc-wrap" id="printable-doc"><div class="bc-page"><div class="bc-top-box"><div class="bc-header-row"><img src="/bacolod.png" class="bc-seal" alt="Bacolod Seal"><div class="bc-header-center"><p>Republic of the Philippines</p><p>Region V</p><p>Province of Camarines Sur</p><p>Municipality of Bato</p><p><strong>BARANGAY BACOLOD</strong></p><p class="bc-oOo">-oOo-</p></div><img src="/Picture1.png" class="bc-seal" alt="Bacolod Seal"></div><div class="bc-office-bar">OFFICE OF THE PUNONG BARANGAY</div></div><div class="bc-body-box"><div class="bc-watermark"><img src="/bacolod.png" alt="watermark"></div><div class="bc-doc-title">FIRST TIME JOB SEEKER CERTIFICATE</div><div class="bc-body-text"><p><strong>TO WHOM IT MAY CONCERN,</strong></p><p class="bc-indent">This is to certify that <strong>{{applicant_name}}</strong>, <strong>{{applicant_age}}</strong> years old, a bonafide resident of <strong>{{applicant_address}}</strong>, is a first-time jobseeker as defined under Republic Act No. 11261 (First Time Jobseekers Assistance Act).</p><p class="bc-indent">This certification is issued to exempt the applicant from payment of fees for government documents and transactions in connection with employment.</p><p class="bc-indent">Issued this <strong>{{issued_day}}</strong> day of <strong>{{issued_month}}</strong>, <strong>{{issued_year}}</strong> at Barangay Bacolod, Bato, Camarines Sur.</p></div><div class="bc-sig-section" style="justify-content:flex-end;"><div style="text-align:center;"><p class="bc-approved-by">Attested by:</p><p class="bc-captain-name">{{captain_name}}</p><p class="bc-captain-title">Punong Barangay</p></div></div></div></div></div>',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'other_document' => [
                'template_key' => 'other_document',
                'name'         => 'Other Document',
                'fields'       => json_encode([
                    ['name' => 'recipient_name',    'label' => 'Recipient Name', 'type' => 'text',     'value' => 'Juan Dela Cruz'],
                    ['name' => 'recipient_address', 'label' => 'Address',        'type' => 'text',     'value' => 'Barangay Bacolod, Bato, Camarines Sur'],
                    ['name' => 'document_title',    'label' => 'Document Title', 'type' => 'text',     'value' => 'BARANGAY CERTIFICATION'],
                    ['name' => 'body_text',         'label' => 'Body Text',      'type' => 'textarea', 'value' => 'This is to certify that the above-named person is known to this office.'],
                    ['name' => 'purpose',           'label' => 'Purpose',        'type' => 'text',     'value' => 'whatever legal purpose it may serve'],
                    ['name' => 'issued_day',        'label' => 'Issued Day',     'type' => 'text',     'value' => '1st'],
                    ['name' => 'issued_month',      'label' => 'Issued Month',   'type' => 'text',     'value' => 'January, 2026'],
                    ['name' => 'issued_year',       'label' => 'Issued Year',    'type' => 'text',     'value' => '2026'],
                ], JSON_UNESCAPED_UNICODE),
                'html'       => '<div class="bc-wrap" id="printable-doc"><div class="bc-page"><div class="bc-top-box"><div class="bc-header-row"><img src="/bacolod.png" class="bc-seal" alt="Bacolod Seal"><div class="bc-header-center"><p>Republic of the Philippines</p><p>Region V</p><p>Province of Camarines Sur</p><p>Municipality of Bato</p><p><strong>BARANGAY BACOLOD</strong></p><p class="bc-oOo">-oOo-</p></div><img src="/Picture1.png" class="bc-seal" alt="Bacolod Seal"></div><div class="bc-office-bar">OFFICE OF THE PUNONG BARANGAY</div></div><div class="bc-body-box"><div class="bc-watermark"><img src="/bacolod.png" alt="watermark"></div><div class="bc-doc-title">{{document_title}}</div><div class="bc-body-text"><p><strong>TO WHOM IT MAY CONCERN,</strong></p><p class="bc-indent">{{body_text}}</p><p class="bc-indent">This is issued for <strong>{{purpose}}</strong>.</p><p class="bc-indent">Issued this <strong>{{issued_day}}</strong> day of <strong>{{issued_month}}</strong>, <strong>{{issued_year}}</strong> at Barangay Bacolod, Bato, Camarines Sur.</p></div><div class="bc-sig-section" style="justify-content:flex-end;"><div style="text-align:center;"><p class="bc-approved-by">Attested by:</p><p class="bc-captain-name">{{captain_name}}</p><p class="bc-captain-title">Punong Barangay</p></div></div></div></div></div>',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($missing as $key => $data) {
            $exists = $this->db->table($this->table)
                ->where('template_key', $key)
                ->countAllResults();
            if (! $exists) {
                $this->db->table($this->table)->insert($data);
            }
        }
    }
}
