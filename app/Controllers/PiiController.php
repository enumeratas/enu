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
        if (! can_role('secretary', 'captain', 'council')) {
            return $this->jsonResponse([], 403);
        }

        $rows = (new UserModel())
            ->where('role', 'resident')
            ->whereIn('status', ['active', 'unverified', 'pending'])
            ->orderBy('last_name', 'ASC')
            ->findAll();

        $out = [];
        foreach ($rows as $r) {
            $first = trim((string) ($r['first_name'] ?? ''));
            $middle = trim((string) ($r['middle_name'] ?? ''));
            $last = trim((string) ($r['last_name'] ?? ''));
            $out[] = [
                'id'       => (int) $r['id'],
                'label'    => trim($last . ', ' . $first . ($middle !== '' ? ' ' . $middle : '')),
                'name'     => trim($first . ' ' . $last),
                'display'  => trim($last . ', ' . $first . ' ' . $middle),
                'username' => (string) ($r['username'] ?? ''),
                'email'    => (string) ($r['email'] ?? ''),
                'age'      => (int) ($r['age'] ?? 0),
            ];
        }

        return $this->jsonResponse($out);
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
