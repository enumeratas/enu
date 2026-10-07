<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentTemplateModel extends Model
{
    protected $table      = 'document_templates';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['template_key', 'name', 'fields', 'html', 'content'];
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
        $key = self::normalizedKey($key);

        if (! $this->tableExists()) {
            return $this->getDefaultTemplates()[$key] ?? null;
        }

        $row = $this->where('template_key', $key)->first();
        if (! $row && $key === 'business_permit') {
            $row = $this->where('template_key', 'business')->first();
        }
        if (! $row) {
            return null;
        }

        $row['fields'] = json_decode($row['fields'] ?? '[]', true) ?: [];
        $row['content'] = self::normalizeContent($row['template_key'] ?? $key, $row['content'] ?? null);

        return $row;
    }

    /** @return array<string, mixed> */
    public static function defaultTypeContent(string $key): array
    {
        $key = self::normalizedKey($key);
        $library = [
            'clearance' => [
                'title' => 'BARANGAY CLEARANCE',
                'salutation' => 'TO WHOM IT MAY CONCERN,',
                'body_html' => '<p>This is to certify that <strong>{{recipient_name}}</strong>, a legal age, <strong>{{recipient_civil_status}}</strong> and a bonafide resident of <strong>{{recipient_address}}</strong>.</p><p>He/She possessed good moral character, trustworthy, a law-abiding Filipino Citizen and cooperative to all undertakings for the progress of the community.</p><p>This Barangay Clearance is being issued upon the request of the above-named person for <strong>{{purpose}}</strong> and for whatever legal purposes it may serve.</p><p>Given this <strong>{{issued_date}}</strong> at <strong>{{full_address}}, Philippines.</strong></p>',
                'signature_label' => 'Approved by:',
                'signature_title' => 'Punong Barangay',
            ],
            'residency' => [
                'title' => 'BARANGAY CERTIFICATION',
                'salutation' => 'TO WHOM IT MAY CONCERN:',
                'body_html' => '<p>This is to certify that <strong>{{recipient_name}}</strong>, both of legal age, {{recipient_civil_status}} Filipino and a Bonafide resident of <strong>{{recipient_address}}.</strong></p><p>This further certifies that according to the records, the above-mentioned name was living in the same household together at the address stated above.</p><p>This certification is issued upon the request of the interested party as <strong>{{purpose}}</strong> and for whatever legal intent this may serve.</p><p>Issued this <strong>{{issued_date}}</strong> at <strong>{{full_address}}. Philippines.</strong></p>',
                'signature_label' => 'Attested by:',
                'signature_title' => 'Punong Barangay',
            ],
            'indigency' => [
                'title' => 'CERTIFICATE OF INDIGENCY',
                'salutation' => 'To Whom It May Concern,',
                'body_html' => '<p>This is to certify that <strong>{{recipient_name}}</strong>, legal age, {{recipient_civil_status}}, bonafide resident of <strong>{{recipient_address}}</strong> and are identified belonging to the "Indigent family" in this community as per record in this office.</p><p>This further certifies that the above-named and whose family earned meager income not enough to augment their basic needs and financial, hence an indigent and qualified to avail for <strong>{{purpose}}</strong>.</p><p>Given this <strong>{{issued_date}}</strong> at <strong>{{full_address}}, Philippines.</strong></p>',
                'signature_label' => 'Attested by:',
                'signature_title' => 'Punong Barangay',
            ],
            'good_moral' => [
                'title' => 'CERTIFICATE OF GOOD MORAL CHARACTER',
                'salutation' => 'TO WHOM IT MAY CONCERN:',
                'body_html' => '<p>This is to certify that <strong>{{recipient_name}}</strong>, legal age, {{recipient_civil_status}}, is a bonafide resident of <strong>{{recipient_address}}</strong>.</p><p>This further certifies that the above-named person is known to this office as a person of good moral character, law-abiding and with no derogatory record on file as of this date.</p><p>This certification is issued upon the request of the interested party for <strong>{{purpose}}</strong> and for whatever legal purpose it may serve.</p><p>Issued this <strong>{{issued_date}}</strong> at <strong>{{full_address}}, Philippines.</strong></p>',
                'signature_label' => 'Attested by:',
                'signature_title' => 'Punong Barangay',
            ],
            'first_time_job_seeker' => [
                'title' => 'FIRST TIME JOB SEEKER CERTIFICATE',
                'salutation' => 'TO WHOM IT MAY CONCERN,',
                'body_html' => '<p>This is to certify that <strong>{{recipient_name}}</strong>, a bonafide resident of <strong>{{recipient_address}}</strong>, is a first-time jobseeker under Republic Act No. 11261.</p><p>This certification is issued to support the applicant\'s exemption from fees for government documents required for employment purposes.</p><p>Issued this <strong>{{issued_date}}</strong> at <strong>{{full_address}}.</strong></p>',
                'signature_label' => 'Attested by:',
                'signature_title' => 'Punong Barangay',
            ],
            'solo_parent' => [
                'title' => 'SOLO PARENT CERTIFICATE',
                'salutation' => 'TO WHOM IT MAY CONCERN,',
                'body_html' => '<p>This is to certify that <strong>{{recipient_name}}</strong>, a bonafide resident of <strong>{{recipient_address}}</strong>, is registered in this barangay as a solo parent.</p><p>This certification is issued upon the request of the interested party for government benefits and other lawful purposes.</p><p>Issued this <strong>{{issued_date}}</strong> at <strong>{{full_address}}.</strong></p>',
                'signature_label' => 'Attested by:',
                'signature_title' => 'Punong Barangay',
            ],
            'business_permit' => [
                'title' => 'BUSINESS PERMIT CLEARANCE',
                'salutation' => 'TO WHOM IT MAY CONCERN,',
                'body_html' => '<p>This is to certify that <strong>{{recipient_name}}</strong> is a bonafide resident of <strong>{{recipient_address}}</strong>.</p><p>This clearance is issued upon the request of the interested party in connection with a business permit application for <strong>{{purpose}}</strong>.</p><p>Issued this <strong>{{issued_date}}</strong> at <strong>{{full_address}}.</strong></p>',
                'signature_label' => 'Approved by:',
                'signature_title' => 'Punong Barangay',
            ],
            'other_document' => [
                'title' => 'BARANGAY CERTIFICATION',
                'salutation' => 'TO WHOM IT MAY CONCERN,',
                'body_html' => '<p>This is to certify that <strong>{{recipient_name}}</strong> is a bonafide resident of <strong>{{recipient_address}}</strong>.</p><p>This certification is issued upon the request of the interested party for <strong>{{purpose}}</strong>.</p><p>Issued this <strong>{{issued_date}}</strong> at <strong>{{full_address}}.</strong></p>',
                'signature_label' => 'Attested by:',
                'signature_title' => 'Punong Barangay',
            ],
        ];

        return $library[$key] ?? $library['clearance'];
    }

    /** @return array<string, string> */
    public static function normalizedKey(string $key): string
    {
        return match ($key) {
            'business' => 'business_permit',
            'first_time_job_seekers' => 'first_time_job_seeker',
            default => $key,
        };
    }

    public static function normalizeContent(string $key, mixed $raw): array
    {
        $key = self::normalizedKey($key);
        $defaults = self::defaultTypeContent($key);
        $saved = [];
        if (is_array($raw)) {
            $saved = $raw;
        } elseif (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            $saved = is_array($decoded) ? $decoded : [];
        }

        return [
            'title' => trim((string) ($saved['title'] ?? $defaults['title'])),
            'salutation' => trim((string) ($saved['salutation'] ?? $defaults['salutation'])),
            'body_html' => sanitize_document_html((string) ($saved['body_html'] ?? $defaults['body_html'])),
            'signature_label' => trim((string) ($saved['signature_label'] ?? $defaults['signature_label'])),
            'signature_title' => trim((string) ($saved['signature_title'] ?? $defaults['signature_title'])),
        ];
    }

    /** @return array<string, array<string, string>> */
    public function allTypeContents(): array
    {
        $keys = ['clearance', 'residency', 'indigency', 'good_moral', 'first_time_job_seeker', 'solo_parent', 'business_permit', 'other_document'];
        $map = [];
        $hasContent = $this->tableExists() && in_array('content', $this->db->getFieldNames($this->table), true);
        foreach ($keys as $key) {
            $row = $hasContent ? $this->where('template_key', $key)->first() : null;
            $map[$key] = self::normalizeContent($key, $row['content'] ?? null);
        }

        return $map;
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
