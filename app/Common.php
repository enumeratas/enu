<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('notification_time')) {
    function notification_time(?string $datetime): string
    {
        if (! $datetime) {
            return '';
        }

        try {
            return (new DateTimeImmutable($datetime, new DateTimeZone('UTC')))
                ->setTimezone(new DateTimeZone('Asia/Manila'))
                ->format('M j, Y · g:i A');
        } catch (Throwable) {
            return '';
        }
    }
}

if (! function_exists('notification_time_iso')) {
    function notification_time_iso(?string $datetime): string
    {
        if (! $datetime) {
            return '';
        }

        try {
            return (new DateTimeImmutable($datetime, new DateTimeZone('UTC')))->format('c');
        } catch (Throwable) {
            return '';
        }
    }
}

if (! function_exists('notification_href_for_role')) {
    /**
     * Keep a notification link inside the signed-in role.
     * A link saved for another role opens that role's empty session
     * and the login page is shown even though this account is still signed in.
     */
    function notification_href_for_role(?string $link, string $role): string
    {
        $role = strtolower(trim($role));
        $fallback = '/' . ($role !== '' ? $role : 'secretary') . '/notifications';
        $link = trim((string) $link);

        if ($link === '' || $link === '#') {
            return $fallback;
        }

        $parts = parse_url($link);
        if ($parts === false) {
            return $fallback;
        }

        $path = (string) ($parts['path'] ?? '');
        if ($path === '' && ! str_contains($link, '://')) {
            $path = $link;
        }
        $path = '/' . ltrim($path, '/');

        if ($role !== '' && preg_match('#^/(admin|captain|secretary|council|resident|sk)(/|$)#', $path) === 1) {
            $path = preg_replace('#^/(admin|captain|secretary|council|resident|sk)#', '/' . $role, $path, 1) ?? $path;
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . $parts['fragment'] : '';

        return $path . $query . $fragment;
    }
}

if (! function_exists('notification_page_for_role')) {
    /**
     * Page a notification should open. Dashboard is only a fallback for
     * permission errors, so a click should land on the subject page.
     */
    function notification_page_for_role(array $notification, string $role): string
    {
        $role = strtolower(trim($role));
        if (! in_array($role, ['resident', 'council'], true)) {
            $role = 'resident';
        }

        $type = strtolower(trim((string) ($notification['type'] ?? '')));
        $title = strtolower((string) ($notification['title'] ?? ''));
        $body = (string) ($notification['body'] ?? '');
        $stored = notification_href_for_role((string) ($notification['link'] ?? ''), $role);

        $subject = null;
        if (str_starts_with($type, 'clearance_')) {
            $subject = '/' . $role . '/clearance';
        } elseif ($type === 'announcement' || str_starts_with($type, 'activity_')) {
            $subject = '/' . $role . '/activities';
        } elseif ($type === 'sk_program' || str_starts_with($type, 'sk_registration')) {
            $subject = $role === 'council' ? '/council/programs' : '/resident/sk-activities';
        } elseif ($type === 'support_ticket') {
            $subject = $role === 'resident' ? '/resident/chatbot' : null;
        } elseif (
            $type === 'new_concern'
            || ($type === 'event_reminder' && (str_contains($title, 'appointment') || str_contains(strtolower($body), 'appointment')))
        ) {
            $subject = $role === 'resident' ? '/resident/concerns' : null;
        } elseif ($type === 'census_update') {
            $subject = '/' . $role . '/census-update';
        } elseif ($type === 'event_reminder') {
            $subject = notification_events_url($body, $role);
        } elseif ($type === 'household_approved' || $type === 'household_rejected') {
            $subject = $role === 'council' ? '/council/census' : '/resident/profile';
        }

        // Rewrite any legacy stored `/events...` link on an event-reminder
        // notification so residents are kept inside the authenticated area
        // instead of being bounced to the public landing page (where they
        // appear to be "logged out"). The href has already been normalized
        // by `notification_href_for_role` above, so we only need the first
        // path segment check.
        if ($type === 'event_reminder' && $role === 'resident') {
            $storedPath = (string) (parse_url($stored, PHP_URL_PATH) ?? $stored);
            if ($storedPath === '/events') {
                $query = (string) (parse_url($stored, PHP_URL_QUERY) ?? '');
                $stored = '/resident/events' . ($query !== '' ? '?' . $query : '');
            }
        }

        if (! notification_path_is_generic($stored, $role) && notification_path_is_open_to_role($stored, $role)) {
            return $stored;
        }

        if ($subject !== null && notification_path_is_open_to_role($subject, $role)) {
            return $subject;
        }

        return '/' . $role . '/notifications';
    }
}

if (! function_exists('notification_events_url')) {
    /**
     * URL for an "Upcoming Event" notification click. Residents are routed
     * to the role-scoped page (`/resident/events`) so the dashboard shell
     * is preserved; everyone else keeps the public `/events` landing page.
     */
    function notification_events_url(string $body, string $role = ''): string
    {
        $base = $role === 'resident' ? '/resident/events' : '/events';
        $body = preg_replace('/\s*\[event_id:\d+\]/', '', $body) ?? $body;
        if (preg_match('/\b([A-Z][a-z]{2,9} \d{1,2}, \d{4})\b/', $body, $match) === 1) {
            $timestamp = strtotime($match[1]);
            if ($timestamp !== false) {
                return $base . '?year=' . date('Y', $timestamp) . '&month=' . (int) date('n', $timestamp);
            }
        }

        return $base;
    }
}

if (! function_exists('notification_path_is_generic')) {
    function notification_path_is_generic(string $path, string $role): bool
    {
        $path = '/' . trim((string) (parse_url($path, PHP_URL_PATH) ?? $path), '/');
        if ($path === '/') {
            return true;
        }

        return (bool) preg_match('#^/(?:' . preg_quote($role, '#') . '/)?(?:dashboard|notifications)$#', $path);
    }
}

if (! function_exists('notification_path_is_open_to_role')) {
    function notification_path_is_open_to_role(string $path, string $role): bool
    {
        $full = $path;
        $path = (string) (parse_url($path, PHP_URL_PATH) ?? $path);
        $path = '/' . trim($path, '/');

        if ($path === '/events' || str_starts_with($path, '/census/update/')) {
            return true;
        }

        $pages = [
            'resident' => ['clearance', 'profile', 'chatbot', 'support-ticket', 'concerns', 'activities', 'events', 'sk-activities', 'sk-profiling', 'census-update'],
            'council'  => ['clearance', 'activities', 'programs', 'sk-profiling', 'settings', 'census', 'household', 'household-finder', 'census-update'],
        ];

        if (! preg_match('#^/' . preg_quote($role, '#') . '/([^/]+)#', $path, $match)) {
            return false;
        }

        return in_array($match[1], $pages[$role] ?? [], true) && $full !== '';
    }
}

if (! function_exists('current_years_of_residency')) {
    function current_years_of_residency(array $household): int
    {
        $startYear = (int) ($household['residency_start_year'] ?? 0);
        if ($startYear > 0) {
            return max(0, (int) date('Y') - $startYear);
        }

        return max(0, (int) ($household['years_of_residency'] ?? 0));
    }
}

if (! function_exists('person_age_from_dob')) {
    function person_age_from_dob(?string $dob): ?int
    {
        $dob = trim((string) $dob);
        if ($dob === '' || str_starts_with($dob, '0000')) {
            return null;
        }

        try {
            $born = new DateTimeImmutable(substr($dob, 0, 10));
            $today = new DateTimeImmutable('today');
            if ($born > $today) {
                return null;
            }

            return (int) $born->diff($today)->y;
        } catch (Throwable) {
            $stamp = strtotime($dob);

            return $stamp === false ? null : (int) date_diff(date_create('@' . $stamp), date_create('today'))->y;
        }
    }
}

if (! function_exists('age_within_inclusive_range')) {
    /**
     * Inclusive min/max check. An empty or zero max is treated as "no maximum"
     * so a leftover 0 from an empty number input cannot reject every adult.
     */
    function age_within_inclusive_range(?int $age, mixed $min, mixed $max): bool
    {
        $minAge = ($min === null || $min === '') ? null : (int) $min;
        $maxAge = ($max === null || $max === '') ? null : (int) $max;
        if ($maxAge !== null && $maxAge <= 0) {
            $maxAge = null;
        }

        if ($age === null) {
            return $minAge === null && $maxAge === null;
        }

        if ($minAge !== null && $age < $minAge) {
            return false;
        }

        if ($maxAge !== null && $age > $maxAge) {
            return false;
        }

        return true;
    }
}

if (! function_exists('resident_census_age')) {
    /**
     * Resolve a resident/council user's age from the census, using
     * case-insensitive name matching so a 50-year-old is not rejected
     * just because the account name casing differs from the household row.
     */
    function resident_census_age(?array $user): ?int
    {
        if (! $user) {
            return null;
        }

        $first = strtoupper(trim((string) ($user['first_name'] ?? '')));
        $last  = strtoupper(trim((string) ($user['last_name'] ?? '')));
        $db    = \Config\Database::connect();

        $ageFromRow = static function (?array $row): ?int {
            return person_age_from_dob($row['date_of_birth'] ?? null);
        };

        $matchName = static function (?array $row) use ($first, $last): bool {
            if (! $row || $first === '' || $last === '') {
                return false;
            }
            $rowFirst = strtoupper(trim((string) ($row['first_name'] ?? '')));
            $rowLast  = strtoupper(trim((string) ($row['last_name'] ?? '')));

            return ($rowFirst === $first && $rowLast === $last)
                || ($rowFirst === $last && $rowLast === $first);
        };

        $householdNo = trim((string) ($user['household_no'] ?? ''));
        if ($householdNo !== '') {
            $household = $db->table('households')->where('household_no', $householdNo)->get()->getRowArray();
            if ($matchName($household)) {
                $age = $ageFromRow($household);
                if ($age !== null) {
                    return $age;
                }
            }

            $members = $db->table('household_members')->where('household_no', $householdNo)->get()->getResultArray();
            foreach ($members as $member) {
                if ($matchName($member)) {
                    $age = $ageFromRow($member);
                    if ($age !== null) {
                        return $age;
                    }
                }
            }

            $age = $ageFromRow($household);
            if ($age !== null && $household && $first !== '' && strtoupper(trim((string) ($household['first_name'] ?? ''))) === $first) {
                return $age;
            }
        }

        if ($first === '' || $last === '') {
            return null;
        }

        $head = $db->table('households')
            ->where('UPPER(first_name)', $first)
            ->where('UPPER(last_name)', $last)
            ->get()
            ->getRowArray();
        $age = $ageFromRow($head);
        if ($age !== null) {
            return $age;
        }

        $member = $db->table('household_members')
            ->where('UPPER(first_name)', $first)
            ->where('UPPER(last_name)', $last)
            ->get()
            ->getRowArray();

        return $ageFromRow($member);
    }
}

if (! function_exists('resident_census_record')) {
    /**
     * Find the census household or member row that belongs to this user.
     *
     * @return array<string, mixed>|null
     */
    function resident_census_record(?array $user): ?array
    {
        if (! $user) {
            return null;
        }

        $first = strtoupper(trim((string) ($user['first_name'] ?? '')));
        $last  = strtoupper(trim((string) ($user['last_name'] ?? '')));
        $db    = \Config\Database::connect();

        $matchName = static function (?array $row) use ($first, $last): bool {
            if (! $row || $first === '' || $last === '') {
                return false;
            }
            $rowFirst = strtoupper(trim((string) ($row['first_name'] ?? '')));
            $rowLast  = strtoupper(trim((string) ($row['last_name'] ?? '')));

            return ($rowFirst === $first && $rowLast === $last)
                || ($rowFirst === $last && $rowLast === $first);
        };

        $household = null;
        $householdNo = trim((string) ($user['household_no'] ?? ''));
        if ($householdNo !== '') {
            $household = $db->table('households')->where('household_no', $householdNo)->get()->getRowArray();
            if ($matchName($household)) {
                return $household;
            }

            $members = $db->table('household_members')->where('household_no', $householdNo)->get()->getResultArray();
            foreach ($members as $member) {
                if ($matchName($member)) {
                    return $member;
                }
            }

            if ($household && $first !== '' && strtoupper(trim((string) ($household['first_name'] ?? ''))) === $first) {
                return $household;
            }
        }

        if ($first === '' || $last === '') {
            return $household ?: null;
        }

        $head = $db->table('households')
            ->where('UPPER(first_name)', $first)
            ->where('UPPER(last_name)', $last)
            ->get()
            ->getRowArray();
        if ($head) {
            return $head;
        }

        $member = $db->table('household_members')
            ->where('UPPER(first_name)', $first)
            ->where('UPPER(last_name)', $last)
            ->get()
            ->getRowArray();

        return $member ?: ($household ?: null);
    }
}

if (! function_exists('resident_sk_youth')) {
    /** @return array<string, mixed>|null */
    function resident_sk_youth(?array $user): ?array
    {
        if (! $user) {
            return null;
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('sk_youth')) {
            return null;
        }

        $userId = (int) ($user['id'] ?? 0);
        if ($userId > 0 && $db->fieldExists('user_id', 'sk_youth')) {
            $row = $db->table('sk_youth')->where('user_id', $userId)->get()->getRowArray();
            if ($row) {
                return $row;
            }
        }

        $first = strtoupper(trim((string) ($user['first_name'] ?? '')));
        $last  = strtoupper(trim((string) ($user['last_name'] ?? '')));
        if ($first === '' || $last === '') {
            return null;
        }

        return $db->table('sk_youth')
            ->where('UPPER(first_name)', $first)
            ->where('UPPER(last_name)', $last)
            ->get()
            ->getRowArray() ?: null;
    }
}

if (! function_exists('resident_education_profile')) {
    /**
     * Census + SK youth fields used for activity educational eligibility.
     *
     * @return array{age:?int,occupation:string,educational_attainment:string,grade_level:string,educational_background:string,school_detail:string}
     */
    function resident_education_profile(?array $user): array
    {
        $census = resident_census_record($user) ?? [];
        $youth  = resident_sk_youth($user) ?? [];

        return [
            'age'                     => resident_census_age($user) ?? person_age_from_dob($youth['date_of_birth'] ?? null),
            'occupation'              => (string) ($census['occupation'] ?? ''),
            'educational_attainment'  => (string) ($census['educational_attainment'] ?? ''),
            'grade_level'             => (string) ($census['grade_level'] ?? ''),
            'educational_background'  => (string) ($youth['educational_background'] ?? ''),
            'school_detail'           => (string) ($youth['school_detail'] ?? ''),
        ];
    }
}

if (! function_exists('resident_matches_education_group')) {
    function resident_matches_education_group(array $profile, string $group): bool
    {
        $attain = strtolower(trim((string) ($profile['educational_attainment'] ?? '')));
        $background = strtolower(trim((string) ($profile['educational_background'] ?? '')));
        $occupation = strtolower(trim((string) ($profile['occupation'] ?? '')));
        $grade = strtolower(trim((string) ($profile['grade_level'] ?? '')));
        $detail = strtolower(trim((string) ($profile['school_detail'] ?? '')));
        $blob = trim($attain . ' ' . $background . ' ' . $occupation . ' ' . $grade . ' ' . $detail);
        if ($blob === '') {
            return false;
        }

        $has = static fn (string $needle): bool => str_contains($blob, $needle);

        if ($group === 'college') {
            if ($background === 'college' || $attain === 'college level' || $has('college student') || $has('college level')) {
                return true;
            }
            if (($has('college') && (preg_match('/\b(1st|2nd|3rd|4th|first|second|third|fourth)\s+year\b/', $blob) === 1 || $has('freshman') || $has('sophomore')))) {
                return true;
            }
            if (($has('college') && $has('student')) && ! $has('college graduate') && ! $has('post graduate')) {
                return true;
            }

            return false;
        }

        if ($group === 'high_school') {
            if (in_array($background, ['junior high school', 'senior high school'], true) || $attain === 'high school level') {
                return true;
            }
            if ($has('junior high') || $has('senior high') || $has('high school student') || $has('high school level')) {
                return true;
            }
            if (preg_match('/\bgrade\s*(7|8|9|10|11|12|seven|eight|nine|ten|eleven|twelve)\b/', $blob) === 1) {
                return true;
            }
            if ($has('high school') && $has('student') && ! $has('high school graduate')) {
                return true;
            }

            return false;
        }

        if ($group === 'osy') {
            return $has('out-of-school')
                || $has('out of school')
                || (bool) preg_match('/\bosy\b/', $blob)
                || $has('alternative learning');
        }

        if ($group === 'graduating') {
            return $has('graduating')
                || (bool) preg_match('/\bgrade\s*(12|twelve)\b/', $blob)
                || (bool) preg_match('/\b(4th|fourth)\s+year\b/', $blob)
                || (bool) preg_match('/\b(last|final)\s+year\b/', $blob)
                || $has('senior year');
        }

        return false;
    }
}

if (! function_exists('official_display_name')) {
    /**
     * Return a real official name, or empty when the role is vacant / a placeholder.
     */
    function official_display_name(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '';
        }

        $normalized = strtoupper(preg_replace('/\s+/', ' ', $name) ?? $name);
        if (in_array($normalized, ['PUNONG BARANGAY', 'BARANGAY SECRETARY', 'SECRETARY', 'CAPTAIN'], true)) {
            return '';
        }

        return $name;
    }
}

if (! function_exists('sanitize_document_html')) {
    function sanitize_document_html(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $html = preg_replace('/<(script|style|iframe|object|embed)[^>]*>.*?<\/\1>/is', '', $html) ?? $html;
        $html = strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><span><div>');
        $html = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace_callback('/style\s*=\s*("|\')(.*?)\1/i', static function (array $match): string {
            if (preg_match('/text-align\s*:\s*(left|right|center|justify)/i', $match[2], $align) === 1) {
                return 'style="text-align:' . strtolower($align[1]) . ';"';
            }

            return '';
        }, $html) ?? $html;

        return $html;
    }
}
