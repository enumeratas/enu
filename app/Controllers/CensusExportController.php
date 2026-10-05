<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\HouseholdMemberModel;

class CensusExportController extends BaseController
{
    /**
     * Render a print-ready landscape HTML page that the browser can save as PDF.
     * Accepts the same GET filters as the census list page.
     */
    public function exportPdf()
    {
        $role = session()->get('role');
        $db   = \Config\Database::connect();

        $filters = [
            'zone'        => trim($_GET['zone']        ?? ''),
            'gender'      => trim($_GET['gender']      ?? ''),
            'search'      => trim($_GET['search']      ?? ''),
            'age_min'     => trim($_GET['age_min']     ?? ''),
            'age_max'     => trim($_GET['age_max']     ?? ''),
            'is_pwd'      => trim($_GET['is_pwd']      ?? ''),
            'is_senior'   => trim($_GET['is_senior']   ?? ''),
            'is_solo'     => trim($_GET['is_solo']     ?? ''),
            'is_4ps'      => trim($_GET['is_4ps']      ?? ''),
            'is_student'  => trim($_GET['is_student']  ?? ''),
            'is_employed' => trim($_GET['is_employed'] ?? ''),
            'is_osy'      => trim($_GET['is_osy']      ?? ''),
            'is_indigent' => trim($_GET['is_indigent'] ?? ''),
            'census_year' => trim($_GET['census_year'] ?? ''),
        ];

        $councilZone = '';
        if ($role === 'council') {
            $councilUser = (new \App\Models\UserModel())->find((int) session()->get('user_id'));
            $councilZone = is_array($councilUser) ? trim((string) ($councilUser['council_zone'] ?? '')) : '';
            if ($filters['zone'] === '' || $filters['zone'] !== $councilZone) {
                $filters['zone'] = $councilZone;
            }
        }

        [$hw, $mw] = $this->filterClauses($db, $filters, (string) $role, $councilZone);
        $needsUnion = $filters['gender'] !== ''
            || $filters['age_min'] !== ''
            || $filters['age_max'] !== ''
            || $filters['is_student'] === '1'
            || $filters['is_employed'] === '1'
            || $filters['is_osy'] === '1'
            || in_array($filters['is_senior'], ['1', 'with_id', 'without_id'], true);

        $memberModel = new HouseholdMemberModel();
        if (! $needsUnion) {
            $hwSql = $hw !== [] ? ' WHERE ' . implode(' AND ', $hw) : '';
            $households = $db->query(
                "SELECT h.* FROM households h{$hwSql} ORDER BY h.zone ASC, h.household_no ASC"
            )->getResultArray();
            foreach ($households as &$hh) {
                $hh['members'] = $memberModel
                    ->where('household_no', $hh['household_no'])
                    ->orderBy('relationship', 'ASC')
                    ->findAll();
                $hh['member_total'] = 1 + count($hh['members']);
                $hh['show_head'] = true;
            }
            unset($hh);
        } else {
            $hwSql = $hw !== [] ? ' WHERE ' . implode(' AND ', $hw) : '';
            $mwSql = $mw !== [] ? ' WHERE ' . implode(' AND ', $mw) : '';
            $idRows = $db->query(
                "SELECT h.household_no FROM households h{$hwSql}
                 UNION
                 SELECT m.household_no FROM household_members m
                 INNER JOIN households h2 ON h2.household_no = m.household_no{$mwSql}"
            )->getResultArray();
            $ids = array_values(array_unique(array_map('strval', array_column($idRows, 'household_no'))));
            $households = [];
            if ($ids !== []) {
                $in = implode(',', array_map(static fn ($id) => "'" . $db->escapeString($id) . "'", $ids));
                $households = $db->query(
                    "SELECT h.* FROM households h WHERE h.household_no IN ({$in}) ORDER BY h.zone ASC, h.household_no ASC"
                )->getResultArray();
            }
            foreach ($households as &$hh) {
                $members = $memberModel
                    ->where('household_no', $hh['household_no'])
                    ->orderBy('relationship', 'ASC')
                    ->findAll();
                $hh['member_total'] = 1 + count($members);
                $hh['show_head'] = $this->personMatches($hh, $filters, true);
                $kept = [];
                foreach ($members as $member) {
                    $member['household_no'] = $hh['household_no'];
                    if ($this->personMatches($member, $filters, false)) {
                        $kept[] = $member;
                    }
                }
                $hh['members'] = $kept;
            }
            unset($hh);
            $households = array_values(array_filter(
                $households,
                static fn ($hh) => ! empty($hh['show_head']) || ! empty($hh['members'])
            ));
        }

        $byZone = [];
        foreach ($households as $hh) {
            $z = $hh['zone'] ?: 'Unassigned';
            $byZone[$z][] = $hh;
        }

        $activeFilters = [];
        if ($filters['zone']) $activeFilters[] = 'Zone: ' . $filters['zone'];
        if ($filters['gender']) $activeFilters[] = 'Gender: ' . $filters['gender'];
        if ($filters['age_min']) $activeFilters[] = 'Age ≥ ' . $filters['age_min'];
        if ($filters['age_max']) $activeFilters[] = 'Age ≤ ' . $filters['age_max'];
        if ($filters['is_pwd'] === '1') $activeFilters[] = 'PWD';
        if ($filters['is_senior'] === '1') $activeFilters[] = 'Senior Citizens (60+)';
        if ($filters['is_senior'] === 'with_id') $activeFilters[] = 'Senior Citizens (60+) with ID';
        if ($filters['is_senior'] === 'without_id') $activeFilters[] = 'Senior Citizens (60+) without ID';
        if ($filters['is_solo'] === '1') $activeFilters[] = 'Solo Parent';
        if ($filters['is_4ps'] === '1') $activeFilters[] = '4Ps';
        if ($filters['is_student'] === '1') $activeFilters[] = 'Has Student';
        if ($filters['is_employed'] === '1') $activeFilters[] = 'Employed / Labor Force';
        if ($filters['is_osy'] === '1') $activeFilters[] = 'Out-of-School Youth';
        if ($filters['is_indigent'] === '1') $activeFilters[] = 'Indigency Eligible';
        if ($filters['search']) $activeFilters[] = 'Search: "' . $filters['search'] . '"';
        if ($filters['census_year'] !== '') $activeFilters[] = 'Year: ' . $filters['census_year'];

        return view('dashboard/captain/census_export_pdf', [
            'byZone'           => $byZone,
            'zone'             => $filters['zone'],
            'censusYear'       => $filters['census_year'] !== '' ? (int) $filters['census_year'] : null,
            'totalProjected'   => count($households),
            'dateAccomplished' => date('m/d/Y'),
            'role'             => $role,
            'activeFilters'    => $activeFilters,
        ]);
    }

    private function censusTextSearchSql(string $alias, string $raw): string
    {
        $like = \Config\Database::connect()->escapeLikeString($raw);
        $parts = [
            "{$alias}.last_name LIKE '%{$like}%'",
            "{$alias}.first_name LIKE '%{$like}%'",
            "{$alias}.middle_name LIKE '%{$like}%'",
            "{$alias}.household_no LIKE '%{$like}%'",
            "DATE_FORMAT({$alias}.date_of_birth, '%Y-%m-%d') LIKE '%{$like}%'",
            "DATE_FORMAT({$alias}.date_of_birth, '%m/%d/%Y') LIKE '%{$like}%'",
            "DATE_FORMAT({$alias}.date_of_birth, '%c/%e/%Y') LIKE '%{$like}%'",
        ];
        $parsed = $this->censusDateEqualsSql($alias . '.date_of_birth', $raw);
        if ($parsed !== '') {
            $parts[] = $parsed;
        }

        return '(' . implode(' OR ', $parts) . ')';
    }

    private function censusDateEqualsSql(string $column, string $raw): string
    {
        $term = trim($raw);
        $months = [
            'jan' => 1, 'january' => 1, 'feb' => 2, 'february' => 2,
            'mar' => 3, 'march' => 3, 'apr' => 4, 'april' => 4,
            'may' => 5, 'jun' => 6, 'june' => 6, 'jul' => 7, 'july' => 7,
            'aug' => 8, 'august' => 8, 'sep' => 9, 'sept' => 9, 'september' => 9,
            'oct' => 10, 'october' => 10, 'nov' => 11, 'november' => 11,
            'dec' => 12, 'december' => 12,
        ];
        $clauses = [];
        if (preg_match('/^(19|20)\d{2}$/', $term) === 1) {
            $clauses[] = 'YEAR(' . $column . ') = ' . (int) $term;
        }
        $lower = strtolower($term);
        if (isset($months[$lower])) {
            $clauses[] = 'MONTH(' . $column . ') = ' . $months[$lower];
        }
        if (preg_match('/^([a-z]+)\s+(\d{1,2})(?:,?\s*(\d{4}))?$/i', $term, $match) === 1) {
            $month = $months[strtolower($match[1])] ?? 0;
            $day = (int) $match[2];
            if ($month >= 1 && $day >= 1 && $day <= 31) {
                $sql = "MONTH({$column}) = {$month} AND DAY({$column}) = {$day}";
                if (($match[3] ?? '') !== '') {
                    $sql .= ' AND YEAR(' . $column . ') = ' . (int) $match[3];
                }
                $clauses[] = '(' . $sql . ')';
            }
        }
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $term, $match) === 1) {
            $month = (int) $match[1];
            $day = (int) $match[2];
            $year = (int) $match[3];
            if ($month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                $clauses[] = sprintf("%s = '%04d-%02d-%02d'", $column, $year, $month, $day);
            }
        }

        return implode(' OR ', $clauses);
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function filterClauses($db, array $filters, string $role, string $councilZone): array
    {
        $hw = [];
        $mw = [];

        if ($filters['zone'] !== '') {
            $z = $db->escapeString($filters['zone']);
            $hw[] = "h.zone = '{$z}'";
            $mw[] = "h2.zone = '{$z}'";
        }
        if ($filters['search'] !== '') {
            $hw[] = $this->censusTextSearchSql('h', $filters['search']);
            $mw[] = $this->censusTextSearchSql('m', $filters['search']);
        }
        if ($filters['gender'] !== '') {
            $g = $db->escapeString($filters['gender']);
            $hw[] = "h.gender = '{$g}'";
            $mw[] = "m.gender = '{$g}'";
        }
        if ($filters['is_pwd'] === '1') {
            $hw[] = 'h.is_pwd = 1';
            $mw[] = '1=0';
        }
        if ($filters['is_senior'] === '1') {
            $seniorCutoff = date('Y-m-d', strtotime('-60 years'));
            $hw[] = "h.date_of_birth IS NOT NULL AND h.date_of_birth <= '{$seniorCutoff}'";
            $mw[] = "m.date_of_birth IS NOT NULL AND m.date_of_birth <= '{$seniorCutoff}'";
        }
        if ($filters['is_senior'] === 'with_id' || $filters['is_senior'] === 'without_id') {
            $seniorCutoff = date('Y-m-d', strtotime('-60 years'));
            if ($filters['is_senior'] === 'with_id') {
                $hw[] = "h.date_of_birth IS NOT NULL AND h.date_of_birth <= '{$seniorCutoff}' AND h.id_senior_path IS NOT NULL AND h.id_senior_path <> ''";
                $mw[] = "m.date_of_birth IS NOT NULL AND m.date_of_birth <= '{$seniorCutoff}' AND m.id_senior_path IS NOT NULL AND m.id_senior_path <> ''";
            } else {
                $hw[] = "h.date_of_birth IS NOT NULL AND h.date_of_birth <= '{$seniorCutoff}' AND (h.id_senior_path IS NULL OR h.id_senior_path = '')";
                $mw[] = "m.date_of_birth IS NOT NULL AND m.date_of_birth <= '{$seniorCutoff}' AND (m.id_senior_path IS NULL OR m.id_senior_path = '')";
            }
        }
        if ($filters['is_solo'] === '1') {
            $hw[] = 'h.is_solo_parent = 1';
            $mw[] = '1=0';
        }
        if ($filters['is_4ps'] === '1') {
            $hw[] = 'h.is_4ps = 1';
            $mw[] = '1=0';
        }
        if ($filters['is_indigent'] === '1') {
            $hw[] = 'h.monthly_income > 0 AND h.monthly_income <= 5000';
            $mw[] = 'm.monthly_income > 0 AND m.monthly_income <= 5000';
        }
        if ($filters['age_min'] !== '') {
            $d = date('Y-m-d', strtotime('-' . (int) $filters['age_min'] . ' years'));
            $hw[] = "h.date_of_birth IS NOT NULL AND h.date_of_birth <= '{$d}'";
            $mw[] = "m.date_of_birth IS NOT NULL AND m.date_of_birth <= '{$d}'";
        }
        if ($filters['age_max'] !== '') {
            $d = date('Y-m-d', strtotime('-' . (int) $filters['age_max'] . ' years'));
            $hw[] = "h.date_of_birth IS NOT NULL AND h.date_of_birth >= '{$d}'";
            $mw[] = "m.date_of_birth IS NOT NULL AND m.date_of_birth >= '{$d}'";
        }
        if ($filters['is_student'] === '1') {
            $hw[] = "UPPER(h.occupation) LIKE '%STUDENT%'";
            $mw[] = "UPPER(m.occupation) LIKE '%STUDENT%'";
        }
        if ($filters['is_employed'] === '1') {
            $hw[] = "TRIM(COALESCE(h.occupation, '')) <> '' AND UPPER(TRIM(h.occupation)) NOT IN ('STUDENT', 'OUT OF SCHOOL')";
            $mw[] = "TRIM(COALESCE(m.occupation, '')) <> '' AND UPPER(TRIM(m.occupation)) NOT IN ('STUDENT', 'OUT OF SCHOOL')";
        }
        if ($filters['is_osy'] === '1') {
            $youthMinDob = date('Y-m-d', strtotime('-30 years'));
            $youthMaxDob = date('Y-m-d', strtotime('-15 years'));
            $hw[] = "h.date_of_birth IS NOT NULL AND h.date_of_birth BETWEEN '{$youthMinDob}' AND '{$youthMaxDob}' AND (UPPER(h.occupation) LIKE '%OUT-OF-SCHOOL%' OR UPPER(h.occupation) LIKE '%OUT OF SCHOOL%' OR UPPER(h.occupation) LIKE '%OSY%')";
            $mw[] = "m.date_of_birth IS NOT NULL AND m.date_of_birth BETWEEN '{$youthMinDob}' AND '{$youthMaxDob}' AND (UPPER(m.occupation) LIKE '%OUT-OF-SCHOOL%' OR UPPER(m.occupation) LIKE '%OUT OF SCHOOL%' OR UPPER(m.occupation) LIKE '%OSY%')";
        }
        if ($filters['census_year'] !== '') {
            $y = (int) $filters['census_year'];
            $hw[] = "h.census_year = {$y}";
            $mw[] = "h2.census_year = {$y}";
        }

        if ($role === 'council') {
            $hw[] = "h.approval_status IN ('approved', 'pending')";
            $mw[] = "h2.approval_status IN ('approved', 'pending')";
            $zone = $db->escapeString($councilZone !== '' ? $councilZone : '__unassigned__');
            $hw[] = "h.zone = '{$zone}'";
            $mw[] = "h2.zone = '{$zone}'";
        } else {
            $hw[] = "h.approval_status = 'approved'";
            $mw[] = "h2.approval_status = 'approved'";
        }

        return [$hw, $mw];
    }

    private function personMatches(array $person, array $filters, bool $isHead): bool
    {
        if (($filters['is_pwd'] === '1' || $filters['is_solo'] === '1' || $filters['is_4ps'] === '1') && ! $isHead) {
            return false;
        }
        if ($filters['is_pwd'] === '1' && empty($person['is_pwd'])) {
            return false;
        }
        if ($filters['gender'] !== '' && strcasecmp((string) ($person['gender'] ?? ''), $filters['gender']) !== 0) {
            return false;
        }
        if ($filters['search'] !== '') {
            $needle = strtolower($filters['search']);
            $dob = (string) ($person['date_of_birth'] ?? '');
            $stamp = $dob !== '' ? strtotime($dob) : false;
            $hay = strtolower(trim(
                ($person['last_name'] ?? '') . ' ' . ($person['first_name'] ?? '') . ' ' . ($person['middle_name'] ?? '') . ' ' . ($person['household_no'] ?? '')
                . ' ' . $dob
                . ' ' . ($stamp ? date('M d, Y', $stamp) . ' ' . date('m/d/Y', $stamp) : '')
            ));
            if (! str_contains($hay, $needle)) {
                return false;
            }
        }

        $dob = (string) ($person['date_of_birth'] ?? '');
        $age = null;
        if ($dob !== '') {
            $born = date_create($dob);
            $age = $born ? (int) date_diff($born, date_create('today'))->y : null;
        }
        if ($filters['age_min'] !== '' && ($age === null || $age < (int) $filters['age_min'])) {
            return false;
        }
        if ($filters['age_max'] !== '' && ($age === null || $age > (int) $filters['age_max'])) {
            return false;
        }

        $occupation = strtoupper(trim((string) ($person['occupation'] ?? '')));
        if ($filters['is_student'] === '1' && ! str_contains($occupation, 'STUDENT')) {
            return false;
        }
        if ($filters['is_employed'] === '1' && ($occupation === '' || in_array($occupation, ['STUDENT', 'OUT OF SCHOOL'], true))) {
            return false;
        }
        if ($filters['is_osy'] === '1') {
            if ($age === null || $age < 15 || $age > 30) {
                return false;
            }
            if (! str_contains($occupation, 'OUT OF SCHOOL') && ! str_contains($occupation, 'OUT-OF-SCHOOL') && ! str_contains($occupation, 'OSY')) {
                return false;
            }
        }
        if (in_array($filters['is_senior'], ['1', 'with_id', 'without_id'], true)) {
            if ($age === null || $age < 60) {
                return false;
            }
            $hasId = trim((string) ($person['id_senior_path'] ?? '')) !== '';
            if ($filters['is_senior'] === 'with_id' && ! $hasId) {
                return false;
            }
            if ($filters['is_senior'] === 'without_id' && $hasId) {
                return false;
            }
        }
        if ($filters['is_indigent'] === '1') {
            $income = (float) ($person['monthly_income'] ?? 0);
            if ($income <= 0 || $income > 5000) {
                return false;
            }
        }

        return true;
    }
}
