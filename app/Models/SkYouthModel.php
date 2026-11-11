<?php

namespace App\Models;

use CodeIgniter\Model;

class SkYouthModel extends Model
{
    protected $table         = 'sk_youth';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $beforeInsert  = ['captureNormalized'];
    protected $afterInsert   = ['storeNormalized'];
    protected $beforeUpdate  = ['captureNormalized'];
    protected $afterUpdate   = ['storeNormalized'];
    protected $afterFind     = ['hydrateNormalized'];

    /** @var array<string, mixed>|null */
    private ?array $normalizedWrite = null;

    private ?bool $normalizedReady = null;

    protected $allowedFields = [
        'user_id',
        'photo_path',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'religion',
        'citizenship',
        'contact_number',
        'email',
        'address',
        'zone',
        'months_in_brgy',
        'skills',
        'hobbies',
        'mother_name',
        'mother_occupation',
        'father_name',
        'father_occupation',
        'organizations',
        'age_group',
        'civil_status',
        'educational_background',
        'school_type',
        'school_detail',
        'governance',
        'health_concerns',
        'social_inclusion',
        'economic_status',
        'monthly_income',
        'advocacy',
        'volunteer',
        'issue_1',
        'issue_2',
        'issue_3',
        'suggestions',
        'recorded_by',
    ];

    /** Standard KK Profiling Form choices shared by the edit form and the print form. */
    public const AGE_GROUPS = [
        '15-17' => 'Child Youth (15-17 y/o)',
        '18-21' => '18-21 y/o',
        '22-24' => '22-24 y/o',
        '25-30' => 'Adult Youth (25-30 y/o)',
    ];

    public const GENDERS = ['Male', 'Female'];

    public const CIVIL_STATUSES = [
        'Single',
        'Married',
        'Widowed',
        'Separated',
        'Annulled',
        'With live-in partner',
        'Teenage Mom/Dad',
        'Solo Parent',
    ];

    public const EDUCATION_OPTIONS = [
        'Junior High School',
        'Senior High School',
        'Technical/Vocational',
        'College',
        'Out-of-School Youth',
        'Alternative Learning System',
        'Young Professional',
    ];

    public const SCHOOL_TYPES = ['Public School', 'Private School'];

    public const GOVERNANCE_OPTIONS = ['COMELEC Registered', 'COMELEC Non-Registered'];

    public const ECONOMIC_STATUSES = [
        'Employed',
        'Unemployed',
        'Engage in Business',
        'Engage in Agriculture',
        'Working outside Municipality',
        'Student',
        'Out-of-School',
        'Self-Employed',
    ];

    public const INCOME_BRACKETS = [
        'Below P5,000.00',
        'P5,000.00 - P10,000.00',
        'P10,000.00 - P20,000.00',
        'P20,000.00 - 35,000.00',
        'P35,000.00 - 60,000.00',
        'P60,000.00 and above',
    ];

    public const HEALTH_OPTIONS = [
        'Engage in Smoking',
        'Engage in Alcohol',
        'Experience Depression',
        'Attempt Suicide',
        'Health Problem',
        'HIV/AIDS',
    ];

    public const SOCIAL_OPTIONS = [
        '4Ps Beneficiary',
        'UCT Beneficiary',
        'IP',
        'PWD',
        'Solo Parent',
        'LGBTQ+',
    ];

    public const ADVOCACY_OPTIONS = [
        'Health',
        'Education',
        'Economic Empowerment',
        'Social Inclusion & Equity',
        'Governance',
        'Active Citizenship',
        'Peace Building & Security',
        'Environment',
        'Global Mobility',
    ];

    public const VOLUNTEER_OPTIONS = [
        'Health',
        'Education',
        'Environment',
        'Disaster Preparedness',
        'Agriculture',
        'LGU Activities',
        'Barangay Activity',
        'SK Activities',
        'Parish Activities',
    ];

    public const CENSUS_CIVIL_STATUSES = ['Single', 'Married', 'Widowed', 'Separated', 'Annulled'];

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Compute age from date_of_birth.
     */
    public static function calcAge(?string $dob): ?int
    {
        return person_age_from_dob($dob);
    }

    /**
     * Derive economic status label from educational_background.
     */
    public static function statusLabel(string $edu): string
    {
        $edu = strtolower($edu);
        if (str_contains($edu, 'out-of-school')) return 'Out-of-School';
        if (str_contains($edu, 'college') || str_contains($edu, 'junior') || str_contains($edu, 'senior')) return 'Student';
        return 'Student';
    }

    /**
     * Get stats for the profiling dashboard.
     */
    public function getStats(): array
    {
        $total    = $this->countAll();
        $male     = (clone $this)->where('gender', 'Male')->countAllResults();
        $female   = (clone $this)->where('gender', 'Female')->countAllResults();
        $students = (clone $this)->like('economic_status', 'Student')->countAllResults();
        $oos      = (clone $this)->like('economic_status', 'Out-of-School')->countAllResults();

        return compact('total', 'male', 'female', 'students', 'oos');
    }

    public static function normalizeName(?string $value): string
    {
        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/[^A-Z0-9]+/', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    public static function normalizeDob(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        $time = strtotime($value);

        return $time ? date('Y-m-d', $time) : $value;
    }

    public static function profileDate(?string $value): ?string
    {
        $value = self::normalizeDob($value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
    }

    public static function ageGroupFor(?string $dob): ?string
    {
        $age = self::calcAge($dob);
        if ($age === null) {
            return null;
        }
        if ($age >= 15 && $age <= 17) {
            return '15-17';
        }
        if ($age >= 18 && $age <= 21) {
            return '18-21';
        }
        if ($age >= 22 && $age <= 24) {
            return '22-24';
        }
        if ($age >= 25 && $age <= 30) {
            return '25-30';
        }

        return null;
    }

    public static function normalizeChoice(?string $value, array $allowed): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        foreach ($allowed as $item) {
            if (strcasecmp($value, (string) $item) === 0) {
                return (string) $item;
            }
        }

        return null;
    }

    public static function educationChoice(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $known = self::normalizeChoice($value, self::EDUCATION_OPTIONS);
        if ($known !== null) {
            return $known;
        }

        $lower = strtolower($value);
        $map = [
            'junior' => 'Junior High School',
            'senior' => 'Senior High School',
            'college' => 'College',
            'bachelor' => 'College',
            'vocational' => 'Technical/Vocational',
            'tech-voc' => 'Technical/Vocational',
            'alternative' => 'Alternative Learning System',
            'out-of-school' => 'Out-of-School Youth',
            'out of school' => 'Out-of-School Youth',
            'professional' => 'Young Professional',
        ];
        foreach ($map as $needle => $choice) {
            if (str_contains($lower, $needle)) {
                return $choice;
            }
        }
        if (preg_match('/\bals\b/', $lower) === 1) {
            return 'Alternative Learning System';
        }

        return self::limitText($value, 100);
    }

    public static function limitText(?string $value, int $max): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
    }

    /**
     * Keep only the standard choices from a checkbox list or comma-separated value.
     */
    public static function encodeChoices(mixed $value, array $allowed, bool $asJson = false): ?string
    {
        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', $value) ?: [];
        }
        if (! is_array($value)) {
            return null;
        }

        $kept = [];
        foreach ($value as $item) {
            $choice = self::normalizeChoice((string) $item, $allowed);
            if ($choice !== null && ! in_array($choice, $kept, true)) {
                $kept[] = $choice;
            }
        }
        if ($kept === []) {
            return null;
        }

        return $asJson ? json_encode($kept) : implode(', ', $kept);
    }

    public static function statusFromOccupation(?string $occupation): string
    {
        $occ = strtolower(trim((string) $occupation));
        if ($occ === '' || $occ === 'none' || $occ === 'n/a' || $occ === '-') {
            return '—';
        }
        if (str_contains($occ, 'student')) {
            return 'Student';
        }
        if (str_contains($occ, 'unemploy')) {
            return 'Unemployed';
        }
        if (str_contains($occ, 'out-of-school') || str_contains($occ, 'osy')) {
            return 'Out-of-School';
        }
        if (str_contains($occ, 'self-employ') || str_contains($occ, 'self employ')) {
            return 'Self-Employed';
        }

        return 'Employed';
    }

    /**
     * Latest submitted/updated SK profile for a census youth or household person.
     *
     * @param array<string, mixed> $person
     */
    public function matchProfile(array $person, ?int $userId = null): ?array
    {
        $this->primeIndexes();
        $userId = (int) $userId;
        if ($userId > 0 && isset($this->indexByUser[$userId])) {
            return $this->indexByUser[$userId];
        }

        $first = self::normalizeName($person['first_name'] ?? '');
        $last  = self::normalizeName($person['last_name'] ?? '');
        $dob   = self::normalizeDob($person['date_of_birth'] ?? null);
        if ($first !== '' && $last !== '' && $dob !== '') {
            $key = $first . '|' . $last . '|' . $dob;
            if (isset($this->indexByNameDob[$key])) {
                return $this->indexByNameDob[$key];
            }
        }
        if ($last !== '' && $dob !== '') {
            $key = $last . '|' . $dob;
            if (isset($this->indexByLastDob[$key]) && count($this->indexByLastDob[$key]) === 1) {
                return $this->indexByLastDob[$key][0];
            }
        }
        if ($first !== '' && $last !== '') {
            $key = $first . '|' . $last;
            if (isset($this->indexByName[$key]) && count($this->indexByName[$key]) === 1) {
                return $this->indexByName[$key][0];
            }
        }

        return null;
    }

    /**
     * Overlay submitted SK profiles onto census youth rows and append unmatched profiles.
     *
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public function overlayCensusYouth(array $rows): array
    {
        $this->primeIndexes();
        $userMap = $this->householdNameUserMap();
        $used    = [];
        $merged  = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $userId  = $this->resolveUserId($row, $userMap);
            $profile = $this->matchProfile($row, $userId);
            if ($profile) {
                foreach ($this->relatedProfileIds($profile) as $relatedId) {
                    $used[$relatedId] = true;
                }
            }
            $merged[] = $this->applyToCensusRow($row, $profile);
        }

        foreach ($this->latestRows as $profile) {
            $id = (int) ($profile['id'] ?? 0);
            if ($id < 1 || isset($used[$id]) || $this->alreadyOnRoster($merged, $profile)) {
                continue;
            }
            $age = self::calcAge($profile['date_of_birth'] ?? null);
            if ($age === null || $age < 15 || $age > 30) {
                continue;
            }
            foreach ($this->relatedProfileIds($profile) as $relatedId) {
                $used[$relatedId] = true;
            }
            $merged[] = $this->applyToCensusRow([
                'source'         => 'profile',
                'id'             => $id,
                'household_no'   => '',
                'relationship'   => 'SK Profile',
                'occupation'     => '',
                'contact_number' => '',
                'civil_status'   => '',
            ], $profile);
        }

        return $merged;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed>|null $profile
     * @return array<string, mixed>
     */
    public function applyToCensusRow(array $row, ?array $profile): array
    {
        if ($profile) {
            foreach (['first_name', 'last_name', 'middle_name', 'date_of_birth', 'gender', 'civil_status', 'contact_number', 'zone'] as $field) {
                $value = trim((string) ($profile[$field] ?? ''));
                if ($value !== '') {
                    $row[$field] = $profile[$field];
                }
            }
            $status = trim((string) ($profile['economic_status'] ?? ''));
            $row['economic_status'] = $status !== '' ? $status : self::statusFromOccupation($row['occupation'] ?? '');
            $row['profile_id'] = (int) ($profile['id'] ?? 0);
            $row['profile_updated_at'] = $profile['updated_at'] ?? ($profile['created_at'] ?? null);
        } else {
            $row['economic_status'] = self::statusFromOccupation($row['occupation'] ?? '');
            $row['profile_id'] = null;
            $row['profile_updated_at'] = null;
        }

        $row['age'] = self::calcAge($row['date_of_birth'] ?? null);

        return $row;
    }

    /** @var list<array<string, mixed>> */
    private array $latestRows = [];

    /** @var array<int, array<string, mixed>> */
    private array $indexByUser = [];

    /** @var array<string, array<string, mixed>> */
    private array $indexByNameDob = [];

    /** @var array<string, list<array<string, mixed>>> */
    private array $indexByName = [];

    /** @var array<string, list<array<string, mixed>>> */
    private array $indexByLastDob = [];

    private bool $indexesReady = false;

    public function resetProfileIndexes(): void
    {
        $this->indexesReady = false;
        $this->latestRows = [];
        $this->indexByUser = [];
        $this->indexByNameDob = [];
        $this->indexByName = [];
        $this->indexByLastDob = [];
    }

    private function primeIndexes(): void
    {
        if ($this->indexesReady) {
            return;
        }

        $this->latestRows      = [];
        $this->indexByUser     = [];
        $this->indexByNameDob  = [];
        $this->indexByName     = [];
        $this->indexByLastDob  = [];

        if (! $this->db->tableExists('sk_youth')) {
            $this->indexesReady = true;
            return;
        }

        $rows = $this->orderBy('updated_at', 'DESC')->orderBy('id', 'DESC')->findAll();
        $seen = [];
        $people = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = (int) ($row['id'] ?? 0);
            if ($id < 1 || isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $personKey = $this->personKey($row);
            if ($personKey !== '' && isset($people[$personKey])) {
                continue;
            }
            if ($personKey !== '') {
                $people[$personKey] = true;
            }
            $this->latestRows[] = $row;

            $userId = (int) ($row['user_id'] ?? 0);
            if ($userId > 0 && ! isset($this->indexByUser[$userId])) {
                $this->indexByUser[$userId] = $row;
            }

            $first = self::normalizeName($row['first_name'] ?? '');
            $last  = self::normalizeName($row['last_name'] ?? '');
            $dob   = self::normalizeDob($row['date_of_birth'] ?? null);
            if ($first !== '' && $last !== '') {
                $nameKey = $first . '|' . $last;
                $this->indexByName[$nameKey] ??= [];
                $this->indexByName[$nameKey][] = $row;
                if ($dob !== '') {
                    $this->indexByNameDob[$first . '|' . $last . '|' . $dob] ??= $row;
                }
            }
            if ($last !== '' && $dob !== '') {
                $lastDob = $last . '|' . $dob;
                $this->indexByLastDob[$lastDob] ??= [];
                $this->indexByLastDob[$lastDob][] = $row;
            }
        }

        $this->indexesReady = true;
    }

    /**
     * @return array<string, int>
     */
    private function householdNameUserMap(): array
    {
        if (! $this->db->tableExists('users')) {
            return [];
        }

        $rows = $this->db->table('users')
            ->select('id, first_name, last_name, household_no')
            ->whereIn('role', ['resident', 'council'])
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $user) {
            $household = trim((string) ($user['household_no'] ?? ''));
            $first     = self::normalizeName($user['first_name'] ?? '');
            $last      = self::normalizeName($user['last_name'] ?? '');
            if ($household === '' || $first === '' || $last === '') {
                continue;
            }
            $key = $household . '|' . $first . '|' . $last;
            if (! isset($map[$key])) {
                $map[$key] = (int) $user['id'];
            }
        }

        return $map;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, int> $userMap
     */
    private function resolveUserId(array $row, array $userMap): ?int
    {
        $direct = (int) ($row['user_id'] ?? 0);
        if ($direct > 0) {
            return $direct;
        }

        $household = trim((string) ($row['household_no'] ?? ''));
        $first     = self::normalizeName($row['first_name'] ?? '');
        $last      = self::normalizeName($row['last_name'] ?? '');
        $key       = $household . '|' . $first . '|' . $last;

        return ($household !== '' && isset($userMap[$key])) ? $userMap[$key] : null;
    }

    /** @return list<int> */
    private function relatedProfileIds(array $profile): array
    {
        $ids = [];
        $id = (int) ($profile['id'] ?? 0);
        if ($id > 0) {
            $ids[] = $id;
        }

        $userId = (int) ($profile['user_id'] ?? 0);
        $key    = $this->personKey($profile);
        foreach ($this->latestRows as $row) {
            $rowId = (int) ($row['id'] ?? 0);
            if ($rowId < 1) {
                continue;
            }
            if ($userId > 0 && (int) ($row['user_id'] ?? 0) === $userId) {
                $ids[] = $rowId;
                continue;
            }
            if ($key !== '' && $this->personKey($row) === $key) {
                $ids[] = $rowId;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param list<array<string, mixed>> $merged
     * @param array<string, mixed> $profile
     */
    private function alreadyOnRoster(array $merged, array $profile): bool
    {
        $profileId = (int) ($profile['id'] ?? 0);
        $first     = self::normalizeName($profile['first_name'] ?? '');
        $last      = self::normalizeName($profile['last_name'] ?? '');
        $dob       = self::normalizeDob($profile['date_of_birth'] ?? null);

        foreach ($merged as $row) {
            if ($profileId > 0 && (int) ($row['profile_id'] ?? 0) === $profileId) {
                return true;
            }
            $sameName = self::normalizeName($row['first_name'] ?? '') === $first
                && self::normalizeName($row['last_name'] ?? '') === $last;
            if (! $sameName || $first === '' || $last === '') {
                continue;
            }
            $rowDob = self::normalizeDob($row['date_of_birth'] ?? null);
            if ($dob === '' || $rowDob === '' || $dob === $rowDob) {
                return true;
            }
        }

        return false;
    }

    /** @param array<string, mixed> $row */
    private function personKey(array $row): string
    {
        $userId = (int) ($row['user_id'] ?? 0);
        if ($userId > 0) {
            return 'u:' . $userId;
        }

        $first = self::normalizeName($row['first_name'] ?? '');
        $last  = self::normalizeName($row['last_name'] ?? '');
        $dob   = self::normalizeDob($row['date_of_birth'] ?? null);
        if ($first !== '' && $last !== '' && $dob !== '') {
            return 'n:' . $first . '|' . $last . '|' . $dob;
        }
        if ($first !== '' && $last !== '') {
            return 'n:' . $first . '|' . $last;
        }

        return '';
    }

    /**
     * Repeating organization rows stored as JSON or as a list of name/position/year.
     *
     * @return list<array{name: string, position: string, year: string}>
     */
    public static function parseOrganizations(mixed $value): array
    {
        $rows = self::decodeList($value);
        $orgs = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = self::limitText($row['name'] ?? '', 150);
            if ($name === null) {
                continue;
            }
            $orgs[] = [
                'name'     => $name,
                'position' => self::limitText($row['position'] ?? '', 100) ?? '',
                'year'     => self::limitText($row['year'] ?? ($row['membership_year'] ?? ''), 20) ?? '',
            ];
        }

        return $orgs;
    }

    /**
     * @return list<string>
     */
    public static function parseChoiceList(mixed $value): array
    {
        $items = self::decodeList($value);
        $kept = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                $item = $item['value'] ?? $item['choice'] ?? '';
            }
            $choice = self::limitText(is_scalar($item) ? (string) $item : '', 120);
            if ($choice !== null && ! in_array($choice, $kept, true)) {
                $kept[] = $choice;
            }
        }

        return $kept;
    }

    /**
     * @return list<mixed>
     */
    private static function decodeList(mixed $value): array
    {
        if (is_array($value)) {
            if (isset($value['name']) && ! array_is_list($value)) {
                return [$value];
            }

            return array_is_list($value) ? $value : array_values($value);
        }
        if (! is_string($value)) {
            return [];
        }
        $value = trim($value);
        if ($value === '') {
            return [];
        }
        if ($value[0] === '[' || $value[0] === '{') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                if (isset($decoded['name']) && ! array_is_list($decoded)) {
                    return [$decoded];
                }

                return array_is_list($decoded) ? $decoded : array_values($decoded);
            }
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn(string $item): bool => $item !== ''));
    }

    /**
     * @param array<string, mixed> $event
     * @return array<string, mixed>
     */
    protected function captureNormalized(array $event): array
    {
        $data = is_array($event['data'] ?? null) ? $event['data'] : [];
        $this->normalizedWrite = null;
        if ($this->usesNormalizedTables()) {
            $this->normalizedWrite = $this->planNormalizedWrite($data);
            unset($data['age_group'], $data['organizations'], $data['health_concerns'], $data['social_inclusion'], $data['advocacy'], $data['volunteer'], $data['issue_1'], $data['issue_2'], $data['issue_3'], $data['mother_name'], $data['mother_occupation'], $data['father_name'], $data['father_occupation']);
        }
        $event['data'] = $data;

        return $event;
    }

    /**
     * @param array<string, mixed> $event
     * @return array<string, mixed>
     */
    protected function storeNormalized(array $event): array
    {
        $plan = $this->normalizedWrite;
        $this->normalizedWrite = null;
        if ($plan === null || ($event['result'] ?? null) === false || ! $this->usesNormalizedTables()) {
            return $event;
        }

        $ids = $event['id'] ?? [];
        if (! is_array($ids)) {
            $ids = [$ids];
        }
        foreach ($ids as $id) {
            $youthId = (int) $id;
            if ($youthId > 0) {
                $this->writeNormalized($youthId, $plan);
            }
        }

        return $event;
    }

    /**
     * @param array<string, mixed> $event
     * @return array<string, mixed>
     */
    protected function hydrateNormalized(array $event): array
    {
        $rows = $event['data'] ?? null;
        if (! is_array($rows)) {
            return $event;
        }

        $single = (bool) ($event['singleton'] ?? false);
        if ($single) {
            $event['data'] = $this->hydrateYouthRow($rows);

            return $event;
        }

        $hydrated = [];
        $pending = [];
        foreach ($rows as $key => $row) {
            if (! is_array($row)) {
                $hydrated[$key] = $row;
                continue;
            }
            $pending[$key] = $row;
        }
        foreach ($this->hydrateYouthRows($pending) as $key => $row) {
            $hydrated[$key] = $row;
        }
        $event['data'] = $hydrated;

        return $event;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function hydrateYouthRow(array $row): array
    {
        $rows = $this->hydrateYouthRows([0 => $row]);

        return $rows[0] ?? $row;
    }

    /**
     * @param array<int|string, array<string, mixed>> $rows
     * @return array<int|string, array<string, mixed>>
     */
    private function hydrateYouthRows(array $rows): array
    {
        foreach ($rows as $key => $row) {
            $rows[$key]['age_group'] = self::ageGroupFor($row['date_of_birth'] ?? null);
        }
        if ($rows === [] || ! $this->usesNormalizedTables()) {
            return $rows;
        }

        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if ($ids === []) {
            return $rows;
        }

        $orgs = [];
        foreach ($this->db->table('sk_youth_organizations')->whereIn('youth_id', array_values($ids))->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->get()->getResultArray() as $org) {
            $orgs[(int) $org['youth_id']][] = [
                'name'     => (string) ($org['organization_name'] ?? ''),
                'position' => (string) ($org['position'] ?? ''),
                'year'     => (string) ($org['membership_year'] ?? ''),
            ];
        }

        $issues = [];
        foreach ($this->db->table('sk_youth_issues')->whereIn('youth_id', array_values($ids))->get()->getResultArray() as $issue) {
            $issues[(int) $issue['youth_id']][(int) $issue['rank_no']] = (string) ($issue['concern'] ?? '');
        }

        $choices = [];
        foreach ($this->db->table('sk_youth_choices')->whereIn('youth_id', array_values($ids))->orderBy('id', 'ASC')->get()->getResultArray() as $choice) {
            $choices[(int) $choice['youth_id']][(string) $choice['choice_group']][] = (string) $choice['choice_value'];
        }

        $guardians = [];
        foreach ($this->db->table('sk_youth_guardians')->whereIn('youth_id', array_values($ids))->get()->getResultArray() as $guardian) {
            $guardians[(int) $guardian['youth_id']][(string) $guardian['role']] = $guardian;
        }

        foreach ($rows as $key => $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id < 1) {
                continue;
            }
            $orgRows = $orgs[$id] ?? [];
            if ($orgRows !== [] || ! array_key_exists('organizations', $row)) {
                $row['organizations'] = $orgRows === [] ? null : json_encode($orgRows);
            }
            $issueRows = $issues[$id] ?? [];
            foreach ([1 => 'issue_1', 2 => 'issue_2', 3 => 'issue_3'] as $rank => $field) {
                if (isset($issueRows[$rank]) || ! array_key_exists($field, $row)) {
                    $row[$field] = $issueRows[$rank] ?? null;
                }
            }
            $choiceRows = $choices[$id] ?? [];
            $row = $this->assignChoiceField($row, 'health_concerns', $choiceRows['health'] ?? null, true);
            $row = $this->assignChoiceField($row, 'social_inclusion', $choiceRows['social'] ?? null, true);
            $row = $this->assignChoiceField($row, 'advocacy', $choiceRows['advocacy'] ?? null, false);
            $row = $this->assignChoiceField($row, 'volunteer', $choiceRows['volunteer'] ?? null, false);
            $guardianRows = $guardians[$id] ?? [];
            foreach (['mother', 'father'] as $role) {
                $nameField = $role . '_name';
                $jobField = $role . '_occupation';
                if (isset($guardianRows[$role]) || ! array_key_exists($nameField, $row)) {
                    $row[$nameField] = $guardianRows[$role]['full_name'] ?? null;
                    $row[$jobField] = $guardianRows[$role]['occupation'] ?? null;
                }
            }
            $rows[$key] = $row;
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $row
     * @param list<string>|null $values
     * @return array<string, mixed>
     */
    private function assignChoiceField(array $row, string $field, ?array $values, bool $asJson): array
    {
        if ($values === null && array_key_exists($field, $row)) {
            return $row;
        }
        if ($values === null || $values === []) {
            $row[$field] = null;

            return $row;
        }
        $row[$field] = $asJson ? json_encode(array_values($values)) : implode(', ', $values);

        return $row;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>|null
     */
    private function planNormalizedWrite(array $data): ?array
    {
        $plan = [];
        $seen = false;
        if (array_key_exists('organizations', $data)) {
            $plan['organizations'] = self::parseOrganizations($data['organizations']);
            $seen = true;
        }
        foreach ([1 => 'issue_1', 2 => 'issue_2', 3 => 'issue_3'] as $rank => $field) {
            if (array_key_exists($field, $data)) {
                $plan['issues'][$rank] = self::limitText($data[$field] ?? '', 200);
                $seen = true;
            }
        }
        foreach (['health_concerns' => 'health', 'social_inclusion' => 'social', 'advocacy' => 'advocacy', 'volunteer' => 'volunteer'] as $field => $group) {
            if (array_key_exists($field, $data)) {
                $plan['choices'][$group] = self::parseChoiceList($data[$field]);
                $seen = true;
            }
        }
        foreach (['mother', 'father'] as $role) {
            $nameField = $role . '_name';
            $jobField = $role . '_occupation';
            if (array_key_exists($nameField, $data) || array_key_exists($jobField, $data)) {
                $plan['guardians'][$role] = [
                    'full_name'  => self::limitText($data[$nameField] ?? '', 150),
                    'occupation' => self::limitText($data[$jobField] ?? '', 100),
                ];
                $seen = true;
            }
        }

        return $seen ? $plan : null;
    }

    /**
     * @param array<string, mixed> $plan
     */
    private function writeNormalized(int $youthId, array $plan): void
    {
        if (isset($plan['organizations']) && is_array($plan['organizations'])) {
            $this->db->table('sk_youth_organizations')->where('youth_id', $youthId)->delete();
            $batch = [];
            foreach (array_values($plan['organizations']) as $index => $org) {
                if (! is_array($org)) {
                    continue;
                }
                $batch[] = [
                    'youth_id'            => $youthId,
                    'sort_order'          => $index + 1,
                    'organization_name'   => $org['name'],
                    'position'            => $org['position'] !== '' ? $org['position'] : null,
                    'membership_year'     => $org['year'] !== '' ? $org['year'] : null,
                ];
            }
            if ($batch !== []) {
                $this->db->table('sk_youth_organizations')->insertBatch($batch);
            }
        }

        if (isset($plan['issues']) && is_array($plan['issues'])) {
            foreach ($plan['issues'] as $rank => $concern) {
                $this->db->table('sk_youth_issues')->where('youth_id', $youthId)->where('rank_no', (int) $rank)->delete();
                if (is_string($concern) && $concern !== '') {
                    $this->db->table('sk_youth_issues')->insert([
                        'youth_id' => $youthId,
                        'rank_no'  => (int) $rank,
                        'concern'  => $concern,
                    ]);
                }
            }
        }

        if (isset($plan['choices']) && is_array($plan['choices'])) {
            foreach ($plan['choices'] as $group => $values) {
                $this->db->table('sk_youth_choices')->where('youth_id', $youthId)->where('choice_group', (string) $group)->delete();
                $batch = [];
                foreach ((array) $values as $value) {
                    $batch[] = [
                        'youth_id'     => $youthId,
                        'choice_group' => (string) $group,
                        'choice_value' => (string) $value,
                    ];
                }
                if ($batch !== []) {
                    $this->db->table('sk_youth_choices')->insertBatch($batch);
                }
            }
        }

        if (isset($plan['guardians']) && is_array($plan['guardians'])) {
            foreach ($plan['guardians'] as $role => $guardian) {
                $this->db->table('sk_youth_guardians')->where('youth_id', $youthId)->where('role', (string) $role)->delete();
                if (! is_array($guardian)) {
                    continue;
                }
                $name = $guardian['full_name'] ?? null;
                $occupation = $guardian['occupation'] ?? null;
                if ($name === null && $occupation === null) {
                    continue;
                }
                $this->db->table('sk_youth_guardians')->insert([
                    'youth_id'   => $youthId,
                    'role'       => (string) $role,
                    'full_name'  => $name,
                    'occupation' => $occupation,
                ]);
            }
        }
    }

    private function usesNormalizedTables(): bool
    {
        if ($this->normalizedReady !== null) {
            return $this->normalizedReady;
        }

        $this->normalizedReady = $this->db->tableExists('sk_youth_organizations')
            && $this->db->tableExists('sk_youth_issues')
            && $this->db->tableExists('sk_youth_choices')
            && $this->db->tableExists('sk_youth_guardians');

        return $this->normalizedReady;
    }
}
