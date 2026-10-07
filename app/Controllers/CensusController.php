<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CensusUpdateAuthorizationModel;
use App\Models\CensusUpdateDriveModel;
use App\Models\HouseholdMemberRequestModel;
use App\Models\HouseholdModel;
use App\Models\HouseholdMemberModel;
use App\Models\NotificationModel;
use App\Models\UserModel;
use App\Libraries\HouseholdUploadStorage;

class CensusController extends BaseController
{
    private const CENSUS_STASH_KEY = 'census_upload_stash';

    protected HouseholdModel       $householdModel;
    protected HouseholdMemberModel $memberModel;

    public function __construct()
    {
        $this->householdModel = new HouseholdModel();
        $this->memberModel    = new HouseholdMemberModel();
    }

    public static function resolveResidentHouseholdAccess(array $user, ?array $household, array $members): array
    {
        $normalize = static function (?string $value): string {
            return strtolower(trim((string) preg_replace('/\s+/', ' ', $value ?? '')));
        };

        $userFirst = $normalize($user['first_name'] ?? '');
        $userLast  = $normalize($user['last_name'] ?? '');
        $sameHousehold = ! empty($household)
            && trim((string) ($user['household_no'] ?? '')) !== ''
            && (string) ($user['household_no'] ?? '') === (string) ($household['household_no'] ?? '');

        $headNameMatches = false;
        if (! empty($household)) {
            $headFirst = $normalize($household['first_name'] ?? '');
            $headLast  = $normalize($household['last_name'] ?? '');
            $headNameMatches = $userFirst !== '' && $userLast !== '' && $headFirst === $userFirst && $headLast === $userLast;
        }

        $matchedMember = null;
        foreach ($members as $member) {
            $memberFirst = $normalize($member['first_name'] ?? '');
            $memberLast  = $normalize($member['last_name'] ?? '');
            if ($userFirst !== '' && $userLast !== '' && $memberFirst === $userFirst && $memberLast === $userLast) {
                $matchedMember = $member;
                break;
            }
        }

        $isHead = $headNameMatches;
        $isMember = ! $isHead && ($matchedMember !== null || $sameHousehold);

        return [
            'is_household_head' => $isHead,
            'is_member' => $isMember,
            'can_edit_household' => $isHead,
            'can_edit_personal' => $isHead || $isMember,
            'member' => $matchedMember,
        ];
    }

    public static function validateTransferDestinationHousehold(?string $sourceHouseholdNo, ?string $destinationHouseholdNo): array
    {
        $destination = trim((string) ($destinationHouseholdNo ?? ''));

        if ($destination === '') {
            return [
                'valid' => false,
                'message' => 'Please select a destination household for this transfer.',
            ];
        }

        if ($sourceHouseholdNo !== null && $destination === (string) $sourceHouseholdNo) {
            return [
                'valid' => false,
                'message' => 'The destination household must be different from the current household.',
            ];
        }

        return [
            'valid' => true,
            'destination_household_no' => $destination,
        ];
    }

    // ── Show create household form as a full page (instead of modal)
    public function create()
    {
        $role = session()->get('role');
        if (! in_array($role, ['admin', 'captain', 'secretary', 'council'], true)) {
            return redirect()->to('/' . $role . '/dashboard')->with('error', 'You do not have access to the census form.');
        }
        $sharedGroups = \Config\Database::connect()->table('households')
            ->select('shared_address_group, family_number, household_no, first_name, last_name, num_families')
            ->where('shared_address_group IS NOT NULL')
            ->where('shared_address_group !=', '')
            ->orderBy('shared_address_group', 'ASC')
            ->orderBy('family_number', 'ASC')
            ->get()->getResultArray();
        $councilZone = '';
        if ($role === 'council') {
            $councilUser = (new \App\Models\UserModel())->find((int) session()->get('user_id'));
            $councilZone = is_array($councilUser) ? trim((string) ($councilUser['council_zone'] ?? '')) : '';
            if ($councilZone === '') {
                return redirect()->to('/council/census')->with('error', 'Your council account has no assigned zone yet.');
            }
        }

        return view('dashboard/secretary/census_form', [
            'role'         => $role,
            'pageTitle'    => 'Add Household',
            'active'       => 'census',
            'sharedGroups' => $sharedGroups,
            'councilZone'  => $councilZone,
        ]);
    }

    /**
     * Preview the head of an existing household when Shared ownership
     * is linked to that household number.
     */
    public function lookupHouseholdHead(string $householdNo)
    {
        $response = $this->response->setContentType('application/json');
        $role = session()->get('role');
        if (! in_array($role, ['admin', 'captain', 'secretary', 'council'], true)) {
            return $response->setStatusCode(403)->setJSON([
                'ok'    => false,
                'error' => 'You do not have access to household records.',
            ]);
        }

        $householdNo = trim($householdNo);
        if (! preg_match('/^\d{1,5}$/', $householdNo)) {
            return $response->setStatusCode(400)->setJSON([
                'ok'    => false,
                'error' => 'Enter a valid household number.',
            ]);
        }

        $row = $this->householdModel->find($householdNo);
        if (! $row) {
            return $response->setJSON([
                'ok'    => false,
                'error' => 'No household found with number ' . $householdNo . '.',
            ]);
        }

        if ($role === 'council') {
            $councilUser = (new UserModel())->find((int) session()->get('user_id'));
            $zone = is_array($councilUser) ? trim((string) ($councilUser['council_zone'] ?? '')) : '';
            if ($zone === '' || strcasecmp(trim((string) ($row['zone'] ?? '')), $zone) !== 0) {
                return $response->setJSON([
                    'ok'    => false,
                    'error' => 'No household found with number ' . $householdNo . ' in your zone.',
                ]);
            }
        }

        $nameParts = array_filter([
            trim((string) ($row['first_name'] ?? '')),
            trim((string) ($row['middle_name'] ?? '')),
            trim((string) ($row['last_name'] ?? '')),
            trim((string) ($row['suffix'] ?? '')),
        ], static fn ($part) => $part !== '');

        $members = $this->memberModel->where('household_no', $householdNo)->findAll();
        $rank = [
            'spouse'          => 1,
            'child'           => 2,
            'spouse_of_child' => 3,
            'grandchild'      => 4,
        ];
        usort($members, static function (array $a, array $b) use ($rank): int {
            $left = $rank[strtolower((string) ($a['relationship'] ?? ''))] ?? 9;
            $right = $rank[strtolower((string) ($b['relationship'] ?? ''))] ?? 9;
            if ($left !== $right) {
                return $left <=> $right;
            }

            return strcasecmp((string) ($a['last_name'] ?? ''), (string) ($b['last_name'] ?? ''))
                ?: strcasecmp((string) ($a['first_name'] ?? ''), (string) ($b['first_name'] ?? ''));
        });

        $memberRows = [];
        foreach ($members as $member) {
            $memberName = array_filter([
                trim((string) ($member['first_name'] ?? '')),
                trim((string) ($member['middle_name'] ?? '')),
                trim((string) ($member['last_name'] ?? '')),
                trim((string) ($member['suffix'] ?? '')),
            ], static fn ($part) => $part !== '');
            $relationship = strtolower(trim((string) ($member['relationship'] ?? '')));
            $relationshipLabel = match ($relationship) {
                'spouse'          => 'Spouse',
                'child'           => 'Child',
                'spouse_of_child' => 'Spouse of Child',
                'grandchild'      => 'Grandchild',
                default           => ucwords(str_replace('_', ' ', $relationship)),
            };

            $memberRows[] = [
                'relationship'  => $relationshipLabel,
                'name'          => implode(' ', $memberName),
                'date_of_birth' => $this->formatLookupDate((string) ($member['date_of_birth'] ?? '')),
                'gender'        => trim((string) ($member['gender'] ?? '')),
                'is_deceased'   => ! empty($member['is_deceased']),
            ];
        }

        return $response->setJSON([
            'ok'      => true,
            'head'    => [
                'household_no'       => (string) ($row['household_no'] ?? $householdNo),
                'name'               => implode(' ', $nameParts),
                'zone'               => trim((string) ($row['zone'] ?? '')),
                'address'            => trim((string) ($row['address'] ?? '')),
                'date_of_birth'      => $this->formatLookupDate((string) ($row['date_of_birth'] ?? '')),
                'gender'             => trim((string) ($row['gender'] ?? '')),
                'civil_status'       => trim((string) ($row['civil_status'] ?? '')),
                'house_ownership'    => trim((string) ($row['house_ownership'] ?? '')),
                'years_of_residency' => (string) ($row['years_of_residency'] ?? ''),
                'contact_number'     => trim((string) ($row['contact_number'] ?? '')),
                'record_status'      => trim((string) ($row['record_status'] ?? '')),
                'is_deceased'        => ! empty($row['is_deceased']),
            ],
            'members' => $memberRows,
        ]);
    }

    private function formatLookupDate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $stamp = strtotime($value);

        return $stamp === false ? $value : date('F j, Y', $stamp);
    }

    public function finder()
    {
        $role = (string) session()->get('role');
        if (! in_array($role, ['admin', 'captain', 'secretary', 'council'], true)) {
            return redirect()->to('/' . $role . '/dashboard')->with('error', 'You do not have access to household records.');
        }

        $query = trim((string) $this->request->getGet('q'));
        $families = [];
        $message = '';
        if ($query !== '') {
            $digits = preg_replace('/\D/', '', $query) ?? '';
            if (! preg_match('/^\d{1,5}$/', $digits)) {
                $message = 'Enter a household number.';
            } else {
                $result = $this->familiesForHouseholdNumber($digits);
                if (is_string($result)) {
                    $message = $result;
                } else {
                    $families = $result;
                }
            }
        }

        return view('dashboard/secretary/household_finder', [
            'role'      => $role,
            'pageTitle' => 'Household Finder',
            'active'    => 'household_finder',
            'query'     => preg_replace('/\D/', '', $query) ?? '',
            'families'  => $families,
            'message'   => $message,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>|string
     */
    private function familiesForHouseholdNumber(string $householdNo): array|string
    {
        $this->ensureMemberFamilyGroup();
        $household = $this->householdModel->find($householdNo);
        if (! $household) {
            return 'No household found with number ' . $householdNo . '.';
        }
        if (! $this->councilCanViewHousehold($household)) {
            return 'No household found with number ' . $householdNo . ' in your zone.';
        }

        $members = $this->memberModel->where('household_no', $householdNo)->orderBy('id', 'ASC')->findAll();
        $primaryMembers = [];
        $extraFamilies = [];
        foreach ($members as $member) {
            $relationship = strtolower(trim((string) ($member['relationship'] ?? '')));
            $group = max(1, (int) ($member['family_group'] ?? 1));
            if ($relationship === 'head') {
                $extraFamilies[$group]['head'] = $member;
                continue;
            }
            if ($group === 1) {
                $primaryMembers[] = $member;
            } else {
                $extraFamilies[$group]['members'][] = $member;
            }
        }

        $families = [[
            'label'   => 'Head 1',
            'head'    => $this->presentStoredHead($household),
            'members' => array_map([$this, 'presentStoredMember'], $primaryMembers),
        ]];

        ksort($extraFamilies);
        $number = 2;
        foreach ($extraFamilies as $family) {
            if (empty($family['head'])) {
                continue;
            }
            $families[] = [
                'label'   => 'Head ' . $number,
                'head'    => $this->presentStoredMember($family['head'], $household),
                'members' => array_map([$this, 'presentStoredMember'], $family['members'] ?? []),
            ];
            $number++;
        }

        $linkedRows = $this->householdModel
            ->where('linked_household_no', $householdNo)
            ->where('household_no !=', $householdNo)
            ->findAll();
        foreach ($linkedRows as $linked) {
            if (! $this->councilCanViewHousehold($linked)) {
                continue;
            }
            $linkedMembers = $this->memberModel
                ->where('household_no', $linked['household_no'])
                ->orderBy('id', 'ASC')
                ->findAll();
            $visibleMembers = array_values(array_filter(
                $linkedMembers,
                static fn (array $member): bool => strtolower(trim((string) ($member['relationship'] ?? ''))) !== 'head'
            ));
            $families[] = [
                'label'   => 'Head ' . $number,
                'head'    => $this->presentStoredHead($linked),
                'members' => array_map([$this, 'presentStoredMember'], $visibleMembers),
            ];
            $number++;
        }

        return $families;
    }

    private function councilCanViewHousehold(array $household): bool
    {
        if (session()->get('role') !== 'council') {
            return true;
        }
        $councilUser = (new UserModel())->find((int) session()->get('user_id'));
        $zone = is_array($councilUser) ? trim((string) ($councilUser['council_zone'] ?? '')) : '';

        return $zone !== '' && strcasecmp(trim((string) ($household['zone'] ?? '')), $zone) === 0;
    }

    private function presentStoredHead(array $household): array
    {
        $name = trim(implode(' ', array_filter([
            trim((string) ($household['first_name'] ?? '')),
            trim((string) ($household['middle_name'] ?? '')),
            trim((string) ($household['last_name'] ?? '')),
            trim((string) ($household['suffix'] ?? '')),
        ])));

        return [
            'name'            => $name,
            'household_no'    => (string) ($household['household_no'] ?? ''),
            'zone'            => trim((string) ($household['zone'] ?? '')),
            'address'         => trim((string) ($household['address'] ?? '')),
            'date_of_birth'   => $this->formatLookupDate((string) ($household['date_of_birth'] ?? '')),
            'gender'          => trim((string) ($household['gender'] ?? '')),
            'civil_status'    => trim((string) ($household['civil_status'] ?? '')),
            'contact_number'  => trim((string) ($household['contact_number'] ?? '')),
            'house_ownership' => trim((string) ($household['house_ownership'] ?? '')),
        ];
    }

    private function presentStoredMember(array $member, ?array $household = null): array
    {
        $name = trim(implode(' ', array_filter([
            trim((string) ($member['first_name'] ?? '')),
            trim((string) ($member['middle_name'] ?? '')),
            trim((string) ($member['last_name'] ?? '')),
            trim((string) ($member['suffix'] ?? '')),
        ])));
        $relationship = strtolower(trim((string) ($member['relationship'] ?? '')));
        $relationshipLabel = match ($relationship) {
            'head'            => 'Head',
            'spouse'          => 'Spouse',
            'child'           => 'Child',
            'spouse_of_child' => 'Spouse of Child',
            'grandchild'      => 'Grandchild',
            default           => ucwords(str_replace('_', ' ', $relationship)),
        };

        return [
            'name'            => $name,
            'relationship'    => $relationshipLabel,
            'household_no'    => (string) ($household['household_no'] ?? ''),
            'zone'            => trim((string) ($household['zone'] ?? '')),
            'address'         => trim((string) ($household['address'] ?? '')),
            'date_of_birth'   => $this->formatLookupDate((string) ($member['date_of_birth'] ?? '')),
            'gender'          => trim((string) ($member['gender'] ?? '')),
            'civil_status'    => trim((string) ($member['marital_status'] ?? '')),
            'house_ownership' => trim((string) ($household['house_ownership'] ?? '')),
            'is_deceased'     => ! empty($member['is_deceased']),
        ];
    }

    private function nextFamilyGroup(string $householdNo): int
    {
        if (! $this->ensureMemberFamilyGroup()) {
            return 2;
        }
        $row = \Config\Database::connect()->query(
            'SELECT MAX(family_group) AS n FROM household_members WHERE household_no = ?',
            [$householdNo]
        )->getRow();

        return max(2, ((int) ($row->n ?? 1)) + 1);
    }

    private function ensureMemberFamilyGroup(): bool
    {
        try {
            $db = \Config\Database::connect();
            if ($db->fieldExists('family_group', 'household_members')) {
                return true;
            }
            $db->query('ALTER TABLE household_members ADD family_group INT NOT NULL DEFAULT 1');

            return $db->fieldExists('family_group', 'household_members');
        } catch (\Throwable $e) {
            log_message('error', 'Could not add household_members.family_group: ' . $e->getMessage());

            return false;
        }
    }

    private function _handleIdUpload(string $fieldName, string $prefix, string $householdNo, ?string $oldPath = null): ?string
    {
        $file = $this->request->getFile($fieldName);
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $ext = $this->uploadExtension($file);
            if ($ext === null) {
                return $oldPath;
            }

            $uploadDir = WRITEPATH . 'uploads/ids/';
            if (! is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }

            $fileName = $prefix . '_' . $householdNo . '_' . time() . '_' . bin2hex(random_bytes(2)) . '.' . $ext;
            if (! $file->move($uploadDir, $fileName)) {
                return $this->promoteStashedPath($fieldName, $prefix, $householdNo, $oldPath);
            }

            (new HouseholdUploadStorage())->store($uploadDir . $fileName, 'uploads/ids/' . $fileName);

            return 'uploads/ids/' . $fileName;
        }

        return $this->promoteStashedPath($fieldName, $prefix, $householdNo, $oldPath);
    }

    private function personAlreadyRecorded(string $lastName, string $firstName, ?string $dateOfBirth, ?string $excludeHousehold = null): bool
    {
        if (trim((string) $dateOfBirth) === '') {
            return false;
        }

        $db = \Config\Database::connect();
        $lastName = strtoupper(trim($lastName));
        $firstName = strtoupper(trim($firstName));

        $householdQuery = $db->table('households')
            ->where('last_name', $lastName)
            ->where('first_name', $firstName)
            ->where('date_of_birth', $dateOfBirth);
        if ($excludeHousehold !== null) {
            $householdQuery->where('household_no !=', $excludeHousehold);
        }
        if ($householdQuery->countAllResults() > 0) {
            return true;
        }

        return $db->table('household_members')
            ->where('last_name', $lastName)
            ->where('first_name', $firstName)
            ->where('date_of_birth', $dateOfBirth)
            ->countAllResults() > 0;
    }

    private function ageFromDate(?string $dateOfBirth): ?int
    {
        return person_age_from_dob($dateOfBirth);
    }

    private function validateFamilyAges(?string $headDob, ?string $spouseDob, array $childDobs): ?string
    {
        if ($headDob && ($headAge = $this->ageFromDate($headDob)) !== null && $headAge < 18) {
            return 'A minor cannot be registered as the household head. The household head must be at least 18 years old.';
        }

        foreach ($childDobs as $index => $childDob) {
            if (! $childDob) continue;
            if ($headDob && $childDob <= $headDob) {
                return 'Child #' . ((int) $index + 1) . ' cannot be older than or the same age as the household head.';
            }
            if ($spouseDob && $childDob <= $spouseDob) {
                return 'Child #' . ((int) $index + 1) . ' cannot be older than or the same age as the spouse of the household head.';
            }
        }

        return null;
    }

    private function validateMinorCivilStatuses(array $members): ?string
    {
        foreach ($members as $index => $member) {
            if (($member['relationship'] ?? '') !== 'child') {
                continue;
            }

            $age = $this->ageFromDate($member['date_of_birth'] ?? null);
            $status = trim((string) ($member['marital_status'] ?? 'Single'));
            if ($age !== null && $age < 18 && $status !== 'Single') {
                return 'A child under 18 must have Civil Status set to Single. Please correct child #' . ((int) $index + 1) . '.';
            }
        }

        return null;
    }

    private function validateMinorCivilStatusesFromPost(array $post): ?string
    {
        $childIndex = 0;
        foreach ((array) ($post['child_last_name'] ?? []) as $i => $lastName) {
            if (trim((string) $lastName) === '') {
                continue;
            }
            $childIndex++;
            $age = $this->ageFromDate($post['child_dob'][$i] ?? null);
            $status = trim((string) ($post['child_marital_status'][$i] ?? 'Single'));
            if ($age !== null && $age < 18 && $status !== 'Single') {
                return 'A child under 18 must have Civil Status set to Single. Please correct child #' . $childIndex . '.';
            }
        }

        return null;
    }

    private function nextAvailableHouseholdNo(): string
    {
        for ($attempt = 0; $attempt < 16; $attempt++) {
            $householdNo = str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
            if ($this->householdModel->find($householdNo) === null) {
                return $householdNo;
            }
        }

        do {
            $householdNo = str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
        } while ($this->householdModel->where('household_no', $householdNo)->countAllResults() > 0);

        return $householdNo;
    }

    /**
     * Store an ownership-change audit row only when the audit table is available.
     * Some existing deployments may not have run the migration that creates this table yet,
     * so we must never crash the household update flow because of a missing audit table.
     */
    private function recordHouseholdOwnershipChange(array $change): void
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('household_ownership_changes')) {
            log_message('warning', 'Skipped ownership audit insert because household_ownership_changes table is missing.');
            return;
        }

        try {
            $db->table('household_ownership_changes')->insert($change);
        } catch (\Throwable $e) {
            log_message('error', 'Household ownership audit insert failed: ' . $e->getMessage());
        }
    }

    private function _handleMemberIdUpload(string $fieldName, string $prefix, int $memberId, ?string $oldPath = null): ?string
    {
        $file = $this->request->getFile($fieldName);
        return $this->_handleUploadedMemberIdFile($file, $prefix, $memberId, $oldPath, $fieldName);
    }

    private function _handleUploadedMemberIdFile($file, string $prefix, int $memberId, ?string $oldPath = null, ?string $stashField = null, ?int $stashIndex = null): ?string
    {
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $ext = $this->uploadExtension($file);
            if ($ext === null) {
                return $oldPath;
            }

            $uploadDir = WRITEPATH . 'uploads/ids/';
            if (! is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }

            $fileName = $prefix . '_member_' . $memberId . '_' . time() . '_' . bin2hex(random_bytes(2)) . '.' . $ext;
            if ($file->move($uploadDir, $fileName)) {
                (new HouseholdUploadStorage())->store($uploadDir . $fileName, 'uploads/ids/' . $fileName);

                return 'uploads/ids/' . $fileName;
            }
        }

        if ($stashField !== null) {
            return $this->promoteStashedPath($stashField, $prefix, 'member_' . $memberId, $oldPath, $stashIndex);
        }

        return $oldPath;
    }

    private function uploadExtension($file): ?string
    {
        $ext = strtolower((string) $file->getExtension());
        if ($ext === '') {
            $ext = strtolower((string) $file->guessExtension());
        }
        if ($ext === '') {
            $ext = strtolower((string) pathinfo((string) $file->getClientName(), PATHINFO_EXTENSION));
        }
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }

        return in_array($ext, ['pdf', 'jpg', 'png', 'webp'], true) ? $ext : null;
    }

    private function uploadIsPresent(string $field, ?int $index = null): bool
    {
        $file = $index === null
            ? $this->request->getFile($field)
            : (($this->request->getFileMultiple($field) ?? [])[$index] ?? null);

        if ($file && $file->isValid() && ! $file->hasMoved()) {
            return true;
        }

        return $this->stashedUpload($field, $index) !== null;
    }

    private function censusFormError(string $message)
    {
        $this->stashCensusUploads();

        return redirect()->back()->with('error', $message)->withInput();
    }

    private function stashCensusUploads(): void
    {
        $existing = session()->get(self::CENSUS_STASH_KEY);
        $stash = is_array($existing) ? $existing : [];
        $dir = WRITEPATH . 'uploads/census_tmp/' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) session_id()) . '/';
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        foreach ([
            'head_supporting_doc', 'id_4ps', 'id_4ps_back', 'id_senior',
            'id_solo_parent', 'id_pwd', 'spouse_supporting_doc', 'spouse_id_pwd', 'spouse_id_senior',
        ] as $field) {
            $saved = $this->stashOneFile($this->request->getFile($field), $dir, $field);
            if ($saved !== null) {
                $stash[$field] = $saved;
            }
        }

        foreach (['child_supporting_doc', 'child_id_pwd', 'child_id_senior', 'other_supporting_doc', 'other_id_senior'] as $field) {
            if (! isset($stash[$field]) || ! is_array($stash[$field])) {
                $stash[$field] = [];
            }
            foreach ($this->request->getFileMultiple($field) ?? [] as $i => $file) {
                $saved = $this->stashOneFile($file, $dir, $field . '_' . $i);
                if ($saved !== null) {
                    $stash[$field][$i] = $saved;
                }
            }
        }

        session()->set(self::CENSUS_STASH_KEY, $stash);
    }

    private function stashOneFile($file, string $dir, string $key): ?array
    {
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }
        $ext = $this->uploadExtension($file);
        if ($ext === null) {
            return null;
        }

        $clientName = $file->getClientName() ?: ($key . '.' . $ext);
        $name = $key . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (! $file->move($dir, $name)) {
            return null;
        }

        return [
            'path' => $dir . $name,
            'name' => $clientName,
        ];
    }

    private function stashedUpload(string $field, ?int $index = null): ?array
    {
        $stash = session()->get(self::CENSUS_STASH_KEY);
        if (! is_array($stash) || ! isset($stash[$field])) {
            return null;
        }

        $entry = $index === null ? $stash[$field] : ($stash[$field][$index] ?? null);
        if (! is_array($entry) || empty($entry['path']) || ! is_file((string) $entry['path'])) {
            return null;
        }

        return $entry;
    }

    private function promoteStashedPath(string $field, string $prefix, string $idPart, ?string $oldPath, ?int $index = null): ?string
    {
        $entry = $this->stashedUpload($field, $index);
        if ($entry === null) {
            return $oldPath;
        }

        $ext = strtolower((string) pathinfo((string) $entry['path'], PATHINFO_EXTENSION));
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        if (! in_array($ext, ['pdf', 'jpg', 'png', 'webp'], true)) {
            return $oldPath;
        }

        $uploadDir = WRITEPATH . 'uploads/ids/';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $fileName = $prefix . '_' . $idPart . '_' . time() . '_' . bin2hex(random_bytes(2)) . '.' . $ext;
        if (! @copy((string) $entry['path'], $uploadDir . $fileName)) {
            return $oldPath;
        }

        (new HouseholdUploadStorage())->store($uploadDir . $fileName, 'uploads/ids/' . $fileName);

        return 'uploads/ids/' . $fileName;
    }

    private function clearCensusUploadStash(): void
    {
        $stash = session()->get(self::CENSUS_STASH_KEY);
        if (is_array($stash)) {
            $walk = static function ($item) use (&$walk): void {
                if (is_array($item) && isset($item['path']) && is_file((string) $item['path'])) {
                    @unlink((string) $item['path']);
                    return;
                }
                if (is_array($item)) {
                    foreach ($item as $child) {
                        $walk($child);
                    }
                }
            };
            $walk($stash);
        }
        session()->remove(self::CENSUS_STASH_KEY);
    }

    /**
     * Shared families must point at an existing household number.
     * Returns the group plan, or an error message.
     *
     * @return array<string, mixed>|string
     */
    private function planSharedHousehold(string $linkedNo, ?string $selfNo = null): array|string
    {
        $linkedNo = trim($linkedNo);
        if ($linkedNo === '' || ! preg_match('/^\d{1,5}$/', $linkedNo)) {
            return 'Enter the household number this shared family belongs to.';
        }
        if ($selfNo !== null && $linkedNo === $selfNo) {
            return 'A household cannot be linked to its own household number.';
        }

        $target = $this->householdModel->find($linkedNo);
        if (! $target) {
            return 'Household ' . $linkedNo . ' was not found. A shared family must be linked to an existing household number.';
        }

        $group = trim((string) ($target['shared_address_group'] ?? ''));
        $stampTarget = false;
        if ($group === '') {
            $group = 'SHR-' . random_int(10000, 99999);
            $familyNumber = 2;
            $stampTarget = true;
        } else {
            $row = \Config\Database::connect()->query(
                'SELECT MAX(family_number) AS n FROM households WHERE shared_address_group = ?',
                [$group]
            )->getRow();
            $familyNumber = ((int) ($row->n ?? 0)) + 1;
        }

        return [
            'linked_household_no'  => $linkedNo,
            'shared_address_group' => $group,
            'family_number'        => $familyNumber,
            'stamp_target'         => $stampTarget,
        ];
    }

    private function attachSharedHousehold(array $plan): void
    {
        if (! empty($plan['stamp_target'])) {
            $this->householdModel->update($plan['linked_household_no'], [
                'house_ownership'      => 'Shared',
                'shared_address_group' => $plan['shared_address_group'],
                'family_number'        => 1,
                'num_families'         => 2,
            ]);
        }

        $count = $this->householdModel
            ->where('shared_address_group', $plan['shared_address_group'])
            ->countAllResults();
        \Config\Database::connect()->table('households')
            ->where('shared_address_group', $plan['shared_address_group'])
            ->update(['num_families' => max(2, $count)]);
    }

    private function missingSupportingDocumentMessage(array $post): ?string
    {
        $headHas = $this->uploadIsPresent('head_supporting_doc')
            || (isset($post['is_4ps']) && $this->uploadIsPresent('id_4ps'))
            || (isset($post['is_senior_citizen']) && $this->uploadIsPresent('id_senior'))
            || (isset($post['is_solo_parent']) && $this->uploadIsPresent('id_solo_parent'))
            || (isset($post['is_pwd']) && $this->uploadIsPresent('id_pwd'));
        if (! $headHas) {
            return 'The household head needs one ID or birth certificate. Use Save as Draft if the document is not available yet.';
        }

        if (trim((string) ($post['spouse_last_name'] ?? '')) !== '') {
            $spouseHas = $this->uploadIsPresent('spouse_supporting_doc')
                || (($post['spouse_pwd'] ?? '0') === '1' && $this->uploadIsPresent('spouse_id_pwd'))
                || (isset($post['spouse_senior']) && $this->uploadIsPresent('spouse_id_senior'));
            if (! $spouseHas) {
                return 'The spouse needs one ID or birth certificate. Use Save as Draft if the document is not available yet.';
            }
        }

        foreach ((array) ($post['child_last_name'] ?? []) as $i => $lastName) {
            if (trim((string) $lastName) === '') {
                continue;
            }
            $has = $this->uploadIsPresent('child_supporting_doc', (int) $i)
                || (($post['child_pwd'][$i] ?? '0') === '1' && $this->uploadIsPresent('child_id_pwd', (int) $i))
                || (isset($post['child_senior'][$i]) && $this->uploadIsPresent('child_id_senior', (int) $i));
            if (! $has) {
                return 'Child #' . ((int) $i + 1) . ' needs one ID or birth certificate. Use Save as Draft if the document is not available yet.';
            }
        }

        foreach ((array) ($post['other_last_name'] ?? []) as $i => $lastName) {
            if (trim((string) $lastName) === '') {
                continue;
            }
            $has = $this->uploadIsPresent('other_supporting_doc', (int) $i)
                || (isset($post['other_senior'][$i]) && $this->uploadIsPresent('other_id_senior', (int) $i));
            if (! $has) {
                return trim($lastName) . ' needs one ID or birth certificate. Use Save as Draft if the document is not available yet.';
            }
        }

        return null;
    }

    private function postFlagIsVerified(array $post, string $key, ?int $index = null): bool
    {
        if ($index === null) {
            return (string) ($post[$key] ?? '0') === '1';
        }

        $flags = $post[$key] ?? [];
        if (! is_array($flags)) {
            return false;
        }

        return (string) ($flags[$index] ?? '0') === '1';
    }

    private function unverifiedDocumentMessage(array $post): ?string
    {
        $fail = static function (string $label): string {
            return 'The uploaded ' . $label . ' does not match the name and birthdate on the form. Please upload the correct document and verify it with OCR before saving.';
        };

        if ($this->uploadIsPresent('head_supporting_doc') && ! $this->postFlagIsVerified($post, 'head_supporting_verified')) {
            return $fail('household head ID or birth certificate');
        }
        if (isset($post['is_4ps']) && $this->uploadIsPresent('id_4ps') && ! $this->postFlagIsVerified($post, 'id_4ps_verified')) {
            return $fail('4Ps Beneficiary ID');
        }
        if (isset($post['is_senior_citizen']) && $this->uploadIsPresent('id_senior') && ! $this->postFlagIsVerified($post, 'id_senior_verified')) {
            return $fail('Senior Citizen ID');
        }
        if (isset($post['is_solo_parent']) && $this->uploadIsPresent('id_solo_parent') && ! $this->postFlagIsVerified($post, 'id_solo_parent_verified')) {
            return $fail('Solo Parent ID');
        }
        if (isset($post['is_pwd']) && $this->uploadIsPresent('id_pwd') && ! $this->postFlagIsVerified($post, 'id_pwd_verified')) {
            return $fail('PWD ID');
        }
        if (trim((string) ($post['spouse_last_name'] ?? '')) !== ''
            && $this->uploadIsPresent('spouse_supporting_doc')
            && ! $this->postFlagIsVerified($post, 'spouse_supporting_verified')
        ) {
            return $fail('spouse ID or birth certificate');
        }

        foreach ((array) ($post['child_last_name'] ?? []) as $i => $lastName) {
            if (trim((string) $lastName) === '') {
                continue;
            }
            if ($this->uploadIsPresent('child_supporting_doc', (int) $i) && ! $this->postFlagIsVerified($post, 'child_supporting_verified', (int) $i)) {
                return $fail('ID or birth certificate for child #' . ((int) $i + 1));
            }
        }

        foreach ((array) ($post['other_last_name'] ?? []) as $i => $lastName) {
            if (trim((string) $lastName) === '') {
                continue;
            }
            if ($this->uploadIsPresent('other_supporting_doc', (int) $i) && ! $this->postFlagIsVerified($post, 'other_supporting_verified', (int) $i)) {
                return $fail('ID or birth certificate for ' . trim((string) $lastName));
            }
        }

        return null;
    }

    private function missingWorkingStudentWork(array $post): ?string
    {
        foreach ((array) ($post['child_occupation'] ?? []) as $i => $occupation) {
            $lastName = trim((string) ($post['child_last_name'][$i] ?? ''));
            if ($lastName === '' || strtoupper(trim((string) $occupation)) !== 'WORKING STUDENT') {
                continue;
            }
            if (trim((string) ($post['child_work'][$i] ?? '')) === '') {
                return 'Specify the work of working student ' . $lastName . '. That work is saved on the census record.';
            }
        }

        return null;
    }

    /**
     * Add a confirmed shared family onto an existing household number
     * without replacing the people already recorded there.
     *
     * @param array<int, array<string, mixed>> $members
     */
    private function storeAppendedFamilyFiles(string $householdNo, array $members): ?string
    {
        $saved = [];
        $keepFamilyGroup = $this->ensureMemberFamilyGroup();
        foreach ($members as $member) {
            $member['household_no'] = $householdNo;
            if (! $keepFamilyGroup) {
                unset($member['family_group']);
            }
            if (trim((string) ($member['philhealth_no'] ?? '')) === '') {
                $member['philhealth_no'] = null;
            }
            try {
                $id = $this->memberModel->insert($member);
            } catch (\Throwable $e) {
                $message = $e->getMessage();
                if (str_contains($message, 'philhealth')) {
                    return 'That PhilHealth number is already registered.';
                }
                throw $e;
            }
            if (! $id) {
                return 'Could not add this family to household ' . $householdNo . '.';
            }
            $saved[] = [
                'id'           => (int) $id,
                'relationship' => (string) ($member['relationship'] ?? ''),
            ];
        }

        $head = null;
        $spouse = null;
        $children = [];
        $others = [];
        foreach ($saved as $row) {
            if ($row['relationship'] === 'Head' && $head === null) {
                $head = $row;
            } elseif ($row['relationship'] === 'spouse' && $spouse === null) {
                $spouse = $row;
            } elseif ($row['relationship'] === 'child') {
                $children[] = $row;
            } elseif (! in_array($row['relationship'], ['Head', 'spouse', 'child', 'spouse_of_child', 'grandchild'], true)) {
                $others[] = $row;
            }
        }

        if ($head) {
            $support = $this->_handleUploadedMemberIdFile($this->request->getFile('head_supporting_doc'), 'support', $head['id'], null, 'head_supporting_doc');
            if ($support) {
                $this->memberModel->update($head['id'], ['supporting_doc_path' => $support]);
            }
            if ($this->request->getPost('is_pwd') !== null) {
                $path = $this->_handleUploadedMemberIdFile($this->request->getFile('id_pwd'), 'pwd', $head['id'], null, 'id_pwd');
                if ($path) {
                    $this->memberModel->update($head['id'], ['id_pwd_path' => $path]);
                }
            }
            if ($this->request->getPost('is_senior_citizen') !== null) {
                $path = $this->_handleUploadedMemberIdFile($this->request->getFile('id_senior'), 'senior', $head['id'], null, 'id_senior');
                if ($path) {
                    $this->memberModel->update($head['id'], ['id_senior_path' => $path]);
                }
            }
        }

        if ($spouse && ($this->request->getPost('spouse_pwd') ?? '0') === '1') {
            $path = $this->_handleMemberIdUpload('spouse_id_pwd', 'pwd', $spouse['id']);
            $this->memberModel->update($spouse['id'], ['id_pwd_path' => $path]);
        }
        if ($spouse && ($this->request->getPost('spouse_senior') ?? '0') === '1') {
            $path = $this->_handleMemberIdUpload('spouse_id_senior', 'senior', $spouse['id']);
            $this->memberModel->update($spouse['id'], ['id_senior_path' => $path]);
        }
        if ($spouse) {
            $spouseSupport = $this->_handleMemberIdUpload('spouse_supporting_doc', 'support', $spouse['id']);
            if ($spouseSupport) {
                $this->memberModel->update($spouse['id'], ['supporting_doc_path' => $spouseSupport]);
            }
        }

        $childFileIndex = 0;
        $childPwdPosted = (array) $this->request->getPost('child_pwd');
        $childSeniorPosted = (array) $this->request->getPost('child_senior');
        $childPwdFiles = $this->request->getFileMultiple('child_id_pwd');
        $childSeniorFiles = $this->request->getFileMultiple('child_id_senior');
        $childSupportFiles = $this->request->getFileMultiple('child_supporting_doc');
        foreach ((array) $this->request->getPost('child_last_name') as $i => $lastName) {
            if (trim((string) $lastName) === '') {
                continue;
            }
            $child = $children[$childFileIndex++] ?? null;
            if (! $child) {
                continue;
            }
            if (($childPwdPosted[$i] ?? '0') === '1') {
                $path = $this->_handleUploadedMemberIdFile($childPwdFiles[$i] ?? null, 'pwd', $child['id'], null, 'child_id_pwd', (int) $i);
                $this->memberModel->update($child['id'], ['id_pwd_path' => $path]);
            }
            if (isset($childSeniorPosted[$i])) {
                $path = $this->_handleUploadedMemberIdFile($childSeniorFiles[$i] ?? null, 'senior', $child['id'], null, 'child_id_senior', (int) $i);
                $this->memberModel->update($child['id'], ['id_senior_path' => $path]);
            }
            $path = $this->_handleUploadedMemberIdFile($childSupportFiles[$i] ?? null, 'support', $child['id'], null, 'child_supporting_doc', (int) $i);
            if ($path) {
                $this->memberModel->update($child['id'], ['supporting_doc_path' => $path]);
            }
        }

        $otherFileIndex = 0;
        $otherSeniorPosted = (array) $this->request->getPost('other_senior');
        $otherSeniorFiles = $this->request->getFileMultiple('other_id_senior');
        $otherSupportFiles = $this->request->getFileMultiple('other_supporting_doc');
        foreach ((array) $this->request->getPost('other_last_name') as $i => $lastName) {
            if (trim((string) $lastName) === '') {
                continue;
            }
            $other = $others[$otherFileIndex++] ?? null;
            if (! $other) {
                continue;
            }
            if (isset($otherSeniorPosted[$i])) {
                $path = $this->_handleUploadedMemberIdFile($otherSeniorFiles[$i] ?? null, 'senior', $other['id'], null, 'other_id_senior', (int) $i);
                $this->memberModel->update($other['id'], ['id_senior_path' => $path]);
            }
            $path = $this->_handleUploadedMemberIdFile($otherSupportFiles[$i] ?? null, 'support', $other['id'], null, 'other_supporting_doc', (int) $i);
            if ($path) {
                $this->memberModel->update($other['id'], ['supporting_doc_path' => $path]);
            }
        }

        return null;
    }

    // ── Save new household from the census form ───────────────────────────────

    public function store()
    {
        $post = $this->request->getPost();
        $saveAsDraft = ($post['save_mode'] ?? 'complete') === 'draft';

        $ageError = $this->validateFamilyAges(
            $post['date_of_birth'] ?? null,
            $post['spouse_dob'] ?? null,
            (array) ($post['child_dob'] ?? [])
        );
        if ($ageError !== null) {
            return $this->censusFormError($ageError);
        }

        $sharedPlan = null;
        if (($post['house_ownership'] ?? 'Owned') === 'Shared') {
            $sharedPlan = $this->planSharedHousehold(trim((string) ($post['linked_household_no'] ?? '')));
            if (is_string($sharedPlan)) {
                return $this->censusFormError($sharedPlan);
            }
        }

        $rawContactNumber = $post['contact_number'] ?? '';
        if ($rawContactNumber !== '' && $this->cleanContactNumber($rawContactNumber) === null) {
            return $this->censusFormError('Contact number must contain exactly 11 digits.');
        }

        // ── Solo Parent validation: must have at least one child ──────────────
        if (isset($post['is_solo_parent'])) {
            $childNames = array_filter(
                array_map('trim', (array) ($post['child_last_name'] ?? [])),
                fn($v) => $v !== ''
            );
            if (empty($childNames)) {
                return $this->censusFormError('A Solo Parent record requires at least one child. Please add the child\'s information in the Family Information section.');
            }
        }

        // ── ID upload validation: REQUIRED for 4PS, Senior, Solo Parent, PWD ─
        $idErrors = [];
        if (isset($post['is_4ps'])) {
            $f = $this->request->getFile('id_4ps');
            if (! $f || ! $f->isValid()) $idErrors[] = '4Ps Beneficiary ID upload is required.';
        }
        if (isset($post['is_solo_parent'])) {
            $f = $this->request->getFile('id_solo_parent');
            if (! $f || ! $f->isValid()) $idErrors[] = 'Solo Parent ID upload is required.';
        }
        if (isset($post['is_pwd'])) {
            $f = $this->request->getFile('id_pwd');
            if (! $f || ! $f->isValid()) $idErrors[] = 'PWD ID upload is required.';
        }
        if (($post['spouse_pwd'] ?? '0') === '1') {
            $f = $this->request->getFile('spouse_id_pwd');
            if (! $f || ! $f->isValid()) $idErrors[] = 'Spouse PWD photo/ID upload is required.';
        }
        $childPwdFiles = $this->request->getFileMultiple('child_id_pwd');
        foreach ((array) ($post['child_pwd'] ?? []) as $i => $pwd) {
            if ($pwd === '1' && (! isset($childPwdFiles[$i]) || ! $childPwdFiles[$i]->isValid())) {
                $idErrors[] = 'PWD photo/ID upload is required for child #' . ((int) $i + 1) . '.';
            }
        }
        if (! $saveAsDraft && ! empty($idErrors)) {
            return $this->censusFormError(implode(' ', $idErrors));
        }
        if (! $saveAsDraft) {
            $documentError = $this->missingSupportingDocumentMessage($post);
            if ($documentError !== null) {
                return $this->censusFormError($documentError);
            }
            $verifyError = $this->unverifiedDocumentMessage($post);
            if ($verifyError !== null) {
                return $this->censusFormError($verifyError);
            }
            $workError = $this->missingWorkingStudentWork($post);
            if ($workError !== null) {
                return $this->censusFormError($workError);
            }
        }

        // ── Step 1: Save household head ───────────────────────────────────
        // Auto keeps a generated number. Manual keeps what was typed or confirmed from a linked household.
        $householdNoMode = ($post['household_no_mode'] ?? 'auto') === 'manual' ? 'manual' : 'auto';
        $householdNo = preg_replace('/\D/', '', (string) ($post['household_no'] ?? '')) ?? '';
        $appendToExisting = false;

        if ($householdNoMode === 'manual') {
            if ($householdNo === '' || ! preg_match('/^\d{1,5}$/', $householdNo)) {
                return $this->censusFormError('Enter a household number, or turn Auto on to generate one.');
            }
            $existingHousehold = $this->householdModel->find($householdNo);
            $linkedNo = preg_replace('/\D/', '', trim((string) ($post['linked_household_no'] ?? ''))) ?? '';
            if ($existingHousehold && ($post['house_ownership'] ?? '') === 'Shared' && $linkedNo === $householdNo) {
                $appendToExisting = true;
            } elseif ($existingHousehold) {
                return $this->censusFormError('Household number ' . $householdNo . ' is already in use. Enter a different number, or turn Auto on to generate one.');
            }
        } elseif ($householdNo === '' || $this->householdModel->where('household_no', $householdNo)->countAllResults() > 0) {
            $householdNo = $this->nextAvailableHouseholdNo();
        }

        $transactionDate = $post['recorded_date'] ?? date('Y-m-d');
        $enteredResidencyYears = max(0, (int) ($post['years_of_residency'] ?? 0));
        $residencyStartYear = (int) date('Y') - $enteredResidencyYears;

        $role = (string) session()->get('role');
        if ($role === 'council') {
            $councilUser = (new \App\Models\UserModel())->find((int) session()->get('user_id'));
            $councilZone = is_array($councilUser) ? trim((string) ($councilUser['council_zone'] ?? '')) : '';
            if ($councilZone === '') {
                return redirect()->to('/council/census')->with('error', 'Your council account has no assigned zone yet.');
            }
            $post['zone'] = $councilZone;
        }

        $householdData = [
            'household_no'           => $householdNo,
            'zone'                   => $post['zone']                   ?? null,
            'last_name'              => $post['last_name']              ?? '',
            'first_name'             => $post['first_name']             ?? '',
            'middle_name'            => $post['middle_name']            ?? null,
            'suffix'                 => $post['suffix']                 ?? null,
            'date_of_birth'          => $post['date_of_birth']          ?? null,
            'place_of_birth'         => $post['place_of_birth']         ?? null,
            'gender'                 => $post['gender']                 ?? 'Male',
            'civil_status'           => $post['civil_status']           ?? 'Single',
            'nationality'            => $post['nationality']            ?? 'Filipino',
            'religion'               => $post['religion']               ?? null,
            'occupation'             => $post['occupation']             ?? null,
            'monthly_income'         => $post['monthly_income']         ?? 0,
            'contact_number'         => $this->cleanContactNumber($rawContactNumber),
            'educational_attainment' => $post['educational_attainment'] ?? null,
            'philhealth_no'          => $post['philhealth_no']          ?? null,
            'address'                => $post['address']                ?? null,
            'years_of_residency'     => $enteredResidencyYears,
            'residency_start_year'   => $residencyStartYear,
            'house_ownership'        => $post['house_ownership']        ?? 'Owned',
            'is_4ps'                 => isset($post['is_4ps'])          ? 1 : 0,
            'is_pwd'                 => isset($post['is_pwd'])          ? 1 : 0,
            'pwd_type'               => (isset($post['is_pwd']) && !empty($post['pwd_type'])) ? $post['pwd_type'] : null,
            'is_senior_citizen'      => isset($post['is_senior_citizen']) ? 1 : 0,
            'is_solo_parent'         => isset($post['is_solo_parent'])  ? 1 : 0,
            'is_indigenous'          => isset($post['is_indigenous'])   ? 1 : 0,
            'registered_voter'       => ($post['registered_voter'] ?? '0') === '1' ? 1 : 0,
            'num_families'           => $sharedPlan
                ? max(2, (int) ($post['num_families'] ?? 2))
                : max(1, (int) ($post['num_families'] ?? 1)),
            'water_source_level'     => $post['water_source']           ?? null,
            'water_safety_managed'   => isset($post['water_managed'])   ? ($post['water_managed'] === 'yes' ? 1 : 0) : null,
            'sanitation_basic'       => $post['sanitation_basic']       ?? null,
            'sanitation_managed'     => $post['sanitation_managed']     ?? null,
            'recorded_by'             => session()->get('user_id'),
            'recorded_date'           => $transactionDate,
            'approval_status'         => session()->get('role') === 'council' ? 'pending' : 'approved',
            'record_status'           => $saveAsDraft ? 'draft' : 'complete',
            'census_year'             => (int) ($post['census_year']     ?? date('Y')),
            'ownership_document_path' => null,
            'ownership_notes'         => $post['ownership_notes']        ?? null,
            'shared_address_group'    => $sharedPlan['shared_address_group'] ?? null,
            'family_number'           => $sharedPlan['family_number'] ?? 1,
            'linked_household_no'     => $sharedPlan['linked_household_no'] ?? null,
        ];

        if ($this->personAlreadyRecorded(
            (string) $householdData['last_name'],
            (string) $householdData['first_name'],
            $householdData['date_of_birth']
        )) {
            return $this->censusFormError('This person is already recorded in the census. Please use the existing household record.');
        }

        $earlyMinorError = $this->validateMinorCivilStatusesFromPost($post);
        if ($earlyMinorError !== null) {
            return $this->censusFormError($earlyMinorError);
        }

        if (! $appendToExisting) {
            // Use insert() directly — save() can behave unexpectedly with string PKs
            try {
                $inserted = $this->householdModel->insert($householdData, false);
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'uq_households_philhealth') || str_contains($msg, 'philhealth_no')) {
                    return $this->censusFormError('That PhilHealth number is already registered to another household head.');
                }
                if (str_contains($msg, 'uq_households_contact') || str_contains($msg, 'contact_number')) {
                    return $this->censusFormError('That contact number is already registered to another household head.');
                }
                if (str_contains($msg, 'uq_households_person')) {
                    return $this->censusFormError('A household head with the same name and date of birth already exists in the census.');
                }
                $this->stashCensusUploads();
                throw $e;
            }

            if ($inserted === false) {
                $errors = implode(' ', $this->householdModel->errors());
                return $this->censusFormError('Failed to save household: ' . $errors);
            }
        } else {
            $this->householdModel->update($householdNo, ['house_ownership' => 'Shared']);
        }

        // household_no is the PK — use it directly
        $householdKey = $householdNo;

        // ── Process head ID uploads and update household ────────────────
        $idUpdate = [];
        if (isset($post['is_4ps'])) {
            $idUpdate['id_4ps_path']     = $this->_handleIdUpload('id_4ps', '4ps', $householdKey);
            $idUpdate['id_4ps_verified'] = ($post['id_4ps_verified'] ?? '0') === '1' ? 1 : 0;
        }
        if (isset($post['is_senior_citizen'])) {
            $idUpdate['id_senior_path']     = $this->_handleIdUpload('id_senior', 'senior', $householdKey);
            $idUpdate['id_senior_verified'] = ($post['id_senior_verified'] ?? '0') === '1' ? 1 : 0;
        }
        if (isset($post['is_solo_parent'])) {
            $idUpdate['id_solo_parent_path']     = $this->_handleIdUpload('id_solo_parent', 'solo', $householdKey);
            $idUpdate['id_solo_parent_verified'] = ($post['id_solo_parent_verified'] ?? '0') === '1' ? 1 : 0;
        }
        if (isset($post['is_pwd'])) {
            $idUpdate['id_pwd_path']     = $this->_handleIdUpload('id_pwd', 'pwd', $householdKey);
            $idUpdate['id_pwd_verified'] = ($post['id_pwd_verified'] ?? '0') === '1' ? 1 : 0;
        }
        $headSupportPath = $this->_handleIdUpload('head_supporting_doc', 'support', $householdKey);
        if ($headSupportPath) {
            $idUpdate['supporting_doc_path'] = $headSupportPath;
        }
        if (! $appendToExisting && ! empty($idUpdate)) {
            $this->householdModel->update($householdKey, $idUpdate);
        }
        if (! $appendToExisting && $sharedPlan) {
            $this->attachSharedHousehold($sharedPlan);
        }

        // ── Step 2: Save members (spouse, children, others) ───────────────
        $members = [];

        // Spouse
        if (! empty($post['spouse_last_name'])) {
            $spousePwd = ($post['spouse_pwd'] ?? '0') === '1' ? 1 : 0;
            $members[] = [
                'relationship'           => 'spouse',
                'last_name'              => $post['spouse_last_name'],
                'first_name'             => $post['spouse_first_name']             ?? '',
                'middle_name'            => $post['spouse_middle_name']            ?? null,
                'suffix'                 => $post['spouse_suffix']                 ?? null,
                'date_of_birth'          => $post['spouse_dob']                    ?? null,
                'gender'                 => $post['spouse_gender']                 ?? null,
                'occupation'             => $post['spouse_occupation']             ?? null,
                'monthly_income'         => $post['spouse_income']                 ?? 0,
                'philhealth_no'          => $post['spouse_philhealth']             ?? null,
                'educational_attainment' => $post['spouse_educational_attainment'] ?? null,
                'grade_level'            => null,
                'is_pwd'                 => $spousePwd,
                'pwd_type'               => $spousePwd ? ($post['spouse_pwd_type'] ?? null) : null,
                'id_pwd_path'            => null,
                'id_senior_path'        => null,
            ];
        }

        // Children — posted as arrays: child_last_name[], child_first_name[], etc.
        if (! empty($post['child_last_name'])) {
            foreach ($post['child_last_name'] as $i => $lastName) {
                if (empty($lastName)) continue;
                $childPwd = ($post['child_pwd'][$i] ?? '0') === '1' ? 1 : 0;
                $members[] = [
                    'relationship'           => 'child',
                    'last_name'              => $lastName,
                    'first_name'             => $post['child_first_name'][$i]  ?? '',
                    'middle_name'            => $post['child_middle_name'][$i] ?? null,
                    'suffix'                 => $post['child_suffix'][$i]      ?? null,
                    'date_of_birth'          => $post['child_dob'][$i]         ?? null,
                    'gender'                 => $post['child_gender'][$i]      ?? null,
                    'marital_status'        => $post['child_marital_status'][$i] ?? 'Single',
                    'occupation'             => $post['child_occupation'][$i]  ?? null,
                    'work_detail'            => strtoupper(trim((string) ($post['child_occupation'][$i] ?? ''))) === 'WORKING STUDENT'
                        ? strtoupper(trim((string) ($post['child_work'][$i] ?? '')))
                        : null,
                    'monthly_income'         => $post['child_income'][$i]      ?? 0,
                    'philhealth_no'          => $post['child_philhealth'][$i]  ?? null,
                    'educational_attainment' => null,
                    'grade_level'            => $post['child_grade'][$i]       ?? null,
                    'is_pwd'                 => $childPwd,
                    'pwd_type'               => $childPwd ? ($post['child_pwd_type'][$i] ?? null) : null,
                    'id_pwd_path'            => null,
                    'id_senior_path'         => null,
                ];

                $childSpouseName = trim((string) ($post['child_spouse_name'][$i] ?? ''));
                if (($post['child_marital_status'][$i] ?? 'Single') === 'Married' && $childSpouseName !== '') {
                    $members[] = [
                        'relationship' => 'spouse_of_child',
                        'last_name' => $childSpouseName,
                        'first_name' => '',
                        'middle_name' => null,
                        'suffix' => null,
                        'date_of_birth' => null,
                        'gender' => null,
                        'occupation' => null,
                        'monthly_income' => 0,
                        'philhealth_no' => null,
                        'educational_attainment' => null,
                        'grade_level' => null,
                        'is_pwd' => 0,
                        'pwd_type' => null,
                        'id_pwd_path' => null,
                        'id_senior_path' => null,
                    ];
                }
                foreach (preg_split('/\r\n|\r|\n/', (string) ($post['child_children_names'][$i] ?? '')) as $grandchildName) {
                    $grandchildName = trim($grandchildName);
                    if ($grandchildName === '') continue;
                    $nameParts = preg_split('/\s+/', $grandchildName);
                    $grandchildLastName = array_pop($nameParts);
                    $members[] = [
                        'relationship' => 'grandchild',
                        'last_name' => $grandchildLastName,
                        'first_name' => implode(' ', $nameParts) ?: $grandchildLastName,
                        'middle_name' => null,
                        'suffix' => null,
                        'date_of_birth' => null,
                        'gender' => null,
                        'occupation' => null,
                        'monthly_income' => 0,
                        'philhealth_no' => null,
                        'educational_attainment' => null,
                        'grade_level' => null,
                        'is_pwd' => 0,
                        'pwd_type' => null,
                        'id_pwd_path' => null,
                        'id_senior_path' => null,
                    ];
                }
            }
        }

        // Other household members
        if (! empty($post['other_last_name'])) {
            foreach ($post['other_last_name'] as $i => $lastName) {
                if (empty($lastName)) continue;
                $otherPwd = ($post['other_pwd'][$i] ?? '0') === '1' ? 1 : 0;
                $members[] = [
                    'relationship'           => $post['other_relationship'][$i] ?? 'other_relative',
                    'last_name'              => $lastName,
                    'first_name'             => $post['other_first_name'][$i]  ?? '',
                    'middle_name'            => $post['other_middle_name'][$i] ?? null,
                    'suffix'                 => $post['other_suffix'][$i]      ?? null,
                    'date_of_birth'          => $post['other_dob'][$i]         ?? null,
                    'gender'                 => $post['other_gender'][$i]      ?? null,
                    'occupation'             => null,
                    'monthly_income'         => 0,
                    'philhealth_no'          => null,
                    'educational_attainment' => null,
                    'grade_level'            => null,
                    'is_pwd'                 => $otherPwd,
                    'pwd_type'               => $otherPwd ? ($post['other_pwd_type'][$i] ?? null) : null,
                    'id_pwd_path'            => null,
                    'id_senior_path'         => null,
                ];
            }
        }


        if ($appendToExisting) {
            $familyGroup = $this->nextFamilyGroup($householdNo);
            array_unshift($members, [
                'relationship'           => 'Head',
                'last_name'              => $post['last_name']              ?? '',
                'first_name'             => $post['first_name']             ?? '',
                'middle_name'            => $post['middle_name']            ?? null,
                'suffix'                 => $post['suffix']                 ?? null,
                'date_of_birth'          => $post['date_of_birth']          ?? null,
                'gender'                 => $post['gender']                 ?? null,
                'occupation'             => $post['occupation']             ?? null,
                'monthly_income'         => $post['monthly_income']         ?? 0,
                'philhealth_no'          => $post['philhealth_no']          ?? null,
                'educational_attainment' => $post['educational_attainment'] ?? null,
                'grade_level'            => null,
                'is_pwd'                 => isset($post['is_pwd']) ? 1 : 0,
                'pwd_type'               => (isset($post['is_pwd']) && ! empty($post['pwd_type'])) ? $post['pwd_type'] : null,
                'id_pwd_path'            => null,
                'id_senior_path'         => null,
                'family_group'           => $familyGroup,
            ]);
            foreach ($members as &$appendedMember) {
                $appendedMember['family_group'] = $familyGroup;
                if (trim((string) ($appendedMember['philhealth_no'] ?? '')) === '') {
                    $appendedMember['philhealth_no'] = null;
                }
            }
            unset($appendedMember);
        }

        if ($appendToExisting) {
            $appendError = $this->storeAppendedFamilyFiles($householdKey, $members);
            if ($appendError !== null) {
                return $this->censusFormError($appendError);
            }
        } elseif (! empty($members)) {
            $this->memberModel->replaceMembers($householdKey, $members);

            $savedSpouse = $this->memberModel->where('household_no', $householdKey)
                ->where('relationship', 'spouse')->first();
            $spouseUpdate = [];
            if ($savedSpouse && ($post['spouse_pwd'] ?? '0') === '1') {
                $spouseUpdate['id_pwd_path'] = $this->_handleMemberIdUpload('spouse_id_pwd', 'pwd', (int) $savedSpouse['id']);
            }
            if ($savedSpouse && ($post['spouse_senior'] ?? '0') === '1') {
                $spouseUpdate['id_senior_path'] = $this->_handleMemberIdUpload('spouse_id_senior', 'senior', (int) $savedSpouse['id']);
            }
            if ($savedSpouse) {
                $spouseSupport = $this->_handleMemberIdUpload('spouse_supporting_doc', 'support', (int) $savedSpouse['id']);
                if ($spouseSupport) {
                    $spouseUpdate['supporting_doc_path'] = $spouseSupport;
                }
            }
            if ($savedSpouse && $spouseUpdate !== []) {
                $this->memberModel->update($savedSpouse['id'], $spouseUpdate);
            }

            $savedChildren = $this->memberModel->where('household_no', $householdKey)
                ->where('relationship', 'child')->orderBy('id', 'ASC')->findAll();
            $childFileIndex = 0;
            $childPwdFiles = $this->request->getFileMultiple('child_id_pwd');
            $childSeniorFiles = $this->request->getFileMultiple('child_id_senior');
            $childSupportFiles = $this->request->getFileMultiple('child_supporting_doc');
            foreach ((array) ($post['child_last_name'] ?? []) as $i => $lastName) {
                if (empty($lastName)) continue;
                $child = $savedChildren[$childFileIndex++] ?? null;
                if (! $child) {
                    continue;
                }
                $childUpdate = [];
                if (($post['child_pwd'][$i] ?? '0') === '1') {
                    $childUpdate['id_pwd_path'] = $this->_handleUploadedMemberIdFile(
                        $childPwdFiles[$i] ?? null, 'pwd', (int) $child['id'], null, 'child_id_pwd', (int) $i
                    );
                }
                if (($post['child_senior'][$i] ?? '0') === '1') {
                    $childUpdate['id_senior_path'] = $this->_handleUploadedMemberIdFile(
                        $childSeniorFiles[$i] ?? null, 'senior', (int) $child['id'], null, 'child_id_senior', (int) $i
                    );
                }
                $path = $this->_handleUploadedMemberIdFile(
                    $childSupportFiles[$i] ?? null, 'support', (int) $child['id'], null, 'child_supporting_doc', (int) $i
                );
                if ($path) {
                    $childUpdate['supporting_doc_path'] = $path;
                }
                if ($childUpdate !== []) {
                    $this->memberModel->update($child['id'], $childUpdate);
                }
            }

            $savedOthers = $this->memberModel->where('household_no', $householdKey)
                ->whereNotIn('relationship', ['spouse', 'child', 'spouse_of_child', 'grandchild'])
                ->orderBy('id', 'ASC')->findAll();
            $otherSeniorFiles = $this->request->getFileMultiple('other_id_senior');
            $otherSupportFiles = $this->request->getFileMultiple('other_supporting_doc');
            $otherFileIndex = 0;
            foreach ((array) ($post['other_last_name'] ?? []) as $i => $lastName) {
                if (empty($lastName)) continue;
                $other = $savedOthers[$otherFileIndex++] ?? null;
                if (! $other) {
                    continue;
                }
                $otherUpdate = [];
                if (($post['other_senior'][$i] ?? '0') === '1') {
                    $otherUpdate['id_senior_path'] = $this->_handleUploadedMemberIdFile(
                        $otherSeniorFiles[$i] ?? null, 'senior', (int) $other['id'], null, 'other_id_senior', (int) $i
                    );
                }
                $path = $this->_handleUploadedMemberIdFile(
                    $otherSupportFiles[$i] ?? null, 'support', (int) $other['id'], null, 'other_supporting_doc', (int) $i
                );
                if ($path) {
                    $otherUpdate['supporting_doc_path'] = $path;
                }
                if ($otherUpdate !== []) {
                    $this->memberModel->update($other['id'], $otherUpdate);
                }
            }
        }

        $role = session()->get('role');
        if ($appendToExisting) {
            $message = 'This family was linked to household ' . $householdNo . '.';
        } elseif ($saveAsDraft) {
            $message = 'Household saved as a draft. Upload each member\'s ID or birth certificate to complete the record.';
        } elseif ($role === 'council') {
            $message = 'Household record submitted and is pending Secretary approval.';
        } else {
            $message = 'Household record saved successfully.';
        }
        $this->clearCensusUploadStash();
        return redirect()->to('/' . $role . '/census')->with('success', $message);
    }

    public function sendResidentUpdateAuthorization()
    {
        if (! can_role('secretary', 'admin')) {
            return redirect()->back()->with('error', 'Only the secretary can send census update authorizations.');
        }

        $selected = $this->request->getPost('user_ids');
        $sendAll = $this->request->getPost('send_all') === '1';

        $userModel = new UserModel();
        $query = $userModel->where('role', 'resident')->where('status', 'active');

        if (! $sendAll && ! empty($selected)) {
            $ids = array_map('intval', (array) $selected);
            $query->whereIn('id', $ids);
        } elseif (! $sendAll && empty($selected)) {
            return redirect()->back()->with('error', 'Please select at least one resident to receive the update authorization.');
        }

        $residents = $query->findAll();
        if (empty($residents)) {
            return redirect()->back()->with('error', 'No active resident accounts were found to notify.');
        }

        $count = 0;
        $authModel = new CensusUpdateAuthorizationModel();
        foreach ($residents as $resident) {
            if (empty($resident['email'])) {
                continue;
            }

            $auth = $authModel->createForUser((int) $resident['id'], $resident['household_no'] ?? null, 7);
            if (! $auth) {
                continue;
            }

            $link = site_url('census/update/' . $auth['token']);
            $deadline = date('F j, Y', strtotime('+7 days'));
            $fullName = trim(($resident['first_name'] ?? '') . ' ' . ($resident['last_name'] ?? ''));

            $sent = (new \App\Libraries\EmailService())->sendCensusUpdateAuthorization(
                $resident['email'],
                $fullName ?: 'Resident',
                $link,
                $deadline
            );

            if ($sent) {
                $authModel->update($auth['id'], ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
                $count++;
            }
        }

        return redirect()->back()->with('success', 'Sent census update authorization to ' . $count . ' resident' . ($count === 1 ? '' : 's') . '.');
    }

    private function censusPortal(string $page): string
    {
        $role = session()->get('role') === 'council' ? 'council' : 'resident';

        return '/' . $role . '/' . $page;
    }

    public function publicUpdateAuthorization(string $token)
    {
        $auth = (new CensusUpdateAuthorizationModel())->findValidByToken($token);
        if (! $auth) {
            return redirect()->to('/login')->with('error', 'This census update link is invalid or has expired.');
        }

        session()->set('pending_census_update_token', $token);

        if (session()->get('user_id')) {
            $currentUserId = (int) session()->get('user_id');
            if ($currentUserId !== (int) $auth['user_id']) {
                return redirect()->to('/logout')->with('error', 'This authorization link belongs to a different resident account.');
            }

            return redirect()->to($this->censusPortal('census-update'));
        }

        return redirect()->to('/login')->with('info', 'Please log in to continue updating your census information.');
    }

    public function residentCensusUpdateForm()
    {
        $userId = session()->get('user_id');
        if (! $userId) {
            return redirect()->to('/login')->with('error', 'Please log in to update your census information.');
        }

        $user = (new UserModel())->find($userId);
        $householdNo = $user['household_no'] ?? session()->get('household_no');
        $household = $householdNo ? $this->householdModel->find($householdNo) : null;
        $members = $household ? $this->memberModel->getByHousehold($household['household_no']) : [];
        $access = self::resolveResidentHouseholdAccess($user ?? [], $household, $members);

        if (! $access['can_edit_personal'] || ! $household) {
            return redirect()->to($this->censusPortal('dashboard'))->with('error', 'You are not linked to a valid household record for census updates.');
        }

        $auth = $this->ensureResidentCensusAuth((int) $userId, (string) $household['household_no']);
        if (! $auth) {
            return redirect()->to($this->censusPortal('dashboard'))->with('error', 'Unable to open the census update form right now.');
        }

        $pendingRequests = [];
        if ($this->memberRequestTableExists() && $access['can_edit_household']) {
            $householdNoKey = (string) $household['household_no'];
            $pendingRequests = array_values(array_filter(
                (new HouseholdMemberRequestModel())->listPending(),
                static fn(array $row): bool => (string) ($row['household_no'] ?? '') === $householdNoKey
            ));
        }

        return view('dashboard/resident/census_update', [
            'role' => session()->get('role') === 'council' ? 'council' : 'resident',
            'user' => $user,
            'household' => $household,
            'members' => $members,
            'token' => $auth['token'],
            'auth' => $auth,
            'pageTitle' => $access['can_edit_household'] ? 'Census Update Form' : 'My Census Information',
            'householdAccess' => $access,
            'pendingMemberRequests' => $pendingRequests,
        ]);
    }

    public function saveResidentCensusUpdate()
    {
        $userId = session()->get('user_id');
        if (! $userId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue.');
        }

        $user = (new UserModel())->find($userId);
        $householdNo = $user['household_no'] ?? session()->get('household_no');
        $household = $householdNo ? $this->householdModel->find($householdNo) : null;
        $members = $household ? $this->memberModel->getByHousehold((string) $householdNo) : [];
        $access = self::resolveResidentHouseholdAccess($user ?? [], $household, $members);

        if (! $access['can_edit_personal'] || ! $household) {
            return redirect()->to($this->censusPortal('dashboard'))->with('error', 'You are not authorized to submit a census update for this household.');
        }

        $auth = $this->ensureResidentCensusAuth((int) $userId, (string) $household['household_no']);
        if (! $auth) {
            return redirect()->to($this->censusPortal('census-update'))->with('error', 'Unable to save this census update.');
        }

        $post = $this->request->getPost();
        $confirmOnly = ($post['submit_action'] ?? '') === 'confirm';
        $payload = [
            'household_no' => $household['household_no'],
            'scope' => $access['can_edit_household'] ? 'head' : 'member',
            'confirm_only' => $confirmOnly,
        ];

        if ($access['can_edit_household']) {
            $moveError = $this->invalidMemberMoveDestination($post);
            if ($moveError !== null) {
                return redirect()->to($this->censusPortal('census-update'))->with('error', $moveError);
            }
            $payload['household'] = $this->collectResidentHouseholdPayload($post);
            $payload['members'] = $this->collectResidentMemberUpdates($post, $members);
            $memberRequestCount = $this->queueHeadMemberRequests((string) $household['household_no'], (int) $userId, $post, $members);
        } else {
            $payload['member'] = $this->collectOwnMemberPayload($post, $access['member'] ?? null, $user ?? []);
        }

        (new CensusUpdateAuthorizationModel())->update($auth['id'], [
            'status' => 'submitted',
            'submitted_at' => date('Y-m-d H:i:s'),
            'notes' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        $this->notifyCensusReviewers(
            $confirmOnly ? 'Census information confirmed' : 'Census update submitted',
            trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) . (
                $confirmOnly
                    ? ' confirmed that their census information is correct.'
                    : ' submitted a census update for household #' . $household['household_no'] . '.'
            )
        );

        $message = $confirmOnly
            ? 'Thank you. You confirmed that your information is correct. The barangay will review it.'
            : 'Your census update has been submitted for Captain or Secretary review.';
        if (($memberRequestCount ?? 0) > 0) {
            $message .= ' Member add, move, or remove requests also need Captain or Secretary approval.';
        }

        return redirect()->to($this->censusPortal('census-update'))->with('success', $message);
    }

    public function approveResidentUpdate(int $id)
    {
        if (! can_role('secretary', 'captain', 'admin')) {
            return redirect()->back()->with('error', 'Only the Captain or Secretary can approve census updates.');
        }

        $authModel = new CensusUpdateAuthorizationModel();
        $auth = $authModel->find($id);
        if (! $auth || ! in_array($auth['status'], ['submitted', 'sent'], true)) {
            return redirect()->back()->with('error', 'No submitted census update request was found.');
        }

        $payload = json_decode((string) $auth['notes'], true);
        if (is_array($payload)) {
            $this->applyResidentCensusPayload($payload);
        }

        $authModel->update($id, [
            'status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s'),
            'reviewed_by' => session()->get('user_id'),
            'notes' => json_encode([
                'approved_by' => session()->get('user_id'),
                'approved_at' => date('Y-m-d H:i:s'),
                'changes' => $payload ?? [],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        if (! empty($auth['user_id'])) {
            NotificationModel::push(
                (int) $auth['user_id'],
                'census_update',
                'Census update approved',
                'Your census information was reviewed and applied to the barangay record.',
                $this->censusLinkForUser((int) $auth['user_id'])
            );
        }

        return redirect()->back()->with('success', 'Census update approved and applied to the record.');
    }

    public function rejectResidentUpdate(int $id)
    {
        if (! can_role('secretary', 'captain', 'admin')) {
            return redirect()->back()->with('error', 'Only the Captain or Secretary can reject census updates.');
        }

        $authModel = new CensusUpdateAuthorizationModel();
        $auth = $authModel->find($id);
        if (! $auth || ! in_array($auth['status'], ['submitted', 'sent'], true)) {
            return redirect()->back()->with('error', 'No submitted census update request was found.');
        }

        $reason = trim((string) $this->request->getPost('remarks'));
        $authModel->update($id, [
            'status' => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
            'reviewed_by' => session()->get('user_id'),
            'notes' => json_encode([
                'rejected_by' => session()->get('user_id'),
                'rejected_at' => date('Y-m-d H:i:s'),
                'reason' => $reason,
            ], JSON_UNESCAPED_UNICODE),
        ]);

        if (! empty($auth['user_id'])) {
            NotificationModel::push(
                (int) $auth['user_id'],
                'census_update',
                'Census update not approved',
                $reason !== '' ? $reason : 'Your census update was not approved. Please visit the barangay hall for help.',
                $this->censusLinkForUser((int) $auth['user_id'])
            );
        }

        return redirect()->back()->with('success', 'Census update request rejected.');
    }

    public function approveHousehold(string $householdNo)
    {
        if (! can_role('secretary')) {
            return redirect()->back()->with('error', 'Only the secretary can approve household records.');
        }

        $household = $this->householdModel->find($householdNo);
        if (! $household || ($household['approval_status'] ?? 'approved') !== 'pending') {
            return redirect()->back()->with('error', 'Pending household record not found.');
        }

        $this->householdModel->update($householdNo, ['approval_status' => 'approved']);
        if (! empty($household['recorded_by'])) {
            \App\Models\NotificationModel::push(
                (int) $household['recorded_by'],
                'household_approved',
                'Household Submission Approved',
                'Your household record for ' . trim($household['first_name'] . ' ' . $household['last_name']) . ' has been approved by the secretary.',
                '/council/census'
            );
        }
        return redirect()->back()->with('success', 'Household record approved.');
    }

    public function rejectHousehold(string $householdNo)
    {
        if (! can_role('secretary')) {
            return redirect()->back()->with('error', 'Only the secretary can reject household records.');
        }

        $household = $this->householdModel->find($householdNo);
        if (! $household || ($household['approval_status'] ?? 'approved') !== 'pending') {
            return redirect()->back()->with('error', 'Pending household record not found.');
        }

        $this->householdModel->update($householdNo, ['approval_status' => 'rejected']);
        if (! empty($household['recorded_by'])) {
            \App\Models\NotificationModel::push(
                (int) $household['recorded_by'],
                'household_rejected',
                'Household Submission Rejected',
                'Your household record for ' . trim($household['first_name'] . ' ' . $household['last_name']) . ' was rejected by the secretary.',
                '/council/census'
            );
        }
        return redirect()->back()->with('success', 'Household record rejected.');
    }

    // ── Update existing household head ────────────────────────────────────────

    public function updateHousehold(string $householdNo)
    {
        $post = $this->request->getPost();
        $saveAsDraft = ($post['save_mode'] ?? 'complete') === 'draft';
        $role = session()->get('role');
        $db   = \Config\Database::connect();

        // ── Fetch current record for audit comparison ─────────────────────────
        $current = $this->householdModel->find($householdNo);
        if (! $current) {
            return redirect()->back()->with('error', 'Household not found.');
        }

        if (($age = $this->ageFromDate($post['date_of_birth'] ?? null)) !== null && $age < 18) {
            return redirect()->back()
                ->with('error', 'A minor cannot be registered as the household head. The household head must be at least 18 years old.')
                ->withInput();
        }

        $existingSpouse = $this->memberModel
            ->where('household_no', $householdNo)
            ->where('relationship', 'spouse')
            ->first();
        $existingChildren = $this->memberModel
            ->where('household_no', $householdNo)
            ->where('relationship', 'child')
            ->findAll();
        $ageError = $this->validateFamilyAges(
            $post['date_of_birth'] ?? null,
            $existingSpouse['date_of_birth'] ?? null,
            array_column($existingChildren, 'date_of_birth')
        );
        if ($ageError !== null) {
            return redirect()->back()->with('error', $ageError)->withInput();
        }

        if (trim((string) ($post['last_name'] ?? '')) === '' || trim((string) ($post['first_name'] ?? '')) === '') {
            return redirect()->back()->with('error', 'Last name and first name are required.')->withInput();
        }

        // ── ID upload validation: REQUIRED for 4PS, Senior, Solo Parent, PWD ─
        // Require upload if the flag is set AND (no existing ID AND no new file uploaded)
        $idErrors = [];
        $check4ps = isset($post['is_4ps']);
        $hasExisting4ps = ! empty($current['id_4ps_path']);
        $hasNew4ps = ($f = $this->request->getFile('id_4ps')) && $f->isValid();
        if ($check4ps && ! $hasExisting4ps && ! $hasNew4ps) {
            $idErrors[] = '4Ps Beneficiary ID upload is required (no existing ID on file).';
        }
        $checkSenior = isset($post['is_senior_citizen']);
        $checkSolo = isset($post['is_solo_parent']);
        $hasExistingSolo = ! empty($current['id_solo_parent_path']);
        $hasNewSolo = ($f = $this->request->getFile('id_solo_parent')) && $f->isValid();
        if ($checkSolo && ! $hasExistingSolo && ! $hasNewSolo) {
            $idErrors[] = 'Solo Parent ID upload is required (no existing ID on file).';
        }
        $checkPwd = isset($post['is_pwd']);
        $hasExistingPwd = ! empty($current['id_pwd_path']);
        $hasNewPwd = ($f = $this->request->getFile('id_pwd')) && $f->isValid();
        if ($checkPwd && ! $hasExistingPwd && ! $hasNewPwd) {
            $idErrors[] = 'PWD ID upload is required (no existing ID on file).';
        }
        if (! $saveAsDraft && ! empty($idErrors)) {
            return redirect()->back()
                ->with('error', implode(' ', $idErrors))
                ->withInput();
        }

        // ── Handle optional ownership proof document upload ───────────────────
        $docPath = $current['ownership_document_path'] ?? null;
        $file    = $this->request->getFile('ownership_document');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
            if (in_array(strtolower($file->getExtension()), $allowed)) {
                $fileName = 'ownership_' . $householdNo . '_' . time() . '.' . $file->getExtension();
                $uploadDir = WRITEPATH . 'uploads/ownership/';
                if (! is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                if (! $file->move($uploadDir, $fileName)) {
                    return redirect()->back()
                        ->with('error', 'The ownership document could not be uploaded.')
                        ->withInput();
                }
                $docPath = 'uploads/ownership/' . $fileName;
                (new HouseholdUploadStorage())->store(WRITEPATH . $docPath, $docPath);
                // Remove old file if it exists
                if (! empty($current['ownership_document_path'])) {
                    foreach ([FCPATH . $current['ownership_document_path'], WRITEPATH . $current['ownership_document_path']] as $oldFile) {
                        if (is_file($oldFile)) {
                            @unlink($oldFile);
                        }
                    }
                }
            }
        }

        if (! $saveAsDraft) {
            $verifyError = $this->unverifiedDocumentMessage($post);
            if ($verifyError !== null) {
                return redirect()->back()->with('error', $verifyError)->withInput();
            }
        }

        $hasNew4psUpload = $this->uploadIsPresent('id_4ps');
        $hasNewSeniorUpload = $this->uploadIsPresent('id_senior');
        $hasNewSoloUpload = $this->uploadIsPresent('id_solo_parent');
        $hasNewPwdUpload = $this->uploadIsPresent('id_pwd');

        // ── Process head ID uploads ───────────────────────────────────────────
        $id4psPath   = $this->_handleIdUpload('id_4ps', '4ps', $householdNo, $current['id_4ps_path'] ?? null);
        $idSeniorPath = $this->_handleIdUpload('id_senior', 'senior', $householdNo, $current['id_senior_path'] ?? null);
        $idSoloPath   = $this->_handleIdUpload('id_solo_parent', 'solo', $householdNo, $current['id_solo_parent_path'] ?? null);
        $idPwdPath    = $this->_handleIdUpload('id_pwd', 'pwd', $householdNo, $current['id_pwd_path'] ?? null);

        // If the flag is being turned OFF, also clear the ID path (and remove file)
        if (! $check4ps && ! empty($current['id_4ps_path']) && is_file(FCPATH . $current['id_4ps_path'])) {
            @unlink(FCPATH . $current['id_4ps_path']);
            $id4psPath = null;
        }
        if (! $checkSenior && ! empty($current['id_senior_path']) && is_file(FCPATH . $current['id_senior_path'])) {
            @unlink(FCPATH . $current['id_senior_path']);
            $idSeniorPath = null;
        }
        if (! $checkSolo && ! empty($current['id_solo_parent_path']) && is_file(FCPATH . $current['id_solo_parent_path'])) {
            @unlink(FCPATH . $current['id_solo_parent_path']);
            $idSoloPath = null;
        }
        if (! $checkPwd && ! empty($current['id_pwd_path']) && is_file(FCPATH . $current['id_pwd_path'])) {
            @unlink(FCPATH . $current['id_pwd_path']);
            $idPwdPath = null;
        }

        $headSupportPath = $this->_handleIdUpload(
            'head_supporting_doc',
            'support',
            $householdNo,
            $current['supporting_doc_path'] ?? null
        );

        if (! $saveAsDraft) {
            $headHasDoc = ! empty($headSupportPath)
                || ($check4ps && ! empty($id4psPath))
                || ($checkSenior && ! empty($idSeniorPath))
                || ($checkSolo && ! empty($idSoloPath))
                || ($checkPwd && ! empty($idPwdPath));
            if (! $headHasDoc) {
                return redirect()->back()
                    ->with('error', 'The household head needs one ID or birth certificate. Use Save as Draft if the document is not available yet.')
                    ->withInput();
            }
        }

        $newOwnership    = $post['house_ownership']  ?? 'Owned';
        $newNumFamilies  = max(1, (int) ($post['num_families'] ?? 1));
        $oldOwnership    = $current['house_ownership']  ?? null;
        $oldNumFamilies  = (int) ($current['num_families'] ?? 1);
        $ownershipChanged = ($newOwnership !== $oldOwnership);
        $postedResidencyYears = max(0, (int) ($post['years_of_residency'] ?? 0));
        $existingStartYear = (int) ($current['residency_start_year'] ?? 0);
        $computedResidencyYears = current_years_of_residency($current);
        if ($existingStartYear > 0 && $postedResidencyYears === $computedResidencyYears) {
            $residencyStartYear = $existingStartYear;
        } else {
            $residencyStartYear = (int) date('Y') - $postedResidencyYears;
        }
        $enteredResidencyYears = max(0, (int) date('Y') - $residencyStartYear);
        $editSharedPlan = null;
        $linkedPosted = trim((string) ($post['linked_household_no'] ?? ''));
        if ($newOwnership === 'Shared' && $linkedPosted !== '' && $linkedPosted !== (string) ($current['linked_household_no'] ?? '')) {
            $editSharedPlan = $this->planSharedHousehold($linkedPosted, (string) $householdNo);
            if (is_string($editSharedPlan)) {
                return redirect()->back()->with('error', $editSharedPlan)->withInput();
            }
        }

        $data = [
            'household_no'            => $householdNo,
            'zone'                    => $post['zone']                   ?? null,
            'last_name'               => strtoupper($post['last_name']   ?? ''),
            'first_name'              => strtoupper($post['first_name']  ?? ''),
            'middle_name'             => strtoupper($post['middle_name'] ?? ''),
            'suffix'                  => $post['suffix']                 ?? null,
            'date_of_birth'           => $post['date_of_birth']          ?? null,
            'place_of_birth'          => strtoupper($post['place_of_birth'] ?? ''),
            'gender'                  => $post['gender']                 ?? 'Male',
            'civil_status'            => $post['civil_status']           ?? 'Single',
            'nationality'             => strtoupper($post['nationality'] ?? 'FILIPINO'),
            'religion'                => strtoupper($post['religion']    ?? ''),
            'occupation'              => strtoupper($post['occupation']  ?? ''),
            'monthly_income'          => $post['monthly_income']         ?? 0,
            'contact_number'          => $post['contact_number']         ?? null,
            'educational_attainment'  => $post['educational_attainment'] ?? null,
            'philhealth_no'           => $post['philhealth_no']          ?? null,
            'address'                 => strtoupper($post['address']     ?? ''),
            'years_of_residency'      => $enteredResidencyYears,
            'residency_start_year'    => $residencyStartYear,
            'house_ownership'         => $newOwnership,
            'num_families'            => $newNumFamilies,
            'ownership_document_path' => $docPath,
            'ownership_notes'         => $post['ownership_notes']        ?? null,
            'shared_address_group'    => $editSharedPlan['shared_address_group'] ?? ($newOwnership === 'Shared'
                ? (trim($post['shared_address_group'] ?? $current['shared_address_group'] ?? '') ?: ('SHR-' . random_int(10000, 99999)))
                : null),
            'family_number'           => $editSharedPlan['family_number'] ?? ($newOwnership === 'Shared'
                ? max(1, (int) ($post['family_number'] ?? $current['family_number'] ?? 1))
                : 1),
            'linked_household_no'     => $newOwnership === 'Shared'
                ? ($editSharedPlan['linked_household_no'] ?? ($current['linked_household_no'] ?? ($linkedPosted !== '' ? $linkedPosted : null)))
                : null,
            'is_4ps'                  => $check4ps ? 1 : 0,
            'id_4ps_path'             => $check4ps ? $id4psPath : null,
            'id_4ps_verified'         => $check4ps
                ? ($hasNew4psUpload ? 1 : (int) ($current['id_4ps_verified'] ?? 0))
                : 0,
            'is_pwd'                  => $checkPwd ? 1 : 0,
            'pwd_type'                => ($checkPwd && ! empty($post['pwd_type'])) ? $post['pwd_type'] : null,
            'id_pwd_path'             => $checkPwd ? $idPwdPath : null,
            'id_pwd_verified'         => $checkPwd
                ? ($hasNewPwdUpload ? 1 : (int) ($current['id_pwd_verified'] ?? 0))
                : 0,
            'is_senior_citizen'       => $checkSenior ? 1 : 0,
            'id_senior_path'          => $checkSenior ? $idSeniorPath : null,
            'id_senior_verified'      => $checkSenior
                ? ($hasNewSeniorUpload ? 1 : (int) ($current['id_senior_verified'] ?? 0))
                : 0,
            'is_solo_parent'          => $checkSolo ? 1 : 0,
            'id_solo_parent_path'     => $checkSolo ? $idSoloPath : null,
            'id_solo_parent_verified' => $checkSolo
                ? ($hasNewSoloUpload ? 1 : (int) ($current['id_solo_parent_verified'] ?? 0))
                : 0,
            'supporting_doc_path'     => $headSupportPath,
            'record_status'           => $saveAsDraft ? 'draft' : 'complete',
            'is_indigenous'           => isset($post['is_indigenous'])   ? 1 : 0,
            'registered_voter'        => ($post['registered_voter'] ?? '0') === '1' ? 1 : 0,
            'water_source_level'      => array_key_exists('water_source', $post) ? ($post['water_source'] !== '' ? $post['water_source'] : null) : ($current['water_source_level'] ?? null),
            'water_safety_managed'    => array_key_exists('water_managed', $post) ? ($post['water_managed'] === 'yes' ? 1 : 0) : ($current['water_safety_managed'] ?? null),
            'sanitation_basic'        => array_key_exists('sanitation_basic', $post) ? ($post['sanitation_basic'] !== '' ? $post['sanitation_basic'] : null) : ($current['sanitation_basic'] ?? null),
            'sanitation_managed'      => array_key_exists('sanitation_managed', $post) ? ($post['sanitation_managed'] !== '' ? $post['sanitation_managed'] : null) : ($current['sanitation_managed'] ?? null),
        ];

        try {
            if (! $this->householdModel->update($householdNo, $data)) {
                $errors = implode(' ', $this->householdModel->errors());
                return redirect()->back()
                    ->with('error', $errors !== '' ? 'Household was not saved: ' . $errors : 'Household was not saved. Please try again.')
                    ->withInput();
            }
        } catch (\Throwable $e) {
            log_message('error', 'Household update failed for ' . $householdNo . ': ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Household was not saved because of a database error. Please try again.')
                ->withInput();
        }

        if ($editSharedPlan) {
            $this->attachSharedHousehold($editSharedPlan);
        }

        // ── Log ownership change to audit table ───────────────────────────────
        $userId = (int) session()->get('user_id');
        $notes  = trim($post['ownership_notes'] ?? '');

        if ($ownershipChanged) {
            // Secretary approves immediately; captain change requires secretary approval
            $approvesNow = in_array($role, ['secretary', 'admin'], true);
            $status = $approvesNow ? 'approved' : 'pending';
            $this->recordHouseholdOwnershipChange([
                'household_no'    => $householdNo,
                'changed_by'      => $userId,
                'old_ownership'   => $oldOwnership,
                'new_ownership'   => $newOwnership,
                'old_num_families' => $oldNumFamilies,
                'new_num_families' => $newNumFamilies,
                'document_path'   => $docPath,
                'notes'           => $notes ?: null,
                'status'          => $status,
                'reviewed_by'     => $approvesNow ? $userId : null,
                'reviewed_at'     => $approvesNow ? date('Y-m-d H:i:s') : null,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);

            if ($status === 'pending') {
                return redirect()->to('/' . $role . '/household/' . $householdNo)
                    ->with('success', 'Household record updated. The ownership change from <strong>' . esc($oldOwnership) . '</strong> to <strong>' . esc($newOwnership) . '</strong> is pending secretary approval.');
            }
        } elseif ($docPath !== ($current['ownership_document_path'] ?? null) || $notes !== ($current['ownership_notes'] ?? '')) {
            // Document or notes changed but ownership itself did not — log as auto_approved
            $this->recordHouseholdOwnershipChange([
                'household_no'    => $householdNo,
                'changed_by'      => $userId,
                'old_ownership'   => $oldOwnership,
                'new_ownership'   => $newOwnership,
                'old_num_families' => $oldNumFamilies,
                'new_num_families' => $newNumFamilies,
                'document_path'   => $docPath,
                'notes'           => $notes ?: null,
                'status'          => 'auto_approved',
                'reviewed_by'     => $userId,
                'reviewed_at'     => date('Y-m-d H:i:s'),
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('/' . $role . '/household/' . $householdNo)
            ->with('success', 'Household record updated successfully.');
    }

    // ── Add a single member ───────────────────────────────────────────────────

    public function addMember(string $householdNo)
    {
        $post = $this->request->getPost();
        $role = session()->get('role');

        $isPwd = ($post['is_pwd'] ?? '0') === '1' ? 1 : 0;
        $isSenior = ($post['is_senior_citizen'] ?? '0') === '1' ? 1 : 0;
        $relationship = strtolower(trim((string) ($post['relationship'] ?? 'other_relative')));

        if (
            $relationship === 'spouse' && $this->memberModel
            ->where('household_no', $householdNo)
            ->where('relationship', 'spouse')
            ->countAllResults() > 0
        ) {
            return redirect()->back()
                ->with('error', 'This household already has a spouse. Only one spouse may be recorded.')
                ->withInput();
        }

        // ── PWD ID upload validation ─────────────────────────────────────
        if ($isPwd) {
            $f = $this->request->getFile('id_pwd');
            if (! $f || ! $f->isValid()) {
                return redirect()->back()
                    ->with('error', 'PWD ID upload is required when marking a member as PWD.')
                    ->withInput();
            }
        }

        try {
            if ($this->personAlreadyRecorded(
                (string) ($post['last_name'] ?? ''),
                (string) ($post['first_name'] ?? ''),
                $post['date_of_birth'] ?? null
            )) {
                return redirect()->back()
                    ->with('error', 'This member is already recorded in another household. Duplicate census information was not saved.')
                    ->withInput();
            }

            $memberId = $this->memberModel->insert([
                'household_no'           => $householdNo,
                'relationship'           => $relationship,
                'last_name'              => strtoupper($post['last_name']   ?? ''),
                'first_name'             => strtoupper($post['first_name']  ?? ''),
                'middle_name'            => strtoupper($post['middle_name'] ?? ''),
                'suffix'                 => $post['suffix']                 ?? null,
                'date_of_birth'          => $post['date_of_birth']          ?? null,
                'gender'                 => $post['gender']                 ?? null,
                'occupation'             => strtoupper($post['occupation']  ?? ''),
                'monthly_income'         => $post['monthly_income']         ?? 0,
                'philhealth_no'          => $post['philhealth_no']          ?? null,
                'educational_attainment' => $post['educational_attainment'] ?? null,
                'grade_level'            => $post['grade_level']            ?? null,
                'is_pwd'                 => $isPwd,
                'pwd_type'               => $isPwd ? ($post['pwd_type'] ?? null) : null,
                'id_pwd_path'            => null,
                'id_senior_path'         => null,
            ], true);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'uq_members_philhealth') || str_contains($msg, 'philhealth_no')) {
                return redirect()->back()->with('error', 'That PhilHealth number is already registered to another member.')->withInput();
            }
            if (str_contains($msg, 'uq_members_person')) {
                return redirect()->back()->with('error', 'A member with the same name and date of birth already exists in this household.')->withInput();
            }
            throw $e;
        }

        // ── Process PWD ID upload for the new member ────────────────────
        if ($isPwd && $memberId) {
            $pwdPath = $this->_handleMemberIdUpload('id_pwd', 'pwd', (int) $memberId);
            $this->memberModel->update($memberId, ['id_pwd_path' => $pwdPath]);
        }
        if ($isSenior && $memberId) {
            $seniorPath = $this->_handleMemberIdUpload('id_senior', 'senior', (int) $memberId);
            $this->memberModel->update($memberId, ['id_senior_path' => $seniorPath]);
        }

        return redirect()->to('/' . $role . '/household/' . $householdNo)
            ->with('success', 'Member added successfully.');
    }

    // ── Add spouse + children + others for existing household (family form) ──

    public function addFamilyMembers(string $householdNo)
    {
        $post = $this->request->getPost();
        $role = session()->get('role');

        $hasExistingSpouse = $this->memberModel
            ->where('household_no', $householdNo)
            ->where('relationship', 'spouse')
            ->countAllResults() > 0;

        if ($hasExistingSpouse && trim((string) ($post['spouse_last_name'] ?? '')) !== '') {
            return redirect()->back()
                ->with('error', 'This household already has a spouse. Add a child or another household member instead.')
                ->withInput();
        }

        $members = [];

        // Spouse
        if (! empty(trim($post['spouse_last_name'] ?? ''))) {
            $spousePwd = ($post['spouse_pwd'] ?? '0') === '1' ? 1 : 0;
            $members[] = [
                'relationship'           => 'spouse',
                'last_name'              => strtoupper(trim($post['spouse_last_name'])),
                'first_name'             => strtoupper(trim($post['spouse_first_name']             ?? '')),
                'middle_name'            => strtoupper(trim($post['spouse_middle_name']            ?? '')) ?: null,
                'suffix'                 => $post['spouse_suffix']                 ?? null,
                'date_of_birth'          => $post['spouse_dob']                    ?? null,
                'gender'                 => $post['spouse_gender']                 ?? null,
                'occupation'             => strtoupper(trim($post['spouse_occupation']             ?? '')),
                'monthly_income'         => $post['spouse_income']                 ?? 0,
                'philhealth_no'          => $post['spouse_philhealth']             ?? null,
                'educational_attainment' => $post['spouse_educational_attainment'] ?? null,
                'grade_level'            => null,
                'is_pwd'                 => $spousePwd,
                'pwd_type'               => $spousePwd ? ($post['spouse_pwd_type'] ?? null) : null,
            ];
        }

        // Children
        if (! empty($post['child_last_name'])) {
            foreach ($post['child_last_name'] as $i => $lastName) {
                if (empty(trim($lastName))) continue;
                $childPwd = ($post['child_pwd'][$i] ?? '0') === '1' ? 1 : 0;
                $members[] = [
                    'relationship'           => 'child',
                    'last_name'              => strtoupper(trim($lastName)),
                    'first_name'             => strtoupper(trim($post['child_first_name'][$i]  ?? '')),
                    'middle_name'            => strtoupper(trim($post['child_middle_name'][$i] ?? '')) ?: null,
                    'suffix'                 => $post['child_suffix'][$i]      ?? null,
                    'date_of_birth'          => $post['child_dob'][$i]         ?? null,
                    'gender'                 => $post['child_gender'][$i]      ?? null,
                    'marital_status'        => $post['child_marital_status'][$i] ?? 'Single',
                    'occupation'             => strtoupper(trim($post['child_occupation'][$i]  ?? '')),
                    'work_detail'            => strtoupper(trim((string) ($post['child_occupation'][$i] ?? ''))) === 'WORKING STUDENT'
                        ? (strtoupper(trim((string) ($post['child_work'][$i] ?? ''))) ?: null)
                        : null,
                    'monthly_income'         => $post['child_income'][$i]      ?? 0,
                    'philhealth_no'          => $post['child_philhealth'][$i]  ?? null,
                    'educational_attainment' => null,
                    'grade_level'            => $post['child_grade'][$i]       ?? null,
                    'is_pwd'                 => $childPwd,
                    'pwd_type'               => $childPwd ? ($post['child_pwd_type'][$i] ?? null) : null,
                ];

                $childSpouseName = trim((string) ($post['child_spouse_name'][$i] ?? ''));
                if (($post['child_marital_status'][$i] ?? 'Single') === 'Married' && $childSpouseName !== '') {
                    $members[] = [
                        'relationship' => 'spouse_of_child',
                        'last_name' => strtoupper($childSpouseName),
                        'first_name' => '',
                        'middle_name' => null,
                        'suffix' => null,
                        'date_of_birth' => null,
                        'gender' => null,
                        'marital_status' => 'Married',
                        'occupation' => null,
                        'monthly_income' => 0,
                        'philhealth_no' => null,
                        'educational_attainment' => null,
                        'grade_level' => null,
                        'is_pwd' => 0,
                        'pwd_type' => null,
                    ];
                }
                foreach (preg_split('/\r\n|\r|\n/', (string) ($post['child_children_names'][$i] ?? '')) as $grandchildName) {
                    $grandchildName = trim($grandchildName);
                    if ($grandchildName === '') continue;
                    $nameParts = preg_split('/\s+/', $grandchildName);
                    $grandchildLastName = array_pop($nameParts);
                    $members[] = [
                        'relationship' => 'grandchild',
                        'last_name' => strtoupper($grandchildLastName),
                        'first_name' => strtoupper(implode(' ', $nameParts) ?: $grandchildLastName),
                        'middle_name' => null,
                        'suffix' => null,
                        'date_of_birth' => null,
                        'gender' => null,
                        'marital_status' => 'Single',
                        'occupation' => null,
                        'monthly_income' => 0,
                        'philhealth_no' => null,
                        'educational_attainment' => null,
                        'grade_level' => null,
                        'is_pwd' => 0,
                        'pwd_type' => null,
                    ];
                }
            }
        }

        // Other members
        if (! empty($post['other_last_name'])) {
            foreach ($post['other_last_name'] as $i => $lastName) {
                if (empty(trim($lastName))) continue;
                $otherPwd = ($post['other_pwd'][$i] ?? '0') === '1' ? 1 : 0;
                $members[] = [
                    'relationship'           => strtolower($post['other_relationship'][$i] ?? 'other_relative'),
                    'last_name'              => strtoupper(trim($lastName)),
                    'first_name'             => strtoupper(trim($post['other_first_name'][$i]  ?? '')),
                    'middle_name'            => strtoupper(trim($post['other_middle_name'][$i] ?? '')) ?: null,
                    'suffix'                 => $post['other_suffix'][$i]      ?? null,
                    'date_of_birth'          => $post['other_dob'][$i]         ?? null,
                    'gender'                 => $post['other_gender'][$i]      ?? null,
                    'occupation'             => null,
                    'monthly_income'         => 0,
                    'philhealth_no'          => null,
                    'educational_attainment' => null,
                    'grade_level'            => null,
                    'is_pwd'                 => $otherPwd,
                    'pwd_type'               => $otherPwd ? ($post['other_pwd_type'][$i] ?? null) : null,
                ];
            }
        }

        $minorCivilStatusError = $this->validateMinorCivilStatuses($members);
        if ($minorCivilStatusError !== null) {
            return redirect()->back()->with('error', $minorCivilStatusError)->withInput();
        }

        if (empty($members)) {
            return redirect()->to('/' . $role . '/household/' . $householdNo)
                ->with('error', 'No members were provided. Please fill in at least one name.');
        }

        $head = $this->householdModel->find($householdNo);
        $existingSpouse = $this->memberModel
            ->where('household_no', $householdNo)
            ->where('relationship', 'spouse')
            ->first();
        $spouseDob = $post['spouse_dob'] ?? ($existingSpouse['date_of_birth'] ?? null);
        $childDobs = [];
        foreach ($members as $member) {
            if (($member['relationship'] ?? '') === 'child') {
                $childDobs[] = $member['date_of_birth'] ?? null;
            }
        }
        $ageError = $this->validateFamilyAges($head['date_of_birth'] ?? null, $spouseDob, $childDobs);
        if ($ageError !== null) {
            return redirect()->back()->with('error', $ageError)->withInput();
        }

        $seenMembers = [];
        foreach ($members as $member) {
            $memberDate = trim((string) ($member['date_of_birth'] ?? ''));
            $memberKey = strtoupper(trim((string) ($member['last_name'] ?? ''))) . '|'
                . strtoupper(trim((string) ($member['first_name'] ?? ''))) . '|'
                . $memberDate;
            if ($memberDate !== '' && isset($seenMembers[$memberKey])) {
                return redirect()->back()
                    ->with('error', 'The same member information was entered more than once. Duplicate information was not saved.')
                    ->withInput();
            }
            if ($memberDate !== '') {
                $seenMembers[$memberKey] = true;
            }
            if ($this->personAlreadyRecorded(
                (string) ($member['last_name'] ?? ''),
                (string) ($member['first_name'] ?? ''),
                $member['date_of_birth'] ?? null
            )) {
                return redirect()->back()
                    ->with('error', 'One or more members are already recorded in the census. Duplicate information was not saved.')
                    ->withInput();
            }
        }

        $added = 0;
        foreach ($members as $m) {
            $m['household_no'] = $householdNo;
            try {
                $this->memberModel->insert($m);
                $added++;
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                // Skip duplicates silently — existing members won't be overwritten
                log_message('warning', 'addFamilyMembers skip duplicate: ' . $e->getMessage());
            }
        }

        return redirect()->to('/' . $role . '/household/' . $householdNo)
            ->with('success', $added . ' member' . ($added !== 1 ? 's' : '') . ' added successfully.');
    }

    // ── Update a single member ────────────────────────────────────────────────

    public function updateMember(int $memberId)
    {
        $post        = $this->request->getPost();
        $role        = session()->get('role');
        $householdNo = $post['household_no'] ?? '';

        $current = $this->memberModel->find($memberId);
        if (! $current) {
            return redirect()->to('/' . $role . '/household/' . $householdNo)
                ->with('error', 'Member not found.');
        }

        $relationship = strtolower(trim((string) ($post['relationship'] ?? 'other_relative')));
        if ($this->personAlreadyRecorded(
            (string) ($post['last_name'] ?? ''),
            (string) ($post['first_name'] ?? ''),
            $post['date_of_birth'] ?? null,
            (string) ($current['household_no'] ?? '')
        )) {
            return redirect()->back()
                ->with('error', 'This person is already recorded elsewhere in the census. Duplicate information was not saved.')
                ->withInput();
        }
        if (
            $relationship === 'spouse' && strtolower((string) ($current['relationship'] ?? '')) !== 'spouse'
            && $this->memberModel
            ->where('household_no', $current['household_no'])
            ->where('relationship', 'spouse')
            ->where('id !=', $memberId)
            ->countAllResults() > 0
        ) {
            return redirect()->back()
                ->with('error', 'This household already has a spouse. Only one spouse may be recorded.')
                ->withInput();
        }

        $isPwd = ($post['is_pwd'] ?? '0') === '1' ? 1 : 0;
        $isSenior = ($post['is_senior_citizen'] ?? '0') === '1' ? 1 : 0;
        $hasExistingPwdId = ! empty($current['id_pwd_path']);
        $hasNewPwdFile = ($f = $this->request->getFile('id_pwd')) && $f->isValid();

        // ── PWD ID upload validation ─────────────────────────────────────
        if ($isPwd && ! $hasExistingPwdId && ! $hasNewPwdFile) {
            return redirect()->back()
                ->with('error', 'PWD ID upload is required (no existing ID on file for this member).')
                ->withInput();
        }

        // ── Process PWD ID upload ────────────────────────────────────────
        $idPwdPath = $this->_handleMemberIdUpload('id_pwd', 'pwd', $memberId, $current['id_pwd_path'] ?? null);
        $idSeniorPath = $this->_handleMemberIdUpload('id_senior', 'senior', $memberId, $current['id_senior_path'] ?? null);

        // If flag is turned OFF, clear the path and remove file
        if (! $isPwd && ! empty($current['id_pwd_path']) && is_file(FCPATH . $current['id_pwd_path'])) {
            @unlink(FCPATH . $current['id_pwd_path']);
            $idPwdPath = null;
        }
        if (! $isSenior && ! empty($current['id_senior_path']) && is_file(FCPATH . $current['id_senior_path'])) {
            @unlink(FCPATH . $current['id_senior_path']);
            $idSeniorPath = null;
        }

        $this->memberModel->update($memberId, [
            'relationship'           => $relationship,
            'last_name'              => strtoupper($post['last_name']   ?? ''),
            'first_name'             => strtoupper($post['first_name']  ?? ''),
            'middle_name'            => strtoupper($post['middle_name'] ?? ''),
            'suffix'                 => $post['suffix']                 ?? null,
            'date_of_birth'          => $post['date_of_birth']          ?? null,
            'gender'                 => $post['gender']                 ?? null,
            'occupation'             => strtoupper($post['occupation']  ?? ''),
            'work_detail'            => strtoupper(trim((string) ($post['occupation'] ?? ''))) === 'WORKING STUDENT'
                ? (array_key_exists('work_detail', $post)
                    ? (strtoupper(trim((string) $post['work_detail'])) ?: null)
                    : ($current['work_detail'] ?? null))
                : null,
            'monthly_income'         => $post['monthly_income']         ?? 0,
            'philhealth_no'          => $post['philhealth_no']          ?? null,
            'educational_attainment' => $post['educational_attainment'] ?? null,
            'grade_level'            => $post['grade_level']            ?? null,
            'is_pwd'                 => $isPwd,
            'pwd_type'               => $isPwd ? ($post['pwd_type'] ?? null) : null,
            'id_pwd_path'            => $isPwd ? $idPwdPath : null,
            'id_senior_path'         => $isSenior ? $idSeniorPath : null,
        ]);

        return redirect()->to('/' . $role . '/household/' . $householdNo)
            ->with('success', 'Member updated successfully.');
    }

    // ── Delete a single member ────────────────────────────────────────────────

    public function deleteMember(int $memberId)
    {
        $role        = session()->get('role');
        $householdNo = $this->request->getPost('household_no') ?? '';

        $this->memberModel->delete($memberId);

        return redirect()->to('/' . $role . '/household/' . $householdNo)
            ->with('success', 'Member removed.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HOUSEHOLD SPLIT WORKFLOW
    // Flowchart: Member Decides to Move Out → Open Original Household Record
    //   → Select "Separate Household" or "Transfer Member"
    //   → System Generates New Household Number
    //   → Update New Household Record + Modify Original Household
    //   → Update Personal & Census Data
    //   → Secretary Approves Changes → Update Confirmed / Request Rejected
    //   → Update Logged in System Audit Trail
    // ─────────────────────────────────────────────────────────────────────────

    // ── Step 1: File a separation request for a member ────────────────────────
    // POST /{role}/census/member/separate/{memberId}
    public function requestSeparation(int $memberId)
    {
        $role    = session()->get('role');
        $post    = $this->request->getPost();

        $member = $this->memberModel->find($memberId);
        if (! $member) {
            return redirect()->back()->with('error', 'Member not found.');
        }

        $householdNo = $member['household_no'];

        // Validate required fields
        $separationType        = $post['separation_type']  ?? 'Separate Household';
        $changeReason          = trim($post['change_reason']  ?? '');
        $newZone               = trim($post['new_zone']       ?? '');
        $newAddress            = trim($post['new_address']    ?? '');
        $destinationHouseholdNo = trim((string) ($post['destination_household_no'] ?? ''));
        $newCivilStatus        = $post['new_civil_status']    ?? null;
        $proofVerified         = isset($post['proof_verified']) ? 1 : 0;
        $isAddFamily           = ($post['add_family'] ?? '0') === '1';

        if ($isAddFamily && (($member['relationship'] ?? '') !== 'child' || ($member['marital_status'] ?? 'Single') !== 'Married')) {
            return redirect()->to('/' . $role . '/household/' . $householdNo)
                ->with('error', 'Only a child marked as Married can add a family.');
        }

        if ($isAddFamily) {
            $separationType = 'Separate Household';
            $changeReason = 'Married';
        }

        if (empty($changeReason)) {
            return redirect()->back()
                ->with('error', 'Change reason is required for a separation request.')
                ->withInput();
        }

        if ($separationType === 'Transfer Member') {
            $transferValidation = self::validateTransferDestinationHousehold($householdNo, $destinationHouseholdNo);
            if (! $transferValidation['valid']) {
                return redirect()->to('/' . $role . '/household/' . $householdNo)
                    ->with('error', $transferValidation['message']);
            }

            $destinationHousehold = $this->householdModel->where('household_no', $destinationHouseholdNo)->first();
            if (! $destinationHousehold) {
                return redirect()->to('/' . $role . '/household/' . $householdNo)
                    ->with('error', 'Selected destination household does not exist.');
            }
        }

        // Check no pending request already exists for this member
        $db = \Config\Database::connect();
        $existing = $db->table('household_separation_requests')
            ->where('member_id', $memberId)
            ->where('status', 'pending')
            ->countAllResults();

        if ($existing > 0) {
            return redirect()->to('/' . $role . '/household/' . $householdNo)
                ->with('error', 'A pending separation request already exists for this member.');
        }

        $requestId = $db->table('household_separation_requests')->insert([
            'member_id'             => $memberId,
            'original_household_no' => $householdNo,
            'separation_type'       => $separationType,
            'status'                => 'pending',
            'requested_by'          => session()->get('user_id'),
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        ], true);

        if (! $requestId) {
            return redirect()->to('/' . $role . '/household/' . $householdNo)
                ->with('error', 'Unable to create the separation request. Please try again.');
        }

        $db->table('household_separation_request_details')->insert([
            'request_id'        => $requestId,
            'change_reason'    => $changeReason ?: null,
            'new_household_no' => ($separationType === 'Transfer Member' ? $destinationHouseholdNo : null),
            'new_zone'         => $newZone ?: null,
            'new_address'      => $newAddress ?: null,
            'new_civil_status' => $newCivilStatus ?: null,
            'proof_verified'   => $proofVerified,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $memberName = trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''));
        $isTransfer = $separationType === 'Transfer Member';
        \App\Models\NotificationModel::pushToRole(
            'captain',
            'household_move',
            $isTransfer ? 'Household transfer submitted' : 'Household separation submitted',
            ($memberName !== '' ? $memberName : 'A household member')
                . ' from household #' . $householdNo
                . ($isTransfer
                    ? ' was requested to transfer to household #' . $destinationHouseholdNo . '.'
                    : ' was requested to form a new household.'),
            '/captain/household/' . $householdNo,
            (int) session()->get('user_id') ?: null
        );

        return redirect()->to('/' . $role . '/household/' . $householdNo)
            ->with('success', 'Separation request filed for <strong>'
                . esc($member['first_name'] . ' ' . $member['last_name'])
                . '</strong>. Awaiting secretary approval.');
    }

    // ── Step 2a: Secretary approves the separation request ────────────────────
    // POST /secretary/census/separation/approve/{requestId}
    public function approveSeparation(int $requestId)
    {
        $db  = \Config\Database::connect();
        $req = $db->table('household_separation_requests r')
            ->select('r.*, d.change_reason, d.new_household_no, d.new_zone, d.new_address, d.new_civil_status, d.proof_verified, d.audit_note, d.rejection_reason')
            ->join('household_separation_request_details d', 'd.request_id = r.id', 'left')
            ->where('r.id', $requestId)
            ->get()
            ->getRowArray();

        if (! $req || $req['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Separation request not found or already processed.');
        }

        $member = $this->memberModel->find($req['member_id']);
        if (! $member) {
            return redirect()->to('/secretary/census')
                ->with('error', 'Member no longer exists in the census.');
        }

        $memberAge = $this->ageFromDate($member['date_of_birth'] ?? null);
        if ($memberAge !== null && $memberAge < 18) {
            return redirect()->back()
                ->with('error', 'A minor cannot become the household head. The member must be at least 18 years old.');
        }

        $memberName  = trim($member['first_name'] . ' ' . $member['last_name']);

        if ($req['separation_type'] === 'Transfer Member') {
            $destinationHouseholdNo = trim((string) ($req['new_household_no'] ?? ''));
            $transferValidation = self::validateTransferDestinationHousehold($req['original_household_no'], $destinationHouseholdNo);

            if (! $transferValidation['valid']) {
                return redirect()->back()->with('error', $transferValidation['message']);
            }

            $destinationHousehold = $this->householdModel->where('household_no', $destinationHouseholdNo)->first();
            if (! $destinationHousehold) {
                return redirect()->back()->with('error', 'Destination household no longer exists.');
            }

            $this->memberModel->update($req['member_id'], [
                'household_no' => $destinationHouseholdNo,
            ]);

            $auditNote = "Record Updated: {$memberName} transferred from Household #{$req['original_household_no']} "
                . "to Household #{$destinationHouseholdNo}. "
                . "Reason: {$req['change_reason']}. "
                . "Type: {$req['separation_type']}.";

            $db->table('household_separation_request_details')->where('request_id', $requestId)->update([
                'new_household_no' => $destinationHouseholdNo,
                'audit_note'      => $auditNote,
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);

            $db->table('household_separation_requests')->where('id', $requestId)->update([
                'status'       => 'approved',
                'processed_by' => session()->get('user_id'),
                'processed_at' => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

            \App\Models\NotificationModel::pushToRole(
                'captain',
                'household_move',
                'Household transfer recorded',
                $auditNote,
                '/captain/household/' . $destinationHouseholdNo,
                (int) session()->get('user_id') ?: null
            );

            return redirect()->to('/secretary/census')
                ->with('success', 'Transfer approved. ' . esc($memberName)
                    . ' is now linked to Household #' . $destinationHouseholdNo . '. '
                    . 'Record: From Household #' . $req['original_household_no']
                    . ' → Household #' . $destinationHouseholdNo . '.');
        }

        // ── Generate new household number ─────────────────────────────────────
        $newHouseholdNo = $this->householdModel->generateHouseholdNo();

        // ── Create the new household from the member's personal data ──────────
        $originalHead = $this->householdModel->find($req['original_household_no']);

        $this->householdModel->insert([
            'household_no'           => $newHouseholdNo,
            'zone'                   => $req['new_zone']        ?? ($originalHead['zone']    ?? null),
            'last_name'              => $member['last_name'],
            'first_name'             => $member['first_name'],
            'middle_name'            => $member['middle_name']  ?? null,
            'suffix'                 => $member['suffix']       ?? null,
            'date_of_birth'          => $member['date_of_birth']        ?? null,
            'gender'                 => $member['gender']               ?? 'Male',
            'civil_status'           => $req['new_civil_status'] ?? 'Single',
            'nationality'            => 'Filipino',
            'occupation'             => $member['occupation']           ?? null,
            'monthly_income'         => $member['monthly_income']       ?? 0,
            'educational_attainment' => $member['educational_attainment'] ?? null,
            'philhealth_no'          => $member['philhealth_no']        ?? null,
            'address'                => $req['new_address']     ?? ($originalHead['address'] ?? null),
            'house_ownership'        => 'Owned',
            'years_of_residency'     => 0,
            'is_4ps'                 => 0,
            'is_pwd'                 => 0,
            'is_senior_citizen'      => 0,
            'is_solo_parent'         => 0,
            'is_indigenous'          => 0,
            'registered_voter'       => 0,
            'num_families'           => 1,
            'recorded_by'            => session()->get('user_id'),
            'recorded_date'          => date('Y-m-d'),
        ]);

        // ── Remove member from original household ─────────────────────────────
        $this->memberModel->delete($req['member_id']);

        // ── Build audit note ──────────────────────────────────────────────────
        $auditNote = "Record Updated: {$memberName} separated from Household #{$req['original_household_no']} "
            . "to new Household #{$newHouseholdNo}. "
            . "Reason: {$req['change_reason']}. "
            . "Type: {$req['separation_type']}.";

        // ── Update the request as approved ────────────────────────────────────
        $db->table('household_separation_request_details')->where('request_id', $requestId)->update([
            'new_household_no' => $newHouseholdNo,
            'audit_note'      => $auditNote,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        $db->table('household_separation_requests')->where('id', $requestId)->update([
            'status'       => 'approved',
            'processed_by' => session()->get('user_id'),
            'processed_at' => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        \App\Models\NotificationModel::pushToRole(
            'captain',
            'household_move',
            'Household move recorded',
            $auditNote,
            '/captain/household/' . $newHouseholdNo,
            (int) session()->get('user_id') ?: null
        );

        return redirect()->to('/secretary/census')
            ->with('success', 'Separation approved. '
                . esc($memberName) . ' is now the head of Household #' . $newHouseholdNo . '.'
                . ' Record: From Household #' . $req['original_household_no']
                . ' → Household #' . $newHouseholdNo . '.');
    }

    // ── Step 2b: Secretary rejects the separation request ─────────────────────
    // POST /secretary/census/separation/reject/{requestId}
    public function rejectSeparation(int $requestId)
    {
        $db     = \Config\Database::connect();
        $req    = $db->table('household_separation_requests r')
            ->select('r.*, m.last_name AS member_last_name, m.first_name AS member_first_name, m.middle_name AS member_middle_name, d.change_reason, d.new_zone, d.new_address, d.new_civil_status, d.proof_verified')
            ->join('household_members m', 'm.id = r.member_id', 'left')
            ->join('household_separation_request_details d', 'd.request_id = r.id', 'left')
            ->where('r.id', $requestId)
            ->get()
            ->getRowArray();

        if (! $req || $req['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Separation request not found or already processed.');
        }

        $reason = trim($this->request->getPost('rejection_reason') ?? '');

        $auditNote = "Request Rejected for {$req['member_first_name']} {$req['member_last_name']} "
            . "(Household #{$req['original_household_no']}). "
            . ($reason ? "Reason: {$reason}." : 'No reason provided.');

        $db->table('household_separation_request_details')->where('request_id', $requestId)->update([
            'rejection_reason' => $reason ?: null,
            'audit_note'      => $auditNote,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        $db->table('household_separation_requests')->where('id', $requestId)->update([
            'status'       => 'rejected',
            'processed_by' => session()->get('user_id'),
            'processed_at' => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/secretary/census')
            ->with('success', 'Separation request rejected and logged in the audit trail.');
    }

    // ── GET: List all pending separations (for secretary census page) ──────────
    // Returns array for embedding in the census view
    public static function getPendingSeparations(): array
    {
        $db = \Config\Database::connect();
        return $db->table('household_separation_requests r')
            ->select('r.*, m.last_name AS member_last_name, m.first_name AS member_first_name, m.middle_name AS member_middle_name, d.change_reason, d.new_zone, d.new_address, d.new_civil_status, d.proof_verified, d.rejection_reason, d.audit_note, u.first_name AS requested_by_name, u.last_name AS requested_by_last')
            ->join('household_members m', 'm.id = r.member_id', 'left')
            ->join('household_separation_request_details d', 'd.request_id = r.id', 'left')
            ->join('users u', 'u.id = r.requested_by', 'left')
            ->where('r.status', 'pending')
            ->orderBy('r.created_at', 'ASC')
            ->get()->getResultArray();
    }

    // ── Delete household ──────────────────────────────────────────────────────

    public function delete(string $id)
    {
        $role = session()->get('role');
        if ($role === 'council') {
            $household = $this->householdModel->find($id);
            if (! $household || (int) ($household['recorded_by'] ?? 0) !== (int) session()->get('user_id')) {
                return redirect()->to('/council/census')->with('error', 'You can only delete households recorded under your account.');
            }
        }

        $this->householdModel->delete($id);
        return redirect()->to('/' . $role . '/census')->with('success', 'Household record deleted.');
    }

    // ── Approve a pending ownership change (secretary only) ───────────────────
    // POST /secretary/census/ownership-change/approve/{id}
    public function approveOwnershipChange(int $changeId)
    {
        $db   = \Config\Database::connect();
        $role = session()->get('role');
        $uid  = (int) session()->get('user_id');

        $change = $db->table('household_ownership_changes')
            ->where('id', $changeId)->where('status', 'pending')
            ->get()->getRowArray();

        if (! $change) {
            return redirect()->back()->with('error', 'Change request not found or already processed.');
        }

        // Apply the ownership change to the household
        $this->householdModel->update($change['household_no'], [
            'house_ownership' => $change['new_ownership'],
            'num_families'    => $change['new_num_families'] ?? 1,
        ]);

        // Mark change as approved
        $db->table('household_ownership_changes')->where('id', $changeId)->update([
            'status'      => 'approved',
            'reviewed_by' => $uid,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/' . $role . '/household/' . $change['household_no'])
            ->with('success', 'Ownership change approved: household is now classified as <strong>' . esc($change['new_ownership']) . '</strong>.');
    }

    // ── Reject a pending ownership change (secretary only) ────────────────────
    // POST /secretary/census/ownership-change/reject/{id}
    public function rejectOwnershipChange(int $changeId)
    {
        $db   = \Config\Database::connect();
        $role = session()->get('role');
        $uid  = (int) session()->get('user_id');

        $change = $db->table('household_ownership_changes')
            ->where('id', $changeId)->where('status', 'pending')
            ->get()->getRowArray();

        if (! $change) {
            return redirect()->back()->with('error', 'Change request not found or already processed.');
        }

        // Revert the household back to old ownership
        $this->householdModel->update($change['household_no'], [
            'house_ownership' => $change['old_ownership'],
            'num_families'    => $change['old_num_families'] ?? 1,
        ]);

        $reviewNote = trim($this->request->getPost('review_note') ?? 'Rejected by secretary.');

        $db->table('household_ownership_changes')->where('id', $changeId)->update([
            'status'      => 'rejected',
            'reviewed_by' => $uid,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_note' => $reviewNote,
        ]);

        return redirect()->to('/' . $role . '/household/' . $change['household_no'])
            ->with('error', 'Ownership change rejected. Household classification reverted to <strong>' . esc($change['old_ownership']) . '</strong>.');
    }

    // ── Mark a member as deceased ─────────────────────────────────────────────
    // POST /[role]/census/member/deceased/{id}
    public function markMemberDeceased(int $memberId)
    {
        $post        = $this->request->getPost();
        $role        = session()->get('role');
        $householdNo = $post['household_no'] ?? '';
        $year        = !empty($post['year_of_death']) ? (int)$post['year_of_death'] : null;

        $member = $this->memberModel->find($memberId);
        $this->memberModel->update($memberId, [
            'is_deceased'   => 1,
            'year_of_death' => $year,
        ]);

        $accountNote = '';
        if ($member) {
            $accountNote = (new \App\Libraries\DeceasedAccountService())
                ->applyOnMarkedDeceased($member, (string) ($member['household_no'] ?? $householdNo));
        }

        return redirect()->to('/' . $role . '/household/' . $householdNo)
            ->with('success', 'Member has been marked as deceased.' . ($accountNote ? ' ' . $accountNote : ''));
    }

    // ── Unmark a member as deceased ───────────────────────────────────────────
    // POST /[role]/census/member/undeceased/{id}
    public function unmarkMemberDeceased(int $memberId)
    {
        $post        = $this->request->getPost();
        $role        = session()->get('role');
        $householdNo = $post['household_no'] ?? '';

        $member = $this->memberModel->find($memberId);
        $this->memberModel->update($memberId, [
            'is_deceased'   => 0,
            'year_of_death' => null,
        ]);

        $accountNote = '';
        if ($member) {
            $accountNote = (new \App\Libraries\DeceasedAccountService())
                ->applyOnClearedDeceased($member, (string) ($member['household_no'] ?? $householdNo));
        }

        return redirect()->to('/' . $role . '/household/' . $householdNo)
            ->with('success', 'Member deceased status has been cleared.' . ($accountNote ? ' ' . $accountNote : ''));
    }

    // ── Mark household head as deceased (with optional reassignment) ──────────
    // POST /[role]/census/head/deceased/{householdNo}
    public function markHeadDeceased(string $householdNo)
    {
        $post = $this->request->getPost();
        $role = session()->get('role');
        $year = !empty($post['year_of_death']) ? (int)$post['year_of_death'] : null;
        $newHeadMemberId = !empty($post['new_head_member_id']) ? (int)$post['new_head_member_id'] : null;

        $newHeadMember = null;
        if ($newHeadMemberId) {
            $newHeadMember = $this->memberModel->find($newHeadMemberId);
            $birthDate = $newHeadMember && ! empty($newHeadMember['date_of_birth'])
                ? date_create($newHeadMember['date_of_birth'])
                : null;
            $isAdult = $birthDate && $birthDate->diff(new \DateTimeImmutable('today'))->y >= 18;

            if (
                ! $newHeadMember || $newHeadMember['household_no'] !== $householdNo
                || ! empty($newHeadMember['is_deceased']) || ! $isAdult
            ) {
                return redirect()->to('/' . $role . '/household/' . $householdNo)
                    ->with('error', 'Only an adult household member aged 18 or older can be assigned as the new household head.');
            }
        }

        $currentHead = $this->householdModel->find($householdNo);

        // Mark head as deceased
        $this->householdModel->update($householdNo, [
            'is_deceased'   => 1,
            'year_of_death' => $year,
        ]);

        $accountNote = '';
        if ($currentHead) {
            $accountNote = (new \App\Libraries\DeceasedAccountService())
                ->applyOnMarkedDeceased($currentHead, $householdNo);
        }

        // Reassign a member as the new head if requested
        if ($newHeadMemberId) {
            $member = $newHeadMember;
            if ($member && $member['household_no'] === $householdNo) {
                $current = $this->householdModel->find($householdNo);

                // Preserve the old (deceased) head as a household_members row
                // so they stay visible in the list, just flagged as deceased.
                $this->memberModel->insert([
                    'household_no'           => $householdNo,
                    'relationship'           => 'former_head',
                    'last_name'              => $current['last_name'],
                    'first_name'             => $current['first_name'],
                    'middle_name'            => $current['middle_name']            ?? null,
                    'suffix'                 => $current['suffix']                 ?? null,
                    'date_of_birth'          => $current['date_of_birth']          ?? null,
                    'gender'                 => $current['gender']                 ?? null,
                    'occupation'             => $current['occupation']             ?? null,
                    'monthly_income'         => $current['monthly_income']         ?? 0,
                    'philhealth_no'          => $current['philhealth_no']          ?? null,
                    'educational_attainment' => $current['educational_attainment'] ?? null,
                    'is_pwd'                 => $current['is_pwd']                 ?? 0,
                    'pwd_type'               => $current['pwd_type']               ?? null,
                    'is_deceased'            => 1,
                    'year_of_death'          => $year,
                ]);

                // Promote the chosen member: copy their data into the household
                // head record and clear the deceased flag on the head row.
                $this->householdModel->update($householdNo, [
                    'last_name'              => $member['last_name'],
                    'first_name'             => $member['first_name'],
                    'middle_name'            => $member['middle_name']             ?? null,
                    'suffix'                 => $member['suffix']                  ?? null,
                    'date_of_birth'          => $member['date_of_birth']           ?? null,
                    'gender'                 => $member['gender']                  ?? $current['gender'],
                    'occupation'             => $member['occupation']              ?? null,
                    'monthly_income'         => $member['monthly_income']          ?? 0,
                    'philhealth_no'          => $member['philhealth_no']           ?? null,
                    'educational_attainment' => $member['educational_attainment']  ?? null,
                    'is_deceased'            => 0,
                    'year_of_death'          => null,
                ]);

                // Remove the promoted member from household_members — they now
                // live in the head row. Their old entry is no longer needed.
                $this->memberModel->delete($newHeadMemberId);

                return redirect()->to('/' . $role . '/household/' . $householdNo)
                    ->with('success', 'Household head marked as deceased and kept in the member list. <strong>'
                        . esc($member['first_name'] . ' ' . $member['last_name'])
                        . '</strong> has been assigned as the new household head.'
                        . ($accountNote ? ' ' . $accountNote : ''));
            }
        }

        return redirect()->to('/' . $role . '/household/' . $householdNo)
            ->with('success', 'Household head has been marked as deceased.'
                . ($accountNote ? ' ' . $accountNote : ''));
    }

    // ── Unmark household head as deceased ─────────────────────────────────────
    // POST /[role]/census/head/undeceased/{householdNo}
    public function unmarkHeadDeceased(string $householdNo)
    {
        $role = session()->get('role');
        $head = $this->householdModel->find($householdNo);

        $this->householdModel->update($householdNo, [
            'is_deceased'   => 0,
            'year_of_death' => null,
        ]);

        $accountNote = '';
        if ($head) {
            $accountNote = (new \App\Libraries\DeceasedAccountService())
                ->applyOnClearedDeceased($head, $householdNo);
        }

        return redirect()->to('/' . $role . '/household/' . $householdNo)
            ->with('success', 'Household head deceased status has been cleared.'
                . ($accountNote ? ' ' . $accountNote : ''));
    }

    public function approveMemberRequest(int $id)
    {
        if (! can_role('secretary', 'captain', 'admin')) {
            return redirect()->back()->with('error', 'Only the Captain or Secretary can approve this request.');
        }

        $result = $this->applyHouseholdMemberRequest($id, true);
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function rejectMemberRequest(int $id)
    {
        if (! can_role('secretary', 'captain', 'admin')) {
            return redirect()->back()->with('error', 'Only the Captain or Secretary can reject this request.');
        }

        $result = $this->applyHouseholdMemberRequest($id, false, trim((string) $this->request->getPost('remarks')));
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    private function ensureResidentCensusAuth(int $userId, string $householdNo): ?array
    {
        $authModel = new CensusUpdateAuthorizationModel();
        $open = $authModel->getPendingByUser($userId);
        if ($open) {
            session()->set('pending_census_update_token', $open['token']);
            return $open;
        }

        $drive = (new CensusUpdateDriveModel())->currentOpen();
        $expiresAt = $drive
            ? $drive['deadline'] . ' 23:59:59'
            : date('Y-m-d H:i:s', strtotime('+30 days'));

        $auth = $authModel->createForUser($userId, $householdNo, 30, $expiresAt);
        if (! $auth) {
            return null;
        }

        $authModel->update($auth['id'], [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
        ]);
        $auth['status'] = 'sent';
        session()->set('pending_census_update_token', $auth['token']);

        return $auth;
    }

    private function collectResidentHouseholdPayload(array $post): array
    {
        $trim = static fn ($v) => trim((string) ($v ?? ''));
        $trimOrNull = static function ($v) use ($trim) {
            $s = $trim($v);
            return $s === '' ? null : $s;
        };
        $boolOrNull = static function ($v) {
            if ($v === null || $v === '') {
                return null;
            }
            return in_array((string) $v, ['1', 'yes', 'true', 'on'], true) ? 1 : 0;
        };
        $allowedEnum = static function ($v, array $options) {
            $v = (string) $v;
            return in_array($v, $options, true) ? $v : null;
        };

        $payload = [
            'last_name'              => $trimOrNull($post['last_name'] ?? null),
            'first_name'             => $trimOrNull($post['first_name'] ?? null),
            'middle_name'            => $trimOrNull($post['middle_name'] ?? null),
            'suffix'                 => $trimOrNull($post['suffix'] ?? null),
            'date_of_birth'          => $trimOrNull($post['date_of_birth'] ?? null),
            'place_of_birth'         => $trimOrNull($post['place_of_birth'] ?? null),
            'gender'                 => $allowedEnum($post['gender'] ?? '', ['Male', 'Female']),
            'civil_status'           => $allowedEnum($post['civil_status'] ?? '', ['Single', 'Married', 'Widowed', 'Separated', 'Annulled']),
            'nationality'            => $trimOrNull($post['nationality'] ?? null),
            'religion'               => $trimOrNull($post['religion'] ?? null),
            'occupation'             => $trimOrNull($post['occupation'] ?? null),
            'monthly_income'         => isset($post['monthly_income']) ? (float) $post['monthly_income'] : null,
            'contact_number'         => $trimOrNull($post['contact_number'] ?? null),
            'educational_attainment' => $trimOrNull($post['educational_attainment'] ?? null),
            'philhealth_no'          => $trimOrNull($post['philhealth_no'] ?? null),
            'registered_voter'       => $boolOrNull($post['registered_voter'] ?? null),
            'address'                => $trimOrNull($post['address'] ?? null),
            'zone'                   => $trimOrNull($post['zone'] ?? null),
            'years_of_residency'     => isset($post['years_of_residency']) ? max(0, (int) $post['years_of_residency']) : null,
            'house_ownership'        => $allowedEnum($post['house_ownership'] ?? '', ['Owned', 'Rented', 'Shared', 'Informal Settler']),
            'num_families'           => isset($post['num_families']) ? max(1, (int) $post['num_families']) : null,
            'is_4ps'                 => $boolOrNull($post['is_4ps'] ?? null),
            'is_senior_citizen'      => $boolOrNull($post['is_senior_citizen'] ?? null),
            'is_solo_parent'         => $boolOrNull($post['is_solo_parent'] ?? null),
            'is_indigenous'          => $boolOrNull($post['is_indigenous'] ?? null),
            'is_pwd'                 => $boolOrNull($post['is_pwd'] ?? null),
            'pwd_type'               => $trimOrNull($post['pwd_type'] ?? null),
            'water_source_level'     => $allowedEnum($post['water_source_level'] ?? '', ['I', 'II', 'III', 'none']),
            'water_safety_managed'   => $boolOrNull($post['water_safety_managed'] ?? null),
            'sanitation_basic'       => $allowedEnum($post['sanitation_basic'] ?? '', ['with', 'without']),
            'sanitation_managed'     => $allowedEnum($post['sanitation_managed'] ?? '', ['with', 'without']),
        ];

        return array_filter($payload, static fn ($v) => ! ($v === null || $v === ''));
    }

    private function collectOwnMemberPayload(array $post, ?array $member, array $user): array
    {
        $trim = static fn ($v) => trim((string) ($v ?? ''));
        $payload = [
            'member_id'              => $member['id'] ?? null,
            'last_name'              => strtoupper($trim($post['member_last_name'] ?? ($member['last_name'] ?? $user['last_name'] ?? ''))),
            'first_name'             => strtoupper($trim($post['member_first_name'] ?? ($member['first_name'] ?? $user['first_name'] ?? ''))),
            'middle_name'            => strtoupper($trim($post['member_middle_name'] ?? ($member['middle_name'] ?? ''))),
            'suffix'                 => $trim($post['member_suffix'] ?? ($member['suffix'] ?? '')),
            'date_of_birth'          => $trim($post['member_date_of_birth'] ?? ($member['date_of_birth'] ?? '')),
            'gender'                 => $trim($post['member_gender'] ?? ($member['gender'] ?? '')),
            'marital_status'         => $trim($post['member_civil_status'] ?? ($member['marital_status'] ?? '')),
            'occupation'             => strtoupper($trim($post['member_occupation'] ?? ($member['occupation'] ?? ''))),
            'monthly_income'         => isset($post['member_monthly_income']) ? (float) $post['member_monthly_income'] : ($member['monthly_income'] ?? null),
            'educational_attainment' => $trim($post['member_educational_attainment'] ?? ($member['educational_attainment'] ?? '')),
            'philhealth_no'          => $trim($post['member_philhealth_no'] ?? ($member['philhealth_no'] ?? '')),
        ];

        return array_filter($payload, static fn ($v) => ! ($v === null || $v === ''));
    }

    /** @param list<array<string, mixed>> $members */
    private function collectResidentMemberUpdates(array $post, array $members): array
    {
        $posted = $post['existing_members'] ?? [];
        if (! is_array($posted)) {
            return [];
        }

        $allowedIds = array_map(static fn ($row) => (int) ($row['id'] ?? 0), $members);
        $updates = [];
        foreach ($posted as $id => $row) {
            $memberId = (int) $id;
            if ($memberId < 1 || ! in_array($memberId, $allowedIds, true) || ! is_array($row)) {
                continue;
            }
            $updates[] = array_filter([
                'id'                     => $memberId,
                'relationship'           => trim((string) ($row['relationship'] ?? '')),
                'last_name'              => strtoupper(trim((string) ($row['last_name'] ?? ''))),
                'first_name'             => strtoupper(trim((string) ($row['first_name'] ?? ''))),
                'middle_name'            => strtoupper(trim((string) ($row['middle_name'] ?? ''))),
                'suffix'                 => trim((string) ($row['suffix'] ?? '')),
                'date_of_birth'          => trim((string) ($row['date_of_birth'] ?? '')),
                'gender'                 => trim((string) ($row['gender'] ?? '')),
                'occupation'             => strtoupper(trim((string) ($row['occupation'] ?? ''))),
                'monthly_income'         => isset($row['monthly_income']) ? (float) $row['monthly_income'] : null,
                'educational_attainment' => trim((string) ($row['educational_attainment'] ?? '')),
            ], static fn ($v) => ! ($v === null || $v === ''));
        }

        return $updates;
    }

    /** @param list<array<string, mixed>> $members */
    private function queueHeadMemberRequests(string $householdNo, int $userId, array $post, array $members): int
    {
        if (! $this->memberRequestTableExists()) {
            return 0;
        }

        $model = new HouseholdMemberRequestModel();
        $memberIds = array_map(static fn ($row) => (int) ($row['id'] ?? 0), $members);
        $count = 0;

        $add = $post['new_member'] ?? [];
        if (is_array($add) && trim((string) ($add['first_name'] ?? '')) !== '' && trim((string) ($add['last_name'] ?? '')) !== '') {
            $model->insert([
                'request_type' => 'add',
                'household_no' => $householdNo,
                'member_id' => null,
                'payload' => json_encode([
                    'relationship' => trim((string) ($add['relationship'] ?? 'other_relative')) ?: 'other_relative',
                    'last_name' => strtoupper(trim((string) $add['last_name'])),
                    'first_name' => strtoupper(trim((string) $add['first_name'])),
                    'middle_name' => strtoupper(trim((string) ($add['middle_name'] ?? ''))),
                    'suffix' => trim((string) ($add['suffix'] ?? '')),
                    'date_of_birth' => trim((string) ($add['date_of_birth'] ?? '')),
                    'gender' => trim((string) ($add['gender'] ?? '')),
                    'occupation' => strtoupper(trim((string) ($add['occupation'] ?? ''))),
                ], JSON_UNESCAPED_UNICODE),
                'reason' => trim((string) ($add['reason'] ?? '')) ?: 'Add household member',
                'status' => 'pending',
                'requested_by' => $userId,
            ]);
            $count++;
        }

        $moves = $post['move_member'] ?? [];
        if (is_array($moves)) {
            foreach ($moves as $memberId => $destination) {
                $memberId = (int) $memberId;
                $destination = strtoupper(trim((string) $destination));
                if ($memberId < 1 || $destination === '' || ! in_array($memberId, $memberIds, true)) {
                    continue;
                }
                $model->insert([
                    'request_type' => 'move',
                    'household_no' => $householdNo,
                    'member_id' => $memberId,
                    'destination_household_no' => $destination,
                    'reason' => 'Move member to another household',
                    'status' => 'pending',
                    'requested_by' => $userId,
                ]);
                $count++;
            }
        }

        $removals = $post['remove_member'] ?? [];
        if (is_array($removals)) {
            foreach ($removals as $memberId => $reason) {
                $memberId = (int) $memberId;
                $reason = trim((string) $reason);
                if ($memberId < 1 || $reason === '' || ! in_array($memberId, $memberIds, true)) {
                    continue;
                }
                $model->insert([
                    'request_type' => 'remove',
                    'household_no' => $householdNo,
                    'member_id' => $memberId,
                    'reason' => $reason,
                    'status' => 'pending',
                    'requested_by' => $userId,
                ]);
                $count++;
            }
        }

        if ($count > 0) {
            $this->notifyCensusReviewers(
                'Household member change requested',
                'The household head of #' . $householdNo . ' requested to add, move, or remove a member.'
            );
        }

        return $count;
    }

    private function applyResidentCensusPayload(array $payload): void
    {
        $householdNo = (string) ($payload['household_no'] ?? '');
        if ($householdNo === '') {
            return;
        }

        $householdFields = is_array($payload['household'] ?? null) ? $payload['household'] : $payload;
        $allowedColumns = [
            'last_name', 'first_name', 'middle_name', 'suffix',
            'date_of_birth', 'place_of_birth', 'gender', 'civil_status',
            'nationality', 'religion', 'occupation', 'monthly_income',
            'contact_number', 'educational_attainment', 'philhealth_no',
            'registered_voter', 'address', 'zone', 'years_of_residency',
            'house_ownership', 'num_families',
            'is_4ps', 'is_senior_citizen', 'is_solo_parent',
            'is_indigenous', 'is_pwd', 'pwd_type',
            'water_source_level', 'water_safety_managed',
            'sanitation_basic', 'sanitation_managed',
        ];
        $updates = [];
        foreach ($allowedColumns as $col) {
            if (isset($householdFields[$col]) && $householdFields[$col] !== '' && $householdFields[$col] !== null) {
                $updates[$col] = $householdFields[$col];
            }
        }
        if ($updates !== [] && ($payload['scope'] ?? '') !== 'member') {
            $this->householdModel->update($householdNo, $updates);
        }

        $memberAllowed = [
            'last_name', 'first_name', 'middle_name', 'suffix', 'date_of_birth',
            'gender', 'marital_status', 'occupation', 'monthly_income',
            'educational_attainment', 'philhealth_no', 'relationship',
        ];

        if (is_array($payload['member'] ?? null)) {
            $memberId = (int) ($payload['member']['member_id'] ?? 0);
            if ($memberId < 1) {
                $first = strtolower(trim((string) ($payload['member']['first_name'] ?? '')));
                $last = strtolower(trim((string) ($payload['member']['last_name'] ?? '')));
                foreach ($this->memberModel->getByHousehold($householdNo) as $row) {
                    if (strtolower(trim((string) ($row['first_name'] ?? ''))) === $first
                        && strtolower(trim((string) ($row['last_name'] ?? ''))) === $last) {
                        $memberId = (int) $row['id'];
                        break;
                    }
                }
            }
            $memberUpdates = [];
            foreach ($memberAllowed as $col) {
                if (isset($payload['member'][$col]) && $payload['member'][$col] !== '') {
                    $memberUpdates[$col] = $payload['member'][$col];
                }
            }
            if ($memberId > 0 && $memberUpdates !== []) {
                $this->memberModel->update($memberId, $memberUpdates);
            }
        }

        if (is_array($payload['members'] ?? null)) {
            foreach ($payload['members'] as $row) {
                if (! is_array($row) || empty($row['id'])) {
                    continue;
                }
                $memberUpdates = [];
                foreach ($memberAllowed as $col) {
                    if (isset($row[$col]) && $row[$col] !== '') {
                        $memberUpdates[$col] = $row[$col];
                    }
                }
                if ($memberUpdates !== []) {
                    $this->memberModel->update((int) $row['id'], $memberUpdates);
                }
            }
        }
    }

    /** @return array{ok:bool,message:string} */
    private function applyHouseholdMemberRequest(int $id, bool $approve, string $reason = ''): array
    {
        if (! $this->memberRequestTableExists()) {
            return ['ok' => false, 'message' => 'Member request records are not available yet.'];
        }

        $model = new HouseholdMemberRequestModel();
        $request = $model->find($id);
        if (! $request || ($request['status'] ?? '') !== 'pending') {
            return ['ok' => false, 'message' => 'No pending member request was found.'];
        }

        if (! $approve) {
            $model->update($id, [
                'status' => 'rejected',
                'reviewed_by' => session()->get('user_id'),
                'reviewed_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => $reason !== '' ? $reason : null,
            ]);
            if (! empty($request['requested_by'])) {
                NotificationModel::push(
                    (int) $request['requested_by'],
                    'census_update',
                    'Member change not approved',
                    $reason !== '' ? $reason : 'Your request to change a household member was not approved.',
                    $this->censusLinkForUser((int) $request['requested_by'])
                );
            }

            return ['ok' => true, 'message' => 'Member request rejected.'];
        }

        $type = $request['request_type'] ?? '';
        try {
            if ($type === 'add') {
                $data = json_decode((string) $request['payload'], true) ?: [];
                $this->memberModel->insert([
                    'household_no' => $request['household_no'],
                    'relationship' => $data['relationship'] ?? 'other_relative',
                    'last_name' => $data['last_name'] ?? '',
                    'first_name' => $data['first_name'] ?? '',
                    'middle_name' => $data['middle_name'] ?? null,
                    'suffix' => $data['suffix'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?: null,
                    'gender' => $data['gender'] ?? null,
                    'occupation' => $data['occupation'] ?? null,
                ]);
            } elseif ($type === 'remove' && ! empty($request['member_id'])) {
                $this->memberModel->delete((int) $request['member_id']);
            } elseif ($type === 'move' && ! empty($request['member_id'])) {
                $destination = strtoupper(trim((string) ($request['destination_household_no'] ?? '')));
                if ($destination === '' || ! $this->householdModel->find($destination)) {
                    return ['ok' => false, 'message' => 'The destination household does not exist.'];
                }
                $this->memberModel->update((int) $request['member_id'], ['household_no' => $destination]);
            } else {
                return ['ok' => false, 'message' => 'This member request could not be applied.'];
            }
        } catch (\Throwable $e) {
            log_message('error', 'Member request apply failed: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'The member change could not be applied.'];
        }

        $model->update($id, [
            'status' => 'approved',
            'reviewed_by' => session()->get('user_id'),
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        if (! empty($request['requested_by'])) {
            NotificationModel::push(
                (int) $request['requested_by'],
                'census_update',
                'Member change approved',
                'Your request to ' . $type . ' a household member has been approved.',
                $this->censusLinkForUser((int) $request['requested_by'])
            );
        }

        return ['ok' => true, 'message' => 'Member request approved and applied.'];
    }

    private function notifyCensusReviewers(string $title, string $body): void
    {
        foreach (['secretary', 'captain', 'admin'] as $role) {
            NotificationModel::pushToRole($role, 'census_update', $title, $body, '/' . $role . '/census-updates');
        }
    }

    private function memberRequestTableExists(): bool
    {
        return \Config\Database::connect()->tableExists('household_member_requests');
    }

    private function censusLinkForUser(int $userId): string
    {
        $user = (new UserModel())->find($userId);
        if (($user['role'] ?? '') === 'council') {
            return '/council/census-update';
        }

        return '/resident/census-update';
    }

    private function invalidMemberMoveDestination(array $post): ?string
    {
        $moves = $post['move_member'] ?? [];
        if (! is_array($moves)) {
            return null;
        }

        foreach ($moves as $destination) {
            $destination = strtoupper(trim((string) $destination));
            if ($destination === '') {
                continue;
            }
            if (! $this->householdModel->find($destination)) {
                return 'Destination household #' . $destination . ' was not found.';
            }
        }

        return null;
    }
}
