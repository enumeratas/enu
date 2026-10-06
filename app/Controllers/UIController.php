<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class UIController extends BaseController
{
    private function councilAssignedZone(?int $userId = null): string
    {
        $userId = $userId ?? (int) session()->get('user_id');
        if ($userId <= 0) {
            return '';
        }
        $user = (new \App\Models\UserModel())->find($userId);

        return is_array($user) ? trim((string) ($user['council_zone'] ?? '')) : '';
    }

    // ── Shared census view builder (captain + secretary) ──────────────────────
    private function _censusView(string $role): \CodeIgniter\HTTP\ResponseInterface|string
    {
        $db = \Config\Database::connect();
        $councilZone = $role === 'council' ? $this->councilAssignedZone() : '';

        $approvedOnly = "h.approval_status = 'approved'";
        $approvedMemberOnly = "h2.approval_status = 'approved'";

        // ── Active filters from GET ───────────────────────────────────────
        $filters = [
            'zone'        => trim($_GET['zone']        ?? ''),
            'gender'      => trim($_GET['gender']      ?? ''),
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
            'search'      => trim($_GET['search']      ?? ''),
            'census_year' => trim($_GET['census_year'] ?? ''),
        ];

        // Council members may only look inside their assigned zone.
        if ($role === 'council' && $filters['zone'] !== '' && $filters['zone'] !== $councilZone) {
            $filters['zone'] = $councilZone !== '' ? $councilZone : '';
        }

        // ── Available census years (distinct years recorded in DB) ────────
        $censusYears = [];
        try {
            $yearRows = $db->query(
                "SELECT DISTINCT census_year FROM households
                 WHERE census_year IS NOT NULL AND census_year > 0
                 ORDER BY census_year DESC"
            )->getResultArray();
            $censusYears = array_column($yearRows, 'census_year');
        } catch (\Exception $e) {
            // Column may not exist yet (migration not run) — degrade gracefully
        }
        // Always ensure current year appears even if no records yet
        $currentYear = (int) date('Y');
        if (! in_array($currentYear, $censusYears)) {
            array_unshift($censusYears, $currentYear);
        }

        // ── Determine filter mode ─────────────────────────────────────────
        // Any filter that can match members (gender, age, student) needs the
        // UNION view. Head-only flags (pwd, solo, 4ps, senior, indigent) stay
        // on the households table only.
        $needsUnion = (
            $filters['gender']     !== '' ||
            $filters['age_min']    !== '' ||
            $filters['age_max']    !== '' ||
            $filters['is_student'] !== '' ||
            $filters['is_employed'] !== '' ||
            $filters['is_osy']     !== '' ||
            $filters['is_senior']  !== ''
        );

        $hasAnyFilter = (
            $filters['zone']        !== '' ||
            $filters['gender']      !== '' ||
            $filters['age_min']     !== '' ||
            $filters['age_max']     !== '' ||
            $filters['is_pwd']      !== '' ||
            $filters['is_senior']   !== '' ||
            $filters['is_solo']     !== '' ||
            $filters['is_4ps']      !== '' ||
            $filters['is_student']  !== '' ||
            $filters['is_employed'] !== '' ||
            $filters['is_osy']      !== '' ||
            $filters['is_indigent'] !== '' ||
            $filters['search']      !== '' ||
            $filters['census_year'] !== ''
        );

        // hasSpecialFilter drives the view to show the filter-results table
        $hasSpecialFilter = $hasAnyFilter;

        // ── Stats: household cards plus the complete head/member population ─
        // Build a fresh query for every count. Cloning the Model reuses one
        // builder, so later cards would drop the zone filter after the first count.
        $statApprovals = $role === 'council' ? ['approved', 'pending'] : ['approved'];
        $statZone = null;
        if ($role === 'council') {
            $statZone = $councilZone !== '' ? $councilZone : '__unassigned__';
        } elseif ($filters['zone'] !== '') {
            $statZone = $filters['zone'];
        }
        $statYear = $filters['census_year'] !== '' ? (int) $filters['census_year'] : null;

        $householdStatQuery = static function () use ($db, $statApprovals, $statZone, $statYear) {
            $query = $db->table('households')->whereIn('approval_status', $statApprovals);
            if ($statZone !== null) {
                $query->where('zone', $statZone);
            }
            if ($statYear !== null) {
                $query->where('census_year', $statYear);
            }

            return $query;
        };

        $populationHead = $db->table('households h')
            ->where('h.approval_status', 'approved');
        $populationMember = $db->table('household_members m')
            ->join('households h', 'h.household_no = m.household_no', 'inner')
            ->where('h.approval_status', 'approved');

        if ($role === 'council') {
            $populationHead->where('h.zone', $councilZone !== '' ? $councilZone : '__unassigned__');
            $populationMember->where('h.zone', $councilZone !== '' ? $councilZone : '__unassigned__');
        }
        if ($filters['zone'] !== '') {
            $populationHead->where('h.zone', $filters['zone']);
            $populationMember->where('h.zone', $filters['zone']);
        }
        if ($filters['census_year'] !== '') {
            $populationHead->where('h.census_year', (int) $filters['census_year']);
            $populationMember->where('h.census_year', (int) $filters['census_year']);
        }

        $totalPopulation = (clone $populationHead)->countAllResults(false)
            + (clone $populationMember)->countAllResults(false);
        $totalMale = (clone $populationHead)->where('h.gender', 'Male')->countAllResults(false)
            + (clone $populationMember)->where('m.gender', 'Male')->countAllResults(false);
        $totalFemale = (clone $populationHead)->where('h.gender', 'Female')->countAllResults(false)
            + (clone $populationMember)->where('m.gender', 'Female')->countAllResults(false);

        $osyHead = (clone $populationHead)
            ->where('h.date_of_birth IS NOT NULL', null, false)
            ->where('h.date_of_birth >=', date('Y-m-d', strtotime('-30 years')))
            ->where('h.date_of_birth <=', date('Y-m-d', strtotime('-15 years')))
            ->groupStart()
            ->like('h.occupation', 'OUT-OF-SCHOOL')
            ->orLike('h.occupation', 'OUT OF SCHOOL')
            ->orLike('h.occupation', 'OSY')
            ->groupEnd()
            ->countAllResults(false);
        $osyMember = (clone $populationMember)
            ->where('m.date_of_birth IS NOT NULL', null, false)
            ->where('m.date_of_birth >=', date('Y-m-d', strtotime('-30 years')))
            ->where('m.date_of_birth <=', date('Y-m-d', strtotime('-15 years')))
            ->groupStart()
            ->like('m.occupation', 'OUT-OF-SCHOOL')
            ->orLike('m.occupation', 'OUT OF SCHOOL')
            ->orLike('m.occupation', 'OSY')
            ->groupEnd()
            ->countAllResults(false);

        $stats = [
            'totalHouseholds'  => $householdStatQuery()->countAllResults(),
            'totalPopulation'  => $totalPopulation,
            'totalMale'        => $totalMale,
            'totalFemale'      => $totalFemale,
            'outOfSchoolYouth' => $osyHead + $osyMember,
            'pwds'             => $householdStatQuery()->where('is_pwd', 1)->countAllResults(),
            'fourPs'           => $householdStatQuery()->where('is_4ps', 1)->countAllResults(),
            'seniors'          => $householdStatQuery()->where('is_senior_citizen', 1)->countAllResults(),
            'soloParent'       => $householdStatQuery()->where('is_solo_parent', 1)->countAllResults(),
        ];

        $perPage = 15;
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        // ── Build WHERE clauses for households (heads) ────────────────────
        $hw = [];   // head WHERE conditions
        $mw = [];   // member WHERE conditions (only used in UNION mode)

        if ($filters['zone'] !== '') {
            $z    = $db->escapeString($filters['zone']);
            $hw[] = "h.zone = '{$z}'";
            $mw[] = "h2.zone = '{$z}'";
        }
        if ($filters['search'] !== '') {
            $hw[] = $this->censusTextSearchSql('h', $filters['search']);
            $mw[] = $this->censusTextSearchSql('m', $filters['search']);
        }
        if ($filters['gender'] !== '') {
            $g    = $db->escapeString($filters['gender']);
            $hw[] = "h.gender = '{$g}'";
            $mw[] = "m.gender = '{$g}'";
        }
        if ($filters['is_pwd'] === '1') {
            $hw[] = "h.is_pwd = 1";
            $mw[] = "1=0";
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
            $hw[] = "h.is_solo_parent = 1";
            $mw[] = "1=0";
        }
        if ($filters['is_4ps'] === '1') {
            $hw[] = "h.is_4ps = 1";
            $mw[] = "1=0";
        }
        if ($filters['is_indigent'] === '1') {
            $hw[] = "h.monthly_income > 0 AND h.monthly_income <= 5000";
            $mw[] = "m.monthly_income > 0 AND m.monthly_income <= 5000";
        }
        if ($filters['age_min'] !== '') {
            // age >= age_min  →  born on or before (today - age_min years)
            $d    = date('Y-m-d', strtotime('-' . (int)$filters['age_min'] . ' years'));
            $hw[] = "h.date_of_birth IS NOT NULL AND h.date_of_birth <= '{$d}'";
            $mw[] = "m.date_of_birth IS NOT NULL AND m.date_of_birth <= '{$d}'";
        }
        if ($filters['age_max'] !== '') {
            // age <= age_max  →  born on or after (today - age_max years)
            $d    = date('Y-m-d', strtotime('-' . (int)$filters['age_max'] . ' years'));
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
            $headOsyCondition = "(UPPER(h.occupation) LIKE '%OUT-OF-SCHOOL%' OR UPPER(h.occupation) LIKE '%OUT OF SCHOOL%' OR UPPER(h.occupation) LIKE '%OSY%')";
            $memberOsyCondition = "(UPPER(m.occupation) LIKE '%OUT-OF-SCHOOL%' OR UPPER(m.occupation) LIKE '%OUT OF SCHOOL%' OR UPPER(m.occupation) LIKE '%OSY%')";
            $hw[] = "h.date_of_birth IS NOT NULL AND h.date_of_birth BETWEEN '{$youthMinDob}' AND '{$youthMaxDob}' AND {$headOsyCondition}";
            $mw[] = "m.date_of_birth IS NOT NULL AND m.date_of_birth BETWEEN '{$youthMinDob}' AND '{$youthMaxDob}' AND {$memberOsyCondition}";
        }
        if ($filters['census_year'] !== '') {
            $y    = (int) $filters['census_year'];
            $hw[] = "h.census_year = {$y}";
            $mw[] = "h2.census_year = {$y}";
        }

        $approvalHeadClause = $role === 'council'
            ? "h.approval_status IN ('approved', 'pending')"
            : $approvedOnly;
        $approvalMemberClause = $role === 'council'
            ? "h2.approval_status IN ('approved', 'pending')"
            : $approvedMemberOnly;
        $hw[] = $approvalHeadClause;
        $mw[] = $approvalMemberClause;
        if ($role === 'council') {
            $zone = $db->escapeString($councilZone ?: '__unassigned__');
            $hw[] = "h.zone = '{$zone}'";
            $mw[] = "h2.zone = '{$zone}'";
        }

        $hwSql = ! empty($hw) ? ' WHERE ' . implode(' AND ', $hw) : '';

        // ── No filter at all: show default heads-only table ───────────────
        if (! $hasAnyFilter) {
            $approvalClause = $role === 'council'
                ? " AND h.approval_status IN ('approved', 'pending')"
                : " AND h.approval_status = 'approved'";
            $approvalCountClause = $role === 'council'
                ? " AND approval_status IN ('approved', 'pending')"
                : " AND approval_status = 'approved'";
            $ownerClause = $role === 'council' ? " AND h.zone = '" . $db->escapeString($councilZone ?: '__unassigned__') . "'" : '';
            $ownerCountClause = $role === 'council' ? " AND zone = '" . $db->escapeString($councilZone ?: '__unassigned__') . "'" : '';
            $households = $db->query(
                "SELECT * FROM households h WHERE 1=1{$approvalClause}{$ownerClause} ORDER BY h.last_name ASC, h.first_name ASC, h.household_no ASC LIMIT {$perPage} OFFSET {$offset}"
            )->getResultArray();
            $totalHouseholdsFiltered = (int) $db->query("SELECT COUNT(*) AS c FROM households WHERE 1=1{$approvalCountClause}{$ownerCountClause}")->getRow()->c;

            $pendingHouseholds = $role === 'secretary'
                ? $db->query("SELECT * FROM households WHERE approval_status = 'pending' ORDER BY created_at ASC")->getResultArray()
                : [];

            $viewFile = ($role === 'captain') ? 'dashboard/captain/census' : 'dashboard/secretary/census';
            return view($viewFile, array_merge($stats, [
                'role'                    => $role,
                'councilZone'             => $councilZone,
                'households'              => $households,
                'persons'                 => [],
                'hasSpecialFilter'        => false,
                'filteredTotal'           => $totalHouseholdsFiltered,
                'totalHouseholdsFiltered' => $totalHouseholdsFiltered,
                'perPage'                 => $perPage,
                'currentPage'             => $page,
                'filters'                 => $filters,
                'censusYears'             => $censusYears,
                'pendingSeparations'      => $role === 'secretary'
                    ? \App\Controllers\CensusController::getPendingSeparations()
                    : [],
                'pendingHouseholds'       => $pendingHouseholds,
            ]));
        }

        // ── Filters active ────────────────────────────────────────────────
        if (! $needsUnion) {
            // ── HEAD-ONLY filter (no age/student) ─────────────────────────
            // All active filters apply only to the households table.
            $sql      = "SELECT * FROM households h{$hwSql} ORDER BY h.last_name ASC, h.first_name ASC, h.household_no ASC";
            $countSql = "SELECT COUNT(*) AS c FROM households h{$hwSql}";

            $filteredTotal = (int) $db->query($countSql)->getRow()->c;
            $persons       = $db->query("{$sql} LIMIT {$perPage} OFFSET {$offset}")->getResultArray();

            // Add a synthetic 'relationship' column so the view template works
            foreach ($persons as &$p) {
                $p['relationship'] = 'Household Head';
            }
            unset($p);
        } else {
            // ── UNION filter (age or student — includes members) ──────────
            $mwSql = ! empty($mw) ? ' WHERE ' . implode(' AND ', $mw) : '';

            $headSql = "SELECT h.household_no, h.last_name, h.first_name, h.middle_name,
                h.suffix, h.date_of_birth, h.gender, h.civil_status, h.occupation,
                '' AS work_detail, '' AS grade_level,
                h.monthly_income, h.philhealth_no, h.educational_attainment,
                h.contact_number, h.zone, h.is_pwd, h.is_senior_citizen, h.id_senior_path,
                h.is_solo_parent, h.is_4ps, h.approval_status, h.record_status, 'Household Head' AS relationship
                FROM households h{$hwSql}";

            $memberSql = "SELECT m.household_no, m.last_name, m.first_name, m.middle_name,
                m.suffix, m.date_of_birth, m.gender, '' AS civil_status, m.occupation,
                m.work_detail, m.grade_level,
                m.monthly_income, m.philhealth_no, m.educational_attainment,
                '' AS contact_number, h2.zone, 0 AS is_pwd, 0 AS is_senior_citizen, m.id_senior_path,
                0 AS is_solo_parent, 0 AS is_4ps, h2.approval_status, h2.record_status, m.relationship
                FROM household_members m
                INNER JOIN households h2 ON h2.household_no = m.household_no{$mwSql}";

            // Keep each SELECT unwrapped for compatibility with MySQL versions
            // that reject parenthesized SELECT statements in a UNION.
            $unionBase     = "{$headSql} UNION ALL {$memberSql}";
            $filteredTotal = (int) $db->query("SELECT COUNT(*) AS total FROM ({$unionBase}) AS c")->getRow()->total;
            $persons       = $db->query(
                "{$unionBase} ORDER BY last_name ASC, first_name ASC, household_no ASC, relationship ASC LIMIT {$perPage} OFFSET {$offset}"
            )->getResultArray();
        }

        $viewFile = ($role === 'captain') ? 'dashboard/captain/census' : 'dashboard/secretary/census';

        $pendingHouseholds = $role === 'secretary'
            ? $db->query("SELECT * FROM households WHERE approval_status = 'pending' ORDER BY created_at ASC")->getResultArray()
            : [];

        return view($viewFile, array_merge($stats, [
            'role'                    => $role,
            'councilZone'             => $councilZone,
            'households'              => [],
            'persons'                 => $persons,
            'hasSpecialFilter'        => true,
            'filteredTotal'           => $filteredTotal,
            'totalHouseholdsFiltered' => $filteredTotal,
            'perPage'                 => $perPage,
            'currentPage'             => $page,
            'filters'                 => $filters,
            'censusYears'             => $censusYears,
            'pendingSeparations'      => $role === 'secretary'
                ? \App\Controllers\CensusController::getPendingSeparations()
                : [],
            'pendingHouseholds'       => $pendingHouseholds,
        ]));
    }

    // ── Auth ──────────────────────────────────────────
    public function home()
    {
        $publicEvents = [];

        try {
            $publicEvents = (new \App\Models\ScheduleModel())->getPublicEvents(
                date('Y-m-d'),
                date('Y-m-d', strtotime('+90 days')),
                6
            );
        } catch (\Throwable $e) {
            log_message('error', 'Public events lookup failed: ' . $e->getMessage());
        }

        return view('index', ['publicEvents' => $publicEvents], ['debug' => false]);
    }
    public function login()
    {
        $rememberedRole = \App\Libraries\SessionHelper::rememberedDashboard();
        if (is_string($rememberedRole) && $rememberedRole !== '') {
            return redirect()->to('/' . $rememberedRole . '/dashboard');
        }

        return view('login');
    }
    public function select_role()
    {
        return view('select_role');
    }
    public function create_acc()
    {
        return view('create_acc');
    }
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
    public function faqs()
    {
        return view('faqs');
    }
    public function privacy_policy()
    {
        return view('privacy_policy');
    }
    public function terms_of_use()
    {
        return view('terms');
    }

    // ── Dashboard data helper (shared by captain + secretary) ────────────────
    private function _dashboardData(): array
    {
        $db = \Config\Database::connect();

        $totalHouseholds  = (int) $db->table('households')->countAllResults();
        $totalMembers     = (int) $db->table('household_members')->countAllResults();
        $totalPopulation  = $totalHouseholds + $totalMembers;

        $pendingClearances  = (int) $db->table('clearance_requests')->where('status', 'pending')->countAllResults();
        $approvedClearances = (int) $db->table('clearance_requests')->where('status', 'approved')->countAllResults();
        $totalClearances    = (int) $db->table('clearance_requests')->countAllResults();

        $pendingBlotter    = (int) $db->table('blotter_reports')->where('status', 'pending')->countAllResults();
        $activeBlotter     = (int) $db->table('blotter_reports')->where('status', 'under_investigation')->countAllResults();
        $resolvedBlotter   = (int) $db->table('blotter_reports')->where('status', 'resolved')->countAllResults();

        $pendingAccounts  = (int) $db->table('users')->where('status', 'pending')->whereIn('role', ['resident', 'sk'])->countAllResults();

        $pwds        = (int) $db->table('households')->where('is_pwd', 1)->countAllResults();
        $seniors     = (int) $db->table('households')->where('is_senior_citizen', 1)->countAllResults();
        $soloParents = (int) $db->table('households')->where('is_solo_parent', 1)->countAllResults();
        $fourPs      = (int) $db->table('households')->where('is_4ps', 1)->countAllResults();

        // Recent clearance requests (last 5)
        $recentClearances = $db->table('clearance_requests cr')
            ->select("cr.id, cr.document_type, cr.status, cr.created_at,
                      CONCAT(TRIM(COALESCE(u.first_name,'')), ' ', TRIM(COALESCE(u.last_name,''))) AS resident_name")
            ->join('users u', 'u.id = cr.user_id', 'left')
            ->orderBy('cr.created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // Recent blotter reports (last 5)
        $recentBlotter = $db->table('blotter_reports')
            ->select('id, complainant_name, incident_type, status, created_at')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // Recent concerns / inquiries (last 5)
        $recentConcerns = $db->tableExists('concern_submissions')
            ? $db->table('concern_submissions')
            ->select('id, full_name, subject, category, appointment_date, appointment_time, status, created_at')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray()
            : [];

        // Today's appointments from schedules
        $todayAppts = $db->table('schedules')
            ->where('event_date', date('Y-m-d'))
            ->orderBy('start_time', 'ASC')
            ->limit(5)
            ->get()->getResultArray();

        // Today's blotter appointments
        $todayBlotterAppts = $db->table('blotter_reports')
            ->select('id, complainant_name, incident_type, appointment_time')
            ->where('appointment_date', date('Y-m-d'))
            ->orderBy('appointment_time', 'ASC')
            ->limit(5)
            ->get()->getResultArray();

        return compact(
            'totalHouseholds',
            'totalMembers',
            'totalPopulation',
            'pendingClearances',
            'approvedClearances',
            'totalClearances',
            'pendingBlotter',
            'activeBlotter',
            'resolvedBlotter',
            'pendingAccounts',
            'pwds',
            'seniors',
            'soloParents',
            'fourPs',
            'recentClearances',
            'recentBlotter',
            'recentConcerns',
            'todayAppts',
            'todayBlotterAppts'
        );
    }

    // ── Captain ───────────────────────────────────────
    public function captain_dashboard()
    {
        // Captain shares the secretary dashboard so the two roles see the same page.
        $data         = $this->_dashboardData();
        $data['role'] = 'captain';
        return view('dashboard/secretary', $data);
    }

    public function admin_dashboard()
    {
        $data         = $this->_dashboardData();
        $data['role'] = 'admin';
        return view('dashboard/secretary', $data);
    }
    public function captain_census()
    {
        return $this->_censusView('captain');
    }

    public function council_census()
    {
        return $this->_censusView('council');
    }

    // ── Fetch ownership change history for a household ────────────────────────
    private function _getOwnershipChanges(string $householdNo): array
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('household_ownership_changes')) {
            return [];
        }
        return $db->table('household_ownership_changes oc')
            ->select('oc.*, CONCAT(COALESCE(u.first_name,""), " ", COALESCE(u.last_name,"")) AS changer_name')
            ->join('users u', 'u.id = oc.changed_by', 'left')
            ->where('oc.household_no', $householdNo)
            ->orderBy('oc.created_at', 'DESC')
            ->get()->getResultArray();
    }

    // ── Fetch all co-families sharing the same address group ──────────────────
    private function _getSharedFamilies(array $household): array
    {
        $group = $household['shared_address_group'] ?? null;
        if (empty($group)) {
            return [];
        }
        $db = \Config\Database::connect();
        try {
            return $db->table('households')
                ->select('household_no, first_name, last_name, zone, address, family_number, num_families')
                ->where('shared_address_group', $group)
                ->where('household_no !=', $household['household_no'])
                ->orderBy('family_number', 'ASC')
                ->get()->getResultArray();
        } catch (\Exception $e) {
            // Column may not exist yet before migration runs
            return [];
        }
    }

    private function _getTransferHouseholds(?string $currentHouseholdNo): array
    {
        $db = \Config\Database::connect();

        $query = $db->table('households')
            ->select('household_no, first_name, last_name, zone, address, num_families')
            ->orderBy('household_no', 'ASC');

        if ($currentHouseholdNo !== null && $currentHouseholdNo !== '') {
            $query->where('household_no !=', $currentHouseholdNo);
        }

        return $query->get()->getResultArray();
    }

    public function captain_household($id = null)
    {
        $householdModel = new \App\Models\HouseholdModel();
        $household      = $householdModel->getWithMembers((string) $id);

        if (! $household) {
            return redirect()->to('/captain/census')->with('error', 'Household not found.');
        }

        return view('dashboard/captain/household', [
            'householdId'      => $household['household_no'],
            'household'        => $household,
            'members'          => $household['members'],
            'hasSpouse'        => count(array_filter($household['members'], static fn($member) => strtolower((string) ($member['relationship'] ?? '')) === 'spouse')) > 0,
            'role'             => 'captain',
            'ownershipChanges' => $this->_getOwnershipChanges($household['household_no']),
            'sharedFamilies'   => $this->_getSharedFamilies($household),
            'transferHouseholds' => $this->_getTransferHouseholds($household['household_no']),
        ]);
    }
    public function captain_clearance()
    {
        return view('dashboard/captain/clearance');
    }
    public function captain_clearance_detail($id = '001')
    {
        return view('dashboard/captain/clearance_detail', ['requestId' => $id, 'role' => 'captain']);
    }
    // ── Shared report data builder ────────────────────────────────────────────
    private function _buildReportData(): array
    {
        $db = \Config\Database::connect();

        // ── Fetch all persons ─────────────────────────────────────────────────
        $heads   = $db->table('households')
            ->select('date_of_birth, gender, civil_status, occupation,
                      is_pwd, is_solo_parent, is_4ps, is_senior_citizen,
                      is_indigenous, monthly_income, educational_attainment,
                      registered_voter, num_families,
                      water_source_level, water_safety_managed,
                      sanitation_basic, sanitation_managed')
            ->get()->getResultArray();

        $members = $db->table('household_members')
            ->select('date_of_birth, gender, occupation, monthly_income, educational_attainment')
            ->get()->getResultArray();

        // ── Age helper ────────────────────────────────────────────────────────
        $age = function (?string $dob): ?int {
            if (empty($dob)) return null;
            try {
                return (int) date_diff(date_create($dob), date_create('today'))->y;
            } catch (\Throwable $e) {
                return null;
            }
        };

        // ── F. Population by Age Bracket (heads + members, with gender) ───────
        $brackets = [
            ['label' => 'Children 0 – 5 years old',   'min' => 0,  'max' => 5],
            ['label' => 'Children 6 – 12 years old',  'min' => 6,  'max' => 12],
            ['label' => 'Children 13 – 17 years old', 'min' => 13, 'max' => 17],
            ['label' => 'Adult 18 – 35 years old',    'min' => 18, 'max' => 35],
            ['label' => 'Adult 36 – 50 years old',    'min' => 36, 'max' => 50],
            ['label' => 'Adult 51 – 65 years old',    'min' => 51, 'max' => 65],
            ['label' => 'Adult 66 years old & above', 'min' => 66, 'max' => 999],
        ];

        $ageBrackets = array_map(fn($b) => array_merge($b, ['male' => 0, 'female' => 0, 'total' => 0]), $brackets);

        $countPerson = function (array $person) use ($age, $brackets, &$ageBrackets): void {
            $a = $age($person['date_of_birth'] ?? null);
            if ($a === null) return;
            foreach ($brackets as $i => $b) {
                if ($a >= $b['min'] && $a <= $b['max']) {
                    $g = strtolower($person['gender'] ?? '');
                    if ($g === 'female')     $ageBrackets[$i]['female']++;
                    elseif ($g === 'male')   $ageBrackets[$i]['male']++;
                    // unknown gender: no male/female increment, but still count total
                    $ageBrackets[$i]['total']++;
                    break;
                }
            }
        };

        foreach ($heads   as $h) $countPerson($h);
        foreach ($members as $m) $countPerson($m);

        // ── G. Population by Sector ───────────────────────────────────────────
        $sectorMap = [
            'Labor Force' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Unemployed' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Out-of-School Youth (OSY) 15–24 y/o' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Out-of-School Children (OSC) 6–14 y/o' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Persons with Disabilities (PWDs)' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Overseas Filipino Workers (OFWs)' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Solo Parents' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Civil Status: Single' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Civil Status: Married' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Civil Status: Widowed' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Civil Status: Separated/Annulled' => ['male' => 0, 'female' => 0, 'total' => 0],
        ];

        $addSectorCount = function (string $label, array $person) use (&$sectorMap): void {
            $gender = strtolower($person['gender'] ?? '');
            $sexKey = ($gender === 'female') ? 'female' : 'male';
            if (! array_key_exists($label, $sectorMap)) {
                return;
            }
            $sectorMap[$label][$sexKey]++;
            $sectorMap[$label]['total']++;
        };

        $collectSectorData = function (array $person) use (&$sectorMap, $age, $addSectorCount): void {
            $occ = strtolower($person['occupation'] ?? '');
            $a   = $age($person['date_of_birth'] ?? null);

            if (! empty($occ) && ! in_array($occ, ['none', 'n/a', 'unemployed', ''])) {
                $addSectorCount('Labor Force', $person);
            }

            if (str_contains($occ, 'unemploy')) {
                $addSectorCount('Unemployed', $person);
            }

            if (str_contains($occ, 'ofw') || str_contains($occ, 'overseas')) {
                $addSectorCount('Overseas Filipino Workers (OFWs)', $person);
            }

            if ((int) ($person['is_pwd'] ?? 0)) {
                $addSectorCount('Persons with Disabilities (PWDs)', $person);
            }

            if ((int) ($person['is_solo_parent'] ?? 0)) {
                $addSectorCount('Solo Parents', $person);
            }

            if ($a !== null && $a >= 15 && $a <= 24 && in_array($occ, ['', 'none', 'n/a', 'student', 'out-of-school'])) {
                $addSectorCount('Out-of-School Youth (OSY) 15–24 y/o', $person);
            }

            if ($a !== null && $a >= 6 && $a <= 14 && in_array($occ, ['', 'none', 'n/a', 'student', 'out-of-school'])) {
                $addSectorCount('Out-of-School Children (OSC) 6–14 y/o', $person);
            }

            $cs = strtolower(trim($person['civil_status'] ?? ''));
            if ($cs === 'single') {
                $addSectorCount('Civil Status: Single', $person);
            }
            if ($cs === 'married') {
                $addSectorCount('Civil Status: Married', $person);
            }
            if ($cs === 'widowed' || $cs === 'widow') {
                $addSectorCount('Civil Status: Widowed', $person);
            }
            if ($cs === 'separated' || $cs === 'annulled') {
                $addSectorCount('Civil Status: Separated/Annulled', $person);
            }
        };

        foreach ($heads as $h) {
            $collectSectorData($h);
        }

        foreach ($members as $m) {
            $collectSectorData($m);
        }

        $sectorRows = [];
        foreach (array_keys($sectorMap) as $label) {
            $sectorRows[] = [
                'label' => $label,
                'male' => (int) $sectorMap[$label]['male'],
                'female' => (int) $sectorMap[$label]['female'],
                'total' => (int) $sectorMap[$label]['total'],
            ];
        }

        // ── Water & Sanitation (H) ────────────────────────────────────────────
        $waterRows = [
            ['label' => 'Level I – Point Source',       'total' => (int)$db->table('households')->where('water_source_level', '1')->countAllResults()],
            ['label' => 'Level II – Communal Faucet',   'total' => (int)$db->table('households')->where('water_source_level', '2')->countAllResults()],
            ['label' => 'Level III – Individual Connection', 'total' => (int)$db->table('households')->where('water_source_level', '3')->countAllResults()],
            ['label' => 'Safe Water (Managed)',          'total' => (int)$db->table('households')->where('water_safety_managed', 1)->countAllResults()],
        ];

        $sanitationRows = [
            ['label' => 'Basic Sanitation Facility',    'total' => (int)$db->table('households')->where('sanitation_basic', 1)->countAllResults()],
            ['label' => 'Safely Managed Sanitation',    'total' => (int)$db->table('households')->where('sanitation_managed', 1)->countAllResults()],
        ];

        // ── Educational Attainment ────────────────────────────────────────────
        $eduCounts = [];
        foreach (
            array_merge(
                array_column($heads,   'educational_attainment'),
                array_column($members, 'educational_attainment')
            ) as $edu
        ) {
            $key = ucwords(strtolower(trim($edu ?? 'Not Specified'))) ?: 'Not Specified';
            $eduCounts[$key] = ($eduCounts[$key] ?? 0) + 1;
        }
        arsort($eduCounts);
        $eduRows = array_map(fn($label, $total) => ['label' => $label, 'total' => $total], array_keys($eduCounts), $eduCounts);

        // ── Summary stats ─────────────────────────────────────────────────────
        $totalPop        = count($heads) + count($members);
        $totalHouseholds = count($heads);
        $totalMale       = array_sum(array_column($ageBrackets, 'male'));
        $totalFemale     = array_sum(array_column($ageBrackets, 'female'));
        $totalClearances = $db->table('clearance_requests')->where('status', 'approved')->countAllResults();
        $avgHHSize       = $totalHouseholds > 0 ? round($totalPop / $totalHouseholds, 1) : 0;
        $registeredVoters = $db->table('households')->where('registered_voter', 1)->countAllResults();
        $totalFamilies   = (int) $db->query("SELECT COALESCE(SUM(num_families),0) AS t FROM households")->getRow()->t;

        return [
            'totalPop'         => $totalPop,
            'totalMale'        => $totalMale,
            'totalFemale'      => $totalFemale,
            'totalHouseholds'  => $totalHouseholds,
            'totalClearances'  => $totalClearances,
            'avgHHSize'        => $avgHHSize,
            'ageBrackets'      => $ageBrackets,
            'sectorRows'       => $sectorRows,
            'waterRows'        => $waterRows,
            'sanitationRows'   => $sanitationRows,
            'eduRows'          => $eduRows,
            'registeredVoters' => $registeredVoters,
            'totalFamilies'    => $totalFamilies,
        ];
    }

    public function captain_reports()
    {
        return view('dashboard/captain/reports', $this->_buildReportData());
    }
    public function captain_chatbot()
    {
        return $this->redirectAwayFromChatbot();
    }
    public function captain_blotter()
    {
        return view('dashboard/captain/blotter');
    }
    public function captain_blotter_detail($id = 'BL-001')
    {
        return view('dashboard/captain/blotter_detail', ['blotterId' => $id, 'role' => 'captain']);
    }
    public function captain_settings()
    {
        return view('dashboard/captain/settings');
    }

    public function captain_create_account()
    {
        $role = session()->get('role');
        if ($role === 'admin') {
            return redirect()->to('/admin/create-account');
        }
        if ($role !== 'captain') {
            return redirect()->to('/' . ($role ?: 'login') . '/dashboard')
                ->with('error', 'Only the captain can appoint a Secretary from this page.');
        }

        return view('dashboard/captain/create_account', $this->officialAccountPageData());
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

    // ── Secretary ─────────────────────────────────────
    public function secretary_dashboard()
    {
        $data         = $this->_dashboardData();
        $data['role'] = 'secretary';
        return view('dashboard/secretary', $data);
    }
    public function secretary_census()
    {
        return $this->_censusView('secretary');
    }

    public function secretary_residents()
    {
        $db = \Config\Database::connect();

        // ── Filters ───────────────────────────────────────────────────────
        $search     = trim($_GET['search']      ?? '');
        $householdNo = trim($_GET['household_no'] ?? '');
        $filterZone = trim($_GET['zone']        ?? '');
        $filterAcct = trim($_GET['account']     ?? ''); // 'active','pending','none'
        $filterAge  = trim($_GET['age_group']   ?? ''); // 'minor','adult'
        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $perPage    = 20;
        $offset     = ($page - 1) * $perPage;

        // Census people are loaded first. Accounts of every role are attached
        // afterwards by name, so secretary, council, SK, and resident accounts
        // all show their real status.
        $headSql = "SELECT
            h.household_no,
            h.last_name,
            h.first_name,
            h.middle_name,
            h.suffix,
            h.date_of_birth,
            h.gender,
            h.zone,
            'Household Head' AS relationship
        FROM households h";

        $memberSql = "SELECT
            m.household_no,
            m.last_name,
            m.first_name,
            m.middle_name,
            m.suffix,
            m.date_of_birth,
            m.gender,
            h2.zone,
            m.relationship
        FROM household_members m
        INNER JOIN households h2 ON h2.household_no = m.household_no";

        // ── Apply WHERE clauses ───────────────────────────────────────────
        $hwhere = [];
        $mwhere = [];

        if ($search !== '') {
            $hwhere[] = \App\Libraries\RecordSearch::clause(
                ['h.last_name', 'h.first_name', 'h.middle_name', 'h.household_no'],
                ['h.date_of_birth'],
                $search
            );
            $mwhere[] = \App\Libraries\RecordSearch::clause(
                ['m.last_name', 'm.first_name', 'm.middle_name', 'm.household_no'],
                ['m.date_of_birth'],
                $search
            );
        }
        if ($householdNo !== '') {
            $householdNoEscaped = $db->escapeLikeString($householdNo);
            $hwhere[] = "h.household_no LIKE '%{$householdNoEscaped}%'";
            $mwhere[] = "m.household_no LIKE '%{$householdNoEscaped}%'";
        }
        if ($filterZone !== '') {
            $z        = $db->escapeString($filterZone);
            $hwhere[] = "h.zone = '{$z}'";
            $mwhere[] = "h2.zone = '{$z}'";
        }
        if ($filterAge === 'minor') {
            $cutoff   = date('Y-m-d', strtotime('-18 years'));
            $hwhere[] = "h.date_of_birth IS NOT NULL AND h.date_of_birth > '{$cutoff}'";
            $mwhere[] = "m.date_of_birth IS NOT NULL AND m.date_of_birth > '{$cutoff}'";
        } elseif ($filterAge === 'adult') {
            $cutoff   = date('Y-m-d', strtotime('-18 years'));
            $hwhere[] = "h.date_of_birth IS NOT NULL AND h.date_of_birth <= '{$cutoff}'";
            $mwhere[] = "m.date_of_birth IS NOT NULL AND m.date_of_birth <= '{$cutoff}'";
        }

        if (! empty($hwhere)) {
            $headSql   .= ' WHERE ' . implode(' AND ', $hwhere);
        }
        if (! empty($mwhere)) {
            $memberSql .= ' WHERE ' . implode(' AND ', $mwhere);
        }

        $unionSql = "({$headSql}) UNION ALL ({$memberSql}) ORDER BY last_name ASC, first_name ASC";

        // A person can exist in both source tables after an old/imported duplicate.
        // De-duplicate the display by the person's identifying census fields so the
        // Residents page represents people, not duplicate rows.
        $allResidents = $this->attachCensusAccounts($db->query($unionSql)->getResultArray());
        if ($filterAcct === 'active') {
            $allResidents = array_values(array_filter(
                $allResidents,
                static fn(array $resident): bool => ($resident['account_status'] ?? '') === 'active'
            ));
        } elseif ($filterAcct === 'pending') {
            $allResidents = array_values(array_filter(
                $allResidents,
                static fn(array $resident): bool => in_array($resident['account_status'] ?? '', ['pending', 'unverified'], true)
            ));
        } elseif ($filterAcct === 'none') {
            $allResidents = array_values(array_filter(
                $allResidents,
                static fn(array $resident): bool => empty($resident['user_id'])
            ));
        }
        usort($allResidents, static function (array $a, array $b): int {
            $rank = static fn(?string $status): int => $status === 'active' ? 0 : ($status !== null && $status !== '' ? 1 : 2);
            $byStatus = $rank($a['account_status'] ?? null) <=> $rank($b['account_status'] ?? null);
            if ($byStatus !== 0) {
                return $byStatus;
            }
            $byLast = strcasecmp((string) $a['last_name'], (string) $b['last_name']);

            return $byLast !== 0 ? $byLast : strcasecmp((string) $a['first_name'], (string) $b['first_name']);
        });
        $uniqueResidents = [];
        foreach ($allResidents as $resident) {
            $identity = implode('|', [
                strtoupper(trim((string) ($resident['last_name'] ?? ''))),
                strtoupper(trim((string) ($resident['first_name'] ?? ''))),
                (string) ($resident['date_of_birth'] ?? ''),
                strtolower(trim((string) ($resident['relationship'] ?? ''))),
            ]);
            if (! isset($uniqueResidents[$identity])) {
                $uniqueResidents[$identity] = $resident;
            }
        }
        $residents = array_values($uniqueResidents);
        $total = count($residents);
        $residents = array_slice($residents, $offset, $perPage);

        // ── Summary counts (unfiltered) ───────────────────────────────────
        $totalHeads   = (int) $db->table('households')->countAllResults();
        $totalMembers = (int) $db->table('household_members')->countAllResults();
        $totalPop     = $totalHeads + $totalMembers;
        // Only count resident accounts (not admin, secretary, captain, council, or SK)
        $activeAccts  = (int) $db->table('users')
            ->where('role', 'resident')
            ->where('status', 'active')
            ->countAllResults();
        $pendingAccts = (int) $db->table('users')
            ->where('role', 'resident')
            ->groupStart()->where('status', 'pending')->orWhere('status', 'unverified')->groupEnd()
            ->countAllResults();
        $cutoffMinor  = date('Y-m-d', strtotime('-18 years'));
        $minorHeads   = (int) $db->query(
            "SELECT COUNT(*) AS c FROM households WHERE date_of_birth IS NOT NULL AND date_of_birth > '{$cutoffMinor}'"
        )->getRow()->c;
        $minorMembers = (int) $db->query(
            "SELECT COUNT(*) AS c FROM household_members WHERE date_of_birth IS NOT NULL AND date_of_birth > '{$cutoffMinor}'"
        )->getRow()->c;
        $totalMinors  = $minorHeads + $minorMembers;

        // ── Pending account approvals (merged into this page) ────────────
        $pendingUsers = $db->table('users')
            ->where('status', 'pending')
            ->whereIn('role', ['sk', 'resident'])
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();

        return view('dashboard/secretary/residents', [
            'residents'    => $residents,
            'total'        => $total,
            'perPage'      => $perPage,
            'currentPage'  => $page,
            'search'       => $search,
            'householdNo'  => $householdNo,
            'filterZone'   => $filterZone,
            'filterAcct'   => $filterAcct,
            'filterAge'    => $filterAge,
            // stats
            'totalPop'     => $totalPop,
            'activeAccts'  => $activeAccts,
            'pendingAccts' => $pendingAccts,
            'totalMinors'  => $totalMinors,
            // pending approvals
            'pendingUsers' => $pendingUsers,
        ]);
    }

    public function secretary_household($id = null)
    {
        $householdModel = new \App\Models\HouseholdModel();
        $household      = $householdModel->getWithMembers((string) $id);

        if (! $household) {
            return redirect()->to('/secretary/census')->with('error', 'Household not found.');
        }

        return view('dashboard/captain/household', [
            'householdId'      => $household['household_no'],
            'household'        => $household,
            'members'          => $household['members'],
            'hasSpouse'        => count(array_filter($household['members'], static fn($member) => strtolower((string) ($member['relationship'] ?? '')) === 'spouse')) > 0,
            'role'             => 'secretary',
            'ownershipChanges' => $this->_getOwnershipChanges($household['household_no']),
            'sharedFamilies'   => $this->_getSharedFamilies($household),
            'transferHouseholds' => $this->_getTransferHouseholds($household['household_no']),
        ]);
    }

    public function council_household($id = null)
    {
        $householdModel = new \App\Models\HouseholdModel();
        $household      = $householdModel->getWithMembers((string) $id);

        $councilZone = $this->councilAssignedZone();
        if (! $household || $councilZone === '' || trim((string) ($household['zone'] ?? '')) !== $councilZone) {
            return redirect()->to('/council/census')->with('error', 'Household not found or outside your assigned zone.');
        }

        return view('dashboard/captain/household', [
            'householdId'      => $household['household_no'],
            'household'        => $household,
            'members'          => $household['members'],
            'role'             => 'council',
            'ownershipChanges' => $this->_getOwnershipChanges($household['household_no']),
            'sharedFamilies'   => $this->_getSharedFamilies($household),
            'transferHouseholds' => $this->_getTransferHouseholds($household['household_no']),
        ]);
    }
    public function secretary_clearance()
    {
        return view('dashboard/secretary/clearance');
    }
    public function secretary_clearance_detail($id = '001')
    {
        return view('dashboard/captain/clearance_detail', ['requestId' => $id, 'role' => 'secretary']);
    }
    public function secretary_requests()
    {
        return view('dashboard/secretary/requests');
    }
    public function secretary_reports()
    {
        return view('dashboard/secretary/reports', $this->_buildReportData());
    }
    public function secretary_chatbot()
    {
        return $this->redirectAwayFromChatbot();
    }

    /**
     * Full-page BIS Assistant, opened from the chat button.
     */
    public function assistant()
    {
        $role = strtolower((string) (session()->get('role') ?? ''));
        if ($role === 'resident') {
            $starter = trim((string) $this->request->getGet('q'));
            $target  = '/resident/chatbot';
            if ($starter !== '') {
                $target .= '?q=' . rawurlencode($starter);
            }

            return redirect()->to($target);
        }
        if (in_array($role, ['admin', 'captain', 'secretary', 'sk', 'council'], true)) {
            return redirect()->to('/' . $role . '/dashboard');
        }
        $endpoints = [
            'resident'  => '/resident/chatbot/api/chat',
            'secretary' => '/secretary/chatbot/api/chat',
            'captain'   => '/captain/chatbot/api/chat',
            'sk'        => '/sk/chatbot/api/chat',
            'council'   => '/council/chatbot/api/chat',
        ];

        $isPublic = ! isset($endpoints[$role]);

        return view('dashboard/assistant', [
            'role'         => $isPublic ? 'guest' : $role,
            'chatEndpoint' => $isPublic ? '/api/chatbot/chat' : $endpoints[$role],
            'chatSource'   => $isPublic ? 'landing' : '',
            'dashboardUrl' => $isPublic ? '/' : '/' . $role . '/dashboard',
            'backLabel'    => $isPublic ? 'Back to home' : 'Back to dashboard',
        ]);
    }

    /**
     * Customer Service page for Secretary and Captain.
     *
     * URLs:
     *   /secretary/customer-service
     *   /captain/customer-service
     */
    public function customerService()
    {
        $role = strtolower(
            (string) (session()->get('role') ?? '')
        );

        if (!in_array($role, ['secretary', 'captain', 'admin'], true)) {
            return redirect()->to('/');
        }

        return view(
            'dashboard/customer_service',
            [
                'pageTitle' => 'Customer Service',
                'role'      => $role,
            ]
        );
    }

    public function secretary_blotter()
    {
        return view('dashboard/secretary/blotter');
    }
    public function secretary_blotter_detail($id = 'BL-001')
    {
        return view('dashboard/captain/blotter_detail', ['blotterId' => $id, 'role' => 'secretary']);
    }
    public function secretary_settings()
    {
        $userModel = new \App\Models\UserModel();
        $users = $userModel
            ->select('id, last_name, first_name, middle_name, username, role')
            ->whereIn('role', ['captain', 'secretary', 'resident', 'sk'])
            ->where('status', 'active')
            ->orderBy('last_name', 'ASC')
            ->findAll();

        return view('dashboard/secretary/settings', ['allUsers' => $users]);
    }
    public function secretary_create_account()
    {
        $role = session()->get('role');
        if ($role === 'captain') {
            return redirect()->to('/captain/create-account');
        }
        if (! in_array($role, ['admin', 'secretary'], true)) {
            return redirect()->to('/' . ($role ?: 'login') . '/dashboard')
                ->with('error', 'You cannot create accounts from this page.');
        }

        return view('dashboard/secretary/create_account', $this->officialAccountPageData());
    }

    /** @return array<string, mixed> */
    private function officialAccountPageData(): array
    {
        $userModel = new \App\Models\UserModel();
        $db        = \Config\Database::connect();

        // Eligible residents: active, age 18+
        // Look up each resident's personal DOB — try household_members first
        // (resident may be a member, not the head), fall back to households head row.
        $residents = $db->table('users u')
            ->select('u.id, u.last_name, u.first_name, u.middle_name, u.username, u.email, u.household_no,
                      h.date_of_birth  AS head_dob,
                      m.date_of_birth  AS member_dob')
            ->join('households h', 'h.household_no = u.household_no', 'left')
            ->join(
                'household_members m',
                "m.household_no = u.household_no
                 AND UPPER(TRIM(m.first_name)) = UPPER(TRIM(u.first_name))
                 AND UPPER(TRIM(m.last_name))  = UPPER(TRIM(u.last_name))",
                'left'
            )
            ->where('u.role', 'resident')
            ->where('u.status', 'active')
            ->orderBy('u.last_name', 'ASC')
            ->orderBy('u.first_name', 'ASC')
            ->get()->getResultArray();

        // Compute accurate age: prefer member DOB, fall back to head DOB
        $eligibleResidents = [];
        foreach ($residents as $r) {
            $dob = !empty($r['member_dob']) ? $r['member_dob']
                : (!empty($r['head_dob'])  ? $r['head_dob']  : null);

            if (empty($dob)) continue;

            // Accurate age: TIMESTAMPDIFF logic in PHP
            $birthDate = date_create($dob);
            if ($birthDate === false) {
                continue;
            }
            $today     = date_create('today');
            $age       = (int) date_diff($birthDate, $today)->y;

            if ($age < 18) continue; // must be 18+

            $r['age']         = $age;
            $r['date_of_birth'] = $dob;
            $eligibleResidents[] = $r;
        }

        $activeSecretaries = $userModel
            ->select('id, last_name, first_name, middle_name, username, email')
            ->where('role', 'secretary')
            ->where('status', 'active')
            ->orderBy('last_name', 'ASC')
            ->findAll();

        $activeCouncils = $userModel
            ->select('id, last_name, first_name, middle_name, username, email, council_zone')
            ->where('role', 'council')
            ->where('status', 'active')
            ->orderBy('last_name', 'ASC')
            ->findAll();

        $activeAdmins = $userModel
            ->select('id, last_name, first_name, middle_name, username, email')
            ->where('role', 'admin')
            ->where('status', 'active')
            ->orderBy('last_name', 'ASC')
            ->findAll();

        return [
            'activeCaptain'      => $userModel->getActiveByRole('captain'),
            'activeSecretaries'  => $activeSecretaries,
            'activeSk'           => $userModel->getActiveByRole('sk'),
            'activeCouncils'     => $activeCouncils,
            'activeAdmins'       => $activeAdmins,
            'eligibleResidents'  => $eligibleResidents,
        ];
    }

    // ── Resident ──────────────────────────────────────
    public function resident_dashboard()
    {
        $userId = (int) session()->get('user_id');
        $role   = session()->get('role') ?: 'resident';
        $db     = \Config\Database::connect();

        // ── Clearance / document request stats ────────────────────────────────
        $totalRequests = (int) $db->table('clearance_requests')
            ->where('user_id', $userId)->countAllResults();

        $approved = (int) $db->table('clearance_requests')
            ->where('user_id', $userId)->where('status', 'approved')->countAllResults();

        $pending = (int) $db->table('clearance_requests')
            ->where('user_id', $userId)->where('status', 'pending')->countAllResults();

        // ── 5 most recent document requests ───────────────────────────────────
        $recentRequests = $db->table('clearance_requests')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        $recentAppointments = $db->table('concern_submissions')
            ->where('user_id', $userId)
            ->where('appointment_date IS NOT NULL')
            ->orderBy('appointment_date', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        $barangayActivities = (new \App\Models\BarangayActivityModel())->visibleToResidents(4);
        $censusUpdateDrive = (new \App\Models\CensusUpdateDriveModel())->currentOpen();
        $censusUpdateAuth  = (new \App\Models\CensusUpdateAuthorizationModel())->getPendingByUser($userId);

        return view('dashboard/resident', [
            'role'           => $role,
            'totalRequests'  => $totalRequests,
            'approved'       => $approved,
            'pending'        => $pending,
            'recentRequests' => $recentRequests,
            'recentAppointments' => $recentAppointments,
            'barangayActivities' => $barangayActivities,
            'censusUpdateDrive' => $censusUpdateDrive,
            'censusUpdateAuth'  => $censusUpdateAuth,
            'residentUser'   => (new \App\Models\UserModel())->select('first_name,last_name,middle_name,email,contact_number')->find($userId),
        ]);
    }

    public function council_dashboard()
    {
        return $this->resident_dashboard();
    }
    public function resident_clearance()
    {
        return view('dashboard/resident/clearance');
    }
    public function resident_profile()
    {
        $userId    = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user      = $userModel->find($userId);

        $household    = null;
        $members      = [];
        $memberRecord = null;

        if (! empty($user['household_no'])) {
            $householdModel = new \App\Models\HouseholdModel();
            $household      = $householdModel->find($user['household_no']);

            if ($household) {
                $memberModel = new \App\Models\HouseholdMemberModel();
                $members     = $memberModel->where('household_no', $user['household_no'])->findAll();
            }

            $memberRecord = self::matchCensusRecord($user, $household, $members);
        }

        return view('dashboard/resident/profile', [
            'user'         => $user,
            'household'    => $household,
            'members'      => $members,
            'memberRecord' => $memberRecord,
        ]);
    }
    public function resident_chatbot()
    {
        return view('dashboard/resident/chatbot');
    }
    public function resident_notifications()
    {
        $userId     = (int) session()->get('user_id');
        $notifModel = new \App\Models\NotificationModel();

        // One reminder per upcoming event. A previous exact-match check never
        // found the saved marker, so each visit inserted another unread row.
        $this->syncResidentEventReminders($userId);

        // ── Fetch all notifications for this user ─────────────────────────────
        $notifs      = $notifModel->getForUser($userId, true);
        $unreadCount = $notifModel->countUnread($userId, true);

        return view('dashboard/resident/notifications', [
            'notifs'      => $notifs,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Create at most one upcoming-event reminder per resident, and remove
     * extra copies left by the old duplicate check.
     */
    private function syncResidentEventReminders(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $db = \Config\Database::connect();
        $upcoming = $db->table('schedules')
            ->where('event_date >=', date('Y-m-d'))
            ->where('event_date <=', date('Y-m-d', strtotime('+7 days')))
            ->where('visibility !=', 'private')
            ->orderBy('event_date', 'ASC')
            ->get()
            ->getResultArray();

        $rows = $db->table('notifications')
            ->select('id, title, body, read_at')
            ->where('user_id', $userId)
            ->where('type', 'event_reminder')
            ->like('title', 'Upcoming Event:', 'after')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $claimed = [];

        foreach ($upcoming as $event) {
            $eventId = (int) ($event['id'] ?? 0);
            if ($eventId <= 0) {
                continue;
            }

            $marker = '[event_id:' . $eventId . ']';
            $title = 'Upcoming Event: ' . (string) ($event['title'] ?? '');
            $matches = [];

            foreach ($rows as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id <= 0 || isset($claimed[$id])) {
                    continue;
                }

                $body = (string) ($row['body'] ?? '');
                $hasThisMarker = str_contains($body, $marker);
                $hasAnyMarker = preg_match('/\[event_id:\d+\]/', $body) === 1;
                if ($hasThisMarker || ((string) ($row['title'] ?? '') === $title && ! $hasAnyMarker)) {
                    $matches[] = $row;
                    $claimed[$id] = true;
                }
            }

            if ($matches === []) {
                $dateLabel = date('M d, Y', strtotime((string) ($event['event_date'] ?? '')));
                $timeLabel = ! empty($event['start_time'])
                    ? ' at ' . date('g:i A', strtotime((string) $event['start_time']))
                    : '';
                $description = trim((string) ($event['description'] ?? ''));
                $location = trim((string) ($event['location'] ?? ''));
                $body = $description !== ''
                    ? $description . ' — ' . $dateLabel . $timeLabel
                    : 'Scheduled on ' . $dateLabel . $timeLabel . ($location !== '' ? '. Venue: ' . $location : '');

                $eventTimestamp = strtotime((string) ($event['event_date'] ?? ''));
                $eventLink = $eventTimestamp
                    ? '/events?year=' . date('Y', $eventTimestamp) . '&month=' . (int) date('n', $eventTimestamp)
                    : '/events';
                \App\Models\NotificationModel::push(
                    $userId,
                    'event_reminder',
                    $title,
                    trim($body) . ' ' . $marker,
                    $eventLink
                );
                continue;
            }

            $keepId = (int) $matches[0]['id'];
            foreach ($matches as $row) {
                if (! empty($row['read_at'])) {
                    $keepId = (int) $row['id'];
                    break;
                }
            }

            $keep = null;
            $deleteIds = [];
            foreach ($matches as $row) {
                if ((int) $row['id'] === $keepId) {
                    $keep = $row;
                    continue;
                }
                $deleteIds[] = (int) $row['id'];
            }

            if ($keep !== null && ! str_contains((string) ($keep['body'] ?? ''), $marker)) {
                $db->table('notifications')
                    ->where('id', $keepId)
                    ->where('user_id', $userId)
                    ->update([
                        'body' => rtrim((string) ($keep['body'] ?? '')) . ' ' . $marker,
                    ]);
            }

            if ($deleteIds !== []) {
                $db->table('notifications')
                    ->where('user_id', $userId)
                    ->whereIn('id', $deleteIds)
                    ->delete();
            }
        }
    }

    // ── SK ────────────────────────────────────────────────
    public function sk_dashboard()
    {
        $db = \Config\Database::connect();

        $youthMax = date('Y-m-d', strtotime('-15 years'));
        $youthMin = date('Y-m-d', strtotime('-30 years'));

        // UNION: household heads + members aged 15–30
        $headSql = "SELECT h.date_of_birth, h.gender, h.occupation, h.last_name, h.first_name, h.zone, h.created_at, 'head' AS source, h.household_no AS rid
            FROM households h
            WHERE h.date_of_birth IS NOT NULL AND h.date_of_birth <= '{$youthMax}' AND h.date_of_birth >= '{$youthMin}'";

        $memberSql = "SELECT m.date_of_birth, m.gender, m.occupation, m.last_name, m.first_name, h2.zone, m.created_at, 'member' AS source, m.id AS rid
            FROM household_members m
            INNER JOIN households h2 ON h2.household_no = m.household_no
            WHERE m.date_of_birth IS NOT NULL AND m.date_of_birth <= '{$youthMax}' AND m.date_of_birth >= '{$youthMin}'";

        $allYouth = $db->query("({$headSql}) UNION ALL ({$memberSql})")->getResultArray();

        $total    = count($allYouth);
        $male     = count(array_filter($allYouth, fn($r) => strtolower(trim($r['gender'] ?? '')) === 'male'));
        $female   = count(array_filter($allYouth, fn($r) => strtolower(trim($r['gender'] ?? '')) === 'female'));
        $oos      = count(array_filter($allYouth, fn($r) =>
        stripos($r['occupation'] ?? '', 'out-of-school') !== false ||
            stripos($r['occupation'] ?? '', 'osy') !== false));
        $employed = count(array_filter($allYouth, fn($r) => (function ($occ) {
            $o = strtolower(trim($occ ?? ''));
            return $o !== '' && $o !== 'none' && $o !== 'n/a' && $o !== '-'
                && strpos($o, 'student') === false
                && strpos($o, 'unemploy') === false
                && strpos($o, 'out-of-school') === false
                && strpos($o, 'osy') === false;
        })($r['occupation'])));

        // Recent 5 youth sorted by created_at
        usort($allYouth, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        $recentYouth = array_slice($allYouth, 0, 5);
        foreach ($recentYouth as &$y) {
            $dob = $y['date_of_birth'] ?? null;
            $y['age'] = !empty($dob) ? (int) date_diff(date_create($dob), date_create('today'))->y : '—';
            $occ = strtolower(trim($y['occupation'] ?? ''));
            if (str_contains($occ, 'student'))                                             $y['status'] = 'Student';
            elseif (str_contains($occ, 'unemploy'))                                        $y['status'] = 'Unemployed';
            elseif (str_contains($occ, 'out-of-school') || str_contains($occ, 'osy'))      $y['status'] = 'Out-of-School';
            elseif ($occ !== '' && $occ !== 'none' && $occ !== 'n/a' && $occ !== '-')      $y['status'] = 'Employed';
            else                                                                            $y['status'] = '—';
        }
        unset($y);

        // Programs count
        $progModel   = new \App\Models\SkProgramModel();
        $progCounts  = $progModel->statusCounts();

        return view('dashboard/sk', [
            'stats' => [
                'total'    => $total,
                'male'     => $male,
                'female'   => $female,
                'oos'      => $oos,
                'employed' => $employed,
                'students' => count(array_filter($allYouth, fn($r) => stripos(trim($r['occupation'] ?? ''), 'student') !== false)),
                'programs' => $progCounts['Active'],
            ],
            'recentYouth' => $recentYouth,
            'progCounts'  => $progCounts,
        ]);
    }
    public function sk_profiling()
    {
        return view('dashboard/sk/profiling');
    }
    public function sk_household($id = null)
    {
        $householdModel = new \App\Models\HouseholdModel();
        $household      = $householdModel->getWithMembers((string) $id);

        if (! $household) {
            return redirect()->to('/sk/profiling')->with('error', 'Household not found.');
        }

        // Find the youth member (15–30) that was clicked from the profiling list
        // The profiling list passes the member's source and id via query string
        $memberSource = $_GET['source'] ?? 'head';
        $memberId     = (int) ($_GET['member_id'] ?? 0);

        $youthMember = null;
        if ($memberSource === 'member' && $memberId > 0) {
            // Find the specific member
            foreach ($household['members'] as $m) {
                if ((int) $m['id'] === $memberId) {
                    $youthMember = $m;
                    break;
                }
            }
        } elseif ($memberSource === 'head') {
            // The head is the youth
            $youthMember = array_merge($household, ['relationship' => 'Household Head', 'id' => null]);
        }

        $youthProfile = null;
        if ($youthMember) {
            $profileModel = new \App\Models\SkYouthModel();
            $youthProfile = $profileModel
                ->where('first_name', $youthMember['first_name'] ?? '')
                ->where('last_name', $youthMember['last_name'] ?? '')
                ->where('date_of_birth', $youthMember['date_of_birth'] ?? null)
                ->orderBy('updated_at', 'DESC')
                ->first();

            if (! $youthProfile) {
                $user = (new \App\Models\UserModel())
                    ->where('first_name', $youthMember['first_name'] ?? '')
                    ->where('last_name', $youthMember['last_name'] ?? '')
                    ->where('household_no', $household['household_no'])
                    ->first();
                if ($user) $youthProfile = $profileModel->where('user_id', $user['id'])->orderBy('updated_at', 'DESC')->first();
            }
        }

        return view('dashboard/sk/print_profile', [
            'householdId' => $household['household_no'],
            'household'   => $household,
            'members'     => $household['members'],
            'youthMember' => $youthMember,
            'youthProfile' => $youthProfile,
            'memberSource' => $memberSource,
            'memberId'    => $memberId,
            'role'        => 'sk',
        ]);
    }
    public function sk_add_youth()
    {
        return view('dashboard/sk/add_youth');
    }
    public function sk_programs()
    {
        // Delegate to SkController
        return (new \App\Controllers\SkController())->programs();
    }
    public function sk_chatbot()
    {
        return $this->redirectAwayFromChatbot();
    }

    private function redirectAwayFromChatbot()
    {
        $role = strtolower((string) (session()->get('role') ?? ''));
        if (! in_array($role, ['admin', 'captain', 'secretary', 'sk', 'council', 'resident'], true)) {
            $role = 'resident';
        }

        return redirect()->to('/' . $role . '/dashboard');
    }
    public function sk_reports()
    {
        $db = \Config\Database::connect();

        $youthMax = date('Y-m-d', strtotime('-15 years'));
        $youthMin = date('Y-m-d', strtotime('-30 years'));

        // UNION: household heads + members aged 15–30
        // Members now include gender (column exists in household_members)
        $headSql = "SELECT h.date_of_birth, h.gender, h.occupation
            FROM households h
            WHERE h.date_of_birth IS NOT NULL
              AND h.date_of_birth <= '{$youthMax}'
              AND h.date_of_birth >= '{$youthMin}'";

        $memberSql = "SELECT m.date_of_birth, m.gender, m.occupation
            FROM household_members m
            INNER JOIN households h2 ON h2.household_no = m.household_no
            WHERE m.date_of_birth IS NOT NULL
              AND m.date_of_birth <= '{$youthMax}'
              AND m.date_of_birth >= '{$youthMin}'";

        $allYouth = $db->query("({$headSql}) UNION ALL ({$memberSql})")->getResultArray();

        // Helper to classify occupation into status
        $classifyStatus = function (string $occ): string {
            $o = strtolower(trim($occ));
            if (str_contains($o, 'student'))                                         return 'Student';
            if (str_contains($o, 'unemploy'))                                        return 'Unemployed';
            if (str_contains($o, 'out-of-school') || str_contains($o, 'osy'))        return 'Out-of-School';
            if ($o !== '' && $o !== 'none' && $o !== 'n/a' && $o !== '-')            return 'Employed';
            return '';
        };

        // Age group buckets: 15–17, 18–24, 25–30
        $groups = [
            '15–17 (Child Youth)'  => ['min' => 15, 'max' => 17],
            '18–24 (Core Youth)'   => ['min' => 18, 'max' => 24],
            '25–30 (Young Adult)'  => ['min' => 25, 'max' => 30],
        ];

        $demographics = [];
        foreach ($groups as $label => $range) {
            $demographics[$label] = [
                'total' => 0,
                'male' => 0,
                'female' => 0,
                'student' => 0,
                'employed' => 0,
                'unemployed' => 0,
                'oos' => 0,
            ];
        }

        $totals = ['total' => 0, 'male' => 0, 'female' => 0, 'student' => 0, 'employed' => 0, 'unemployed' => 0, 'oos' => 0];

        foreach ($allYouth as $y) {
            // Accurate age: use date_diff which accounts for birthday in current year
            $dob = $y['date_of_birth'] ?? null;
            if (empty($dob)) continue;
            $age = (int) date_diff(date_create($dob), date_create('today'))->y;

            $g = strtolower(trim($y['gender'] ?? ''));
            $s = $classifyStatus($y['occupation'] ?? '');

            foreach ($groups as $label => $range) {
                if ($age >= $range['min'] && $age <= $range['max']) {
                    $d = &$demographics[$label];
                    $d['total']++;
                    if ($g === 'male')               $d['male']++;
                    if ($g === 'female')             $d['female']++;
                    if ($s === 'Student')            $d['student']++;
                    if ($s === 'Employed')           $d['employed']++;
                    if ($s === 'Unemployed')         $d['unemployed']++;
                    if ($s === 'Out-of-School')      $d['oos']++;
                    break;
                }
            }

            $totals['total']++;
            if ($g === 'male')               $totals['male']++;
            if ($g === 'female')             $totals['female']++;
            if ($s === 'Student')            $totals['student']++;
            if ($s === 'Employed')           $totals['employed']++;
            if ($s === 'Unemployed')         $totals['unemployed']++;
            if ($s === 'Out-of-School')      $totals['oos']++;
        }

        // Programs from DB
        $progModel  = new \App\Models\SkProgramModel();
        $programs   = $progModel->orderBy('start_date', 'DESC')->findAll();
        $progCounts = $progModel->statusCounts();

        // Total actual participants across all programs
        $totalParticipants = (int) array_sum(array_column($programs, 'actual_participants'));

        return view('dashboard/sk/reports', [
            'demographics'      => $demographics,
            'totals'            => $totals,
            'programs'          => $programs,
            'progCounts'        => $progCounts,
            'totalParticipants' => $totalParticipants,
        ]);
    }
    public function sk_settings()
    {
        return view('dashboard/sk/settings');
    }

    public function sk_clearance()
    {
        // Delegate to the clearance controller's resident logic — it reads user_id from session
        return (new \App\Controllers\ClearanceController())->residentIndex();
    }

    public function sk_blotter()
    {
        return view('dashboard/sk/blotter');
    }

    /**
     * Link each census person to their user account, whatever the role is.
     *
     * @param list<array<string, mixed>> $people
     * @return list<array<string, mixed>>
     */
    private function attachCensusAccounts(array $people, bool $residentsOnly = true): array
    {
        $query = \Config\Database::connect()->table('users')
            ->select('id, first_name, middle_name, last_name, username, email, status, role, household_no');
        
        // On the Residents page, only match resident accounts (not admin, secretary, etc.)
        if ($residentsOnly) {
            $query->where('role', 'resident');
        }
        
        $users = $query->get()->getResultArray();
        $users = array_values(array_filter(
            $users,
            static fn(array $user): bool => ($user['status'] ?? '') !== 'deleted'
        ));

        foreach ($people as &$person) {
            $match = $this->bestCensusAccount($person, $users);
            $person['user_id'] = $match['id'] ?? null;
            $person['username'] = $match['username'] ?? null;
            $person['email'] = $match['email'] ?? null;
            $person['account_status'] = $match['status'] ?? null;
            $person['account_role'] = $match['role'] ?? null;
        }
        unset($person);

        return $people;
    }

    /**
     * @param array<string, mixed> $person
     * @param list<array<string, mixed>> $users
     * @return array<string, mixed>|null
     */
    private function bestCensusAccount(array $person, array $users): ?array
    {
        $best = null;
        $bestRank = -1;
        foreach ($users as $user) {
            if (! $this->censusPersonMatchesAccount($person, $user)) {
                continue;
            }
            $rank = $this->censusAccountRank((string) ($user['status'] ?? ''));
            $personHousehold = trim((string) ($person['household_no'] ?? ''));
            $userHousehold = trim((string) ($user['household_no'] ?? ''));
            if ($personHousehold !== '' && $personHousehold === $userHousehold) {
                $rank += 10;
            }
            if ($best === null || $rank > $bestRank || ($rank === $bestRank && (int) $user['id'] < (int) $best['id'])) {
                $best = $user;
                $bestRank = $rank;
            }
        }

        return $best;
    }

    /** @param array<string, mixed> $person @param array<string, mixed> $user */
    private function censusPersonMatchesAccount(array $person, array $user): bool
    {
        $personLast = $this->censusNameKey((string) ($person['last_name'] ?? ''));
        $userLast = $this->censusNameKey((string) ($user['last_name'] ?? ''));
        if ($personLast === '' || $personLast !== $userLast) {
            return false;
        }

        $personFirst = $this->censusNameKey((string) ($person['first_name'] ?? ''));
        $userFirst = $this->censusNameKey((string) ($user['first_name'] ?? ''));
        $personMiddle = $this->censusNameKey((string) ($person['middle_name'] ?? ''));
        $userMiddle = $this->censusNameKey((string) ($user['middle_name'] ?? ''));
        $firstMatches = $personFirst !== '' && (
            $personFirst === $userFirst
            || ($userMiddle !== '' && $personFirst === $userFirst . $userMiddle)
            || ($personMiddle !== '' && $userFirst === $personFirst . $personMiddle)
        );
        if (! $firstMatches) {
            return false;
        }

        return $personMiddle === ''
            || $userMiddle === ''
            || $this->censusMiddleAgrees($personMiddle, $userMiddle)
            || $personFirst === $userFirst . $userMiddle
            || $userFirst === $personFirst . $personMiddle;
    }

    private function censusMiddleAgrees(string $left, string $right): bool
    {
        if ($left === $right) {
            return true;
        }

        return (mb_strlen($left) === 1 && str_starts_with($right, $left))
            || (mb_strlen($right) === 1 && str_starts_with($left, $right));
    }

    private function censusAccountRank(string $status): int
    {
        return match ($status) {
            'active' => 40,
            'pending', 'unverified' => 30,
            'rejected' => 10,
            default => 20,
        };
    }

    private function censusNameKey(string $value): string
    {
        $value = mb_strtoupper(trim($value), 'UTF-8');
        $value = str_replace(['.', '-', ' ', "'", '’'], '', $value);

        return $value;
    }
}
