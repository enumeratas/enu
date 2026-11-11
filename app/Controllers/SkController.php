<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SkYouthModel;

class SkController extends BaseController
{
    protected SkYouthModel $model;

    private ?string $lastYouthSaveError = null;

    private ?string $accountSyncWarning = null;

    public function __construct()
    {
        $this->model = new SkYouthModel();
    }

    // ── Profiling list — pulls from households + household_members ───────────

    public function profiling()
    {
        $search    = trim($_GET['search']      ?? '');
        $ageFilter = trim($_GET['age']         ?? '');
        $gender    = trim($_GET['gender']      ?? '');
        $status    = trim($_GET['status']      ?? '');
        $zone      = trim($_GET['zone']        ?? '');
        $civil     = trim($_GET['civil']       ?? '');

        $youth = $this->mergedYouthRoster();

        $statsTotal    = count($youth);
        $statsMale     = count(array_filter($youth, static fn($r) => strtolower((string) ($r['gender'] ?? '')) === 'male'));
        $statsFemale   = count(array_filter($youth, static fn($r) => strtolower((string) ($r['gender'] ?? '')) === 'female'));
        $statsStudents = count(array_filter($youth, static fn($r) => strcasecmp((string) ($r['economic_status'] ?? ''), 'Student') === 0));
        $statsOos      = count(array_filter($youth, static fn($r) => stripos((string) ($r['economic_status'] ?? ''), 'out-of-school') !== false));

        $youth = \App\Libraries\RecordSearch::filter(
            $youth,
            $search,
            static fn(array $row): string => implode(' ', [
                (string) ($row['last_name'] ?? ''),
                (string) ($row['first_name'] ?? ''),
                (string) ($row['middle_name'] ?? ''),
                (string) ($row['contact_number'] ?? ''),
                (string) ($row['zone'] ?? ''),
                (string) ($row['economic_status'] ?? ''),
            ]),
            static fn(array $row): array => [$row['date_of_birth'] ?? null]
        );

        if ($gender !== '') {
            $youth = array_values(array_filter($youth, static fn($r) => strcasecmp((string) ($r['gender'] ?? ''), $gender) === 0));
        }
        if ($zone !== '') {
            $youth = array_values(array_filter($youth, static fn($r) => strcasecmp(trim((string) ($r['zone'] ?? '')), $zone) === 0));
        }
        if ($civil !== '') {
            $youth = array_values(array_filter($youth, static fn($r) => strcasecmp((string) ($r['civil_status'] ?? ''), $civil) === 0));
        }
        if ($status !== '') {
            $youth = array_values(array_filter($youth, static function ($r) use ($status) {
                $value = (string) ($r['economic_status'] ?? '');
                if ($status === 'Out-of-School') {
                    return stripos($value, 'out-of-school') !== false;
                }

                return strcasecmp($value, $status) === 0;
            }));
        }
        if ($ageFilter !== '') {
            [$amin, $amax] = array_pad(explode('-', $ageFilter), 2, '');
            $amin = (int) $amin;
            $amax = (int) $amax;
            $youth = array_values(array_filter($youth, static function ($r) use ($amin, $amax) {
                $age = $r['age'] ?? null;

                return is_numeric($age) && (int) $age >= $amin && (int) $age <= $amax;
            }));
        }

        usort($youth, static function (array $a, array $b): int {
            return [strtoupper((string) ($a['last_name'] ?? '')), strtoupper((string) ($a['first_name'] ?? ''))]
                <=> [strtoupper((string) ($b['last_name'] ?? '')), strtoupper((string) ($b['first_name'] ?? ''))];
        });

        $perPage = 15;
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $total   = count($youth);
        $rows    = array_slice($youth, ($page - 1) * $perPage, $perPage);

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
            'profilingOpen' => $this->profilingWindow()['open'],
            'profilingClose' => $this->profilingWindow()['close'],
        ]);
    }

    /** @return list<array<string, mixed>> */
    private function mergedYouthRoster(): array
    {
        $db       = \Config\Database::connect();
        $youthMax = date('Y-m-d', strtotime('-15 years'));
        $youthMin = date('Y-m-d', strtotime('-30 years'));

        $headSql = "SELECT
                'head'         AS source,
                h.household_no AS id,
                h.last_name, h.first_name, h.middle_name,
                h.date_of_birth, h.gender, h.civil_status,
                h.occupation, h.contact_number, h.zone, h.household_no
            FROM households h
            WHERE h.date_of_birth IS NOT NULL
              AND h.date_of_birth <= '{$youthMax}'
              AND h.date_of_birth >= '{$youthMin}'";

        $memberSql = "SELECT
                'member'       AS source,
                m.id           AS id,
                m.last_name, m.first_name, m.middle_name,
                m.date_of_birth, m.gender,
                m.marital_status AS civil_status,
                m.occupation,
                ''             AS contact_number,
                h2.zone, m.household_no
            FROM household_members m
            INNER JOIN households h2 ON h2.household_no = m.household_no
            WHERE m.date_of_birth IS NOT NULL
              AND m.date_of_birth <= '{$youthMax}'
              AND m.date_of_birth >= '{$youthMin}'";

        $rows = $db->query("({$headSql}) UNION ALL ({$memberSql})")->getResultArray();

        return $this->model->overlayCensusYouth($rows);
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
        $closedMessage = $this->profilingClosedMessage();
        if ($closedMessage !== null) {
            $dashboard = $role === 'council' ? '/council/dashboard' : '/resident/dashboard';

            return redirect()->to($dashboard)->with('error', $closedMessage);
        }

        $userId = (int) session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (empty($user['household_no'])) {
            return redirect()->to($redirectBase === '/council/sk-profiling' ? '/council/dashboard' : '/resident/dashboard')->with('error', 'Your household record is not linked yet. Please update your profile first.');
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
                'data' => array_merge(
                    $this->buildResidentProfileData($self, $household, $members, $user, $selfIsHead),
                    ['user_id' => $userId]
                ),
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
            return redirect()->to($role === 'council' ? '/council/dashboard' : '/resident/dashboard')->with('error', 'No valid profile option found in your household census record.');
        }

        foreach ($options as $index => $option) {
            $linkedId = ($option['id'] ?? '') === 'self' ? $userId : null;
            $savedProfileRow = $this->model->matchProfile($option['data'], $linkedId);
            if ($savedProfileRow) {
                $options[$index]['data'] = array_merge($option['data'], $savedProfileRow);
                $options[$index]['profile_id'] = (int) ($savedProfileRow['id'] ?? 0);
            }
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
            $existing = $this->model->matchProfile(
                $pick['data'],
                ($pick['id'] ?? '') === 'self' ? $userId : null
            );
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
        $ageGroup = SkYouthModel::ageGroupFor($target['date_of_birth'] ?? null) ?? '';

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

        $data['date_of_birth'] = SkYouthModel::profileDate($data['date_of_birth'] ?? null) ?? '';
        $gender = SkYouthModel::normalizeChoice($data['gender'] ?? '', SkYouthModel::GENDERS);
        if ($gender !== null) {
            $data['gender'] = $gender;
        }
        $education = SkYouthModel::educationChoice($data['educational_background'] ?? null);
        if ($education !== null) {
            $data['educational_background'] = $education;
        }
        $civil = SkYouthModel::normalizeChoice((string) ($data['civil_status'] ?? ''), SkYouthModel::CIVIL_STATUSES);
        if ($civil !== null) {
            $data['civil_status'] = $civil;
        }
        $income = SkYouthModel::normalizeChoice((string) ($data['monthly_income'] ?? ''), SkYouthModel::INCOME_BRACKETS);
        if ($income !== null) {
            $data['monthly_income'] = $income;
        }
        $economic = SkYouthModel::normalizeChoice((string) ($data['economic_status'] ?? ''), SkYouthModel::ECONOMIC_STATUSES);
        if ($economic !== null) {
            $data['economic_status'] = $economic;
        }

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
                'data' => $this->withSavedYouthProfile(
                    $this->buildResidentProfileData($target, $household, $members, $account, $isHead),
                    (int) $account['id']
                ),
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

        $closedMessage = $this->profilingClosedMessage();
        if ($closedMessage !== null) {
            $dashboard = $role === 'council' ? '/council/dashboard' : '/resident/dashboard';

            return redirect()->to($dashboard)->with('error', $closedMessage);
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
            if ($target) {
                $post = array_merge($this->buildResidentProfileData($target, $household, $members, $user, $isHead), $post);
            }
            $post['user_id'] = $userId;
        } else {
            foreach ($members as $member) {
                $memberId = 'child_' . ($member['id'] ?? md5(($member['first_name'] ?? '') . ':' . ($member['last_name'] ?? '') . ':' . ($member['date_of_birth'] ?? '')));
                if ($selectedProfile === $memberId) {
                    $target = $member;
                    break;
                }
            }

            if ($target) {
                $post = array_merge($this->buildResidentProfileData($target, $household, $members), $post);
            }
            unset($post['user_id']);
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
        if ($recordId < 1) {
            session()->setFlashdata('error', $this->lastYouthSaveError ?? 'The profile could not be saved. Check the date of birth and sex, then try again.');

            return $this->residentProfilingForm(null, $post, $selectedProfile);
        }

        $saved = $this->model->find($recordId) ?: ['id' => $recordId];
        $message = $this->finishYouthSave(
            $recordId,
            $this->buildYouthPayload($post, ! empty($post['user_id']) ? (int) $post['user_id'] : null),
            $saved['photo_path'] ?? null
        );
        $this->notifySkProfilingUpdate($skUser, $post, 'submitted');
        $saved = $this->model->find($recordId) ?: $saved;
        $post = array_merge($post, $saved, ['id' => $recordId]);

        return $this->residentProfilingForm($message, $post, $selectedProfile);
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
        if ($recordId < 1) {
            return redirect()->to('/sk/profiling/add')
                ->with('error', $this->lastYouthSaveError ?? 'The profile could not be saved. Check the date of birth and sex, then try again.');
        }

        $saved = $this->model->find($recordId) ?: ['id' => $recordId];
        $message = $this->finishYouthSave(
            $recordId,
            $this->buildYouthPayload($post, ! empty($post['user_id']) ? (int) $post['user_id'] : null),
            $saved['photo_path'] ?? null
        );

        return redirect()->to('/sk/profiling/edit/' . $recordId)->with('success', $message);
    }

    protected function storeYouthRecord(array $post, int $recordedBy, string $redirectUrl): int
    {
        $payload = $this->buildYouthPayload($post, ! empty($post['user_id']) ? (int) $post['user_id'] : null);
        $existingId = $this->findExistingYouthId($post);

        try {
            if ($existingId) {
                $saved = $this->model->update($existingId, $payload);
                if ($saved === false) {
                    $this->lastYouthSaveError = $this->youthSaveError();

                    return 0;
                }
                $this->model->resetProfileIndexes();

                return $existingId;
            }

            $inserted = $this->model->insert($payload + [
                'photo_path'  => null,
                'recorded_by' => $recordedBy > 0 ? $recordedBy : null,
            ]);
            if ($inserted === false) {
                $this->lastYouthSaveError = $this->youthSaveError();

                return 0;
            }
            $this->model->resetProfileIndexes();

            return (int) $this->model->getInsertID();
        } catch (\Throwable $e) {
            log_message('error', 'SK youth profile save failed: ' . $e->getMessage());
            $this->lastYouthSaveError = 'The profile could not be saved. Check the date of birth and sex, then try again.';

            return 0;
        }
    }

    /** @param array<string, mixed> $post */
    private function findExistingYouthId(array $post): ?int
    {
        $matched = $this->model->matchProfile($post, ! empty($post['user_id']) ? (int) $post['user_id'] : null);

        return $matched ? (int) $matched['id'] : null;
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

        if (in_array($role, ['resident', 'council'], true)) {
            $closedMessage = $this->profilingClosedMessage();
            if ($closedMessage !== null) {
                $dashboard = $role === 'council' ? '/council/dashboard' : '/resident/dashboard';

                return redirect()->to($dashboard)->with('error', $closedMessage);
            }
        }

        $age = SkYouthModel::calcAge($post['date_of_birth'] ?? null);
        if (($age === null || $age < 15 || $age > 30) && ! $allowMinorProfile) {
            $redirect = $role === 'resident' ? '/resident/sk-profiling' : ($role === 'council' ? '/council/sk-profiling' : '/sk/profiling');

            return redirect()->to($redirect)->with('error', 'Only youth ages 15 to 30 may submit the SK Profiling form, except for a parent submitting a minor child profile.');
        }

        $existing = $this->model->find($id);
        if (! $existing) {
            return redirect()->to('/sk/profiling')->with('error', 'Record not found.');
        }

        $linkedUserId = ! empty($post['user_id'])
            ? (int) $post['user_id']
            : (int) ($existing['user_id'] ?? 0);
        if (in_array($role, ['resident', 'council'], true) && ($post['profile_option'] ?? 'self') === 'self') {
            $linkedUserId = (int) session()->get('user_id') ?: $linkedUserId;
        }

        $payload = $this->buildYouthPayload($post, $linkedUserId > 0 ? $linkedUserId : null);
        try {
            $saved = $this->model->update($id, $payload);
        } catch (\Throwable $e) {
            log_message('error', 'SK youth profile update failed: ' . $e->getMessage());
            $saved = false;
            $this->lastYouthSaveError = 'The profile could not be saved. Check the date of birth and sex, then try again.';
        }
        if ($saved === false) {
            $message = $this->lastYouthSaveError ?? $this->youthSaveError();
            if (in_array($role, ['resident', 'council'], true)) {
                session()->setFlashdata('error', $message);

                return $this->residentProfilingForm(null, array_merge($existing, $post, ['id' => $id]), $post['profile_option'] ?? 'self');
            }

            return redirect()->to('/sk/profiling/edit/' . $id)->with('error', $message);
        }
        $this->model->resetProfileIndexes();
        $message = $this->finishYouthSave($id, $payload, $existing['photo_path'] ?? null);

        if (in_array($role, ['resident', 'council'], true)) {
            $skUser = (new \App\Models\UserModel())
                ->where('role', 'sk')
                ->where('status', 'active')
                ->orderBy('id', 'ASC')
                ->first();
            if ($skUser) {
                $this->notifySkProfilingUpdate($skUser, $post, 'updated');
            }

            $saved = $this->model->find($id) ?: ['id' => $id];
            $post = array_merge($post, $saved, ['id' => $id]);

            return $this->residentProfilingForm($message, $post, $post['profile_option'] ?? 'self');
        }

        return redirect()->to('/sk/profiling/edit/' . $id)->with('success', $message);
    }

    public function uploadPhoto(int $id)
    {
        $youth = $this->model->find($id);
        $role = session()->get('role');
        if (! $youth || ! $this->canManageYouthPhoto($youth)) {
            $fallback = $role === 'resident' ? '/resident/sk-profiling' : ($role === 'council' ? '/council/sk-profiling' : '/sk/profiling');
            return redirect()->to($fallback)->with('error', 'Youth profile not found.');
        }

        $photo = $this->acceptPostedPhoto($youth['photo_path'] ?? null);
        if (! $photo['uploaded']) {
            $message = $photo['error'] ?? 'Choose a photo first, then upload it.';
            if (in_array($role, ['resident', 'council'], true)) {
                session()->setFlashdata('error', $message);
                return $this->residentProfilingForm(null, $youth, $this->request->getPost('profile_option') ?: 'self');
            }
            return redirect()->to('/sk/profiling/edit/' . $id)->with('error', $message);
        }

        $this->model->update($id, ['photo_path' => $photo['path']]);
        $youth['photo_path'] = $photo['path'];

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
            '/sk/profiling?search=' . rawurlencode($name)
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function withSavedYouthProfile(array $data, ?int $userId): array
    {
        $saved = $this->model->matchProfile($data, $userId);

        return $saved ? array_merge($data, $saved) : $data;
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function buildYouthPayload(array $post, ?int $fallbackUserId = null): array
    {
        $userId = ! empty($post['user_id']) ? (int) $post['user_id'] : (int) $fallbackUserId;
        $orgs = [];
        if (! empty($post['org_name']) && is_array($post['org_name'])) {
            foreach ($post['org_name'] as $index => $name) {
                $name = trim((string) $name);
                if ($name === '') {
                    continue;
                }
                $orgs[] = [
                    'name'     => $name,
                    'position' => $post['org_position'][$index] ?? '',
                    'year'     => $post['org_year'][$index] ?? '',
                ];
            }
        }

        $dob = SkYouthModel::profileDate($post['date_of_birth'] ?? null);
        $civil = SkYouthModel::normalizeChoice($post['civil_status'] ?? '', SkYouthModel::CIVIL_STATUSES)
            ?? SkYouthModel::limitText($post['civil_status'] ?? '', 50);
        $economic = SkYouthModel::normalizeChoice($post['economic_status'] ?? '', SkYouthModel::ECONOMIC_STATUSES)
            ?? SkYouthModel::limitText($post['economic_status'] ?? '', 50);
        $income = SkYouthModel::normalizeChoice($post['monthly_income'] ?? '', SkYouthModel::INCOME_BRACKETS)
            ?? SkYouthModel::limitText($post['monthly_income'] ?? '', 80);

        return [
            'user_id'                => $userId > 0 ? $userId : null,
            'last_name'              => strtoupper(trim((string) ($post['last_name'] ?? ''))),
            'first_name'             => strtoupper(trim((string) ($post['first_name'] ?? ''))),
            'middle_name'            => strtoupper(trim((string) ($post['middle_name'] ?? ''))) ?: null,
            'suffix'                 => SkYouthModel::limitText($post['suffix'] ?? '', 10),
            'date_of_birth'          => $dob,
            'place_of_birth'         => SkYouthModel::limitText($post['place_of_birth'] ?? '', 150),
            'gender'                 => SkYouthModel::normalizeChoice($post['gender'] ?? '', SkYouthModel::GENDERS),
            'religion'               => SkYouthModel::limitText($post['religion'] ?? '', 80),
            'citizenship'            => SkYouthModel::limitText($post['citizenship'] ?? '', 60) ?? 'Filipino',
            'contact_number'         => SkYouthModel::limitText($post['contact_number'] ?? '', 20),
            'email'                  => SkYouthModel::limitText($post['email'] ?? '', 150),
            'address'                => SkYouthModel::limitText($post['address'] ?? '', 1000),
            'zone'                   => SkYouthModel::limitText($post['zone'] ?? '', 50),
            'months_in_brgy'         => SkYouthModel::limitText($post['months_in_brgy'] ?? '', 50),
            'skills'                 => SkYouthModel::limitText($post['skills'] ?? '', 2000),
            'hobbies'                => SkYouthModel::limitText($post['hobbies'] ?? '', 2000),
            'mother_name'            => SkYouthModel::limitText($post['mother_name'] ?? '', 150),
            'mother_occupation'      => SkYouthModel::limitText($post['mother_occupation'] ?? '', 100),
            'father_name'            => SkYouthModel::limitText($post['father_name'] ?? '', 150),
            'father_occupation'      => SkYouthModel::limitText($post['father_occupation'] ?? '', 100),
            'organizations'          => $orgs !== [] ? json_encode($orgs) : null,
            'age_group'              => SkYouthModel::ageGroupFor($dob),
            'civil_status'           => $civil,
            'educational_background' => SkYouthModel::educationChoice($post['educational_background'] ?? null),
            'school_type'            => SkYouthModel::normalizeChoice($post['school_type'] ?? '', SkYouthModel::SCHOOL_TYPES),
            'school_detail'          => SkYouthModel::limitText($post['school_detail'] ?? '', 200),
            'governance'             => SkYouthModel::normalizeChoice($post['governance'] ?? '', SkYouthModel::GOVERNANCE_OPTIONS),
            'health_concerns'        => SkYouthModel::encodeChoices($post['health'] ?? [], SkYouthModel::HEALTH_OPTIONS, true),
            'social_inclusion'       => SkYouthModel::encodeChoices($post['social'] ?? [], SkYouthModel::SOCIAL_OPTIONS, true),
            'economic_status'        => $economic,
            'monthly_income'         => $income,
            'advocacy'               => SkYouthModel::encodeChoices($post['advocacy'] ?? [], SkYouthModel::ADVOCACY_OPTIONS),
            'volunteer'              => SkYouthModel::encodeChoices($post['volunteer'] ?? [], SkYouthModel::VOLUNTEER_OPTIONS),
            'issue_1'                => SkYouthModel::limitText($post['issue_1'] ?? '', 200),
            'issue_2'                => SkYouthModel::limitText($post['issue_2'] ?? '', 200),
            'issue_3'                => SkYouthModel::limitText($post['issue_3'] ?? '', 200),
            'suggestions'            => SkYouthModel::limitText($post['suggestions'] ?? '', 4000),
        ];
    }

    private function youthSaveError(): string
    {
        $errors = $this->model->errors();
        if (is_array($errors) && $errors !== []) {
            return implode(' ', array_map('strval', $errors));
        }

        return 'The profile could not be saved. Check the date of birth and sex, then try again.';
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function finishYouthSave(int $id, array $payload, ?string $existingPhoto): string
    {
        $photo = $this->acceptPostedPhoto($existingPhoto);
        $photoPath = $existingPhoto;
        if ($photo['uploaded'] && ! empty($photo['path'])) {
            $this->model->update($id, ['photo_path' => $photo['path']]);
            $photoPath = $photo['path'];
            $this->model->resetProfileIndexes();
        }

        $userId = (int) ($payload['user_id'] ?? 0);
        if ($userId > 0) {
            $this->syncYouthAccount($userId, $payload);
        }

        $message = $this->profilingSavedMessage($photoPath, $photo['uploaded'], $photo['error']);
        if ($this->accountSyncWarning) {
            $message .= ' ' . $this->accountSyncWarning;
            $this->accountSyncWarning = null;
        }

        return $message;
    }

    private function profilingSavedMessage(?string $photoPath, bool $uploaded, ?string $photoError): string
    {
        if ($photoError) {
            return 'Profile saved. The other information was saved. ' . $photoError;
        }
        if ($uploaded) {
            return 'Profile saved. The photo and updated information are on this SK account.';
        }
        if (trim((string) $photoPath) !== '') {
            return 'Changes saved. This SK account shows the updated profile.';
        }

        return 'Profile saved. You can keep editing the other information. A 2 × 2 photo is still needed to complete profiling.';
    }

    /**
     * @return array{path: ?string, uploaded: bool, error: ?string}
     */
    private function acceptPostedPhoto(?string $existingPath): array
    {
        $file = $this->request->getFile('profile_photo');
        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return ['path' => $existingPath, 'uploaded' => false, 'error' => null];
        }

        $invalid = 'Use a JPG, PNG, or WEBP photo up to 5 MB.';
        if (
            ! $file->isValid()
            || $file->getSize() > 5 * 1024 * 1024
            || ! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true)
        ) {
            return ['path' => $existingPath, 'uploaded' => false, 'error' => $invalid];
        }

        $directory = FCPATH . 'uploads/sk_profiles/';
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            return ['path' => $existingPath, 'uploaded' => false, 'error' => $invalid];
        }

        $fileName = $file->getRandomName();
        try {
            $file->move($directory, $fileName);
        } catch (\Throwable $e) {
            log_message('error', 'SK profile photo upload failed: ' . $e->getMessage());

            return ['path' => $existingPath, 'uploaded' => false, 'error' => $invalid];
        }

        if ($existingPath && is_file($directory . basename($existingPath))) {
            @unlink($directory . basename($existingPath));
        }

        return ['path' => 'sk_profiles/' . $fileName, 'uploaded' => true, 'error' => null];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function syncYouthAccount(int $userId, array $payload): void
    {
        try {
            $first = trim((string) ($payload['first_name'] ?? ''));
            $last = trim((string) ($payload['last_name'] ?? ''));
            if ($first === '' || $last === '') {
                return;
            }

            $users = new \App\Models\UserModel();
            $user = $users->find($userId);
            if (! is_array($user)) {
                return;
            }

            $this->syncCensusPerson($user, $payload);

            $update = [
                'first_name'     => $payload['first_name'] ?? $user['first_name'],
                'last_name'      => $payload['last_name'] ?? $user['last_name'],
                'middle_name'    => $payload['middle_name'] ?? null,
                'contact_number' => $payload['contact_number'] ?? null,
            ];
            $email = trim((string) ($payload['email'] ?? ''));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $taken = $users->where('email', $email)->where('id !=', $userId)->first();
                if (! $taken) {
                    $update['email'] = $email;
                }
            }
            $users->skipValidation(true)->update($userId, $update);

            if ((int) session()->get('user_id') === $userId) {
                $display = trim(
                    ($update['first_name'] ?? '') . ' ' .
                    (($update['middle_name'] ?? '') !== '' && ($update['middle_name'] ?? null) !== null ? $update['middle_name'] . ' ' : '') .
                    ($update['last_name'] ?? '')
                );
                session()->set([
                    'first_name'  => $update['first_name'],
                    'last_name'   => $update['last_name'],
                    'middle_name' => $update['middle_name'] ?? '',
                    'full_name'   => preg_replace('/\s+/', ' ', $display) ?? $display,
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'SK profile account sync failed for user ' . $userId . ': ' . $e->getMessage());
            $this->accountSyncWarning = 'The profile was saved. The linked account could not be updated.';
        }
    }

    /**
     * @param array<string, mixed> $user
     * @param array<string, mixed> $payload
     */
    private function syncCensusPerson(array $user, array $payload): void
    {
        $householdNo = trim((string) ($user['household_no'] ?? ''));
        if ($householdNo === '') {
            return;
        }

        $person = [];
        foreach (['first_name', 'last_name', 'middle_name'] as $field) {
            $value = trim((string) ($payload[$field] ?? ''));
            if ($value !== '') {
                $person[$field] = $value;
            }
        }
        if (! empty($payload['date_of_birth'])) {
            $person['date_of_birth'] = $payload['date_of_birth'];
        }
        if (! empty($payload['gender'])) {
            $person['gender'] = $payload['gender'];
        }
        $civil = SkYouthModel::normalizeChoice($payload['civil_status'] ?? '', SkYouthModel::CENSUS_CIVIL_STATUSES);

        $oldFirst = trim((string) ($user['first_name'] ?? ''));
        $oldLast = trim((string) ($user['last_name'] ?? ''));
        if ($oldFirst === '' || $oldLast === '') {
            return;
        }

        $db = \Config\Database::connect();
        $household = $db->table('households')->where('household_no', $householdNo)->get()->getRowArray();
        if (
            $household
            && strcasecmp(trim((string) ($household['first_name'] ?? '')), $oldFirst) === 0
            && strcasecmp(trim((string) ($household['last_name'] ?? '')), $oldLast) === 0
        ) {
            $head = $person;
            if ($civil !== null) {
                $head['civil_status'] = $civil;
            }
            $contact = trim((string) ($payload['contact_number'] ?? ''));
            if ($contact !== '') {
                $head['contact_number'] = $contact;
            }
            if ($head !== []) {
                $db->table('households')->where('household_no', $householdNo)->update($head);
            }

            return;
        }

        $matches = [];
        foreach ($db->table('household_members')->where('household_no', $householdNo)->get()->getResultArray() as $member) {
            if (
                strcasecmp(trim((string) ($member['first_name'] ?? '')), $oldFirst) === 0
                && strcasecmp(trim((string) ($member['last_name'] ?? '')), $oldLast) === 0
            ) {
                $matches[] = $member;
            }
        }
        if (count($matches) !== 1) {
            return;
        }

        $memberUpdate = $person;
        if ($civil !== null) {
            $memberUpdate['marital_status'] = $civil;
        }
        if ($memberUpdate !== []) {
            $db->table('household_members')->where('id', (int) $matches[0]['id'])->update($memberUpdate);
        }
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
        return resident_census_age($user);
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
        if ($search !== '') {
            $builder->where(\App\Libraries\RecordSearch::clause(
                ['name', 'venue', 'category'],
                ['conducted_date', 'start_date', 'end_date'],
                $search
            ), null, false);
        }
        if ($catF !== '')    $builder->where('category', $catF);
        if ($statusF !== '') $builder->where('status', $statusF);

        $programs = $builder->findAll();
        $db = \Config\Database::connect();
        foreach ($programs as &$program) {
            $this->syncProgramCalendar((int) ($program['id'] ?? 0), $program);
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
        $cleanup = \App\Models\BarangayActivityModel::isCleanupDrive((string) ($post['category'] ?? ''), (string) ($post['name'] ?? ''));
        if ($cleanup) {
            $startDate = null;
            $endDate = null;
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

        $reqStr = $cleanup ? null : \App\Models\BarangayActivityModel::encodeRequirements(
            \App\Models\BarangayActivityModel::collectPostedRequirements($this->request)
        );
        $eligibilityError = null;
        $eligibility = \App\Models\BarangayActivityModel::collectPostedEligibility($this->request, $eligibilityError);
        if ($eligibility === null) {
            return redirect()->to($this->programBase() . '/new')->with('error', $eligibilityError ?: 'Eligibility settings are not valid.')->withInput();
        }

        $programPayload = [
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
        ];
        $db = \Config\Database::connect();
        if ($db->tableExists('sk_programs') && $db->fieldExists('eligibility_groups', 'sk_programs')) {
            $programPayload = $programPayload + $eligibility;
        }

        $programId = $progModel->insert($programPayload, true);

        $this->syncProgramCalendar((int) $programId, [
            'name' => trim($post['name']),
            'category' => (string) ($post['category'] ?? ''),
            'venue' => trim((string) ($post['venue'] ?? '')),
            'conducted_date' => $conductedDate,
            'created_by' => (int) session()->get('user_id'),
        ]);

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
        $cleanup = \App\Models\BarangayActivityModel::isCleanupDrive((string) ($post['category'] ?? $prog['category'] ?? ''), (string) ($post['name'] ?? ''));
        if ($cleanup) {
            $startDate = null;
            $endDate = null;
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

        $reqStr = $cleanup ? null : \App\Models\BarangayActivityModel::encodeRequirements(
            \App\Models\BarangayActivityModel::collectPostedRequirements($this->request)
        );
        $eligibilityError = null;
        $eligibility = \App\Models\BarangayActivityModel::collectPostedEligibility($this->request, $eligibilityError);
        if ($eligibility === null) {
            return redirect()->to($editUrl)->with('error', $eligibilityError ?: 'Eligibility settings are not valid.')->withInput();
        }

        $programPayload = [
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
        ];
        $db = \Config\Database::connect();
        if ($db->tableExists('sk_programs') && $db->fieldExists('eligibility_groups', 'sk_programs')) {
            $programPayload = $programPayload + $eligibility;
        }
        $progModel->update($id, $programPayload);

        $this->syncProgramCalendar($id, [
            'name' => trim($post['name']),
            'category' => (string) ($post['category'] ?? $prog['category'] ?? ''),
            'venue' => trim((string) ($post['venue'] ?? '')),
            'conducted_date' => $conductedDate,
            'created_by' => $prog['created_by'] ?? session()->get('user_id'),
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
        (new \App\Models\ScheduleModel())->deleteMarkedEvent('[sk-program:' . $id . ']');
        return redirect()->to('/sk/programs')->with('success', 'Program deleted.');
    }

    public function saveProfilingWindow()
    {
        $role = (string) session()->get('role');
        if (! in_array($role, ['sk', 'admin'], true)) {
            return redirect()->to('/')->with('error', 'Only the SK can set when profiling is open.');
        }

        $open = trim((string) $this->request->getPost('profiling_open'));
        $close = trim((string) $this->request->getPost('profiling_close'));
        $valid = static function (string $date): bool {
            $parsed = \DateTime::createFromFormat('Y-m-d', $date);

            return $parsed instanceof \DateTime && $parsed->format('Y-m-d') === $date;
        };
        if (! $valid($open) || ! $valid($close) || $close < $open) {
            return redirect()->to('/' . $role . '/profiling')->with('error', 'Set a start date and an end date for SK Profiling. The end date must be on or after the start date.');
        }

        $model = new \App\Models\BarangaySettingsModel();
        if (! $model->tableExists()) {
            return redirect()->to('/' . $role . '/profiling')->with('error', 'Profiling dates could not be saved.');
        }

        foreach ([
            'sk_profiling_open' => [$open, 'SK Profiling Opens', 310],
            'sk_profiling_close' => [$close, 'SK Profiling Closes', 311],
        ] as $key => [$value, $label, $sort]) {
            $existing = $model->where('setting_key', $key)->first();
            if ($existing) {
                $model->update($existing['id'], ['setting_value' => $value]);
            } else {
                $model->insert([
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'label' => $label,
                    'group' => 'system',
                    'sort_order' => $sort,
                ]);
            }
        }

        return redirect()->to('/' . $role . '/profiling')->with('success', 'SK Profiling is open from ' . date('M d, Y', strtotime($open)) . ' to ' . date('M d, Y', strtotime($close)) . '.');
    }

    public function saveChairpersonName()
    {
        $role = (string) session()->get('role');
        if (! in_array($role, ['sk', 'admin'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Only the SK can change the chairperson name.',
            ]);
        }

        $name = trim((string) preg_replace('/\s+/', ' ', (string) $this->request->getPost('sk_chairperson_name')));
        if (mb_strlen($name) > 120) {
            $name = mb_substr($name, 0, 120);
        }

        $model = new \App\Models\BarangaySettingsModel();
        if (! $model->saveValue('sk_chairperson_name', $name, 'SK Chairperson', 'identity', 312)) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'The chairperson name could not be saved.',
            ]);
        }

        return $this->response->setJSON(['success' => true, 'name' => $name]);
    }

    /** @return array{open: string, close: string} */
    private function profilingWindow(): array
    {
        $model = new \App\Models\BarangaySettingsModel();

        return [
            'open' => $model->getValue('sk_profiling_open'),
            'close' => $model->getValue('sk_profiling_close'),
        ];
    }

    private function profilingClosedMessage(): ?string
    {
        $window = $this->profilingWindow();
        if ($window['open'] === '' || $window['close'] === '') {
            return 'SK Profiling is closed until the SK sets the dates when residents can open it.';
        }

        $today = date('Y-m-d');
        if ($today < $window['open'] || $today > $window['close']) {
            return 'SK Profiling is open from ' . date('M d, Y', strtotime($window['open'])) . ' to ' . date('M d, Y', strtotime($window['close'])) . '.';
        }

        return null;
    }

    private function syncProgramCalendar(int $programId, array $program): void
    {
        $date = trim((string) ($program['conducted_date'] ?? ''));
        if ($programId <= 0 || $date === '') {
            return;
        }

        (new \App\Models\ScheduleModel())->upsertMarkedEvent('[sk-program:' . $programId . ']', [
            'title' => (string) ($program['name'] ?? 'SK activity'),
            'description' => trim((string) ($program['category'] ?? 'SK activity') . ' at ' . ((string) ($program['venue'] ?? 'Barangay Hall'))),
            'event_date' => $date,
            'location' => $program['venue'] ?? 'Barangay Hall',
            'created_by' => $program['created_by'] ?? null,
        ]);
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

        $search = \App\Libraries\RecordSearch::term();
        $programs = \App\Libraries\RecordSearch::filter(
            $programs,
            $search,
            static fn(array $program): string => implode(' ', [
                (string) ($program['name'] ?? ''),
                (string) ($program['category'] ?? ''),
                (string) ($program['venue'] ?? ''),
                (string) ($program['status'] ?? ''),
                (string) ($program['description'] ?? ''),
            ]),
            static fn(array $program): array => [
                $program['start_date'] ?? null,
                $program['end_date'] ?? null,
                $program['conducted_date'] ?? null,
            ]
        );

        return view('dashboard/resident/sk_activities', [
            'programs' => $programs,
            'search'   => $search,
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
        $age = resident_census_age($user);
        if (! age_within_inclusive_range($age, $program['min_age'] ?? null, $program['max_age'] ?? null)) {
            $range = trim(($program['min_age'] !== null ? $program['min_age'] . '+' : '') . ($program['max_age'] !== null ? ' to ' . $program['max_age'] : ''));
            return redirect()->to('/resident/sk-activities')->with('error', 'You are not within the required age range' . ($range ? ' (' . $range . ' years).' : '.'));
        }
        $educationError = \App\Models\BarangayActivityModel::educationEligibilityError($user, $program);
        if ($educationError !== null) {
            return redirect()->to('/resident/sk-activities')->with('error', $educationError);
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
            static fn(string $requirement): bool => \App\Models\BarangayActivityModel::isFileRequirement($requirement)
        ));
        $uploadedFiles = $this->request->getFileMultiple('attachments') ?? [];
        $attachments = [];
        foreach ($uploadRequirements as $index => $requirement) {
            $file = $uploadedFiles[$index] ?? null;
            if (! $file || ! $file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
                return redirect()->to('/resident/sk-activities')->with('error', 'Please attach all required documents and images before joining.');
            }
            $kind = \App\Models\BarangayActivityModel::requirementKind($requirement);
            $allowedMimes = $kind === 'image'
                ? ['image/jpeg', 'image/png', 'image/webp']
                : ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            if ($file->getSize() > 5 * 1024 * 1024 || ! in_array($file->getMimeType(), $allowedMimes, true)) {
                return redirect()->to('/resident/sk-activities')->with('error', $kind === 'image'
                    ? 'Each image must be a JPG, PNG, or WebP file up to 5 MB.'
                    : 'Each document must be a JPG, PNG, WebP, or PDF file up to 5 MB.');
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

        $search = \App\Libraries\RecordSearch::term();
        $registrations = \App\Libraries\RecordSearch::filter(
            $registrations,
            $search,
            static fn(array $row): string => implode(' ', [
                (string) ($row['resident_name'] ?? ''),
                (string) ($row['username'] ?? ''),
                (string) ($row['email'] ?? ''),
                (string) ($row['notes'] ?? ''),
                (string) ($row['status'] ?? ''),
                (string) ($row['requirements_submitted'] ?? ''),
            ]),
            static fn(array $row): array => [$row['created_at'] ?? null]
        );

        return view('dashboard/sk/registrations', [
            'program'       => $program,
            'registrations' => $registrations,
            'reqList'       => \App\Models\SkProgramModel::parseRequirements($program['requirements']),
            'search'        => $search,
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
