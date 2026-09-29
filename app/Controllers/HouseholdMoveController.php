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

        $payload = [
            'source_household_no'        => $source,
            'destination_type'           => $destinationType,
            'destination_household_no'   => $destinationNo,
            'includes_head'              => $includeHead ? 1 : 0,
            'member_ids'                 => json_encode($memberIds),
            'replacement_head_member_id' => $replacementHead,
            'move_reason'                => substr(trim((string) ($post['move_reason'] ?? '')), 0, 60) ?: null,
            'notes'                      => trim((string) ($post['notes'] ?? '')) ?: null,
            'new_zone'                   => $destinationType === 'new' ? $newZone : null,
            'new_address'                => $destinationType === 'new' ? $newAddress : null,
            'requested_by'               => (int) session()->get('user_id') ?: null,
            'status'                     => 'pending',
        ];

        // Secretary filings stay pending. Captain and admin apply the move immediately.
        if (! in_array($role, ['captain', 'admin'], true)) {
            $this->moveModel->insert($payload + ['status' => 'pending']);
            $this->notifyCaptains('Household move request', 'A new household move request needs your approval.');

            return redirect()->to('/' . $role . '/moves')
                ->with('success', 'Move filed as pending. The barangay captain still needs to approve it.');
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
        if (! in_array($role, ['captain', 'admin'], true)) {
            return redirect()->to('/' . $role . '/moves')->with('error', 'Only the barangay captain can approve a move.');
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
        if (! in_array($role, ['captain', 'admin'], true)) {
            return redirect()->to('/' . $role . '/moves')->with('error', 'Only the barangay captain can reject a move.');
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
            NotificationModel::push(
                (int) $move['requested_by'],
                'household_move',
                'Household move rejected',
                'Your household move request was rejected' . ($reason !== '' ? ': ' . $reason : '.'),
                '/' . $role . '/moves'
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

        // Resolve or create destination household.
        if ($move['destination_type'] === 'new') {
            $destinationNo = $this->householdModel->generateHouseholdNo();
            $newHead       = $includeHead ? $source : ($movingRows[0] ?? null);
            if (! $newHead) {
                return ['ok' => false, 'message' => 'A new household needs at least one person moving.'];
            }

            $this->householdModel->insert($this->newHouseholdRow($newHead, $destinationNo, (string) $move['new_zone'], (string) $move['new_address'], $includeHead ? $source : null));

            // If a moving member became the new head, drop their old member row.
            $promotedMemberId = ! $includeHead ? (int) ($movingRows[0]['id'] ?? 0) : 0;
            if ($promotedMemberId > 0) {
                $this->memberModel->delete($promotedMemberId);
            }

            // Update remaining moving members to point at the new household.
            foreach ($movingRows as $row) {
                if ($promotedMemberId > 0 && (int) $row['id'] === $promotedMemberId) {
                    continue;
                }
                $this->memberModel->update((int) $row['id'], ['household_no' => $destinationNo]);
            }
        } else {
            $destinationNo = (string) $move['destination_household_no'];
            $destination   = $this->householdModel->find($destinationNo);
            if (! $destination) {
                return ['ok' => false, 'message' => 'The destination household no longer exists.'];
            }

            foreach ($movingRows as $row) {
                $this->memberModel->update((int) $row['id'], ['household_no' => $destinationNo]);
            }

            if ($includeHead) {
                // Move the head over as a regular member of the destination household.
                $this->memberModel->insert([
                    'household_no'           => $destinationNo,
                    'relationship'           => 'other',
                    'last_name'              => $source['last_name'],
                    'first_name'             => $source['first_name'],
                    'middle_name'            => $source['middle_name']       ?? null,
                    'suffix'                 => $source['suffix']            ?? null,
                    'date_of_birth'          => $source['date_of_birth']     ?? null,
                    'gender'                 => $source['gender']            ?? 'Male',
                    'marital_status'         => $source['civil_status']      ?? 'Single',
                    'occupation'             => $source['occupation']        ?? null,
                    'monthly_income'         => $source['monthly_income']    ?? 0,
                    'philhealth_no'          => $source['philhealth_no']     ?? null,
                    'educational_attainment' => $source['educational_attainment'] ?? null,
                    'is_pwd'                 => $source['is_pwd']            ?? 0,
                    'pwd_type'               => $source['pwd_type']          ?? null,
                    'id_pwd_path'            => $source['id_pwd_path']       ?? null,
                    'id_senior_path'         => $source['id_senior_path']    ?? null,
                ]);
            }
        }

        // Handle the source household after the head moves.
        if ($includeHead) {
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
                    return ['ok' => false, 'message' => 'The picked replacement head is no longer in the source household.'];
                }
                $this->householdModel->update($source['household_no'], [
                    'last_name'              => $replacement['last_name'],
                    'first_name'             => $replacement['first_name'],
                    'middle_name'            => $replacement['middle_name']     ?? null,
                    'suffix'                 => $replacement['suffix']          ?? null,
                    'date_of_birth'          => $replacement['date_of_birth']   ?? null,
                    'gender'                 => $replacement['gender']          ?? 'Male',
                    'civil_status'           => $replacement['marital_status']  ?? 'Single',
                    'occupation'             => $replacement['occupation']      ?? null,
                    'monthly_income'         => $replacement['monthly_income']  ?? 0,
                    'philhealth_no'          => $replacement['philhealth_no']   ?? null,
                    'educational_attainment' => $replacement['educational_attainment'] ?? null,
                ]);
                $this->memberModel->delete($replacementId);
            } elseif ($stillThere === []) {
                // Nobody is left in the source household after the head moves — remove it.
                $this->householdModel->delete($source['household_no']);
            }
        }

        $summary = $this->composeSummary($move, $source, $destinationNo);

        $this->moveModel->update($moveId, [
            'status'                   => 'approved',
            'destination_household_no' => $destinationNo,
            'processed_by'             => (int) session()->get('user_id') ?: null,
            'processed_at'             => date('Y-m-d H:i:s'),
            'summary'                  => $summary,
        ]);

        if (! empty($move['requested_by']) && (int) $move['requested_by'] !== (int) session()->get('user_id')) {
            NotificationModel::push(
                (int) $move['requested_by'],
                'household_move',
                'Household move approved',
                $summary,
                '/' . $this->currentRole() . '/moves'
            );
        }

        return ['ok' => true, 'message' => $summary];
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

        return ucfirst($who) . ' moved from household #' . $source['household_no']
            . ' to ' . $dest . ($move['move_reason'] ? ' (' . $move['move_reason'] . ')' : '') . '.';
    }

    private function notifyCaptains(string $title, string $body): void
    {
        $db = Database::connect();
        $rows = $db->table('users')
            ->select('id')
            ->where('role', 'captain')
            ->where('status', 'active')
            ->get()
            ->getResultArray();
        foreach ($rows as $row) {
            NotificationModel::push(
                (int) $row['id'],
                'household_move',
                $title,
                $body,
                '/captain/moves?status=pending'
            );
        }
    }

    private function currentRole(): string
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'secretary', 'captain', 'council'], true) ? $role : 'secretary';
    }
}
