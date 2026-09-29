<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClearanceRequestModel;
use App\Models\DocumentTemplateModel;
use App\Models\BarangaySettingsModel;
use App\Models\HouseholdModel;
use App\Models\HouseholdMemberModel;
use App\Models\NotificationModel;
use App\Models\UserModel;
use App\Libraries\EmailService;

class ClearanceController extends BaseController
{
    protected ClearanceRequestModel $model;

    public function __construct()
    {
        $this->model = new ClearanceRequestModel();
    }

    private function clearanceLinkForUser(int $userId): string
    {
        $user = (new UserModel())->select('role')->find($userId);
        $role = strtolower((string) ($user['role'] ?? 'resident'));

        return match ($role) {
            'captain', 'secretary', 'council', 'sk', 'resident' => '/' . $role . '/clearance',
            default => '/resident/clearance',
        };
    }

    private function notifyClearanceByEmail(array $request, string $status, string $remarks = ''): void
    {
        if (! (new BarangaySettingsModel())->enabled('email_notifications')) {
            return;
        }

        if (empty($request['user_id'])) {
            return;
        }

        try {
            $user = (new UserModel())->find((int) $request['user_id']);
            $email = trim($user['email'] ?? '');
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['username'] ?? 'Resident');
            $estimatedRelease = ! empty($request['est_release_date'])
                ? date('M d, Y', strtotime($request['est_release_date']))
                : '';

            (new EmailService())->sendClearanceUpdate(
                $email,
                $name,
                $request['document_type'] ?? 'clearance document',
                $status,
                $remarks,
                $estimatedRelease
            );
        } catch (\Throwable $e) {
            log_message('error', 'Clearance update email failed: ' . $e->getMessage());
        }
    }

    // ── Resident: show clearance page ─────────────────────────────────────────

    public function residentIndex()
    {
        $userId    = (int) session()->get('user_id');
        $userModel = new UserModel();
        $user      = $userModel->find($userId);

        // Load household members for the "for whom" dropdown
        $members = [];
        $householdTotalIncome = 0;
        $occupation = '';   // household head's occupation for FTJS eligibility
        $isSoloParent = false;

        if (! empty($user['household_no'])) {
            $hm         = new HouseholdModel();
            $head       = $hm->find($user['household_no']);
            $memModel   = new HouseholdMemberModel();
            $rawMembers = $memModel->where('household_no', $user['household_no'])->findAll();

            $cutoff = (new \DateTime('today'))->modify('-18 years')->format('Y-m-d');

            // Identify the logged-in user's own census record
            // Match by name against the household head first, then household_members
            $ownerName         = strtolower(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
            $headName          = $head ? strtolower(trim(($head['first_name'] ?? '') . ' ' . ($head['last_name'] ?? ''))) : '';
            $ownerIsHead       = $head && ($ownerName === $headName);
            $ownerIsSpouse     = false;
            $ownerMemberRecord = null;

            foreach ($rawMembers as $m) {
                $mName = strtolower(trim($m['first_name'] . ' ' . $m['last_name']));
                if ($mName === $ownerName) {
                    $ownerMemberRecord = $m;
                    if (strtolower(trim($m['relationship'] ?? '')) === 'spouse') {
                        $ownerIsSpouse = true;
                    }
                    break;
                }
            }

            // --- Step 1: Add only the account owner as the requestable person ---
            if ($ownerIsHead && $head) {
                $members[] = [
                    'name'          => trim($head['first_name'] . ' ' . $head['last_name']),
                    'relationship'  => 'Household Head',
                    'date_of_birth' => $head['date_of_birth'] ?? null,
                    'is_minor'      => false,
                ];
                $occupation = $head['occupation'] ?? '';
                $isSoloParent = (int) ($head['is_solo_parent'] ?? 0) === 1;
            } elseif ($ownerMemberRecord) {
                $relLabel = ucfirst($ownerMemberRecord['relationship'] ?? 'Member');
                $members[] = [
                    'name'          => trim($ownerMemberRecord['first_name'] . ' ' . $ownerMemberRecord['last_name']),
                    'relationship'  => $relLabel,
                    'date_of_birth' => $ownerMemberRecord['date_of_birth'] ?? null,
                    'is_minor'      => false,
                ];
                $occupation = $ownerMemberRecord['occupation'] ?? '';
            } else {
                // Fallback: user name from session (no matching census record found)
                $members[] = [
                    'name'          => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                    'relationship'  => 'Resident',
                    'date_of_birth' => null,
                    'is_minor'      => false,
                ];
            }

            // --- Step 2: Head or Spouse may also request for minor children ---
            if ($ownerIsHead || $ownerIsSpouse) {
                foreach ($rawMembers as $m) {
                    $rel     = strtolower(trim($m['relationship'] ?? ''));
                    $dob     = $m['date_of_birth'] ?? null;
                    $isMinor = $dob && $dob > $cutoff;

                    if ($isMinor) {
                        $members[] = [
                            'name'          => trim($m['first_name'] . ' ' . $m['last_name']),
                            'relationship'  => ucfirst($rel ?: 'Member') . ' (Minor)',
                            'date_of_birth' => $dob,
                            'is_minor'      => true,
                        ];
                    }
                }
            }

            // --- Income totals (all members, for indigency check) ---
            if ($head) {
                $householdTotalIncome += (float) ($head['monthly_income'] ?? 0);
                if (empty($occupation)) {
                    $occupation = $head['occupation'] ?? '';
                }
            }
            foreach ($rawMembers as $m) {
                $householdTotalIncome += (float) ($m['monthly_income'] ?? 0);
            }
        }

        $requests = $this->model->getByUser($userId);
        $goodMoralBlocked = \Config\Database::connect()->table('blotter_reports')
            ->where('respondent_user_id', $userId)
            ->where('status', 'file_to_action')
            ->countAllResults() > 0;

        return view('dashboard/resident/clearance', [
            'requests'             => $requests,
            'members'              => $members,
            'user'                 => $user,
            'householdTotalIncome' => $householdTotalIncome,
            'occupation'           => $occupation,
            'isSoloParent'         => $isSoloParent,
            'goodMoralBlocked'     => $goodMoralBlocked,
        ]);
    }

    // ── Resident: submit new request ──────────────────────────────────────────

    public function store()
    {
        $userId  = (int) session()->get('user_id');
        $userModel = new UserModel();
        $user    = $userModel->find($userId);

        if (! $user) {
            return redirect()->to('/login')->with('error', 'Your session has expired. Please log in again.');
        }

        $forMember   = $this->request->getPost('for_member');
        $memberRel   = $this->request->getPost('member_relationship');
        $docType     = trim((string) $this->request->getPost('document_type'));
        $purpose     = trim($this->request->getPost('purpose') ?? '');
        $notes       = trim($this->request->getPost('notes') ?? '');

        if (empty($forMember) || empty($docType) || empty($purpose)) {
            return redirect()->back()->with('error', 'Please fill in all required fields.')->withInput();
        }

        if (! in_array($docType, \Config\ClearanceDocuments::TYPES, true)) {
            return redirect()->back()->with('error', 'Please select a valid document type.')->withInput();
        }

        $head = null;
        $ownerIsHead = false;
        if (! empty($user['household_no'])) {
            $head = (new HouseholdModel())->find($user['household_no']);
            $ownerName = strtolower(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
            $headName = $head ? strtolower(trim(($head['first_name'] ?? '') . ' ' . ($head['last_name'] ?? ''))) : '';
            $ownerIsHead = $head !== null && $ownerName === $headName;
        }

        if ($docType === 'Solo Parent Certificate' && (! $ownerIsHead || ! $head || (int) ($head['is_solo_parent'] ?? 0) !== 1)) {
            return redirect()->back()
                ->with('error', 'Solo Parent Certificate requests are only available to residents with a validated Solo Parent record.')
                ->withInput();
        }

        if (
            $docType === 'Certificate of Good Moral'
            && \Config\Database::connect()->table('blotter_reports')
            ->where('respondent_user_id', $userId)
            ->where('status', 'file_to_action')
            ->countAllResults() > 0
        ) {
            return redirect()->back()
                ->with('error', 'You cannot request a Certificate of Good Moral while you are listed as a respondent in a case filed for action.')
                ->withInput();
        }

        // ── Validate that the requested member is an allowed recipient ─────────
        if (! empty($user['household_no'])) {
            $hm         = new HouseholdModel();
            $head       = $hm->find($user['household_no']);
            $memModel   = new HouseholdMemberModel();
            $rawMembers = $memModel->where('household_no', $user['household_no'])->findAll();
            $cutoff     = (new \DateTime('today'))->modify('-18 years')->format('Y-m-d');

            $ownerName     = strtolower(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
            $headName      = $head ? strtolower(trim(($head['first_name'] ?? '') . ' ' . ($head['last_name'] ?? ''))) : '';
            $ownerIsHead   = $head && ($ownerName === $headName);
            $ownerIsSpouse = false;

            foreach ($rawMembers as $m) {
                $mName = strtolower(trim($m['first_name'] . ' ' . $m['last_name']));
                if ($mName === $ownerName && strtolower(trim($m['relationship'] ?? '')) === 'spouse') {
                    $ownerIsSpouse = true;
                    break;
                }
            }

            // Build the allowed name list: owner + all minor household members (only if head/spouse)
            $allowedNames = [$ownerName];
            if ($ownerIsHead || $ownerIsSpouse) {
                foreach ($rawMembers as $m) {
                    $rel     = strtolower(trim($m['relationship'] ?? ''));
                    $dob     = $m['date_of_birth'] ?? null;
                    $isMinor = $dob && $dob > $cutoff;
                    if ($isMinor) {
                        $allowedNames[] = strtolower(trim($m['first_name'] . ' ' . $m['last_name']));
                    }
                }
            }

            if (! in_array(strtolower(trim($forMember)), $allowedNames, true)) {
                return redirect()->back()
                    ->with('error', 'Document requests can only be made for yourself' . ($ownerIsHead || $ownerIsSpouse ? ' or any minor household member' : '') . '.')
                    ->withInput();
            }
        }

        // ── Indigency income qualification check ──────────────────────────────
        if ($docType === 'Certificate of Indigency' && ! empty($user['household_no'])) {
            $hm          = new HouseholdModel();
            $head        = $hm->find($user['household_no']);
            $memModel    = new HouseholdMemberModel();
            $members     = $memModel->where('household_no', $user['household_no'])->findAll();

            $headIncome   = (float) ($head['monthly_income'] ?? 0);
            $memberIncome = array_sum(array_column($members, 'monthly_income'));
            $totalIncome  = $headIncome + $memberIncome;

            if ($totalIncome > 12000) {
                // Auto-reject: insert as rejected immediately
                $autoRejectRemarks = 'Automatically rejected: household net monthly income of ₱' . number_format($totalIncome, 2) . ' exceeds the ₱12,000.00 indigency threshold.';
                $this->model->insert([
                    'user_id'             => $userId,
                    'household_no'        => $user['household_no'] ?? null,
                    'for_member'          => $forMember,
                    'member_relationship' => $memberRel,
                    'document_type'       => $docType,
                    'purpose'             => $purpose,
                    'notes'               => $notes ?: null,
                    'status'              => 'rejected',
                    'remarks'             => $autoRejectRemarks,
                    'processed_at'        => date('Y-m-d H:i:s'),
                    'est_release_date'    => null,
                ]);

                NotificationModel::push(
                    $userId,
                    'clearance_rejected',
                    'Request Not Approved — ' . $docType,
                    'Your ' . $docType . ' request was automatically rejected. Reason: ' . $autoRejectRemarks,
                    $this->clearanceLinkForUser($userId)
                );
                $this->notifyClearanceByEmail([
                    'user_id'          => $userId,
                    'document_type'    => $docType,
                    'est_release_date' => null,
                ], 'rejected', $autoRejectRemarks);

                $role = match (session()->get('role')) {
                    'sk'      => 'sk',
                    'captain' => 'captain',
                    'council' => 'council',
                    default   => 'resident',
                };
                return redirect()->to('/' . $role . '/clearance')->with(
                    'error',
                    'Your request for a Certificate of Indigency was automatically rejected. ' .
                        'Your household\'s net monthly income (₱' . number_format($totalIncome, 2) . ') ' .
                        'exceeds the ₱12,000.00 eligibility threshold.'
                );
            }
        }

        // Estimate release: today if weekday, otherwise next Monday
        $todayDow   = (int) date('N'); // 1=Mon … 7=Sun
        $estRelease = ($todayDow <= 5)
            ? date('Y-m-d')                                    // Mon–Fri → today
            : date('Y-m-d', strtotime('next Monday'));          // Sat/Sun  → next Monday

        $this->model->insert([
            'user_id'             => $userId,
            'household_no'        => $user['household_no'] ?? null,
            'for_member'          => $forMember,
            'member_relationship' => $memberRel,
            'document_type'       => $docType,
            'purpose'             => $purpose,
            'notes'               => $notes ?: null,
            'status'              => 'pending',
            'est_release_date'    => $estRelease,
        ]);

        $saved = [
            'user_id'          => $userId,
            'document_type'    => $docType,
            'est_release_date' => $estRelease,
            'resident_name'    => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
        ];
        $autoApproved = $this->autoApproveIfEnabled((int) $this->model->getInsertID(), $saved);
        $this->emailStaffClearance($saved, $autoApproved ? 'auto_approved' : 'submitted');

        $role = match (session()->get('role')) {
            'sk'      => 'sk',
            'captain' => 'captain',
            'council' => 'council',
            'admin'   => 'admin',
            'secretary' => 'secretary',
            default   => 'resident',
        };
        $releaseLabel = date('M d, Y', strtotime($autoApproved ? date('Y-m-d') : $estRelease));
        $message = $autoApproved
            ? 'Request approved automatically. Estimated release: ' . $releaseLabel
            : 'Request submitted successfully! Estimated release: ' . $releaseLabel;

        return redirect()->to('/' . $role . '/clearance')->with('success', $message);
    }

    /**
     * Build the same document eligibility rules used by the resident request flow.
     */
    private function getSecretaryEligibility(array $user): array
    {
        $db = \Config\Database::connect();
        $householdTotalIncome = 0.0;
        $occupation = '';
        $household = null;

        if (! empty($user['household_no'])) {
            $household = (new HouseholdModel())->find($user['household_no']);
            $members   = (new HouseholdMemberModel())->where('household_no', $user['household_no'])->findAll();

            if ($household) {
                $householdTotalIncome += (float) ($household['monthly_income'] ?? 0);
                $occupation = (string) ($household['occupation'] ?? '');
            }
            foreach ($members as $member) {
                $householdTotalIncome += (float) ($member['monthly_income'] ?? 0);
            }
        }

        $isEmployed = $occupation !== ''
            && ! in_array(strtolower(trim($occupation)), ['none', 'n/a', 'unemployed', 'student', 'out-of-school'], true);
        $isSoloParent = false;
        if ($household) {
            $ownerName = strtolower(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
            $headName = strtolower(trim(($household['first_name'] ?? '') . ' ' . ($household['last_name'] ?? '')));
            $isSoloParent = $ownerName !== '' && $ownerName === $headName
                && (int) ($household['is_solo_parent'] ?? 0) === 1;
        }
        $goodMoralBlocked = $db->table('blotter_reports')
            ->where('respondent_user_id', (int) $user['id'])
            ->where('status', 'file_to_action')
            ->countAllResults() > 0;

        $documents = \Config\ClearanceDocuments::TYPES;
        $reasons = [];

        if ($householdTotalIncome > 12000) {
            $documents = array_values(array_diff($documents, ['Certificate of Indigency']));
            $reasons['Certificate of Indigency'] = 'Not available because household monthly income exceeds ₱12,000.00.';
        }
        if ($goodMoralBlocked) {
            $documents = array_values(array_diff($documents, ['Certificate of Good Moral']));
            $reasons['Certificate of Good Moral'] = 'Not available while there is an active blotter case filed for action.';
        }
        if (! $isSoloParent) {
            $documents = array_values(array_diff($documents, ['Solo Parent Certificate']));
            $reasons['Solo Parent Certificate'] = 'Not available without a validated Solo Parent record.';
        }

        return [
            'documents' => $documents,
            'reasons' => $reasons,
            'income' => $householdTotalIncome,
            'good_moral_blocked' => $goodMoralBlocked,
            'is_solo_parent' => $isSoloParent,
        ];
    }

    /** Secretary: create a request for an existing resident account. */
    public function storeSecretary()
    {
        if (! can_role('secretary')) {
            return redirect()->to('/login')->with('error', 'You do not have permission to create resident requests.');
        }

        $userId    = (int) $this->request->getPost('resident_user_id');
        $docType   = trim((string) $this->request->getPost('document_type'));
        $purpose   = trim((string) $this->request->getPost('purpose'));
        $notes     = trim((string) $this->request->getPost('notes'));
        $userModel = new UserModel();
        $user      = $userModel->where('role', 'resident')->where('status', 'active')->find($userId);

        if (! $user || $docType === '' || $purpose === '') {
            return redirect()->back()->with('error', 'Please select an active resident, document type, and purpose.');
        }

        if (! in_array($docType, \Config\ClearanceDocuments::TYPES, true)) {
            return redirect()->back()->with('error', 'Please select a valid document type.');
        }

        $eligibility = $this->getSecretaryEligibility($user);
        if (! in_array($docType, $eligibility['documents'], true)) {
            $reason = $eligibility['reasons'][$docType] ?? 'The resident does not meet the requirements for this document type.';
            return redirect()->back()->with('error', $reason);
        }

        $residentName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $todayDow     = (int) date('N');
        $estRelease   = $todayDow <= 5 ? date('Y-m-d') : date('Y-m-d', strtotime('next Monday'));

        $this->model->insert([
            'user_id'             => $userId,
            'household_no'        => $user['household_no'] ?? null,
            'for_member'          => $residentName,
            'member_relationship' => 'Resident',
            'document_type'       => $docType,
            'purpose'             => $purpose,
            'notes'               => $notes ?: null,
            'status'              => 'pending',
            'est_release_date'    => $estRelease,
        ]);

        $saved = [
            'user_id'          => $userId,
            'document_type'    => $docType,
            'est_release_date' => $estRelease,
            'resident_name'    => $residentName,
        ];
        $autoApproved = $this->autoApproveIfEnabled((int) $this->model->getInsertID(), $saved, (int) session()->get('user_id'));

        if (! $autoApproved) {
            NotificationModel::push(
                $userId,
                'clearance_submitted',
                'New Clearance Request',
                'A secretary filed a ' . $docType . ' request for your account.',
                $this->clearanceLinkForUser($userId)
            );
        }

        $this->emailStaffClearance($saved, $autoApproved ? 'auto_approved' : 'submitted');

        $message = $autoApproved
            ? 'Request for ' . $residentName . ' was approved automatically.'
            : 'Request created and linked to ' . $residentName . '.';

        return redirect()->to('/' . session_role() . '/clearance')->with('success', $message);
    }

    // ── Admin (captain/secretary): list all requests — grouped by resident ───

    public function adminIndex(string $role)
    {
        $db = \Config\Database::connect();

        // Stats
        $model    = $this->model;
        $pending  = $model->where('status', 'pending')->countAllResults();
        $approved = $model->where('status', 'approved')->countAllResults();
        $rejected = $model->where('status', 'rejected')->countAllResults();
        $total    = $model->countAll();

        // Filters
        $statusFilter = $_GET['status'] ?? '';
        $typeFilter   = $_GET['type']   ?? '';
        $search       = $_GET['search'] ?? '';

        // Group by resident (user_id) — show one row per resident with their latest request
        $builder = $db->table('clearance_requests cr')
            ->select("
                cr.user_id,
                CONCAT(TRIM(COALESCE(u.first_name,'')), ' ', TRIM(COALESCE(u.last_name,''))) AS resident_name,
                u.username,
                h.zone,
                h.address,
                h.contact_number,
                COUNT(cr.id) AS total_requests,
                SUM(cr.status = 'pending')  AS pending_count,
                SUM(cr.status = 'approved') AS approved_count,
                SUM(cr.status = 'rejected') AS rejected_count,
                GROUP_CONCAT(DISTINCT cr.document_type ORDER BY cr.document_type SEPARATOR ', ') AS document_types,
                MAX(cr.created_at) AS latest_filed
            ")
            ->join('users u', 'u.id = cr.user_id', 'left')
            ->join('households h', 'h.household_no = u.household_no', 'left')
            ->groupBy('cr.user_id')
            ->orderBy('latest_filed', 'DESC');

        if ($statusFilter !== '') {
            $builder->having('SUM(cr.status = "' . $db->escapeString($statusFilter) . '") >', 0);
        }
        if ($typeFilter !== '') {
            $builder->where('cr.document_type', $typeFilter);
        }
        if ($search !== '') {
            $builder->groupStart()
                ->like('u.first_name', $search)
                ->orLike('u.last_name', $search)
                ->groupEnd();
        }

        $perPage       = 10;
        $page          = (int) ($_GET['page'] ?? 1);
        $offset        = ($page - 1) * $perPage;
        $filteredTotal = $builder->countAllResults(false);
        $residents     = $builder->limit($perPage, $offset)->get()->getResultArray();

        // Fetch active captain name for document signatures
        $userModel   = new \App\Models\UserModel();
        $captainRow  = $userModel->getActiveByRole('captain');
        $captainName = $captainRow
            ? strtoupper(trim(($captainRow['first_name'] ?? '') . ' ' . ($captainRow['middle_name'] ?? '') . ' ' . ($captainRow['last_name'] ?? '')))
            : 'PUNONG BARANGAY';

        $templateModel = new DocumentTemplateModel();
        $templates     = $templateModel->tableExists()
            ? $templateModel->getTemplatesIndexedByKey()
            : $templateModel->getDefaultTemplates();

        $settingsModel   = new BarangaySettingsModel();
        $barangaySettings = $settingsModel->getAll();
        // getAll() always overrides captain_name with the live appointed captain
        $captainName = $barangaySettings['captain_name'] ?: $captainName;

        $residentAccounts = (new UserModel())
            ->select('id, first_name, last_name, household_no')
            ->where('role', 'resident')
            ->where('status', 'active')
            ->orderBy('last_name', 'ASC')
            ->orderBy('first_name', 'ASC')
            ->findAll();
        $residentEligibility = [];
        foreach ($residentAccounts as $resident) {
            $residentEligibility[$resident['id']] = $this->getSecretaryEligibility($resident);
        }

        $viewFile = ($role === 'captain')
            ? 'dashboard/captain/clearance'
            : 'dashboard/secretary/clearance';

        // For captain: also load their own requests so they can view + submit personal requests
        $captainOwnRequests = [];
        $captainMembers     = [];
        if ($role === 'captain') {
            $captainUserId      = (int) session()->get('user_id');
            $captainOwnRequests = $this->model->getByUser($captainUserId);

            // Build member list for the "who is this for?" picker (same logic as residentIndex)
            $captainUser = (new UserModel())->find($captainUserId);
            if ($captainUser && ! empty($captainUser['household_no'])) {
                $hm         = new HouseholdModel();
                $head       = $hm->find($captainUser['household_no']);
                $memModel   = new HouseholdMemberModel();
                $rawMembers = $memModel->where('household_no', $captainUser['household_no'])->findAll();
                $cutoff     = (new \DateTime('today'))->modify('-18 years')->format('Y-m-d');

                $ownerName     = strtolower(trim(($captainUser['first_name'] ?? '') . ' ' . ($captainUser['last_name'] ?? '')));
                $headName      = $head ? strtolower(trim(($head['first_name'] ?? '') . ' ' . ($head['last_name'] ?? ''))) : '';
                $ownerIsHead   = $head && ($ownerName === $headName);
                $ownerIsSpouse = false;

                foreach ($rawMembers as $m) {
                    if (
                        strtolower(trim($m['first_name'] . ' ' . $m['last_name'])) === $ownerName
                        && strtolower(trim($m['relationship'] ?? '')) === 'spouse'
                    ) {
                        $ownerIsSpouse = true;
                        break;
                    }
                }

                // Add the captain themselves first
                $captainMembers[] = [
                    'name'         => trim(($captainUser['first_name'] ?? '') . ' ' . ($captainUser['last_name'] ?? '')),
                    'relationship' => 'Captain',
                    'is_minor'     => false,
                ];

                // Add all minor household members if captain is head or spouse
                if ($ownerIsHead || $ownerIsSpouse) {
                    foreach ($rawMembers as $m) {
                        $rel     = strtolower(trim($m['relationship'] ?? ''));
                        $dob     = $m['date_of_birth'] ?? null;
                        $isMinor = $dob && $dob > $cutoff;
                        if ($isMinor) {
                            $captainMembers[] = [
                                'name'         => trim($m['first_name'] . ' ' . $m['last_name']),
                                'relationship' => ucfirst($rel ?: 'Member') . ' (Minor)',
                                'is_minor'     => true,
                            ];
                        }
                    }
                }
            } else {
                // No household linked — captain can only request for themselves
                $captainMembers[] = [
                    'name'         => trim((session()->get('first_name') ?? '') . ' ' . (session()->get('last_name') ?? '')),
                    'relationship' => 'Captain',
                    'is_minor'     => false,
                ];
            }
        }

        return view($viewFile, [
            'residents'           => $residents,
            'pending'             => $pending,
            'approved'            => $approved,
            'rejected'            => $rejected,
            'total'               => $total,
            'filteredTotal'       => $filteredTotal,
            'perPage'             => $perPage,
            'currentPage'         => $page,
            'statusFilter'        => $statusFilter,
            'typeFilter'          => $typeFilter,
            'search'              => $search,
            'captainName'         => $captainName,
            'templates'           => $templates,
            'barangaySettings'    => $barangaySettings,
            'captainOwnRequests'  => $captainOwnRequests,
            'captainMembers'      => $captainMembers,
            'residentAccounts'    => $residentAccounts,
            'residentEligibility' => $residentEligibility,
        ]);
    }

    // ── Admin: view all requests for one resident ─────────────────────────────

    public function residentDetail(int $userId)
    {
        $role     = session()->get('role');
        $db       = \Config\Database::connect();

        // Get resident info
        $userModel = new UserModel();
        $user      = $userModel->find($userId);
        if (! $user) {
            return redirect()->to('/' . $role . '/clearance')->with('error', 'Resident not found.');
        }

        // Get census data — match user to correct census record (head or member)
        $household    = null;
        $members      = [];
        $censusRecord = null;
        if (! empty($user['household_no'])) {
            $hm          = new HouseholdModel();
            $household   = $hm->find($user['household_no']);
            if ($household) {
                $memModel = new HouseholdMemberModel();
                $members    = $memModel->where('household_no', $user['household_no'])->findAll();
            }
            $censusRecord = self::matchCensusRecord($user, $household, $members);
        }

        // Get all requests for this resident
        $requests = $db->table('clearance_requests')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        // Keep the database primary key stable, but show a readable sequence
        // that starts at #001 for each Philippine calendar day.
        $dailyCounters = [];
        $dailyNumbers  = [];
        $allRequestRows = $db->table('clearance_requests')
            ->select('id, created_at')
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();
        foreach ($allRequestRows as $row) {
            $day = (new \DateTimeImmutable($row['created_at'], new \DateTimeZone('UTC')))
                ->setTimezone(new \DateTimeZone('Asia/Manila'))
                ->format('Y-m-d');
            $dailyCounters[$day] = ($dailyCounters[$day] ?? 0) + 1;
            $dailyNumbers[(int) $row['id']] = $dailyCounters[$day];
        }
        foreach ($requests as &$request) {
            $request['daily_request_number'] = $dailyNumbers[(int) $request['id']] ?? 0;
        }
        unset($request);

        // Per-request census lookup: each request may be FOR a different member
        // (e.g. a minor child). Build request_id → {name, civil, zone, age, gender, occupation}
        // so the JS preview uses the correct person's data when switching requests.
        $calcAge = static function (?string $dob): string {
            if (empty($dob)) return '—';
            try {
                return (string)(int)date_diff(date_create($dob), date_create('today'))->y;
            } catch (\Throwable $e) {
                return '—';
            }
        };

        $requestCensus = [];
        foreach ($requests as $r) {
            if (($r['status'] ?? '') === 'released' && empty($r['issued_document_snapshot'])) {
                $legacySnapshot = $this->buildReleasedDocumentSnapshot($r);
                $this->model->update((int) $r['id'], [
                    'issued_document_snapshot' => json_encode($legacySnapshot),
                ]);
                $r['issued_document_snapshot'] = json_encode($legacySnapshot);
            }

            $forName = trim($r['for_member'] ?? '');
            $rec = ($forName !== '')
                ? self::matchCensusByName($forName, $household, $members)
                : $censusRecord;
            if (! $rec) $rec = $censusRecord ?? $household;

            $requestCensus[$r['id']] = [
                'name'       => $forName ?: ($rec ? trim(($rec['first_name'] ?? '') . ' ' . ($rec['last_name'] ?? '')) : ''),
                'civil'      => $rec ? ($rec['civil_status'] ?? 'Single') : 'Single',
                'zone'       => $rec ? ($rec['zone'] ?? '') : '',
                'age'        => $rec ? $calcAge($rec['date_of_birth'] ?? null) : '—',
                'gender'     => $rec ? ($rec['gender'] ?? '—') : '—',
                'occupation' => $rec ? ($rec['occupation'] ?? '—') : '—',
                'address'    => $rec ? ($rec['address'] ?? ($rec['zone'] ?? '')) : '',
            ];
            if (($r['status'] ?? '') === 'released' && ! empty($r['issued_document_snapshot'])) {
                $snapshot = json_decode($r['issued_document_snapshot'], true);
                if (is_array($snapshot)) {
                    $requestCensus[$r['id']] = $snapshot;
                }
            }
        }

        // Fetch active captain name for document signatures
        $captainRow  = $userModel->getActiveByRole('captain');
        $captainName = $captainRow
            ? strtoupper(trim(($captainRow['first_name'] ?? '') . ' ' . ($captainRow['middle_name'] ?? '') . '. ' . ($captainRow['last_name'] ?? '')))
            : 'PUNONG BARANGAY';

        $settingsModel    = new BarangaySettingsModel();
        $barangaySettings = $settingsModel->getAll();
        // getAll() always overrides captain_name with the live appointed captain
        $captainName = $barangaySettings['captain_name'] ?: $captainName;

        return view('dashboard/captain/clearance_detail', [
            'role'             => $role,
            'user'             => $user,
            'household'        => $household,
            'censusRecord'     => $censusRecord,
            'requests'         => $requests,
            'requestCensus'    => $requestCensus,
            'requestId'        => $userId,
            'captainName'      => $captainName,
            'barangaySettings' => $barangaySettings,
        ]);
    }

    // ── Admin: approve a request ──────────────────────────────────────────────

    public function approve(int $id)
    {
        $req = $this->model->find($id);

        if (! $req || $req['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requests can be approved.');
        }

        $this->markApproved($id, (int) session()->get('user_id'), $req);
        $this->emailStaffClearance($req, 'approved');

        return redirect()->back()->with('success', 'Request approved.');
    }

    /**
     * Approve a request that already passed the eligibility checks.
     */
    private function autoApproveIfEnabled(int $id, array $req, ?int $processedBy = null): bool
    {
        if ($id < 1 || ! (new BarangaySettingsModel())->enabled('auto_approve_clearances')) {
            return false;
        }

        $this->markApproved($id, $processedBy, $req);

        return true;
    }

    /**
     * Write the approval snapshot and tell the resident.
     * Resident email is sent only when Email Notifications is on.
     */
    private function markApproved(int $id, ?int $processedBy, array $req): void
    {
        $barangaySettings = (new BarangaySettingsModel())->getAll();
        $snapshotCaptain  = $barangaySettings['captain_name'] ?: 'PUNONG BARANGAY';
        $snapshotDate     = date('Y-m-d');

        $this->model->update($id, [
            'status'              => 'approved',
            'processed_by'        => $processedBy,
            'processed_at'        => date('Y-m-d H:i:s'),
            'issued_captain_name' => $snapshotCaptain,
            'issued_date'         => $snapshotDate,
            'est_release_date'    => $snapshotDate,
        ]);

        if (empty($req['user_id'])) {
            return;
        }

        $req['est_release_date'] = $snapshotDate;
        $document = $req['document_type'] ?? 'clearance';
        $estDate  = ' Estimated release: ' . date('M d, Y', strtotime($snapshotDate)) . '.';
        NotificationModel::push(
            (int) $req['user_id'],
            'clearance_approved',
            'Request Approved — ' . $document,
            'Your ' . $document . ' request has been approved.' . $estDate . ' You may pick it up at the barangay hall during office hours.',
            $this->clearanceLinkForUser((int) $req['user_id'])
        );
        $this->notifyClearanceByEmail($req, 'approved');
    }

    /**
     * Email admin, secretary, and captain about a new or approved clearance.
     */
    private function emailStaffClearance(array $req, string $event): void
    {
        if (! (new BarangaySettingsModel())->enabled('email_notifications')) {
            return;
        }

        try {
            $this->deliverStaffClearanceEmail($req, $event);
        } catch (\Throwable $e) {
            log_message('error', 'Staff clearance email failed: ' . $e->getMessage());
        }
    }

    private function deliverStaffClearanceEmail(array $req, string $event): void
    {
        $document = (string) ($req['document_type'] ?? 'Clearance');
        $who      = trim((string) ($req['resident_name'] ?? ''));
        if ($who === '' && ! empty($req['user_id'])) {
            $person = (new UserModel())->find((int) $req['user_id']);
            $who    = trim(($person['first_name'] ?? '') . ' ' . ($person['last_name'] ?? ''));
        }
        $who = $who !== '' ? $who : 'A resident';

        [$subject, $message] = match ($event) {
            'approved' => [
                'Clearance approved — ' . $document,
                $who . ' has an approved ' . $document . ' request.',
            ],
            'auto_approved' => [
                'Clearance auto-approved — ' . $document,
                $who . ' requested a ' . $document . ' and it was approved automatically because it met the requirements.',
            ],
            default => [
                'New clearance request — ' . $document,
                $who . ' submitted a ' . $document . ' request and it is waiting for review.',
            ],
        };

        $staff = \Config\Database::connect()->table('users')
            ->select('email, first_name, last_name')
            ->whereIn('role', ['admin', 'secretary', 'captain'])
            ->where('status', 'active')
            ->get()
            ->getResultArray();

        $mail = new EmailService();
        foreach ($staff as $person) {
            $email = trim((string) ($person['email'] ?? ''));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $name = trim(($person['first_name'] ?? '') . ' ' . ($person['last_name'] ?? '')) ?: 'Staff';
            try {
                $mail->sendStaffNotice($email, $name, $subject, $message);
            } catch (\Throwable $e) {
                log_message('error', 'Staff clearance email failed: ' . $e->getMessage());
            }
        }
    }

    // ── Admin: release an approved request ───────────────────────────────────

    public function release(int $id)
    {
        $req = $this->model->find($id);

        if (! $req || $req['status'] !== 'approved') {
            return redirect()->back()->with('error', 'Only approved requests can be released.');
        }

        $snapshot = $this->buildReleasedDocumentSnapshot($req);
        $settings = (new BarangaySettingsModel())->getAll();
        $captainName = $settings['captain_name'] ?: 'PUNONG BARANGAY';

        $this->model->update($id, [
            'status'       => 'released',
            'processed_by' => session()->get('user_id'),
            'processed_at' => date('Y-m-d H:i:s'),
            'issued_captain_name' => $captainName,
            'issued_date' => date('Y-m-d'),
            'issued_document_snapshot' => json_encode($snapshot),
        ]);

        if (! empty($req['user_id'])) {
            \App\Models\NotificationModel::push(
                (int) $req['user_id'],
                'clearance_released',
                'Document Released — ' . $req['document_type'],
                'Your ' . $req['document_type'] . ' is ready and has been released. Please visit the barangay hall if you have not yet received it.',
                $this->clearanceLinkForUser((int) $req['user_id'])
            );
            $this->notifyClearanceByEmail($req, 'released');
        }

        return redirect()->back()->with('success', 'Request marked as released.');
    }

    private function buildReleasedDocumentSnapshot(array $request): array
    {
        $user = (new UserModel())->find((int) ($request['user_id'] ?? 0));
        $household = null;
        $members = [];
        if ($user && ! empty($user['household_no'])) {
            $household = (new HouseholdModel())->find($user['household_no']);
            $members = (new HouseholdMemberModel())->where('household_no', $user['household_no'])->findAll();
        }

        $record = self::matchCensusByName($request['for_member'] ?? '', $household, $members);
        if (! $record) {
            $record = self::matchCensusRecord($user ?? [], $household, $members) ?: $household;
        }

        $age = '—';
        if (! empty($record['date_of_birth'])) {
            try {
                $age = (string) (int) date_diff(date_create($record['date_of_birth']), date_create('today'))->y;
            } catch (\Throwable $e) {
                $age = '—';
            }
        }

        return [
            'name'       => trim($request['for_member'] ?? '') ?: trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '')),
            'civil'      => $record['civil_status'] ?? 'Single',
            'zone'       => $record['zone'] ?? '',
            'age'        => $age,
            'gender'     => $record['gender'] ?? '—',
            'occupation' => $record['occupation'] ?? '—',
            'address'    => $record['address'] ?? ($record['zone'] ?? ''),
        ];
    }

    // ── Admin: reject a request ───────────────────────────────────────────────

    public function reject(int $id)
    {
        $role    = session()->get('role');
        $remarks = trim($this->request->getPost('remarks') ?? '');
        $req     = $this->model->find($id);

        if (! $req || $req['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requests can be rejected.');
        }

        if ($remarks === '') {
            return redirect()->back()->with('error', 'A rejection note is required.');
        }

        $this->model->update($id, [
            'status'       => 'rejected',
            'remarks'      => $remarks,
            'processed_by' => session()->get('user_id'),
            'processed_at' => date('Y-m-d H:i:s'),
        ]);

        // Notify the resident
        if ($req && ! empty($req['user_id'])) {
            $reasonPart = $remarks ? ' Reason: ' . $remarks : '';
            \App\Models\NotificationModel::push(
                (int) $req['user_id'],
                'clearance_rejected',
                'Request Not Approved — ' . $req['document_type'],
                'Your ' . $req['document_type'] . ' request could not be approved.' . $reasonPart . ' Please visit the barangay hall for assistance.',
                $this->clearanceLinkForUser((int) $req['user_id'])
            );
            $this->notifyClearanceByEmail($req, 'rejected', $remarks);
        }

        return redirect()->back()->with('success', 'Request rejected.');
    }

    public function cancel(int $id)
    {
        $userId  = (int) session()->get('user_id');
        $request = $this->model->find($id);

        if (! $request || (int)$request['user_id'] !== $userId || $request['status'] !== 'pending') {
            $role = match (session()->get('role')) {
                'sk' => 'sk',
                'captain' => 'captain',
                default => 'resident'
            };
            return redirect()->to('/' . $role . '/clearance')->with('error', 'Cannot cancel this request.');
        }

        $this->model->delete($id);
        $role = match (session()->get('role')) {
            'sk' => 'sk',
            'captain' => 'captain',
            default => 'resident'
        };
        return redirect()->to('/' . $role . '/clearance')->with('success', 'Request cancelled successfully.');
    }
}
