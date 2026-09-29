<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SkYouthModel;

class SkController extends BaseController
{
    protected SkYouthModel $model;

    public function __construct()
    {
        $this->model = new SkYouthModel();
    }

    // ── Profiling list — pulls from households + household_members ───────────

    public function profiling()
    {
        $db        = \Config\Database::connect();
        $search    = trim($_GET['search']      ?? '');
        $ageFilter = trim($_GET['age']         ?? '');
        $gender    = trim($_GET['gender']      ?? '');
        $status    = trim($_GET['status']      ?? '');
        $zone      = trim($_GET['zone']        ?? '');
        $civil     = trim($_GET['civil']       ?? '');

        // Age range boundaries for youth (15–30)
        $youthMax = date('Y-m-d', strtotime('-15 years'));
        $youthMin = date('Y-m-d', strtotime('-30 years'));

        // ── Build WHERE clauses ───────────────────────────────────────────────
        $hw = ["h.date_of_birth IS NOT NULL", "h.date_of_birth <= '{$youthMax}'", "h.date_of_birth >= '{$youthMin}'"];
        $mw = ["m.date_of_birth IS NOT NULL", "m.date_of_birth <= '{$youthMax}'", "m.date_of_birth >= '{$youthMin}'"];

        if ($search !== '') {
            $s    = $db->escapeLikeString($search);
            $hw[] = "(h.last_name LIKE '%{$s}%' OR h.first_name LIKE '%{$s}%' OR h.contact_number LIKE '%{$s}%')";
            $mw[] = "(m.last_name LIKE '%{$s}%' OR m.first_name LIKE '%{$s}%')";
        }
        if ($gender !== '') {
            $g    = $db->escapeString($gender);
            $hw[] = "h.gender = '{$g}'";
            $mw[] = "m.gender = '{$g}'";
        }
        if ($zone !== '') {
            $z    = $db->escapeString($zone);
            $hw[] = "h.zone = '{$z}'";
            $mw[] = "h2.zone = '{$z}'";
        }
        if ($civil !== '') {
            $c    = $db->escapeString($civil);
            $hw[] = "h.civil_status = '{$c}'";
            $mw[] = "1=0"; // members have no civil_status
        }
        if ($ageFilter !== '') {
            [$amin, $amax] = explode('-', $ageFilter);
            $dMax = date('Y-m-d', strtotime('-' . (int)$amin . ' years'));
            $dMin = date('Y-m-d', strtotime('-' . (int)$amax . ' years'));
            $hw[] = "h.date_of_birth <= '{$dMax}' AND h.date_of_birth >= '{$dMin}'";
            $mw[] = "m.date_of_birth <= '{$dMax}' AND m.date_of_birth >= '{$dMin}'";
        }
        // Status filter — apply in SQL via occupation keyword
        if ($status !== '') {
            $statusSqlMap = [
                'Student'       => "UPPER(h.occupation) LIKE '%STUDENT%'",
                'Employed'      => "UPPER(h.occupation) NOT LIKE '%STUDENT%' AND UPPER(h.occupation) NOT LIKE '%UNEMPLOY%' AND h.occupation IS NOT NULL AND h.occupation != '' AND UPPER(h.occupation) != 'NONE'",
                'Unemployed'    => "UPPER(h.occupation) LIKE '%UNEMPLOY%'",
                'Out-of-School' => "(UPPER(h.occupation) LIKE '%OUT-OF-SCHOOL%' OR UPPER(h.occupation) LIKE '%OSY%')",
            ];
            $statusMemberMap = [
                'Student'       => "UPPER(m.occupation) LIKE '%STUDENT%'",
                'Employed'      => "UPPER(m.occupation) NOT LIKE '%STUDENT%' AND UPPER(m.occupation) NOT LIKE '%UNEMPLOY%' AND m.occupation IS NOT NULL AND m.occupation != '' AND UPPER(m.occupation) != 'NONE'",
                'Unemployed'    => "UPPER(m.occupation) LIKE '%UNEMPLOY%'",
                'Out-of-School' => "(UPPER(m.occupation) LIKE '%OUT-OF-SCHOOL%' OR UPPER(m.occupation) LIKE '%OSY%')",
            ];
            if (isset($statusSqlMap[$status])) {
                $hw[] = $statusSqlMap[$status];
                $mw[] = $statusMemberMap[$status];
            }
        }

        $hwSql = ' WHERE ' . implode(' AND ', $hw);
        $mwSql = ' WHERE ' . implode(' AND ', $mw);

        // ── UNION: heads + members ────────────────────────────────────────────
        $headSql = "SELECT
                'head'         AS source,
                h.household_no AS id,
                h.last_name, h.first_name, h.middle_name,
                h.date_of_birth, h.gender, h.civil_status,
                h.occupation, h.contact_number, h.zone, h.household_no
            FROM households h{$hwSql}";

        $memberSql = "SELECT
                'member'       AS source,
                m.id           AS id,
                m.last_name, m.first_name, m.middle_name,
                m.date_of_birth, m.gender,
                ''             AS civil_status,
                m.occupation,
                ''             AS contact_number,
                h2.zone, m.household_no
            FROM household_members m
            INNER JOIN households h2 ON h2.household_no = m.household_no{$mwSql}";

        $union = "({$headSql}) UNION ALL ({$memberSql}) ORDER BY last_name ASC, first_name ASC";

        $perPage = 15;
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        $total = (int) $db->query("SELECT COUNT(*) AS c FROM ({$union}) AS t")->getRow()->c;
        $rows  = $db->query("{$union} LIMIT {$perPage} OFFSET {$offset}")->getResultArray();

        foreach ($rows as &$y) {
            $y['age'] = \App\Models\SkYouthModel::calcAge($y['date_of_birth']);
            $occ      = strtolower($y['occupation'] ?? '');
            if (str_contains($occ, 'student'))                                         $y['economic_status'] = 'Student';
            elseif (str_contains($occ, 'unemploy'))                                    $y['economic_status'] = 'Unemployed';
            elseif (str_contains($occ, 'out-of-school') || str_contains($occ, 'osy')) $y['economic_status'] = 'Out-of-School';
            elseif (! empty($occ) && $occ !== 'none' && $occ !== 'n/a')               $y['economic_status'] = 'Employed';
            else                                                                        $y['economic_status'] = '—';
        }
        unset($y);

        // ── Stats (always full unfiltered youth population) ───────────────────
        $baseHw = ["h.date_of_birth IS NOT NULL", "h.date_of_birth <= '{$youthMax}'", "h.date_of_birth >= '{$youthMin}'"];
        $baseMw = ["m.date_of_birth IS NOT NULL", "m.date_of_birth <= '{$youthMax}'", "m.date_of_birth >= '{$youthMin}'"];
        $baseHeadSql   = "SELECT h.date_of_birth, h.gender, h.occupation FROM households h WHERE " . implode(' AND ', $baseHw);
        $baseMemberSql = "SELECT m.date_of_birth, m.gender, m.occupation FROM household_members m INNER JOIN households h2 ON h2.household_no = m.household_no WHERE " . implode(' AND ', $baseMw);
        $allYouth      = $db->query("({$baseHeadSql}) UNION ALL ({$baseMemberSql})")->getResultArray();

        $statsTotal    = count($allYouth);
        $statsMale     = count(array_filter($allYouth, fn($r) => strtolower($r['gender'] ?? '') === 'male'));
        $statsFemale   = count(array_filter($allYouth, fn($r) => strtolower($r['gender'] ?? '') === 'female'));
        $statsStudents = count(array_filter($allYouth, fn($r) => stripos($r['occupation'] ?? '', 'student') !== false));
        $statsOos      = count(array_filter(
            $allYouth,
            fn($r) =>
            stripos($r['occupation'] ?? '', 'out-of-school') !== false ||
                stripos($r['occupation'] ?? '', 'osy') !== false
        ));

        return view('dashboard/sk/profiling', [
            'youth'       => $rows,
            'stats'       => ['total' => $statsTotal, 'male' => $statsMale, 'female' => $statsFemale, 'students' => $statsStudents, 'oos' => $statsOos],
            'total'       => $total,
            'perPage'     => $perPage,
            'currentPage' => $page,
            'search'      => $search,
            'ageFilter'   => $ageFilter,
            'gender'      => $gender,
            'status'      => $status,
            'zone'        => $zone,
            'civil'       => $civil,
        ]);
    }

    // ── View single youth ─────────────────────────────────────────────────────

    public function view(int $id)
    {
        $youth = $this->model->find($id);
        if (! $youth) {
            return redirect()->to('/sk/profiling')->with('error', 'Record not found.');
        }
        $youth['age'] = SkYouthModel::calcAge($youth['date_of_birth']);
        return view('dashboard/sk/view_youth', ['youth' => $youth]);
    }

    // ── Show add form ─────────────────────────────────────────────────────────

    public function residentProfilingForm(?string $successMessage = null, ?array $savedProfile = null, ?string $selectedProfile = null)
    {
        $role = session()->get('role');
        if (! in_array($role, ['resident', 'council'], true)) {
            return redirect()->to('/sk/profiling')->with('error', 'This form is only available to residents and barangay council members aged 15 to 30.');
        }

        $redirectBase = $role === 'council' ? '/council/sk-profiling' : '/resident/sk-profiling';

        $userId = (int) session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (empty($user['household_no'])) {
            return redirect()->to('/resident/dashboard')->with('error', 'Your household record is not linked yet. Please update your profile first.');
        }

        $db = \Config\Database::connect();
        $household = $db->table('households')->where('household_no', $user['household_no'])->get()->getRowArray();
        $members = $db->table('household_members')->where('household_no', $user['household_no'])->orderBy('relationship', 'ASC')->get()->getResultArray();

        $self = null;
        $selfIsHead = false;
        if ($household) {
            $self = $household;
            $selfIsHead = true;
            if (
                ! empty($user['first_name']) && strcasecmp(trim((string) $user['first_name']), trim((string) $household['first_name'])) !== 0
                || ! empty($user['last_name']) && strcasecmp(trim((string) $user['last_name']), trim((string) $household['last_name'])) !== 0
            ) {
                $self = null;
                $selfIsHead = false;
            }
        }

        if (! $self) {
            foreach ($members as $member) {
                if (
                    strcasecmp(trim((string) ($member['first_name'] ?? '')), trim((string) ($user['first_name'] ?? ''))) === 0
                    && strcasecmp(trim((string) ($member['last_name'] ?? '')), trim((string) ($user['last_name'] ?? ''))) === 0
                ) {
                    $self = $member;
                    $selfIsHead = false;
                    break;
                }
            }
        }

        $selfAge = $self && ! empty($self['date_of_birth']) ? SkYouthModel::calcAge($self['date_of_birth']) : null;
        $minorChildren = [];
        foreach ($members as $member) {
            $dob = $member['date_of_birth'] ?? null;
            $age = $dob ? SkYouthModel::calcAge($dob) : null;
            $rel = strtolower((string) ($member['relationship'] ?? ''));
            $isMinorChild = $age !== null && $age < 18 && (str_contains($rel, 'child') || str_contains($rel, 'son') || str_contains($rel, 'daughter'));
            if ($isMinorChild) {
                $minorChildren[] = $member;
            }
        }

        $eligible = ($selfAge !== null && $selfAge >= 15 && $selfAge <= 30) || ! empty($minorChildren);
        if (! $eligible) {
            $dashboard = $role === 'council' ? '/council/dashboard' : '/resident/dashboard';
            return redirect()->to($dashboard)->with('error', "You can't access this page. SK Profiling is only available to youth ages 15 to 30, or to parents who have a minor child in the household.");
        }

        $options = [];
        if ($selfAge !== null && $selfAge >= 15 && $selfAge <= 30) {
            $options[] = [
                'id' => 'self',
                'full_name' => trim((string) (($self['first_name'] ?? '') . ' ' . ($self['last_name'] ?? ''))),
                'label' => 'Resident',
                'is_minor' => false,
                'data' => $this->buildResidentProfileData($self, $household, $members, $user, $selfIsHead),
            ];
        }

        foreach ($minorChildren as $child) {
            $options[] = [
                'id' => 'child_' . ($child['id'] ?? md5(($child['first_name'] ?? '') . ':' . ($child['last_name'] ?? '') . ':' . ($child['date_of_birth'] ?? ''))),
                'full_name' => trim((string) (($child['first_name'] ?? '') . ' ' . ($child['last_name'] ?? ''))),
                'label' => 'Minor Child',
                'is_minor' => true,
                'data' => $this->buildResidentProfileData($child, $household, $members, [], false),
            ];
        }

        if (empty($options)) {
            return redirect()->to('/resident/dashboard')->with('error', 'No valid profile option found in your household census record.');
        }

        $youth = $savedProfile ?? [];
        if (empty($youth['id'])) {
            $pick = $options[0];
            if ($selectedProfile) {
                foreach ($options as $option) {
                    if ($option['id'] === $selectedProfile) {
                        $pick = $option;
                        break;
                    }
                }
            }
            $existing = $this->model
                ->where('first_name', strtoupper(trim((string) ($pick['data']['first_name'] ?? ''))))
                ->where('last_name', strtoupper(trim((string) ($pick['data']['last_name'] ?? ''))))
                ->orderBy('id', 'DESC')
                ->first();
            if ($existing) {
                $youth = array_merge($pick['data'], $existing);
            }
        }

        return view('dashboard/sk/add_youth', [
            'residentMode' => true,
            'editMode'     => ! empty($youth['id']),
            'youth'        => $youth,
            'profileOptions' => $options,
            'residentProfile' => $self,
            'successMessage' => $successMessage,
            'selectedProfileOption' => $selectedProfile,
        ]);
    }

    protected function buildResidentProfileData(array $target, ?array $household, array $members, array $user = [], bool $isHead = false): array
    {
        $age = SkYouthModel::calcAge($target['date_of_birth'] ?? null);
        $ageGroup = '';
        if ($age !== null) {
            if ($age >= 15 && $age <= 17) $ageGroup = '15-17';
            elseif ($age >= 18 && $age <= 21) $ageGroup = '18-21';
            elseif ($age >= 22 && $age <= 24) $ageGroup = '22-24';
            elseif ($age >= 25 && $age <= 30) $ageGroup = '25-30';
        }

        $parents = ['mother' => null, 'father' => null];
        foreach ($members as $member) {
            $relationship = strtolower((string) ($member['relationship'] ?? ''));
            if (str_contains($relationship, 'mother')) $parents['mother'] = $member;
            if (str_contains($relationship, 'father')) $parents['father'] = $member;
        }

        $targetRelationship = strtolower((string) ($target['relationship'] ?? ''));
        if (! $isHead && str_contains($targetRelationship, 'child') && $household) {
            $headGender = strtolower((string) ($household['gender'] ?? ''));
            $headKey = str_contains($headGender, 'female') ? 'mother' : 'father';
            $parents[$headKey] ??= $household;
            foreach ($members as $member) {
                if (strtolower((string) ($member['relationship'] ?? '')) !== 'spouse') continue;
                $spouseGender = strtolower((string) ($member['gender'] ?? ''));
                $spouseKey = str_contains($spouseGender, 'female') ? 'mother' : 'father';
                $parents[$spouseKey] ??= $member;
            }
        }

        $motherOccupation = $parents['mother']['occupation'] ?? '';
        if ($motherOccupation === '' && ! empty($target['mother_occupation'])) {
            $motherOccupation = $target['mother_occupation'];
        }
        $fatherOccupation = $parents['father']['occupation'] ?? '';
        if ($fatherOccupation === '' && ! empty($target['father_occupation'])) {
            $fatherOccupation = $target['father_occupation'];
        }

        $occupation = $target['occupation'] ?? '';
        $economicStatus = '';
        if (stripos((string) $occupation, 'student') !== false) $economicStatus = 'Student';
        elseif (stripos((string) $occupation, 'unemploy') !== false) $economicStatus = 'Unemployed';
        elseif (stripos((string) $occupation, 'out-of-school') !== false || stripos((string) $occupation, 'osy') !== false) $economicStatus = 'Out-of-School';
        elseif ($occupation !== '' && strtolower((string) $occupation) !== 'none') $economicStatus = 'Employed';

        $monthlyIncome = (float) ($target['monthly_income'] ?? 0);
        $incomeBracket = '';
        if ($monthlyIncome > 0 && $monthlyIncome < 5000) $incomeBracket = 'Below ₱5,000';
        elseif ($monthlyIncome >= 5000 && $monthlyIncome < 10000) $incomeBracket = '₱5,000 – ₱10,000';
        elseif ($monthlyIncome >= 10000 && $monthlyIncome < 20000) $incomeBracket = '₱10,000 – ₱20,000';
        elseif ($monthlyIncome >= 20000 && $monthlyIncome < 40000) $incomeBracket = '₱20,000 – ₱40,000';
        elseif ($monthlyIncome >= 40000) $incomeBracket = 'Above ₱40,000';

        $barangaySuffix = 'Bacolod, Bato, Camarines Sur';
        $addressParts = [];
        $baseAddress = trim((string) ($target['address'] ?? ($household['address'] ?? '')));
        $zone = trim((string) ($target['zone'] ?? ($household['zone'] ?? '')));
        if ($baseAddress !== '') $addressParts[] = trim($baseAddress, " ,");
        if ($zone !== '' && stripos($baseAddress, $zone) === false) $addressParts[] = $zone;
        if (stripos($baseAddress, $barangaySuffix) === false) $addressParts[] = $barangaySuffix;
        $completeAddress = implode(', ', array_filter($addressParts));

        $civilStatus = $target['civil_status']
            ?? $target['marital_status']
            ?? ($isHead ? ($household['civil_status'] ?? '') : '');
        $registeredVoter = array_key_exists('registered_voter', $target)
            ? (int) $target['registered_voter']
            : (int) ($household['registered_voter'] ?? 0);

        $data = array_merge($target, [
            'email' => $target['email'] ?? ($user['email'] ?? ''),
            'contact_number' => $target['contact_number'] ?? ($isHead ? ($user['contact_number'] ?? '') : ($household['contact_number'] ?? '')),
            'address' => $completeAddress,
            'zone' => $zone,
            'citizenship' => $target['citizenship'] ?? ($target['nationality'] ?? ($household['nationality'] ?? 'Filipino')),
            'religion' => $target['religion'] ?? ($household['religion'] ?? ''),
            'months_in_brgy' => $target['months_in_brgy'] ?? ($household['years_of_residency'] ?? ''),
            'age_group' => $ageGroup,
            'civil_status' => $civilStatus,
            'governance' => $registeredVoter === 1 ? 'COMELEC Registered' : 'COMELEC Non-Registered',
            'educational_background' => $target['educational_background'] ?? ($target['educational_attainment'] ?? ''),
            'economic_status' => $economicStatus,
            'monthly_income' => $incomeBracket,
            'mother_name' => $parents['mother'] ? trim(($parents['mother']['first_name'] ?? '') . ' ' . ($parents['mother']['middle_name'] ?? '') . ' ' . ($parents['mother']['last_name'] ?? '')) : '',
            'mother_occupation' => $motherOccupation,
            'father_name' => $parents['father'] ? trim(($parents['father']['first_name'] ?? '') . ' ' . ($parents['father']['middle_name'] ?? '') . ' ' . ($parents['father']['last_name'] ?? '')) : '',
            'father_occupation' => $fatherOccupation,
        ]);

        return $data;
    }

    public function addForm()
    {
        $accounts = [];
        $selectedAccountId = null;
        $selectedHouseholdNo = trim((string) ($this->request->getGet('household_no') ?? ''));
        $selectedSource = $this->request->getGet('source') ?? '';
        $selectedMemberId = (int) ($this->request->getGet('member_id') ?? 0);
        $db = \Config\Database::connect();
        $users = $db->table('users u')
            ->select('u.id, u.email, u.contact_number, u.household_no, u.first_name, u.last_name, u.middle_name, u.role')
            ->whereIn('u.role', ['resident', 'council'])
            ->where('u.status', 'active')
            ->orderBy('u.last_name', 'ASC')
            ->get()->getResultArray();

        foreach ($users as $account) {
            if (empty($account['household_no'])) continue;
            $household = $db->table('households')->where('household_no', $account['household_no'])->get()->getRowArray();
            $members = $db->table('household_members')->where('household_no', $account['household_no'])->get()->getResultArray();
            $target = null;
            $isHead = $household && strcasecmp($account['first_name'], $household['first_name']) === 0 && strcasecmp($account['last_name'], $household['last_name']) === 0;
            if ($isHead) $target = $household;
            foreach ($members as $member) {
                if (strcasecmp($account['first_name'], $member['first_name']) === 0 && strcasecmp($account['last_name'], $member['last_name']) === 0) {
                    $target = $member;
                    $isHead = false;
                    break;
                }
            }
            if (! $target || empty($target['date_of_birth'])) continue;
            $age = SkYouthModel::calcAge($target['date_of_birth']);
            if ($age === null || $age < 15 || $age > 30) continue;
            if ($selectedHouseholdNo !== '' && $account['household_no'] === $selectedHouseholdNo) {
                $matchesTarget = $selectedSource === 'member'
                    ? (int) ($target['id'] ?? 0) === $selectedMemberId
                    : $isHead;
                if ($matchesTarget) $selectedAccountId = (int) $account['id'];
            }
            $accounts[] = [
                'id' => (int) $account['id'],
                'label' => trim($account['last_name'] . ', ' . $account['first_name'] . ' ' . ($account['middle_name'] ?? '')) . ' — ' . $account['email'],
                'data' => $this->buildResidentProfileData($target, $household, $members, $account, $isHead),
            ];
        }

        return view('dashboard/sk/add_youth', [
            'youthAccounts' => $accounts,
            'selectedAccountId' => $selectedAccountId,
        ]);
    }

    // ── Store new youth ───────────────────────────────────────────────────────

    public function storeResidentProfiling()
    {
        $post = $this->request->getPost();
        $role = session()->get('role');

        if (! in_array($role, ['resident', 'council'], true)) {
            return redirect()->to('/sk/profiling')->with('error', 'Only residents and barangay council members aged 15 to 30 can submit this form.');
        }

        $redirectBase = $role === 'council' ? '/council/sk-profiling' : '/resident/sk-profiling';

        $userId = (int) session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        $db = \Config\Database::connect();
        $householdNo = $user['household_no'] ?? null;

        $household = $householdNo ? $db->table('households')->where('household_no', $householdNo)->get()->getRowArray() : null;
        $members = $householdNo ? $db->table('household_members')->where('household_no', $householdNo)->get()->getResultArray() : [];

        $selectedProfile = $post['profile_option'] ?? 'self';
        $target = null;

        if ($selectedProfile === 'self') {
            $isHead = false;
            if ($household && strcasecmp(trim((string) ($user['first_name'] ?? '')), trim((string) $household['first_name'])) === 0 && strcasecmp(trim((string) ($user['last_name'] ?? '')), trim((string) $household['last_name'])) === 0) {
                $target = $household;
                $isHead = true;
            } else {
                foreach ($members as $member) {
                    if (strcasecmp(trim((string) ($member['first_name'] ?? '')), trim((string) ($user['first_name'] ?? ''))) === 0 && strcasecmp(trim((string) ($member['last_name'] ?? '')), trim((string) ($user['last_name'] ?? ''))) === 0) {
                        $target = $member;
                        break;
                    }
                }
            }
            if ($target) $post = array_merge($post, $this->buildResidentProfileData($target, $household, $members, $user, $isHead));
        } else {
            foreach ($members as $member) {
                $memberId = 'child_' . ($member['id'] ?? md5(($member['first_name'] ?? '') . ':' . ($member['last_name'] ?? '') . ':' . ($member['date_of_birth'] ?? '')));
                if ($selectedProfile === $memberId) {
                    $target = $member;
                    break;
                }
            }

            if ($target) $post = array_merge($post, $this->buildResidentProfileData($target, $household, $members));
        }

        $age = SkYouthModel::calcAge($post['date_of_birth'] ?? null);
        $isParentOverride = ($age === null || $age < 15 || $age > 30) && $selectedProfile !== 'self';
        if (! $isParentOverride) {
            if ($age === null || $age < 15 || $age > 30) {
                return redirect()->to($redirectBase)->with('error', 'This SK Profiling form is available only to youth ages 15 to 30 years old, or for a parent submitting a minor child profile.');
            }
        }

        $skUser = (new \App\Models\UserModel())
            ->where('role', 'sk')
            ->where('status', 'active')
            ->orderBy('id', 'ASC')
            ->first();

        if (! $skUser) {
            return redirect()->to($redirectBase)->with('error', 'No active SK official is assigned yet. Please contact the barangay office.');
        }

        $recordId = $this->storeYouthRecord($post, (int) $skUser['id'], $redirectBase);
        $this->notifySkProfilingUpdate($skUser, $post, 'submitted');
        $saved = $this->model->find($recordId) ?: ['id' => $recordId];
        $post = array_merge($saved, $post, ['id' => $recordId]);
        $post['organizations'] = ! empty($post['org_name']) ? json_encode(array_map(
            static fn($index) => [
                'name' => $post['org_name'][$index] ?? '',
                'position' => $post['org_position'][$index] ?? '',
                'year' => $post['org_year'][$index] ?? '',
            ],
            array_keys($post['org_name'] ?? [])
        )) : ($saved['organizations'] ?? null);
        $post['health_concerns'] = ! empty($post['health']) ? json_encode((array) $post['health']) : ($saved['health_concerns'] ?? null);
        $post['social_inclusion'] = ! empty($post['social']) ? json_encode((array) $post['social']) : ($saved['social_inclusion'] ?? null);
        return $this->residentProfilingForm(
            'Profile saved. You can now upload a photo below.',
            $post,
            $selectedProfile
        );
    }

    public function store()
    {
        $post = $this->request->getPost();

        if (in_array(session()->get('role'), ['sk', 'admin'], true)) {
            $linkedUser = (new \App\Models\UserModel())
                ->where('id', (int) ($post['user_id'] ?? 0))
                ->whereIn('role', ['resident', 'council'])
                ->where('status', 'active')
                ->first();
            if (! $linkedUser) {
                return redirect()->to('/sk/profiling/add')->with('error', 'Select a valid active youth account before saving the profile.');
            }
        }

        $age = SkYouthModel::calcAge($post['date_of_birth'] ?? null);
        if ($age === null || $age < 15 || $age > 30) {
            $redirect = session()->get('role') === 'resident' ? '/resident/sk-profiling' : '/sk/profiling';
            return redirect()->to($redirect)->with('error', 'Only youth ages 15 to 30 may submit the SK Profiling form.');
        }

        $recordedBy = session()->get('user_id');
        $recordId = $this->storeYouthRecord($post, (int) $recordedBy, '/sk/profiling');

        return redirect()->to('/sk/profiling/edit/' . $recordId)
            ->with('success', 'Profile saved. You can now upload a photo.');
    }

    protected function storeYouthRecord(array $post, int $recordedBy, string $redirectUrl): int
    {
        // Derive age group from DOB
        $age      = SkYouthModel::calcAge($post['date_of_birth'] ?? null);
        $ageGroup = '';
        if ($age !== null) {
            if ($age >= 15 && $age <= 17)      $ageGroup = '15-17';
            elseif ($age >= 18 && $age <= 21)  $ageGroup = '18-21';
            elseif ($age >= 22 && $age <= 24)  $ageGroup = '22-24';
            elseif ($age >= 25 && $age <= 30)  $ageGroup = '25-30';
        }

        // Derive economic status
        $edu    = $post['educational_background'] ?? '';
        $status = $post['economic_status'] ?? SkYouthModel::statusLabel($edu);

        // Encode JSON fields
        $orgs    = [];
        if (! empty($post['org_name'])) {
            foreach ($post['org_name'] as $i => $name) {
                if (empty($name)) continue;
                $orgs[] = [
                    'name'     => $name,
                    'position' => $post['org_position'][$i] ?? '',
                    'year'     => $post['org_year'][$i]     ?? '',
                ];
            }
        }

        $this->model->insert([
            'user_id'                 => ! empty($post['user_id']) ? (int) $post['user_id'] : null,
            'photo_path'              => null,
            'last_name'              => strtoupper(trim($post['last_name']   ?? '')),
            'first_name'             => strtoupper(trim($post['first_name']  ?? '')),
            'middle_name'            => strtoupper(trim($post['middle_name'] ?? '')) ?: null,
            'suffix'                 => $post['suffix']          ?? null,
            'date_of_birth'          => $post['date_of_birth']   ?? null,
            'place_of_birth'         => $post['place_of_birth']  ?? null,
            'gender'                 => $post['gender']          ?? null,
            'religion'               => $post['religion']        ?? null,
            'citizenship'            => $post['citizenship']     ?? 'Filipino',
            'contact_number'         => $post['contact_number']  ?? null,
            'email'                  => $post['email']           ?? null,
            'address'                => $post['address']         ?? null,
            'zone'                   => $post['zone']            ?? null,
            'months_in_brgy'         => $post['months_in_brgy']  ?? null,
            'skills'                 => $post['skills']          ?? null,
            'hobbies'                => $post['hobbies']         ?? null,
            'mother_name'            => $post['mother_name']     ?? null,
            'mother_occupation'      => $post['mother_occupation'] ?? null,
            'father_name'            => $post['father_name']     ?? null,
            'father_occupation'      => $post['father_occupation'] ?? null,
            'organizations'          => ! empty($orgs) ? json_encode($orgs) : null,
            'age_group'              => $ageGroup ?: null,
            'civil_status'           => $post['civil_status']    ?? null,
            'educational_background' => $edu                     ?: null,
            'school_type'            => $post['school_type']     ?? null,
            'school_detail'          => $post['school_detail']   ?? null,
            'governance'             => $post['governance']       ?? null,
            'health_concerns'        => ! empty($post['health'])  ? json_encode((array)$post['health'])  : null,
            'social_inclusion'       => ! empty($post['social'])  ? json_encode((array)$post['social'])  : null,
            'economic_status'        => $status,
            'monthly_income'         => $post['monthly_income']  ?? null,
            'advocacy'               => $post['advocacy']        ?? null,
            'volunteer'              => $post['volunteer']       ?? null,
            'issue_1'                => $post['issue_1']         ?? null,
            'issue_2'                => $post['issue_2']         ?? null,
            'issue_3'                => $post['issue_3']         ?? null,
            'suggestions'            => $post['suggestions']     ?? null,
            'recorded_by'            => $recordedBy,
        ]);

        return (int) $this->model->getInsertID();
    }

    // ── Show edit form ────────────────────────────────────────────────────────

    public function editForm(int $id)
    {
        $youth = $this->model->find($id);
        if (! $youth) {
            return redirect()->to('/sk/profiling')->with('error', 'Record not found.');
        }
        $youth['age'] = SkYouthModel::calcAge($youth['date_of_birth']);
        return view('dashboard/sk/add_youth', ['youth' => $youth, 'editMode' => true]);
    }

    // ── Update youth ──────────────────────────────────────────────────────────

    public function update(int $id)
    {
        $post = $this->request->getPost();
        $role = session()->get('role');
        $allowMinorProfile = in_array($role, ['resident', 'council'], true)
            && ! in_array($post['profile_option'] ?? 'self', ['self', ''], true);

        $age      = SkYouthModel::calcAge($post['date_of_birth'] ?? null);
        $ageGroup = '';
        if ($age !== null) {
            if ($age >= 15 && $age <= 17)      $ageGroup = '15-17';
            elseif ($age >= 18 && $age <= 21)  $ageGroup = '18-21';
            elseif ($age >= 22 && $age <= 24)  $ageGroup = '22-24';
            elseif ($age >= 25 && $age <= 30)  $ageGroup = '25-30';
        }

        $edu    = $post['educational_background'] ?? '';
        $status = $post['economic_status'] ?? SkYouthModel::statusLabel($edu);

        $orgs = [];
        if (! empty($post['org_name'])) {
            foreach ($post['org_name'] as $i => $name) {
                if (empty($name)) continue;
                $orgs[] = [
                    'name'     => $name,
                    'position' => $post['org_position'][$i] ?? '',
                    'year'     => $post['org_year'][$i]     ?? '',
                ];
            }
        }

        if (($age === null || $age < 15 || $age > 30) && ! $allowMinorProfile) {
            $redirect = $role === 'resident' ? '/resident/sk-profiling' : ($role === 'council' ? '/council/sk-profiling' : '/sk/profiling');
            return redirect()->to($redirect)->with('error', 'Only youth ages 15 to 30 may submit the SK Profiling form, except for a parent submitting a minor child profile.');
        }

        $this->model->update($id, [
            'user_id'                 => ! empty($post['user_id']) ? (int) $post['user_id'] : null,
            'last_name'              => strtoupper(trim($post['last_name']   ?? '')),
            'first_name'             => strtoupper(trim($post['first_name']  ?? '')),
            'middle_name'            => strtoupper(trim($post['middle_name'] ?? '')) ?: null,
            'suffix'                 => $post['suffix']          ?? null,
            'date_of_birth'          => $post['date_of_birth']   ?? null,
            'place_of_birth'         => $post['place_of_birth']  ?? null,
            'gender'                 => $post['gender']          ?? null,
            'religion'               => $post['religion']        ?? null,
            'citizenship'            => $post['citizenship']     ?? 'Filipino',
            'contact_number'         => $post['contact_number']  ?? null,
            'email'                  => $post['email']           ?? null,
            'address'                => $post['address']         ?? null,
            'zone'                   => $post['zone']            ?? null,
            'months_in_brgy'         => $post['months_in_brgy']  ?? null,
            'skills'                 => $post['skills']          ?? null,
            'hobbies'                => $post['hobbies']         ?? null,
            'mother_name'            => $post['mother_name']     ?? null,
            'mother_occupation'      => $post['mother_occupation'] ?? null,
            'father_name'            => $post['father_name']     ?? null,
            'father_occupation'      => $post['father_occupation'] ?? null,
            'organizations'          => ! empty($orgs) ? json_encode($orgs) : null,
            'age_group'              => $ageGroup ?: null,
            'civil_status'           => $post['civil_status']    ?? null,
            'educational_background' => $edu                     ?: null,
            'school_type'            => $post['school_type']     ?? null,
            'school_detail'          => $post['school_detail']   ?? null,
            'governance'             => $post['governance']       ?? null,
            'health_concerns'        => ! empty($post['health'])  ? json_encode((array)$post['health'])  : null,
            'social_inclusion'       => ! empty($post['social'])  ? json_encode((array)$post['social'])  : null,
            'economic_status'        => $status,
            'monthly_income'         => $post['monthly_income']  ?? null,
            'advocacy'               => $post['advocacy']        ?? null,
            'volunteer'              => $post['volunteer']       ?? null,
            'issue_1'                => $post['issue_1']         ?? null,
            'issue_2'                => $post['issue_2']         ?? null,
            'issue_3'                => $post['issue_3']         ?? null,
            'suggestions'            => $post['suggestions']     ?? null,
        ]);

        if (in_array($role, ['resident', 'council'], true)) {
            $skUser = (new \App\Models\UserModel())
                ->where('role', 'sk')
                ->where('status', 'active')
                ->orderBy('id', 'ASC')
                ->first();
            if ($skUser) {
                $this->notifySkProfilingUpdate($skUser, $post, 'updated');
            }

            $post['id'] = $id;
            $post['organizations'] = ! empty($post['org_name']) ? json_encode(array_map(
                static fn($index) => [
                    'name' => $post['org_name'][$index] ?? '',
                    'position' => $post['org_position'][$index] ?? '',
                    'year' => $post['org_year'][$index] ?? '',
                ],
                array_keys($post['org_name'])
            )) : null;
            $post['health_concerns'] = ! empty($post['health']) ? json_encode((array) $post['health']) : null;
            $post['social_inclusion'] = ! empty($post['social']) ? json_encode((array) $post['social']) : null;
            $saved = $this->model->find($id) ?: ['id' => $id];
            $post = array_merge($saved, $post, ['id' => $id]);
            return $this->residentProfilingForm('Profile saved. You can now upload a photo below.', $post, $post['profile_option'] ?? 'self');
        }

        return redirect()->to('/sk/profiling/edit/' . $id)->with('success', 'Profile saved. You can now upload a photo.');
    }

    public function uploadPhoto(int $id)
    {
        $youth = $this->model->find($id);
        $role = session()->get('role');
        if (! $youth || ! $this->canManageYouthPhoto($youth)) {
            $fallback = $role === 'resident' ? '/resident/sk-profiling' : ($role === 'council' ? '/council/sk-profiling' : '/sk/profiling');
            return redirect()->to($fallback)->with('error', 'Youth profile not found.');
        }

        $photoPath = $this->storeYouthPhoto($youth['photo_path'] ?? null);
        if ($photoPath === ($youth['photo_path'] ?? null)) {
            $file = $this->request->getFile('profile_photo');
            $message = (! $file || $file->getError() === UPLOAD_ERR_NO_FILE)
                ? 'Choose a photo first, then upload it.'
                : 'Use a JPG, PNG, or WEBP photo up to 5 MB.';
            if (in_array($role, ['resident', 'council'], true)) {
                session()->setFlashdata('error', $message);
                return $this->residentProfilingForm(null, $youth, $this->request->getPost('profile_option') ?: 'self');
            }
            return redirect()->to('/sk/profiling/edit/' . $id)->with('error', $message);
        }

        $this->model->update($id, ['photo_path' => $photoPath]);
        $youth['photo_path'] = $photoPath;

        if (in_array($role, ['resident', 'council'], true)) {
            return $this->residentProfilingForm('Photo uploaded.', $youth, $this->request->getPost('profile_option') ?: 'self');
        }

        return redirect()->to('/sk/profiling/edit/' . $id)->with('success', 'Photo uploaded.');
    }

    private function canManageYouthPhoto(array $youth): bool
    {
        $role = session()->get('role');
        if (in_array($role, ['sk', 'admin'], true)) {
            return true;
        }
        if (! in_array($role, ['resident', 'council'], true)) {
            return false;
        }

        $userId = (int) session()->get('user_id');
        if ($userId > 0 && (int) ($youth['user_id'] ?? 0) === $userId) {
            return true;
        }

        $user = (new \App\Models\UserModel())->find($userId);
        $householdNo = trim((string) ($user['household_no'] ?? ''));
        if ($householdNo === '') {
            return false;
        }

        $db = \Config\Database::connect();
        $names = [];
        $household = $db->table('households')->where('household_no', $householdNo)->get()->getRowArray();
        if ($household) {
            $names[] = strtoupper(trim(($household['first_name'] ?? '') . ' ' . ($household['last_name'] ?? '')));
        }
        foreach ($db->table('household_members')->where('household_no', $householdNo)->get()->getResultArray() as $member) {
            $names[] = strtoupper(trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')));
        }

        $youthName = strtoupper(trim(($youth['first_name'] ?? '') . ' ' . ($youth['last_name'] ?? '')));

        return $youthName !== '' && in_array($youthName, $names, true);
    }

    private function notifySkProfilingUpdate(array $skUser, array $profile, string $action): void
    {
        $name = trim(
            ($profile['first_name'] ?? '') . ' ' .
                ($profile['middle_name'] ?? '') . ' ' .
                ($profile['last_name'] ?? '')
        );
        $name = $name !== '' ? strtoupper($name) : 'A youth resident';

        \App\Models\NotificationModel::push(
            (int) $skUser['id'],
            'youth_profiling',
            'Youth Profiling ' . ucfirst($action),
            $name . ' has ' . $action . ' a youth profiling record.',
            '/sk/profiling'
        );
    }

    protected function storeYouthPhoto(?string $existingPath): ?string
    {
        $file = $this->request->getFile('profile_photo');
        if (! $file || ! $file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) return $existingPath;
        if ($file->getSize() > 5 * 1024 * 1024 || ! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true)) return $existingPath;

        $directory = FCPATH . 'uploads/sk_profiles/';
        if (! is_dir($directory)) mkdir($directory, 0755, true);
        $fileName = $file->getRandomName();
        $file->move($directory, $fileName);
        if ($existingPath && is_file($directory . basename($existingPath))) @unlink($directory . basename($existingPath));
        return 'sk_profiles/' . $fileName;
    }

    // ── Delete youth ──────────────────────────────────────────────────────────

    public function delete(int $id)
    {
        $this->model->delete($id);
        return redirect()->to('/sk/profiling')->with('success', 'Youth profile deleted.');
    }

    // ── Programs & Events ─────────────────────────────────────────────────────

    private function normalizeProgramAge($age): ?int
    {
        if ($age === null || $age === '') {
            return null;
        }
        $age = (int) $age;
        return ($age >= 0 && $age <= 120) ? $age : null;
    }

    private function residentAge(?array $user): ?int
    {
        if (! $user || empty($user['household_no'])) {
            return null;
        }

        $household = (new \App\Models\HouseholdModel())->find($user['household_no']);
        $fullName = strtoupper(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
        if ($household && strtoupper(trim(($household['first_name'] ?? '') . ' ' . ($household['last_name'] ?? ''))) === $fullName) {
            return ! empty($household['date_of_birth']) ? \App\Models\SkYouthModel::calcAge($household['date_of_birth']) : null;
        }

        $member = (new \App\Models\HouseholdMemberModel())
            ->where('household_no', $user['household_no'])
            ->where('first_name', $user['first_name'] ?? '')
            ->where('last_name', $user['last_name'] ?? '')
            ->first();
        return $member && ! empty($member['date_of_birth'])
            ? \App\Models\SkYouthModel::calcAge($member['date_of_birth'])
            : null;
    }

    public function programs()
    {
        $progModel = new \App\Models\SkProgramModel();
        $progModel->syncDateStatuses();

        $search   = trim($_GET['search']   ?? '');
        $catF     = trim($_GET['category'] ?? '');
        $statusF  = trim($_GET['status']   ?? '');

        // Show the latest conducted events first; undated legacy records fall back to start date.
        $builder = $progModel
            ->orderBy('conducted_date IS NULL', 'ASC', false)
            ->orderBy('conducted_date', 'DESC')
            ->orderBy('start_date', 'DESC');
        if ($search !== '')  $builder->groupStart()->like('name', $search)->orLike('venue', $search)->groupEnd();
        if ($catF !== '')    $builder->where('category', $catF);
        if ($statusF !== '') $builder->where('status', $statusF);

        $programs = $builder->findAll();
        $db = \Config\Database::connect();
        foreach ($programs as &$program) {
            $program['actual_participants'] = (int) $db->table('sk_program_registrations')
                ->where('program_id', $program['id'])
                ->where('status', 'approved')
                ->countAllResults();
        }
        unset($program);
        $counts   = $progModel->statusCounts();

        return view('dashboard/sk/programs', [
            'programs' => $programs,
            'counts'   => $counts,
            'search'   => $search,
            'catF'     => $catF,
            'statusF'  => $statusF,
        ]);
    }

    public function programForm()
    {
        if (! can_role('sk')) {
            return redirect()->to('/' . route_prefix() . '/dashboard');
        }

        return view('dashboard/sk/program_form', ['program' => null]);
    }

    public function editProgramForm(int $id)
    {
        if (! can_role('sk')) {
            return redirect()->to('/' . route_prefix() . '/dashboard');
        }

        $program = (new \App\Models\SkProgramModel())->find($id);
        if (! $program) {
            return redirect()->to($this->programBase())->with('error', 'Program not found.');
        }

        return view('dashboard/sk/program_form', ['program' => $program]);
    }

    private function programBase(): string
    {
        return '/' . (session_role() === 'admin' ? 'admin' : 'sk') . '/programs';
    }

    public function storeProgram()
    {
        $progModel = new \App\Models\SkProgramModel();
        $post      = $this->request->getPost();

        if (empty($post['name']) || empty($post['category'])) {
            return redirect()->to($this->programBase() . '/new')->with('error', 'Program name and category are required.')->withInput();
        }

        // Auto-derive status from dates
        $today     = date('Y-m-d');
        $startDate = $post['start_date'] ?? null;
        $endDate   = $post['end_date']   ?? null;
        $conductedDate = $post['conducted_date'] ?? null;
        $minAge = $this->normalizeProgramAge($post['min_age'] ?? null);
        $maxAge = $this->normalizeProgramAge($post['max_age'] ?? null);
        if ($minAge !== null && $maxAge !== null && $minAge > $maxAge) {
            return redirect()->to($this->programBase() . '/new')->with('error', 'Minimum age cannot be greater than maximum age.')->withInput();
        }
        if ($conductedDate && $startDate && $conductedDate < $startDate) {
            return redirect()->to($this->programBase() . '/new')->with('error', 'The conducted date cannot be before the start date.')->withInput();
        }
        $status    = $post['status']     ?? '';
        if (empty($status) || $status !== 'Cancelled') {
            $status = $conductedDate && $conductedDate < $today
                ? 'Completed'
                : ($conductedDate === $today ? 'Active' : 'Upcoming');
        }

        // Parse requirements (one per line from textarea → store as comma-separated)
        $reqRaw  = $post['requirements'] ?? [];
        $reqList = is_array($reqRaw) ? array_filter(array_map('trim', $reqRaw)) : array_filter(array_map('trim', preg_split('/[\n,]+/', trim($reqRaw))));
        $reqStr  = implode(', ', $reqList) ?: null;

        $programId = $progModel->insert([
            'name'                => trim($post['name']),
            'category'            => $post['category'],
            'description'         => trim($post['description'] ?? '') ?: null,
            'requirements'        => $reqStr,
            'start_date'          => $startDate ?: null,
            'end_date'            => $endDate   ?: null,
            'conducted_date'      => $conductedDate ?: null,
            'min_age'             => $minAge,
            'max_age'             => $maxAge,
            'venue'               => trim($post['venue'] ?? '') ?: null,
            'target_participants' => (int)($post['target_participants'] ?? 0),
            'actual_participants' => (int)($post['actual_participants'] ?? 0),
            'budget'              => (float)($post['budget'] ?? 0),
            'status'              => $status,
            'created_by'          => (int)session()->get('user_id'),
            'notify_residents'    => 0,
        ], true);

        // Notify all active residents about the new program
        $this->_notifyResidentsOfProgram((int)$programId, trim($post['name']), $status, $startDate);

        return redirect()->to($this->programBase())->with('success', 'Program "' . esc(trim($post['name'])) . '" added and residents notified.');
    }

    /**
     * Push a notification to every active resident about a new SK program.
     */
    private function _notifyResidentsOfProgram(int $programId, string $name, string $status, ?string $startDate): void
    {
        $db = \Config\Database::connect();

        $residents = $db->table('users')
            ->select('id')
            ->where('role', 'resident')
            ->where('status', 'active')
            ->get()->getResultArray();

        $dateLabel = $startDate ? ' on ' . date('M d, Y', strtotime($startDate)) : '';
        $body      = "A new SK activity has been added: \"{$name}\" ({$status}){$dateLabel}. Open the SK Activities page to learn more and join!";
        $link      = '/resident/sk-activities';

        foreach ($residents as $res) {
            \App\Models\NotificationModel::push(
                (int) $res['id'],
                'sk_program',
                'New SK Activity: ' . $name,
                $body,
                $link
            );
        }

        // Mark program as notified
        $db->table('sk_programs')->where('id', $programId)->update(['notify_residents' => 1]);
    }

    public function updateProgram(int $id)
    {
        $progModel = new \App\Models\SkProgramModel();
        $prog      = $progModel->find($id);
        if (! $prog) return redirect()->to($this->programBase())->with('error', 'Program not found.');

        $post = $this->request->getPost();
        $editUrl = $this->programBase() . '/edit/' . $id;
        if (empty($post['name'])) return redirect()->to($editUrl)->with('error', 'Program name is required.')->withInput();

        $today     = date('Y-m-d');
        $startDate = $post['start_date'] ?? null;
        $endDate   = $post['end_date']   ?? null;
        $conductedDate = $post['conducted_date'] ?? null;
        $minAge = $this->normalizeProgramAge($post['min_age'] ?? null);
        $maxAge = $this->normalizeProgramAge($post['max_age'] ?? null);
        if ($minAge !== null && $maxAge !== null && $minAge > $maxAge) {
            return redirect()->to($editUrl)->with('error', 'Minimum age cannot be greater than maximum age.')->withInput();
        }
        if ($conductedDate && $startDate && $conductedDate < $startDate) {
            return redirect()->to($editUrl)->with('error', 'The conducted date cannot be before the start date.')->withInput();
        }
        $status    = $post['status'] ?? $prog['status'];
        if ($status !== 'Cancelled') {
            $status = $conductedDate && $conductedDate < $today
                ? 'Completed'
                : ($conductedDate === $today ? 'Active' : 'Upcoming');
        }

        // Parse requirements
        $reqRaw  = $post['requirements'] ?? [];
        $reqList = is_array($reqRaw) ? array_filter(array_map('trim', $reqRaw)) : array_filter(array_map('trim', preg_split('/[\n,]+/', trim($reqRaw))));
        $reqStr  = implode(', ', $reqList) ?: null;

        $progModel->update($id, [
            'name'                => trim($post['name']),
            'category'            => $post['category'] ?? $prog['category'],
            'description'         => trim($post['description'] ?? '') ?: null,
            'requirements'        => $reqStr,
            'start_date'          => $startDate ?: null,
            'end_date'            => $endDate   ?: null,
            'conducted_date'      => $conductedDate ?: null,
            'min_age'             => $minAge,
            'max_age'             => $maxAge,
            'venue'               => trim($post['venue'] ?? '') ?: null,
            'target_participants' => (int)($post['target_participants'] ?? 0),
            'actual_participants' => (int)($post['actual_participants'] ?? 0),
            'budget'              => (float)($post['budget'] ?? 0),
            'status'              => $status,
        ]);

        return redirect()->to($this->programBase())->with('success', 'Program updated successfully.');
    }

    public function updateProgramStatus(int $id)
    {
        $progModel = new \App\Models\SkProgramModel();
        $program = $progModel->find($id);
        if (! $program) return redirect()->to('/sk/programs')->with('error', 'Program not found.');
        $progModel->syncDateStatuses();
        return redirect()->to('/sk/programs')->with('success', 'Program status is managed automatically from the conducted date.');
    }

    public function deleteProgram(int $id)
    {
        $progModel = new \App\Models\SkProgramModel();
        $progModel->delete($id);
        return redirect()->to('/sk/programs')->with('success', 'Program deleted.');
    }

    // ── Resident: view SK activities ──────────────────────────────────────────

    public function residentActivities()
    {
        $userId    = (int) session()->get('user_id');
        $progModel = new \App\Models\SkProgramModel();
        $programs  = $progModel->getVisibleToResidents();

        // Attach registration status and count for each program
        foreach ($programs as &$p) {
            $p['registration']       = $progModel->isRegistered((int)$p['id'], $userId);
            $p['registration_count'] = $progModel->getRegistrationCount((int)$p['id']);
            $p['requirements_list']  = \App\Models\SkProgramModel::parseRequirements($p['requirements']);
        }
        unset($p);

        return view('dashboard/resident/sk_activities', [
            'programs' => $programs,
        ]);
    }

    // ── Resident: join an SK program ──────────────────────────────────────────

    public function joinProgram(int $programId)
    {
        $userId    = (int) session()->get('user_id');
        $progModel = new \App\Models\SkProgramModel();
        $progModel->syncDateStatuses();
        $program   = $progModel->find($programId);

        if (! $program || ! in_array($program['status'], ['Active', 'Upcoming'])) {
            return redirect()->to('/resident/sk-activities')->with('error', 'This program is not available for registration.');
        }

        $user = (new \App\Models\UserModel())->find($userId);
        $age = $this->residentAge($user);
        if (($program['min_age'] !== null && ($age === null || $age < (int) $program['min_age']))
            || ($program['max_age'] !== null && ($age === null || $age > (int) $program['max_age']))
        ) {
            $range = trim(($program['min_age'] !== null ? $program['min_age'] . '+' : '') . ($program['max_age'] !== null ? ' to ' . $program['max_age'] : ''));
            return redirect()->to('/resident/sk-activities')->with('error', 'You are not within the required age range' . ($range ? ' (' . $range . ' years).' : '.'));
        }

        // Block registration if the schedule end date has already passed
        if (! empty($program['end_date']) && $program['end_date'] < date('Y-m-d')) {
            return redirect()->to('/resident/sk-activities')->with('error', 'Registration for "' . esc($program['name']) . '" is no longer available — the scheduled date has already passed.');
        }

        // Check already registered
        if ($progModel->isRegistered($programId, $userId)) {
            return redirect()->to('/resident/sk-activities')->with('error', 'You are already registered for this program.');
        }

        // Collect submitted requirements (checkboxes)
        $submitted = $this->request->getPost('requirements') ?? [];
        if (is_array($submitted)) {
            $submitted = array_filter(array_map('trim', $submitted));
        }

        $uploadRequirements = array_values(array_filter(
            \App\Models\SkProgramModel::parseRequirements($program['requirements']),
            static fn(string $requirement): bool => preg_match('/^(document|photo)\s*:/i', $requirement) === 1
        ));
        $uploadedFiles = $this->request->getFileMultiple('attachments') ?? [];
        $attachments = [];
        foreach ($uploadRequirements as $index => $requirement) {
            $file = $uploadedFiles[$index] ?? null;
            if (! $file || ! $file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
                return redirect()->to('/resident/sk-activities')->with('error', 'Please attach all required documents/photos before joining.');
            }
            if ($file->getSize() > 5 * 1024 * 1024 || ! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true)) {
                return redirect()->to('/resident/sk-activities')->with('error', 'Each attachment must be a JPG, PNG, WebP, or PDF file up to 5 MB.');
            }
            $directory = FCPATH . 'uploads/sk_programs/';
            if (! is_dir($directory)) mkdir($directory, 0755, true);
            $fileName = $file->getRandomName();
            $file->move($directory, $fileName);
            $attachments[] = ['requirement' => $requirement, 'path' => 'sk_programs/' . $fileName];
        }

        $db = \Config\Database::connect();
        $db->table('sk_program_registrations')->insert([
            'program_id'             => $programId,
            'user_id'                => $userId,
            'status'                 => 'pending',
            'requirements_submitted' => !empty($submitted) ? implode(', ', $submitted) : null,
            'attachments'           => !empty($attachments) ? json_encode($attachments) : null,
            'notes'                  => trim($this->request->getPost('notes') ?? '') ?: null,
            'created_at'             => date('Y-m-d H:i:s'),
            'updated_at'             => date('Y-m-d H:i:s'),
        ]);

        // Notify SK officer
        $skUser = $db->table('users')->where('role', 'sk')->where('status', 'active')->get()->getRowArray();
        if ($skUser) {
            $resName = trim(session()->get('first_name') . ' ' . session()->get('last_name'));
            \App\Models\NotificationModel::push(
                (int) $skUser['id'],
                'sk_join',
                'New Registration — ' . $program['name'],
                $resName . ' has registered for "' . $program['name'] . '" and is awaiting approval.',
                '/sk/programs/registrations/' . $programId
            );
        }

        return redirect()->to('/resident/sk-activities')->with('success', 'Successfully registered for "' . esc($program['name']) . '"! The SK will review your registration.');
    }

    // ── Resident: unjoin an SK program ────────────────────────────────────────

    public function unjoinProgram(int $programId)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();
        $db->table('sk_program_registrations')
            ->where('program_id', $programId)
            ->where('user_id', $userId)
            ->delete();

        return redirect()->to('/resident/sk-activities')->with('success', 'Registration cancelled.');
    }

    // ── SK: view registrations for a program ──────────────────────────────────

    public function viewRegistrations(int $programId)
    {
        $progModel = new \App\Models\SkProgramModel();
        $program   = $progModel->find($programId);
        if (! $program) {
            return redirect()->to('/sk/programs')->with('error', 'Program not found.');
        }

        $db = \Config\Database::connect();
        $registrations = $db->table('sk_program_registrations r')
            ->select('r.*, CONCAT(u.first_name," ",u.last_name) AS resident_name, u.email, u.username')
            ->join('users u', 'u.id = r.user_id', 'left')
            ->where('r.program_id', $programId)
            ->orderBy('r.created_at', 'ASC')
            ->get()->getResultArray();

        return view('dashboard/sk/registrations', [
            'program'       => $program,
            'registrations' => $registrations,
            'reqList'       => \App\Models\SkProgramModel::parseRequirements($program['requirements']),
        ]);
    }

    // ── SK: approve/reject a registration ────────────────────────────────────

    public function updateRegistration(int $regId)
    {
        $newStatus = $this->request->getPost('status');
        if (! in_array($newStatus, ['approved', 'rejected'])) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $rejectionReason = trim((string) $this->request->getPost('rejection_reason'));
        if ($newStatus === 'rejected' && $rejectionReason === '') {
            return redirect()->back()->with('error', 'Please provide a reason before rejecting this registration.');
        }

        $db  = \Config\Database::connect();
        $reg = $db->table('sk_program_registrations')->where('id', $regId)->get()->getRowArray();
        if (! $reg) return redirect()->back()->with('error', 'Registration not found.');

        $db->table('sk_program_registrations')->where('id', $regId)->update([
            'status'           => $newStatus,
            'rejection_reason' => $newStatus === 'rejected' ? $rejectionReason : null,
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $approvedParticipants = (int) $db->table('sk_program_registrations')
            ->where('program_id', $reg['program_id'])
            ->where('status', 'approved')
            ->countAllResults();
        $db->table('sk_programs')
            ->where('id', $reg['program_id'])
            ->update(['actual_participants' => $approvedParticipants]);

        // Notify resident
        $progModel = new \App\Models\SkProgramModel();
        $program   = $progModel->find($reg['program_id']);
        if ($program) {
            $msg = $newStatus === 'approved'
                ? 'Your registration for "' . $program['name'] . '" has been approved! See you there.'
                : 'Your registration for "' . $program['name'] . '" was not approved. Reason: ' . $rejectionReason;
            \App\Models\NotificationModel::push(
                (int) $reg['user_id'],
                'sk_registration_' . $newStatus,
                'Registration ' . ucfirst($newStatus) . ' — ' . $program['name'],
                $msg,
                '/resident/sk-activities'
            );
        }

        return redirect()->back()->with('success', 'Registration ' . $newStatus . '.');
    }
}
