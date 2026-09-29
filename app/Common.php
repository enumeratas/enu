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
