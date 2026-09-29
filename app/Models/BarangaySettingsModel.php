<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangaySettingsModel extends Model
{
    protected $table      = 'barangay_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['setting_key', 'setting_value', 'label', 'group', 'sort_order'];

    /** Check whether the table has been created yet (migration guard). */
    public function tableExists(): bool
    {
        return $this->db !== null && $this->db->tableExists($this->table);
    }

    /**
     * Always read the active captain's full name from the users table.
     * Returns an uppercased string or empty string if no captain is appointed.
     */
    public function getLiveCaptainName(): string
    {
        $row = $this->db->table('users')
            ->select('first_name, middle_name, last_name')
            ->where('role', 'captain')
            ->where('status', 'active')
            ->get()
            ->getRowArray();

        if (! $row) {
            return '';
        }

        $parts = array_filter([
            trim($row['first_name']   ?? ''),
            trim($row['middle_name']  ?? ''),
            trim($row['last_name']    ?? ''),
        ]);

        return strtoupper(implode(' ', $parts));
    }

    /**
     * Return all settings as a flat key→value map.
     * captain_name is always overridden with the live value from users table.
     * Falls back to safe defaults if the table does not exist yet.
     *
     * @return array<string, string>
     */
    public function getAll(): array
    {
        if (! $this->tableExists()) {
            $defaults = $this->getDefaults();
            $defaults['captain_name'] = $this->getLiveCaptainName() ?: $defaults['captain_name'];
            return $defaults;
        }

        $rows = $this->orderBy('sort_order')->findAll();
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['setting_key']] = $row['setting_value'] ?? '';
        }

        $merged = array_merge($this->getDefaults(), $map);

        // Always override captain_name with the live appointed captain
        $liveCaptain = $this->getLiveCaptainName();
        if ($liveCaptain !== '') {
            $merged['captain_name'] = $liveCaptain;
        }

        return $merged;
    }

    /**
     * Return all rows grouped by their 'group' column (for the edit form).
     * captain_name row is excluded — it's managed automatically from users table.
     *
     * @return array<string, array>
     */
    public function getAllGrouped(): array
    {
        if (! $this->tableExists()) {
            return [];
        }

        $rows   = $this->orderBy('sort_order')->findAll();
        $groups = [];
        foreach ($rows as $row) {
            // Skip captain_name — it's always pulled live from users table
            if ($row['setting_key'] === 'captain_name') {
                continue;
            }
            $groups[$row['group']][] = $row;
        }

        return $groups;
    }

    /**
     * Get a single setting value by key.
     * captain_name is always resolved live from users table.
     */
    public function getValue(string $key, string $default = ''): string
    {
        if ($key === 'captain_name') {
            return $this->getLiveCaptainName() ?: $default;
        }

        if (! $this->tableExists()) {
            return $this->getDefaults()[$key] ?? $default;
        }

        $row = $this->where('setting_key', $key)->first();
        return $row ? ($row['setting_value'] ?? $default) : $default;
    }

    /**
     * Upsert a batch of key→value pairs.
     * captain_name is intentionally ignored — managed from users table.
     *
     * @param array<string, string> $data
     */
    public function saveAll(array $data): void
    {
        // Never allow overwriting captain_name via settings form
        unset($data['captain_name']);

        foreach ($data as $key => $value) {
            $existing = $this->where('setting_key', $key)->first();
            if ($existing) {
                $this->update($existing['id'], ['setting_value' => $value]);
            }
            // Skip unknown keys — only seeded keys are editable.
        }
    }

    /**
     * True when a system preference is turned on.
     * Missing rows use the built-in default for that key.
     */
    public function enabled(string $key): bool
    {
        $defaults = $this->getDefaults();
        $fallback = (string) ($defaults[$key] ?? '0');

        return $this->getValue($key, $fallback) === '1';
    }

    /**
     * Save the System Settings switches. Inserts a row when the key is new.
     *
     * @param array<string, bool> $flags
     */
    public function savePreferences(array $flags): void
    {
        $meta = [
            'email_notifications'     => ['Email Notifications', 200],
            'auto_approve_clearances' => ['Auto-approve Clearances', 201],
            'account_approval_alerts' => ['Account Approval Alerts', 202],
        ];

        if (! $this->db->tableExists($this->table)) {
            return;
        }

        foreach ($meta as $key => [$label, $sort]) {
            if (! array_key_exists($key, $flags)) {
                continue;
            }

            $value    = $flags[$key] ? '1' : '0';
            $existing = $this->where('setting_key', $key)->first();

            if ($existing) {
                $this->update($existing['id'], ['setting_value' => $value]);
                continue;
            }

            $this->insert([
                'setting_key'   => $key,
                'setting_value' => $value,
                'label'         => $label,
                'group'         => 'system',
                'sort_order'    => $sort,
            ]);
        }
    }

    /**
     * Report front-page metadata used on generated report covers.
     *
     * @return array<string, string>
     */
    public function getReportFrontPageSettings(): array
    {
        $defaults = [
            'country'                     => 'Republic of the Philippines',
            'region'                     => 'V (Bicol)',
            'province'                   => 'Camarines Sur',
            'municipality'               => 'BATO',
            'barangay_name'              => 'BACOLOD',
            'barangay_profile_title'     => 'BARANGAY PROFILE',
            'report_front_header'        => 'Department of the Interior and Local Government',
            'report_front_office'       => 'NATIONAL BARANGAY OPERATIONS OFFICE',
            'report_front_department'   => 'Department of the Interior and Local Government',
            'report_front_footer'       => 'Annex A',
            'report_front_footer_note'  => 'Barangay Profile DCF No. 1',
            'report_front_annex_note'   => '(BP DC No. 1 s. 2020)',
            'report_front_district'     => 'V (Rinconada)',
            'report_front_legal_basis'  => '',
            'report_front_ratification_date' => '',
            'report_front_precincts'    => '2',
            'report_front_officials'    => '',
            'report_front_barangay_treasurer' => '',
            'report_front_sk_councilors' => '',
            'report_front_fiscal_year' => '2025',
            'report_front_fiscal_ira' => '',
            'report_front_fiscal_donation_grant' => '',
            'report_front_fiscal_national_wealth' => '',
            'report_front_fiscal_external_subsidy' => '',
            'report_front_fiscal_general_fund' => '',
            'report_front_fiscal_sk_fund' => '',
            'report_front_fiscal_rpt_share' => '',
            'report_front_fiscal_fees_charges' => '',
            'report_front_fiscal_local_others' => '',
            'report_front_fiscal_definitions' => 'Indicate the total income for the period under review. External sources include the Internal Revenue Allotment, donations or grants, share from national wealth, and other external subsidies. Local sources include real property tax share, fees and charges, and other local income.',
            'report_front_population'   => '35',
            'report_front_households'   => '15',
            'report_front_families'     => '19',
            'report_front_registered_voters' => '11',
            'report_front_area'         => '405.1240',
            'report_front_category'      => 'Urban',
            'report_front_classification' => 'Lowland',
            'report_front_land_location' => 'Tabing-ilog',
            'report_front_economic'     => 'Agricultural',
            'report_front_fiscal_area' => '405.1240',
            'report_front_fiscal_category' => 'Urban',
            'report_front_fiscal_classification' => 'Lowland',
            'report_front_fiscal_land_location' => 'Tabing-ilog',
            'report_front_fiscal_economic' => 'Agricultural',
            'report_front_profile_text' => "Barangay : BACOLOD\nProvince : CAMARINES SUR\nCity/Municipality : BATO\nRegion : V (BICOL)\nCongressional District : V (Rinconada)",
        ];

        $settings = $this->getAll();
        $manualTreasurer = trim((string) ($settings['report_front_barangay_treasurer'] ?? ''));
        $manualSkCouncilors = trim((string) ($settings['report_front_sk_councilors'] ?? ''));
        $officialLines = $this->getAppointedOfficialLines();
        if ($manualTreasurer !== '') {
            $officialLines[] = 'Barangay Treasurer: ' . strtoupper($manualTreasurer);
        }
        foreach (preg_split('/\r\n|\r|\n/', $manualSkCouncilors) ?: [] as $councilor) {
            $councilor = trim($councilor);
            if ($councilor !== '') {
                $officialLines[] = 'SK Councilor: ' . strtoupper($councilor);
            }
        }
        $defaults['report_front_officials'] = implode("\n", $officialLines);
        foreach ($defaults as $key => $value) {
            $defaults[$key] = (string) ($settings[$key] ?? $value);
        }
        $defaults['report_front_officials'] = implode("\n", $officialLines);

        return $defaults;
    }

    /**
     * Return active appointed officials whose names are managed by user roles.
     * Treasurer and SK Councilor entries are intentionally excluded because
     * those positions are maintained manually by the secretary/admin.
     *
     * @return list<string>
     */
    private function getAppointedOfficialLines(): array
    {
        if (! $this->db->tableExists('users')) {
            return [];
        }

        $rows = $this->db->table('users')
            ->select('role, first_name, middle_name, last_name, username')
            ->where('status', 'active')
            ->whereIn('role', ['captain', 'secretary', 'council', 'sk'])
            ->orderBy('last_name', 'ASC')
            ->get()
            ->getResultArray();

        $roleOrder = ['captain' => 1, 'council' => 2, 'secretary' => 3, 'sk' => 4];
        usort(
            $rows,
            static fn(array $left, array $right): int => ($roleOrder[$left['role']] ?? 99) <=> ($roleOrder[$right['role']] ?? 99)
        );

        $lines = [];
        foreach ($rows as $row) {
            $name = trim(implode(' ', array_filter([
                trim((string) ($row['first_name'] ?? '')),
                trim((string) ($row['middle_name'] ?? '')),
                trim((string) ($row['last_name'] ?? '')),
            ])));
            if ($name === '' && ! empty($row['username'])) {
                $name = trim((string) $row['username']);
            }
            if ($name === '') {
                continue;
            }

            $position = match ($row['role']) {
                'captain' => 'Punong Barangay',
                'secretary' => 'Barangay Secretary',
                'council' => 'Barangay Kagawad',
                'sk' => 'SK Chairperson',
                default => '',
            };
            if ($position !== '') {
                $lines[] = $position . ': ' . strtoupper($name);
            }
        }

        return $lines;
    }

    /**
     * Save front-page report fields, creating missing rows when needed.
     *
     * @param array<string, string> $data
     */
    public function saveReportFrontPageData(array $data): void
    {
        $allowed = [
            'country',
            'region',
            'province',
            'municipality',
            'barangay_name',
            'barangay_profile_title',
            'report_front_header',
            'report_front_office',
            'report_front_department',
            'report_front_footer',
            'report_front_footer_note',
            'report_front_annex_note',
            'report_front_district',
            'report_front_legal_basis',
            'report_front_ratification_date',
            'report_front_precincts',
            'report_front_barangay_treasurer',
            'report_front_sk_councilors',
            'report_front_fiscal_year',
            'report_front_fiscal_ira',
            'report_front_fiscal_donation_grant',
            'report_front_fiscal_national_wealth',
            'report_front_fiscal_external_subsidy',
            'report_front_fiscal_general_fund',
            'report_front_fiscal_sk_fund',
            'report_front_fiscal_rpt_share',
            'report_front_fiscal_fees_charges',
            'report_front_fiscal_local_others',
            'report_front_fiscal_definitions',
            'report_front_population',
            'report_front_households',
            'report_front_families',
            'report_front_registered_voters',
            'report_front_area',
            'report_front_category',
            'report_front_classification',
            'report_front_land_location',
            'report_front_economic',
            'report_front_fiscal_area',
            'report_front_fiscal_category',
            'report_front_fiscal_classification',
            'report_front_fiscal_land_location',
            'report_front_fiscal_economic',
            'report_front_profile_text',
        ];

        foreach ($allowed as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $value = trim((string) $data[$key]);
            $existing = $this->where('setting_key', $key)->first();

            if ($existing) {
                $this->update($existing['id'], ['setting_value' => $value]);
                continue;
            }

            $this->insert([
                'setting_key'   => $key,
                'setting_value' => $value,
                'label'         => ucfirst(str_replace('_', ' ', $key)),
                'group'         => 'identity',
                'sort_order'    => 999,
            ]);
        }
    }

    /**
     * Hard-coded fallback defaults used before the migration runs.
     *
     * @return array<string, string>
     */
    public function getDefaults(): array
    {
        return [
            'barangay_name'  => 'BARANGAY BACOLOD',
            'municipality'   => 'Municipality of Bato',
            'province'       => 'Province of Camarines Sur',
            'region'         => 'Region V',
            'country'        => 'Republic of the Philippines',
            'full_address'   => 'Barangay Bacolod, Bato, Camarines Sur',
            'office_header'  => 'OFFICE OF THE PUNONG BARANGAY',
            'captain_name'   => 'PUNONG BARANGAY',
            'captain_title'  => 'Punong Barangay',
            'secretary_name' => '',
            'clearance_fee'  => '₱50.00',
            'residency_fee'  => '₱30.00',
            'indigency_fee'  => 'Free',
            'deceased_account_action' => 'block',
            'email_notifications'     => '1',
            'auto_approve_clearances' => '0',
            'account_approval_alerts' => '1',
            'public_address'          => 'Barangay Bacolod, Bato, Camarines Sur, Philippines',
            'public_phone'            => '+63 (054) 000-0000',
            'public_email'            => 'barangaybacolod@bato.gov.ph',
            'public_hours'            => 'Mon – Fri: 8:00 AM – 5:00 PM',
            'public_facebook'         => '',
            'public_twitter'          => '',
            'public_email_link'       => '',
        ];
    }

    /**
     * Save the public website contact details and social links.
     *
     * @param array<string, string> $values
     */
    public function savePublicContact(array $values): void
    {
        $meta = [
            'public_address'  => ['Public Address', 210],
            'public_phone'    => ['Public Phone', 211],
            'public_email'    => ['Public Email', 212],
            'public_hours'    => ['Office Hours', 213],
            'public_facebook'   => ['Facebook Link', 214],
            'public_twitter'    => ['Twitter Link', 215],
            'public_email_link' => ['Email Icon Link', 216],
        ];

        if (! $this->db->tableExists($this->table)) {
            return;
        }

        foreach ($meta as $key => [$label, $sort]) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $value    = trim((string) $values[$key]);
            $existing = $this->where('setting_key', $key)->first();

            if ($existing) {
                $this->update($existing['id'], ['setting_value' => $value]);
                continue;
            }

            $this->insert([
                'setting_key'   => $key,
                'setting_value' => $value,
                'label'         => $label,
                'group'         => 'website',
                'sort_order'    => $sort,
            ]);
        }
    }
}
