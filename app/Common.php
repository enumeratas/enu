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
            $subject = notification_events_url($body);
        } elseif ($type === 'household_approved' || $type === 'household_rejected') {
            $subject = $role === 'council' ? '/council/census' : '/resident/profile';
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
    function notification_events_url(string $body): string
    {
        $body = preg_replace('/\s*\[event_id:\d+\]/', '', $body) ?? $body;
        if (preg_match('/\b([A-Z][a-z]{2,9} \d{1,2}, \d{4})\b/', $body, $match) === 1) {
            $timestamp = strtotime($match[1]);
            if ($timestamp !== false) {
                return '/events?year=' . date('Y', $timestamp) . '&month=' . (int) date('n', $timestamp);
            }
        }

        return '/events';
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
            'resident' => ['clearance', 'profile', 'chatbot', 'support-ticket', 'concerns', 'activities', 'sk-activities', 'sk-profiling', 'census-update'],
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
