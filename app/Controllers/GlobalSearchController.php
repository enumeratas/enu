<?php

namespace App\Controllers;

class GlobalSearchController extends BaseController
{
    public function index()
    {
        $role = session_role();
        $query = trim((string) $this->request->getGet('q'));
        if (mb_strlen($query) < 2) {
            return $this->response->setJSON(['results' => []]);
        }
        if (mb_strlen($query) > 80) {
            $query = mb_substr($query, 0, 80);
        }

        $results = array_merge($this->pages($role, $query), $this->records($role, $query));

        return $this->response->setJSON(['results' => array_slice($results, 0, 20)]);
    }

    /** @return list<array<string, string|null>> */
    private function pages(string $role, string $query): array
    {
        $needle = mb_strtolower($query);
        $results = [];
        foreach ($this->pageIndex() as $page) {
            if (! in_array($role, $page['roles'], true)) {
                continue;
            }
            $haystack = mb_strtolower($page['label'] . ' ' . $page['keys']);
            if (! str_contains($haystack, $needle)) {
                continue;
            }
            $results[] = $this->item('Pages', $page['label'], '/' . $role . '/' . $page['path'], null, 'Page');
            if (count($results) >= 6) {
                break;
            }
        }

        return $results;
    }

    /** @return list<array<string, string|null>> */
    private function records(string $role, string $query): array
    {
        $db = \Config\Database::connect();
        $prefix = '/' . $role;
        $userId = (int) session()->get('user_id');
        $results = [];
        $staff = in_array($role, ['admin', 'secretary', 'captain'], true);

        if ($staff) {
            $results = array_merge(
                $results,
                $this->attempt(fn() => $this->users($db, $query, $prefix, $role)),
                $this->attempt(fn() => $this->households($db, $query, $prefix)),
                $this->attempt(fn() => $this->members($db, $query, $prefix)),
                $this->attempt(fn() => $this->clearances($db, $query, $prefix . '/clearance', null)),
                $this->attempt(fn() => $this->blotters($db, $query, $prefix)),
                $this->attempt(fn() => $this->concerns($db, $query, $prefix . '/concern', null)),
                $this->attempt(fn() => $this->activities($db, $query, $prefix . '/activities', false, true)),
                $this->attempt(fn() => $this->schedules($db, $query, $prefix))
            );
        }

        if ($role === 'admin' || $role === 'sk') {
            $results = array_merge(
                $results,
                $this->attempt(fn() => $this->programs($db, $query, $prefix . '/programs', true)),
                $this->attempt(fn() => $this->youth($db, $query, $prefix))
            );
        } elseif (in_array($role, ['resident', 'council'], true)) {
            $programLink = $role === 'resident' ? '/resident/sk-activities' : '/council/programs';
            $results = array_merge($results, $this->attempt(fn() => $this->programs($db, $query, $programLink, false)));
        }

        if ($role === 'resident') {
            $results = array_merge(
                $results,
                $this->attempt(fn() => $this->clearances($db, $query, '/resident/clearance', $userId)),
                $this->attempt(fn() => $this->concerns($db, $query, '/resident/concerns', $userId)),
                $this->attempt(fn() => $this->activities($db, $query, '/resident/activities', true, false))
            );
        }

        if ($role === 'sk') {
            $results = array_merge(
                $results,
                $this->attempt(fn() => $this->households($db, $query, $prefix)),
                $this->attempt(fn() => $this->members($db, $query, $prefix)),
                $this->attempt(fn() => $this->clearances($db, $query, '/sk/clearance', $userId)),
                $this->attempt(fn() => $this->concerns($db, $query, '/sk/concerns', $userId))
            );
        }

        if ($role === 'council') {
            $councilZone = '';
            $councilUser = (new \App\Models\UserModel())->find($userId);
            if (is_array($councilUser)) {
                $councilZone = trim((string) ($councilUser['council_zone'] ?? ''));
            }
            $results = array_merge($results, $this->attempt(fn() => $this->households($db, $query, $prefix, $councilZone)));
        }

        return $results;
    }

    /** @param callable(): list<array<string, string|null>> $section */
    private function attempt(callable $section): array
    {
        try {
            return $section();
        } catch (\Throwable $e) {
            log_message('error', 'Global search section failed: ' . $e->getMessage());

            return [];
        }
    }

    /** @return list<array<string, string|null>> */
    private function users($db, string $query, string $prefix, string $role): array
    {
        if (! $db->tableExists('users')) {
            return [];
        }

        $rows = $db->table('users')
            ->select('id, first_name, last_name, role, household_no, username')
            ->groupStart()
                ->like('first_name', $query)
                ->orLike('last_name', $query)
                ->orLike('username', $query)
                ->orLike('email', $query)
                ->orLike('household_no', $query)
            ->groupEnd()
            ->where('status !=', 'deleted')
            ->limit(5)
            ->get()
            ->getResultArray();

        $results = [];
        foreach ($rows as $row) {
            $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $household = trim((string) ($row['household_no'] ?? ''));
            if ($household !== '' && in_array($role, ['admin', 'secretary', 'captain'], true)) {
                $href = $prefix . '/household/' . rawurlencode($household);
            } elseif (in_array($role, ['admin', 'secretary'], true)) {
                $href = $prefix . '/residents';
            } else {
                $href = $prefix . '/census';
            }
            $results[] = $this->withName('People', '', $name, $href, ucfirst((string) ($row['role'] ?? 'user')));
        }

        return $results;
    }

    /** @return list<array<string, string|null>> */
    private function households($db, string $query, string $prefix, string $zone = ''): array
    {
        if (! $db->tableExists('households')) {
            return [];
        }

        $builder = $db->table('households')
            ->select('household_no, first_name, last_name, zone')
            ->groupStart()
                ->like('household_no', $query)
                ->orLike('first_name', $query)
                ->orLike('last_name', $query)
                ->orLike('address', $query)
                ->orLike('zone', $query)
            ->groupEnd();
        if ($zone !== '') {
            $builder->where('zone', $zone);
        } elseif ($prefix === '/council') {
            $builder->where('zone', '__unassigned__');
        }
        $rows = $builder
            ->limit(4)
            ->get()
            ->getResultArray();

        $results = [];
        foreach ($rows as $row) {
            $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $hint = 'Household ' . ($row['household_no'] ?? '');
            if (! empty($row['zone'])) {
                $hint .= ' · Zone ' . $row['zone'];
            }
            $results[] = $this->withName(
                'Census',
                '',
                $name,
                $prefix . '/household/' . rawurlencode((string) $row['household_no']),
                $hint
            );
        }

        return $results;
    }

    /** @return list<array<string, string|null>> */
    private function members($db, string $query, string $prefix): array
    {
        if (! $db->tableExists('household_members')) {
            return [];
        }

        $rows = $db->table('household_members')
            ->select('household_no, first_name, last_name, relationship')
            ->groupStart()
                ->like('first_name', $query)
                ->orLike('last_name', $query)
            ->groupEnd()
            ->limit(4)
            ->get()
            ->getResultArray();

        $results = [];
        foreach ($rows as $row) {
            $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $results[] = $this->withName(
                'Census',
                '',
                $name,
                $prefix . '/household/' . rawurlencode((string) $row['household_no']),
                'Household ' . ($row['household_no'] ?? '')
            );
        }

        return $results;
    }

    /** @return list<array<string, string|null>> */
    private function clearances($db, string $query, string $link, ?int $userId): array
    {
        if (! $db->tableExists('clearance_requests')) {
            return [];
        }

        $builder = $db->table('clearance_requests c')
            ->select('c.id, c.document_type, c.status, c.household_no, u.first_name, u.last_name')
            ->join('users u', 'u.id = c.user_id', 'left')
            ->groupStart()
                ->like('c.document_type', $query)
                ->orLike('c.purpose', $query)
                ->orLike('c.household_no', $query)
                ->orLike('u.first_name', $query)
                ->orLike('u.last_name', $query)
            ->groupEnd();
        if ($userId !== null) {
            $builder->where('c.user_id', $userId);
        }
        $rows = $builder->orderBy('c.id', 'DESC')->limit(4)->get()->getResultArray();

        $results = [];
        foreach ($rows as $row) {
            $href = $userId === null ? $link . '/request/' . (int) $row['id'] : $link;
            $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $results[] = $userId === null
                ? $this->withName('Clearance', (string) ($row['document_type'] ?? 'Clearance'), $name, $href, ucfirst((string) ($row['status'] ?? '')))
                : $this->item('Clearance', (string) ($row['document_type'] ?? 'Clearance'), $href, null, ucfirst((string) ($row['status'] ?? '')));
        }

        return $results;
    }

    /** @return list<array<string, string|null>> */
    private function blotters($db, string $query, string $prefix): array
    {
        if (! $db->tableExists('blotter_reports')) {
            return [];
        }

        $rows = $db->table('blotter_reports')
            ->select('id, incident_type, status, complainant_name')
            ->groupStart()
                ->like('incident_type', $query)
                ->orLike('complainant_name', $query)
                ->orLike('respondent_name', $query)
                ->orLike('location', $query)
            ->groupEnd()
            ->orderBy('id', 'DESC')
            ->limit(4)
            ->get()
            ->getResultArray();

        $results = [];
        foreach ($rows as $row) {
            $results[] = $this->withName(
                'Blotter',
                (string) ($row['incident_type'] ?? 'Blotter'),
                (string) ($row['complainant_name'] ?? ''),
                $prefix . '/blotter/' . (int) $row['id'],
                ucfirst((string) ($row['status'] ?? ''))
            );
        }

        return $results;
    }

    /** @return list<array<string, string|null>> */
    private function concerns($db, string $query, string $link, ?int $userId): array
    {
        if (! $db->tableExists('concern_submissions')) {
            return [];
        }

        $builder = $db->table('concern_submissions')
            ->select('id, subject, category, status, full_name')
            ->groupStart()
                ->like('subject', $query)
                ->orLike('category', $query)
                ->orLike('full_name', $query)
            ->groupEnd();
        if ($userId !== null) {
            $builder->where('user_id', $userId);
        }
        $rows = $builder->orderBy('id', 'DESC')->limit(4)->get()->getResultArray();

        $results = [];
        foreach ($rows as $row) {
            $href = $userId === null ? $link . '/' . (int) $row['id'] : $link;
            $results[] = $userId === null
                ? $this->withName('Concerns', (string) ($row['subject'] ?? $row['category'] ?? 'Concern'), (string) ($row['full_name'] ?? ''), $href, ucfirst((string) ($row['status'] ?? '')))
                : $this->item('Concerns', (string) ($row['subject'] ?? $row['category'] ?? 'Concern'), $href, null, ucfirst((string) ($row['status'] ?? '')));
        }

        return $results;
    }

    /** @return list<array<string, string|null>> */
    private function activities($db, string $query, string $href, bool $publicOnly, bool $deepLink): array
    {
        if (! $db->tableExists('barangay_activities')) {
            return [];
        }

        $builder = $db->table('barangay_activities')
            ->select('id, title, category, venue, status')
            ->groupStart()
                ->like('title', $query)
                ->orLike('category', $query)
                ->orLike('venue', $query)
            ->groupEnd();
        if ($publicOnly) {
            $builder->whereIn('status', ['Upcoming', 'Active', 'Posted']);
        }
        $rows = $builder->orderBy('id', 'DESC')->limit(4)->get()->getResultArray();

        $results = [];
        foreach ($rows as $row) {
            $link = $deepLink ? $href . '/registrations/' . (int) $row['id'] : $href;
            $results[] = $this->item('Activities', (string) ($row['title'] ?? 'Activity'), $link, null, (string) ($row['status'] ?? $row['category'] ?? ''));
        }

        return $results;
    }

    /** @return list<array<string, string|null>> */
    private function programs($db, string $query, string $link, bool $canOpen): array
    {
        if (! $db->tableExists('sk_programs')) {
            return [];
        }

        $rows = $db->table('sk_programs')
            ->select('id, name, category, venue, status')
            ->groupStart()
                ->like('name', $query)
                ->orLike('category', $query)
                ->orLike('venue', $query)
            ->groupEnd()
            ->orderBy('id', 'DESC')
            ->limit(4)
            ->get()
            ->getResultArray();

        $results = [];
        foreach ($rows as $row) {
            $href = $canOpen ? $link . '/edit/' . (int) $row['id'] : $link;
            $results[] = $this->item('Programs', (string) ($row['name'] ?? 'Program'), $href, null, (string) ($row['category'] ?? $row['status'] ?? ''));
        }

        return $results;
    }

    /** @return list<array<string, string|null>> */
    private function youth($db, string $query, string $prefix): array
    {
        if (! $db->tableExists('sk_youth')) {
            return [];
        }

        $rows = $db->table('sk_youth')
            ->select('id, first_name, last_name')
            ->groupStart()
                ->like('first_name', $query)
                ->orLike('last_name', $query)
            ->groupEnd()
            ->limit(4)
            ->get()
            ->getResultArray();

        $results = [];
        foreach ($rows as $row) {
            $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $results[] = $this->withName('SK Profiling', '', $name, $prefix . '/profiling/view/' . (int) $row['id'], 'Youth profile');
        }

        return $results;
    }

    /** @return list<array<string, string|null>> */
    private function schedules($db, string $query, string $prefix): array
    {
        if (! $db->tableExists('schedules')) {
            return [];
        }

        $rows = $db->table('schedules')
            ->select('id, title, event_date, location')
            ->groupStart()
                ->like('title', $query)
                ->orLike('location', $query)
                ->orLike('description', $query)
            ->groupEnd()
            ->orderBy('event_date', 'DESC')
            ->limit(3)
            ->get()
            ->getResultArray();

        $results = [];
        foreach ($rows as $row) {
            $when = ! empty($row['event_date']) ? date('M j, Y', strtotime($row['event_date'])) : 'Calendar';
            $results[] = $this->item('Calendar', (string) ($row['title'] ?? 'Event'), $prefix . '/calendar/view/' . (int) $row['id'], null, $when);
        }

        return $results;
    }

    /** @return array<string, string|null> */
    private function withName(string $group, string $label, string $name, string $href, string $hint): array
    {
        $name = trim($name);
        if (\App\Libraries\PiiGuard::showsPlainText()) {
            $title = $label;
            if ($name !== '' && ($title === '' || stripos($title, $name) === false)) {
                $title = $title === '' ? $name : $title . ' — ' . $name;
            }

            return $this->item($group, $title, $href, null, $hint);
        }

        return $this->item($group, $label, $href, $this->nameImage($name), $hint);
    }

    private function nameImage(string $name): ?string
    {
        $name = trim($name);

        return $name === '' ? null : pii_url($name, 'name');
    }

    /** @return array<string, string|null> */
    private function item(string $group, string $label, string $href, ?string $image, string $hint): array
    {
        return [
            'group' => $group,
            'label' => $label,
            'image' => $image,
            'hint'  => $hint,
            'href'  => $href,
        ];
    }

    /** @return list<array{label: string, path: string, roles: list<string>, keys: string}> */
    private function pageIndex(): array
    {
        $allStaff = ['admin', 'captain', 'secretary'];

        return [
            ['label' => 'Dashboard', 'path' => 'dashboard', 'roles' => ['admin', 'captain', 'secretary', 'resident', 'sk', 'council'], 'keys' => 'home overview'],
            ['label' => 'Calendar', 'path' => 'calendar', 'roles' => $allStaff, 'keys' => 'schedule events'],
            ['label' => 'Brgy Activities', 'path' => 'activities', 'roles' => ['admin', 'captain', 'secretary', 'resident', 'council'], 'keys' => 'barangay events activities'],
            ['label' => 'Census Records', 'path' => 'census', 'roles' => ['admin', 'captain', 'secretary', 'council'], 'keys' => 'household population data records'],
            ['label' => 'Household Moves', 'path' => 'moves', 'roles' => $allStaff, 'keys' => 'transfer relocation'],
            ['label' => 'Deceased Accounts', 'path' => 'deceased-accounts', 'roles' => $allStaff, 'keys' => 'death'],
            ['label' => 'Census Update Drive', 'path' => 'census-updates', 'roles' => $allStaff, 'keys' => 'update survey'],
            ['label' => 'Residents', 'path' => 'residents', 'roles' => ['admin', 'secretary'], 'keys' => 'accounts people users'],
            ['label' => 'Clearance', 'path' => 'clearance', 'roles' => $allStaff, 'keys' => 'document certificate barangay clearance'],
            ['label' => 'Document Request', 'path' => 'clearance', 'roles' => ['resident', 'sk', 'council'], 'keys' => 'clearance certificate indigency'],
            ['label' => 'Blotter Reports', 'path' => 'blotter', 'roles' => $allStaff, 'keys' => 'incident complaint'],
            ['label' => 'Blotter', 'path' => 'blotter', 'roles' => ['sk'], 'keys' => 'incident complaint'],
            ['label' => 'Reports', 'path' => 'reports', 'roles' => ['admin', 'captain', 'secretary', 'sk'], 'keys' => 'statistics'],
            ['label' => 'Appointment / Concerns', 'path' => 'concerns', 'roles' => ['admin', 'captain', 'secretary', 'resident', 'sk'], 'keys' => 'appointment concern complaint management'],
            ['label' => 'Customer Service', 'path' => 'customer-service', 'roles' => $allStaff, 'keys' => 'chat support'],
            ['label' => 'Support Tickets', 'path' => 'support-tickets', 'roles' => ['admin', 'secretary'], 'keys' => 'ticket speak chat approval concern'],
            ['label' => 'SK Profiling', 'path' => 'profiling', 'roles' => ['admin', 'sk'], 'keys' => 'youth'],
            ['label' => 'SK Profiling', 'path' => 'sk-profiling', 'roles' => ['resident', 'council'], 'keys' => 'youth'],
            ['label' => 'Programs & Events', 'path' => 'programs', 'roles' => ['admin', 'sk', 'council'], 'keys' => 'sk activities events'],
            ['label' => 'SK Activities', 'path' => 'sk-activities', 'roles' => ['admin', 'resident'], 'keys' => 'programs events youth'],
            ['label' => 'SK Reports', 'path' => 'sk-reports', 'roles' => ['admin'], 'keys' => 'youth reports'],
            ['label' => 'Users', 'path' => 'users', 'roles' => ['admin'], 'keys' => 'users accounts logins roles staff officials'],
            ['label' => 'Create Official Account', 'path' => 'create-account', 'roles' => ['admin'], 'keys' => 'official user account'],
            ['label' => 'Create Resident Account', 'path' => 'create-account', 'roles' => ['secretary'], 'keys' => 'resident account create'],
            ['label' => 'Appoint Secretary', 'path' => 'create-account', 'roles' => ['captain'], 'keys' => 'appoint secretary official'],
            ['label' => 'Settings', 'path' => 'settings', 'roles' => ['admin', 'captain', 'secretary', 'sk', 'council'], 'keys' => 'password profile preferences'],
            ['label' => 'Profile', 'path' => 'profile', 'roles' => ['resident'], 'keys' => 'account settings'],
            ['label' => 'Notifications', 'path' => 'notifications', 'roles' => ['admin', 'captain', 'secretary', 'resident', 'sk', 'council'], 'keys' => 'alerts'],
            ['label' => 'Chatbot', 'path' => 'chatbot', 'roles' => ['resident'], 'keys' => 'assistant help'],
        ];
    }
}
