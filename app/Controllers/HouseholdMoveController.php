<?php

namespace App\Controllers;

use App\Models\HouseholdMemberModel;
use App\Models\HouseholdModel;
use App\Models\HouseholdMoveModel;
use App\Models\NotificationModel;
use Config\Database;

class HouseholdMoveController extends BaseController
{
    private HouseholdModel $householdModel;
    private HouseholdMemberModel $memberModel;
    private HouseholdMoveModel $moveModel;

    public function __construct()
    {
        $this->householdModel = new HouseholdModel();
        $this->memberModel    = new HouseholdMemberModel();
        $this->moveModel      = new HouseholdMoveModel();
    }

    public function index()
    {
        $role   = $this->currentRole();
        $status = trim((string) $this->request->getGet('status'));
        $search = trim((string) $this->request->getGet('q'));

        return view('dashboard/moves/index', [
            'role'    => $role,
            'moves'   => $this->moveModel->listAll($status !== '' ? $status : null, $search !== '' ? $search : null),
            'counts'  => $this->moveModel->countByStatus(),
            'status'  => $status,
            'search'  => $search,
        ]);
    }

    public function create()
    {
        $role   = $this->currentRole();
        $source = trim((string) $this->request->getGet('source'));

        $sourceHousehold = null;
        $sourceMembers   = [];
        if ($source !== '') {
            $sourceHousehold = $this->householdModel->find($source);
            if ($sourceHousehold) {
                $sourceMembers = $this->memberModel->getByHousehold($source);
            }
        }

        return view('dashboard/moves/create', [
            'role'            => $role,
            'sourceHousehold' => $sourceHousehold,
            'sourceMembers'   => $sourceMembers,
        ]);
    }

    /** Autocomplete helper for the source and destination household pickers. */
    public function search()
    {
        $q = trim((string) $this->request->getGet('q'));
        if ($q === '') {
            return $this->response->setJSON([]);
        }
        $rows = $this->householdModel
            ->select('household_no, last_name, first_name, address, zone')
            ->groupStart()
                ->like('household_no', $q)
                ->orLike('last_name', $q)
                ->orLike('first_name', $q)
            ->groupEnd()
            ->orderBy('household_no', 'ASC')
            ->limit(20)
            ->findAll();

        return $this->response->setJSON(array_map(static function ($row) {
            return [
                'household_no' => $row['household_no'],
                'head'         => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                'address'      => $row['address'] ?? '',
                'zone'         => $row['zone'] ?? '',
            ];
        }, $rows));
    }

    public function store()
    {
        $role = $this->currentRole();

        $post   = $this->request->getPost();
        $source = trim((string) ($post['source_household_no'] ?? ''));

        $sourceHousehold = $this->householdModel->find($source);
        if (! $sourceHousehold) {
            return redirect()->to('/' . $role . '/moves/new')->with('error', 'Pick a valid source household first.');
        }

        $includeHead = ($post['include_head'] ?? '0') === '1';
        $memberIds   = array_values(array_filter(array_map('intval', (array) ($post['member_ids'] ?? [])), static fn($v) => $v > 0));

        if (! $includeHead && $memberIds === []) {
            return redirect()->to('/' . $role . '/moves/new?source=' . urlencode($source))
                ->with('error', 'Pick at least one person to move.');
        }

        // Every selected member must belong to the source household.
        if ($memberIds !== []) {
            $found = $this->memberModel->whereIn('id', $memberIds)->where('household_no', $source)->findAll();
            if (count($found) !== count($memberIds)) {
                return redirect()->to('/' . $role . '/moves/new?source=' . urlencode($source))
                    ->with('error', 'One or more selected members do not belong to that household.');
            }
        }

        $destinationType = ($post['destination_type'] ?? 'new') === 'existing' ? 'existing' : 'new';
        $destinationNo   = trim((string) ($post['destination_household_no'] ?? ''));
        $newZone         = trim((string) ($post['new_zone'] ?? ''));
        $newAddress      = trim((string) ($post['new_address'] ?? ''));

        if ($destinationType === 'existing') {
            if ($destinationNo === '' || $destinationNo === $source) {
                return redirect()->to('/' . $role . '/moves/new?source=' . urlencode($source))
                    ->with('error', 'Pick a different existing household as the destination.');
            }
            $destinationHousehold = $this->householdModel->find($destinationNo);
            if (! $destinationHousehold) {
                return redirect()->to('/' . $role . '/moves/new?source=' . urlencode($source))
                    ->with('error', 'The destination household number does not exist.');
            }
        } else {
            $destinationNo = null;
            if ($newAddress === '' || $newZone === '') {
                return redirect()->to('/' . $role . '/moves/new?source=' . urlencode($source))
                    ->with('error', 'Fill in the new zone and address for the new household.');
            }
        }

        // If the head is moving, the source keeps existing members with no head unless the operator picks a replacement.
        $replacementHead = (int) ($post['replacement_head_member_id'] ?? 0);
        $remainingMembers = $this->remainingMembers($source, $memberIds);
        if ($includeHead && $remainingMembers !== [] && $replacementHead <= 0) {
            return redirect()->to('/' . $role . '/moves/new?source=' . urlencode($source))
                ->with('error', 'The head is moving. Choose a replacement head from the members who stay.');
        }
        if ($replacementHead > 0) {
            $isRemaining = false;
            foreach ($remainingMembers as $r) {
                if ((int) $r['id'] === $replacementHead) {
                    $isRemaining = true;
                    break;
                }
            }
            if (! $isRemaining) {
                return redirect()->to('/' . $role . '/moves/new?source=' . urlencode($source))
                    ->with('error', 'The replacement head must be a member who stays in the source household.');
            }
        } else {
            $replacementHead = null;
        }

        $designated = $this->resolveDesignatedHead(
            trim((string) ($post['designated_head'] ?? '')),
            $includeHead,
            $memberIds,
            $destinationType,
            $sourceHousehold,
            $memberIds === [] ? [] : $this->memberModel->whereIn('id', $memberIds)->where('household_no', $source)->findAll(),
            $destinationType === 'existing' ? $this->householdModel->find($destinationNo) : null
        );
        if (is_string($designated)) {
            return redirect()->to('/' . $role . '/moves/new?source=' . urlencode($source))
                ->with('error', $designated);
        }

        $payload = [
            'source_household_no'        => $source,
            'destination_type'           => $destinationType,
            'destination_household_no'   => $destinationNo,
            'includes_head'              => $includeHead ? 1 : 0,
            'member_ids'                 => json_encode($memberIds),
            'replacement_head_member_id' => $replacementHead,
            'designated_head_type'       => $designated['type'],
            'designated_head_member_id'  => $designated['member_id'],
            'designated_head_name'       => $designated['name'],
            'move_reason'                => substr(trim((string) ($post['move_reason'] ?? '')), 0, 60) ?: null,
            'notes'                      => trim((string) ($post['notes'] ?? '')) ?: null,
            'new_zone'                   => $destinationType === 'new' ? $newZone : null,
            'new_address'                => $destinationType === 'new' ? $newAddress : null,
            'requested_by'               => (int) session()->get('user_id') ?: null,
            'status'                     => 'pending',
        ];

        // Secretary filings stay pending. Captain and admin apply the move immediately.
        if (! in_array($role, ['captain', 'admin'], true)) {
            $moveId = (int) $this->moveModel->insert($payload + ['status' => 'pending']);
            $this->notifyCaptainsOfMove(
                $payload,
                $sourceHousehold,
                'Household move submitted',
                'A household move from #' . $source . ' needs Captain or Secretary approval.',
                '/captain/moves?status=pending'
            );
            NotificationModel::pushToRole(
                'secretary',
                'household_move',
                'Household move submitted',
                'A household move from #' . $source . ' needs Captain or Secretary approval.',
                '/secretary/moves?status=pending'
            );

            return redirect()->to('/' . $role . '/moves')
                ->with('success', 'Move filed as pending. The Captain or Secretary still needs to approve it.');
        }

        $moveId = $this->moveModel->insert($payload, true);
        $result = $this->execute((int) $moveId);
        if ($result['ok'] === false) {
            $this->moveModel->update((int) $moveId, ['status' => 'rejected', 'rejection_reason' => 'Auto-cancelled: ' . $result['message']]);
            return redirect()->to('/' . $role . '/moves')->with('error', 'Move could not be saved: ' . $result['message']);
        }
        return redirect()->to('/' . $role . '/moves')->with('success', 'Move saved. ' . $result['message']);
    }

    public function approve(int $id)
    {
        $role = $this->currentRole();
        if (! in_array($role, ['captain', 'admin', 'secretary'], true)) {
            return redirect()->to('/' . $role . '/moves')->with('error', 'Only the Captain or Secretary can approve a move.');
        }

        $move = $this->moveModel->find($id);
        if (! $move || $move['status'] !== 'pending') {
            return redirect()->to('/' . $role . '/moves')->with('error', 'Move not found or already processed.');
        }

        $result = $this->execute($id);
        if ($result['ok'] === false) {
            return redirect()->to('/' . $role . '/moves')->with('error', 'Move could not be applied: ' . $result['message']);
        }

        return redirect()->to('/' . $role . '/moves')->with('success', 'Move approved. ' . $result['message']);
    }

    public function reject(int $id)
    {
        $role = $this->currentRole();
        if (! in_array($role, ['captain', 'admin', 'secretary'], true)) {
            return redirect()->to('/' . $role . '/moves')->with('error', 'Only the Captain or Secretary can reject a move.');
        }

        $move = $this->moveModel->find($id);
        if (! $move || $move['status'] !== 'pending') {
            return redirect()->to('/' . $role . '/moves')->with('error', 'Move not found or already processed.');
        }

        $reason = trim((string) $this->request->getPost('rejection_reason'));

        $this->moveModel->update($id, [
            'status'           => 'rejected',
            'processed_by'     => (int) session()->get('user_id') ?: null,
            'processed_at'     => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason !== '' ? $reason : null,
        ]);

        if (! empty($move['requested_by'])) {
            $requester = (new \App\Models\UserModel())->select('role')->find((int) $move['requested_by']);
            $requesterRole = strtolower((string) ($requester['role'] ?? 'secretary'));
            if (! in_array($requesterRole, ['admin', 'secretary', 'captain', 'council'], true)) {
                $requesterRole = 'secretary';
            }
            NotificationModel::push(
                (int) $move['requested_by'],
                'household_move',
                'Household move rejected',
                'Your household move request was rejected' . ($reason !== '' ? ': ' . $reason : '.'),
                '/' . $requesterRole . '/moves'
            );
        }

        return redirect()->to('/' . $role . '/moves')->with('success', 'Move request rejected.');
    }

    // ── Execution ──────────────────────────────────────────────────────────

    /** @return array{ok:bool,message:string} */
    private function execute(int $moveId): array
    {
        $move = $this->moveModel->find($moveId);
        if (! $move) {
            return ['ok' => false, 'message' => 'Move record not found.'];
        }

        $db     = Database::connect();
        $source = $this->householdModel->find($move['source_household_no']);
        if (! $source) {
            return ['ok' => false, 'message' => 'Source household no longer exists.'];
        }

        $memberIds  = json_decode((string) ($move['member_ids'] ?? '[]'), true);
        $memberIds  = is_array($memberIds) ? array_values(array_map('intval', $memberIds)) : [];
        $movingRows = $memberIds === [] ? [] : $this->memberModel->whereIn('id', $memberIds)->where('household_no', $source['household_no'])->findAll();

        // Bail if some rows were removed since the request was filed.
        if (count($movingRows) !== count($memberIds)) {
            return ['ok' => false, 'message' => 'Some selected members were changed before approval. Please file a new request.'];
        }

        $includeHead = (int) $move['includes_head'] === 1;
        $plan        = $this->planDesignatedHead($move, $includeHead, $movingRows);
        if (is_string($plan)) {
            return ['ok' => false, 'message' => $plan];
        }

        $db->transBegin();
        try {
            $applied = $this->applyMove($move, $source, $movingRows, $includeHead, $plan);
            if (is_string($applied)) {
                $db->transRollback();
                return ['ok' => false, 'message' => $applied];
            }
            $destinationNo = $applied['destination_no'];
            if ($db->transStatus() === false) {
                $db->transRollback();
                return ['ok' => false, 'message' => 'The move could not be applied.'];
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            $detail = $e->getMessage();
            $message = str_contains($detail, 'philhealth')
                ? 'That PhilHealth number is already on another household record.'
                : 'The move could not be applied.';
            return ['ok' => false, 'message' => $message];
        }

        $summary = $this->composeSummary($move, $source, $destinationNo);
        $actorId = (int) session()->get('user_id');

        if (! empty($move['requested_by']) && (int) $move['requested_by'] !== $actorId) {
            $requester = (new \App\Models\UserModel())->select('role')->find((int) $move['requested_by']);
            $requesterRole = strtolower((string) ($requester['role'] ?? 'secretary'));
            if (! in_array($requesterRole, ['admin', 'secretary', 'captain', 'council'], true)) {
                $requesterRole = 'secretary';
            }
            NotificationModel::push(
                (int) $move['requested_by'],
                'household_move',
                'Household move approved',
                $summary,
                '/' . $requesterRole . '/moves'
            );
        }

        $this->notifyCaptainsOfMove(
            $move,
            $source,
            'Household move recorded',
            $summary,
            '/captain/moves',
            $actorId
        );

        return ['ok' => true, 'message' => $summary];
    }

    /**
     * @param array<string, mixed> $move
     * @param list<array<string, mixed>> $movingRows
     * @return array{type:string,member:?array<string,mixed>}|string
     */
    private function planDesignatedHead(array $move, bool $includeHead, array $movingRows): array|string
    {
        $type = (string) ($move['designated_head_type'] ?? '');
        $memberId = (int) ($move['designated_head_member_id'] ?? 0);

        if ($type === '') {
            if (($move['destination_type'] ?? '') === 'new') {
                $type = $includeHead ? 'source_head' : 'member';
                $memberId = $includeHead ? 0 : (int) ($movingRows[0]['id'] ?? 0);
            } else {
                $type = 'keep';
            }
        }

        if ($type === 'keep') {
            if (($move['destination_type'] ?? '') === 'new') {
                return 'Choose who will serve as the household head of the new household.';
            }
            return ['type' => 'keep', 'member' => null];
        }

        if ($type === 'source_head') {
            if (! $includeHead) {
                return 'The current head was chosen as household head, but they are not part of this move.';
            }
            return ['type' => 'source_head', 'member' => null];
        }

        $member = null;
        foreach ($movingRows as $row) {
            if ((int) $row['id'] === $memberId) {
                $member = $row;
                break;
            }
        }
        if ($member === null) {
            return 'The person chosen as household head is no longer part of this move.';
        }

        return ['type' => 'member', 'member' => $member];
    }

    /**
     * @param array<string, mixed> $move
     * @param array<string, mixed> $source
     * @param list<array<string, mixed>> $movingRows
     * @param array{type:string,member:?array<string,mixed>} $plan
     * @return array{destination_no:string}|string destination household number, or an error message
     */
    private function applyMove(array $move, array $source, array $movingRows, bool $includeHead, array $plan): array|string
    {
        $promotedMemberId = $plan['type'] === 'member' ? (int) ($plan['member']['id'] ?? 0) : 0;

        if ($move['destination_type'] === 'new') {
            if ($plan['type'] === 'keep') {
                return 'Choose who will serve as the household head of the new household.';
            }
            $destinationNo = $this->householdModel->generateHouseholdNo();
            $newHead = $plan['type'] === 'source_head' ? $source : $plan['member'];
            if (! is_array($newHead)) {
                return 'A new household needs a household head.';
            }
            if ($plan['type'] === 'source_head') {
                $this->releaseHouseholdPhilhealth((string) $source['household_no']);
            }
            $this->householdModel->insert($this->newHouseholdRow(
                $newHead,
                $destinationNo,
                (string) $move['new_zone'],
                (string) $move['new_address'],
                $source
            ));
            $this->relocateMovers($movingRows, $destinationNo, $promotedMemberId, $includeHead && $plan['type'] !== 'source_head', $source);
        } else {
            $destinationNo = (string) $move['destination_household_no'];
            $destination   = $this->householdModel->find($destinationNo);
            if (! $destination) {
                return 'The destination household no longer exists.';
            }

            if ($plan['type'] === 'keep') {
                $this->relocateMovers($movingRows, $destinationNo, 0, $includeHead, $source);
            } else {
                $this->memberModel->insert($this->headAsMember($destination, $destinationNo, 'former_head'));
                $incoming = $plan['type'] === 'source_head' ? $source : $plan['member'];
                if (! is_array($incoming)) {
                    return 'Choose who will serve as the household head.';
                }
                if ($plan['type'] === 'source_head') {
                    $this->releaseHouseholdPhilhealth((string) $source['household_no']);
                }
                $this->householdModel->update($destinationNo, $this->identityFromPerson($incoming, $plan['type'] === 'source_head'));
                $this->relocateMovers($movingRows, $destinationNo, $promotedMemberId, $includeHead && $plan['type'] !== 'source_head', $source);
            }
        }

        if ($includeHead) {
            $leftBehind = $this->settleSourceHead($move, $source);
            if (is_string($leftBehind)) {
                return $leftBehind;
            }
        }

        $summary = $this->composeSummary($move, $source, $destinationNo);
        $this->moveModel->update((int) $move['id'], [
            'status'                   => 'approved',
            'destination_household_no' => $destinationNo,
            'processed_by'             => (int) session()->get('user_id') ?: null,
            'processed_at'             => date('Y-m-d H:i:s'),
            'summary'                  => $summary,
        ]);

        return ['destination_no' => $destinationNo];
    }

    /**
     * @param list<array<string, mixed>> $movingRows
     * @param array<string, mixed> $sourceHead
     */
    private function relocateMovers(array $movingRows, string $destinationNo, int $promotedMemberId, bool $insertSourceHead, array $sourceHead): void
    {
        foreach ($movingRows as $row) {
            if ($promotedMemberId > 0 && (int) $row['id'] === $promotedMemberId) {
                $this->memberModel->delete($promotedMemberId);
                continue;
            }
            $this->memberModel->update((int) $row['id'], ['household_no' => $destinationNo]);
        }
        if ($insertSourceHead) {
            $this->memberModel->insert($this->headAsMember($sourceHead, $destinationNo, 'former_head'));
        }
    }

    /** @param array<string, mixed> $move @param array<string, mixed> $source */
    private function settleSourceHead(array $move, array $source): ?string
    {
        $replacementId = (int) ($move['replacement_head_member_id'] ?? 0);
        $stillThere    = $this->memberModel->where('household_no', $source['household_no'])->findAll();

        if ($replacementId > 0) {
            $replacement = null;
            foreach ($stillThere as $row) {
                if ((int) $row['id'] === $replacementId) {
                    $replacement = $row;
                    break;
                }
            }
            if (! $replacement) {
                return 'The picked replacement head is no longer in the source household.';
            }
            $this->householdModel->update($source['household_no'], $this->identityFromPerson($replacement, false));
            $this->memberModel->delete($replacementId);
        } elseif ($stillThere === []) {
            $this->householdModel->delete($source['household_no']);
        }

        return null;
    }

    private function releaseHouseholdPhilhealth(string $householdNo): void
    {
        $this->householdModel->update($householdNo, ['philhealth_no' => null]);
    }

    /** @param array<string, mixed> $person */
    private function identityFromPerson(array $person, bool $fromHousehold): array
    {
        $philhealth = trim((string) ($person['philhealth_no'] ?? ''));

        return [
            'last_name'              => $person['last_name'],
            'first_name'             => $person['first_name'],
            'middle_name'            => $person['middle_name'] ?? null,
            'suffix'                 => $person['suffix'] ?? null,
            'date_of_birth'          => $person['date_of_birth'] ?? null,
            'gender'                 => $person['gender'] ?? 'Male',
            'civil_status'           => $fromHousehold
                ? ($person['civil_status'] ?? 'Single')
                : ($person['marital_status'] ?? ($person['civil_status'] ?? 'Single')),
            'occupation'             => $person['occupation'] ?? null,
            'monthly_income'         => $person['monthly_income'] ?? 0,
            'philhealth_no'          => $philhealth !== '' ? $philhealth : null,
            'educational_attainment' => $person['educational_attainment'] ?? null,
        ];
    }

    /** @param array<string, mixed> $head */
    private function headAsMember(array $head, string $householdNo, string $relationship): array
    {
        $philhealth = trim((string) ($head['philhealth_no'] ?? ''));

        return [
            'household_no'           => $householdNo,
            'relationship'           => $relationship,
            'last_name'              => $head['last_name'],
            'first_name'             => $head['first_name'],
            'middle_name'            => $head['middle_name'] ?? null,
            'suffix'                 => $head['suffix'] ?? null,
            'date_of_birth'          => $head['date_of_birth'] ?? null,
            'gender'                 => $head['gender'] ?? 'Male',
            'marital_status'         => $head['civil_status'] ?? ($head['marital_status'] ?? 'Single'),
            'occupation'             => $head['occupation'] ?? null,
            'monthly_income'         => $head['monthly_income'] ?? 0,
            'philhealth_no'          => $philhealth !== '' ? $philhealth : null,
            'educational_attainment' => $head['educational_attainment'] ?? null,
            'is_pwd'                 => $head['is_pwd'] ?? 0,
            'pwd_type'               => $head['pwd_type'] ?? null,
            'id_pwd_path'            => $head['id_pwd_path'] ?? null,
            'id_senior_path'         => $head['id_senior_path'] ?? null,
        ];
    }

    /**
     * @param list<int> $memberIds
     * @param list<array<string, mixed>> $movingMembers
     * @param array<string, mixed>|null $destinationHousehold
     * @return array{type:string,member_id:?int,name:string}|string
     */
    private function resolveDesignatedHead(
        string $posted,
        bool $includeHead,
        array $memberIds,
        string $destinationType,
        array $sourceHousehold,
        array $movingMembers,
        ?array $destinationHousehold
    ): array|string {
        $sourceName = trim(($sourceHousehold['first_name'] ?? '') . ' ' . ($sourceHousehold['last_name'] ?? ''));

        if ($posted === 'keep') {
            if ($destinationType !== 'existing') {
                return 'Choose who will serve as the household head of the new household.';
            }
            $current = trim((($destinationHousehold['first_name'] ?? '') . ' ' . ($destinationHousehold['last_name'] ?? '')));
            return [
                'type'      => 'keep',
                'member_id' => null,
                'name'      => $current !== '' ? $current : 'Current destination head',
            ];
        }

        if ($posted === 'source_head') {
            if (! $includeHead) {
                return 'The current head can be the household head only when they are included in the move.';
            }
            return [
                'type'      => 'source_head',
                'member_id' => null,
                'name'      => $sourceName !== '' ? $sourceName : 'Current household head',
            ];
        }

        if (preg_match('/^member-(\d+)$/', $posted, $match) === 1) {
            $memberId = (int) $match[1];
            if (! in_array($memberId, $memberIds, true)) {
                return 'The household head must be one of the people who are moving.';
            }
            $name = '';
            foreach ($movingMembers as $member) {
                if ((int) $member['id'] === $memberId) {
                    $name = trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''));
                    break;
                }
            }
            return [
                'type'      => 'member',
                'member_id' => $memberId,
                'name'      => $name !== '' ? $name : 'Selected member',
            ];
        }

        return 'Choose who will serve as the household head.';
    }

    /** @return array<string, mixed> */
    private function newHouseholdRow(array $head, string $destinationNo, string $newZone, string $newAddress, ?array $fromSource): array
    {
        return [
            'household_no'           => $destinationNo,
            'zone'                   => $newZone !== '' ? $newZone : ($fromSource['zone'] ?? null),
            'last_name'              => $head['last_name'],
            'first_name'             => $head['first_name'],
            'middle_name'            => $head['middle_name']    ?? null,
            'suffix'                 => $head['suffix']         ?? null,
            'date_of_birth'          => $head['date_of_birth']  ?? null,
            'gender'                 => $head['gender']         ?? 'Male',
            'civil_status'           => $head['civil_status']   ?? ($head['marital_status'] ?? 'Single'),
            'nationality'            => $head['nationality']    ?? 'Filipino',
            'occupation'             => $head['occupation']     ?? null,
            'monthly_income'         => $head['monthly_income'] ?? 0,
            'educational_attainment' => $head['educational_attainment'] ?? null,
            'philhealth_no'          => $head['philhealth_no']  ?? null,
            'address'                => $newAddress !== '' ? $newAddress : ($fromSource['address'] ?? ''),
            'house_ownership'        => 'Owned',
            'years_of_residency'     => 0,
            'residency_start_year'   => (int) date('Y'),
            'is_4ps'                 => 0,
            'is_pwd'                 => 0,
            'is_senior_citizen'      => 0,
            'is_solo_parent'         => 0,
            'is_indigenous'          => 0,
            'registered_voter'       => 0,
            'num_families'           => 1,
            'recorded_by'            => (int) session()->get('user_id') ?: null,
            'recorded_date'          => date('Y-m-d'),
        ];
    }

    private function remainingMembers(string $householdNo, array $movingIds): array
    {
        $all = $this->memberModel->getByHousehold($householdNo);
        if ($movingIds === []) {
            return $all;
        }
        $movingIds = array_flip(array_map('intval', $movingIds));
        return array_values(array_filter($all, static fn($m) => ! isset($movingIds[(int) $m['id']])));
    }

    private function composeSummary(array $move, array $source, ?string $destinationNo): string
    {
        $headName = trim(($source['first_name'] ?? '') . ' ' . ($source['last_name'] ?? ''));
        $whoBits  = [];
        if ((int) $move['includes_head'] === 1) {
            $whoBits[] = 'head (' . $headName . ')';
        }
        $memberIds = json_decode((string) ($move['member_ids'] ?? '[]'), true);
        $count     = is_array($memberIds) ? count($memberIds) : 0;
        if ($count > 0) {
            $whoBits[] = $count . ($count === 1 ? ' member' : ' members');
        }
        $who = $whoBits === [] ? 'the household' : implode(' and ', $whoBits);

        $dest = $move['destination_type'] === 'new'
            ? 'a new household #' . ($destinationNo ?? '—')
            : 'household #' . ($destinationNo ?? '—');

        $line = ucfirst($who) . ' moved from household #' . $source['household_no']
            . ' to ' . $dest . ($move['move_reason'] ? ' (' . $move['move_reason'] . ')' : '') . '.';
        $headName = trim((string) ($move['designated_head_name'] ?? ''));
        if ($headName !== '' && ($move['designated_head_type'] ?? '') !== 'keep') {
            $line .= ' ' . $headName . ' is the household head.';
        }

        return $line;
    }

    private function notifyCaptainsOfMove(
        array $move,
        array $source,
        string $title,
        string $body,
        string $link = '/captain/moves',
        ?int $exceptUserId = null
    ): void {
        $headName = trim(($source['first_name'] ?? '') . ' ' . ($source['last_name'] ?? ''));
        $sourceNo = (string) ($move['source_household_no'] ?? $source['household_no'] ?? '');
        $detail = trim($body);
        if ($sourceNo !== '' && ! str_contains($detail, '#' . $sourceNo)) {
            $detail = 'Household #' . $sourceNo
                . ($headName !== '' ? ' (' . $headName . ')' : '')
                . ': ' . $detail;
        }

        NotificationModel::pushToRole(
            'captain',
            'household_move',
            $title,
            $detail,
            $link,
            $exceptUserId
        );
    }

    private function currentRole(): string
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'secretary', 'captain', 'council'], true) ? $role : 'secretary';
    }
}
