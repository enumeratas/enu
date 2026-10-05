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
