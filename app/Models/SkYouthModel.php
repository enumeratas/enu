<?php

namespace App\Models;

use CodeIgniter\Model;

class SkYouthModel extends Model
{
    protected $table         = 'sk_youth';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

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
}
