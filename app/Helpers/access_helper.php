<?php

if (! function_exists('session_role')) {
    function session_role(): string
    {
        return strtolower(trim((string) (session()->get('role') ?? '')));
    }
}

if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        return session_role() === 'admin';
    }
}

if (! function_exists('can_role')) {
    function can_role(string ...$roles): bool
    {
        $current = session_role();
        if ($current === 'admin') {
            return true;
        }

        $allowed = array_map(static fn(string $role): string => strtolower(trim($role)), $roles);

        return $current !== '' && in_array($current, $allowed, true);
    }
}

if (! function_exists('route_prefix')) {
    function route_prefix(): string
    {
        $role = session_role();

        return $role !== '' ? $role : 'resident';
    }
}

if (! function_exists('staff_view_folder')) {
    function staff_view_folder(?string $role = null): string
    {
        $role = strtolower(trim((string) ($role ?? session_role())));

        return $role === 'admin' ? 'secretary' : $role;
    }
}
