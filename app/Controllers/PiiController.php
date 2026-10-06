<?php

namespace App\Controllers;

use App\Libraries\PiiGuard;
use App\Models\HouseholdMemberModel;
use App\Models\UserModel;

class PiiController extends BaseController
{
    public function image(string $token)
    {
        if (! session()->get('user_id')) {
            return $this->response->setStatusCode(403);
        }

        $payload = PiiGuard::load($token);
        if ($payload === null) {
            return $this->response->setStatusCode(404);
        }

        $bytes = PiiGuard::png((string) $payload['t'], (string) ($payload['v'] ?? 'body'));
        if (ob_get_level() > 0) {
            ob_clean();
        }
        $isSvg = str_starts_with($bytes, '<svg');

        return $this->response
            ->setHeader('Content-Type', $isSvg ? 'image/svg+xml' : 'image/png')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($bytes);
    }

    public function member(int $id)
    {
        if (! can_role('secretary', 'captain', 'council', 'sk')) {
            return $this->jsonResponse(['error' => 'Unauthorized.'], 403);
        }

        $member = (new HouseholdMemberModel())->find($id);
        if (! $member) {
            return $this->jsonResponse(['error' => 'Member not found.'], 404);
        }

        $fullName = trim(($member['first_name'] ?? '') . ' ' . ($member['middle_name'] ?? '') . ' ' . ($member['last_name'] ?? ''));
        $shortName = trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''));
        $member['name_img'] = PiiGuard::url($shortName !== '' ? $shortName : $fullName, 'name');
        $member['full_name_img'] = PiiGuard::url($fullName !== '' ? $fullName : $shortName, 'name');

        return $this->jsonResponse($member);
    }

    public function residentDirectory()
    {
        if (! can_role('secretary', 'captain', 'council', 'admin')) {
            return $this->jsonResponse([], 403);
        }

        $db = \Config\Database::connect();
        
        // Fetch all active resident accounts
        $rows = $db->table('users u')
            ->select('u.id, u.first_name, u.middle_name, u.last_name, u.username, u.email, u.household_no,
                      h.date_of_birth AS head_dob, h.first_name AS head_first, h.middle_name AS head_middle, 
                      h.last_name AS head_last, h.zone')
            ->join('households h', 'h.household_no = u.household_no', 'left')
            ->where('u.role', 'resident')
            ->where('u.status', 'active')
            ->orderBy('u.last_name', 'ASC')
            ->orderBy('u.first_name', 'ASC')
            ->get()->getResultArray();

        // Get all household numbers to fetch members
        $householdNos = array_values(array_filter(array_unique(array_map(
            static fn(array $row): string => trim((string) ($row['household_no'] ?? '')),
            $rows
        ))));
        
        // Fetch all household members for matching
        $membersByHousehold = [];
        if ($householdNos !== []) {
            $members = $db->table('household_members')
                ->select('household_no, first_name, middle_name, last_name, date_of_birth, relationship')
                ->whereIn('household_no', $householdNos)
                ->get()->getResultArray();
            foreach ($members as $member) {
                $membersByHousehold[(string) $member['household_no']][] = $member;
            }
        }

        $out = [];
        foreach ($rows as $r) {
            $first = trim((string) ($r['first_name'] ?? ''));
            $middle = trim((string) ($r['middle_name'] ?? ''));
            $last = trim((string) ($r['last_name'] ?? ''));
            $dob = null;
            $relationship = 'Resident';
            $zone = trim((string) ($r['zone'] ?? ''));

            // Check if user is household head
            $headFirst = trim((string) ($r['head_first'] ?? ''));
            $headLast = trim((string) ($r['head_last'] ?? ''));
            $isHead = $headFirst !== ''
                && strcasecmp($first, $headFirst) === 0
                && ($last === '' || strcasecmp($last, $headLast) === 0);
            
            if ($isHead) {
                if ($last === '') {
                    $last = $headLast;
                }
                if ($middle === '') {
                    $middle = trim((string) ($r['head_middle'] ?? ''));
                }
                $dob = $r['head_dob'] ?? null;
                $relationship = 'Household Head';
            }

            // Check if user matches a household member
            foreach ($membersByHousehold[(string) ($r['household_no'] ?? '')] ?? [] as $member) {
                $memberFirst = trim((string) ($member['first_name'] ?? ''));
                $memberLast = trim((string) ($member['last_name'] ?? ''));
                $samePerson = strcasecmp($first, $memberFirst) === 0
                    && ($last === '' || strcasecmp($last, $memberLast) === 0);
                if (! $samePerson) {
                    continue;
                }
                if ($last === '') {
                    $last = $memberLast;
                }
                if ($middle === '') {
                    $middle = trim((string) ($member['middle_name'] ?? ''));
                }
                if (! empty($member['date_of_birth'])) {
                    $dob = $member['date_of_birth'];
                }
                // Get relationship from census
                $rel = trim((string) ($member['relationship'] ?? ''));
                if ($rel !== '') {
                    $relationship = ucfirst(strtolower($rel));
                }
                break;
            }

            $given = trim($first . ($middle !== '' ? ' ' . $middle : ''));
            if ($last !== '' && $given !== '') {
                $label = $last . ', ' . $given;
            } else {
                $label = $given !== '' ? $given : ($last !== '' ? $last : 'Resident');
            }

            $out[] = [
                'id'           => (int) $r['id'],
                'label'        => $label,
                'name'         => trim($given . ($last !== '' ? ' ' . $last : '')),
                'display'      => $label,
                'username'     => (string) ($r['username'] ?? ''),
                'email'        => (string) ($r['email'] ?? ''),
                'age'          => $this->ageFromDob(is_string($dob) ? $dob : null),
                'relationship' => $relationship,
                'zone'         => $zone,
            ];
        }

        return $this->jsonResponse($out);
    }

    private function ageFromDob(?string $dob): ?int
    {
        $dob = trim((string) $dob);
        if ($dob === '' || str_starts_with($dob, '0000')) {
            return null;
        }
        $birth = date_create($dob);
        if ($birth === false) {
            return null;
        }

        return (int) date_diff($birth, date_create('today'))->y;
    }

    public function residentCard(int $id)
    {
        if (! can_role('secretary', 'captain', 'council')) {
            return $this->jsonResponse(['error' => 'Unauthorized.'], 403);
        }

        $user = (new UserModel())->find($id);
        if (! $user || ($user['role'] ?? '') !== 'resident') {
            return $this->jsonResponse(['error' => 'Resident not found.'], 404);
        }

        $db = \Config\Database::connect();
        $household = [];
        if ($db->tableExists('households') && ! empty($user['household_no'])) {
            $household = $db->table('households')
                ->where('household_no', $user['household_no'])
                ->get()->getRowArray() ?: [];
        }

        $zone = trim((string) ($household['zone'] ?? ''));
        $address = $zone !== '' ? $zone . ', Bacolod City, Bacolod' : 'Bacolod City, Bacolod';

        return $this->jsonResponse([
            'id'      => (int) $user['id'],
            'name'    => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            'email'   => (string) ($user['email'] ?? ''),
            'address' => $address,
        ]);
    }
}
