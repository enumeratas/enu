<?php

namespace App\Libraries;

/**
 * SessionHelper
 *
 * Centralises the logic for role-scoped session cookie names.
 *
 * WHY THIS EXISTS
 * ───────────────
 * A browser stores exactly ONE cookie per (host, name, path) tuple.
 * If every role uses the same cookie name ("bis2_ci_session"), the second
 * user to log in on the same browser profile overwrites the first
 * user's session cookie, effectively logging them out.
 *
 * By assigning a distinct cookie name to each role we ensure that
 * captain, secretary, resident, and sk sessions are stored as four
 * separate cookies — so all four users can be logged in simultaneously
 * on the same device and browser.
 *
 * HOW TO USE
 * ──────────
 * Call SessionHelper::configureForRole($role) BEFORE the CodeIgniter
 * Session service is first accessed on a given request.  After that
 * point the session singleton is locked in and the cookie name cannot
 * be changed for that request.
 *
 * Typical call sites:
 *   • AuthController::login()   — called after credentials are verified,
 *                                 before session()->regenerate()
 *   • AuthFilter::before()      — called before session()->get('user_id'),
 *                                 using the role inferred from the URL path
 *   • RoleFilter::before()      — same as AuthFilter
 *   • AuthController::logout()  — called before session()->destroy(),
 *                                 using the role stored in $_COOKIE
 */
class SessionHelper
{
    /**
     * Map of role → cookie name.
     *
     * Keep names short, lowercase, and using only [0-9a-z_-] characters
     * (CodeIgniter requirement for cookie names).
     */
    private const COOKIE_NAMES = [
        'admin'     => 'bis2_sess_admin',
        'captain'   => 'bis2_sess_captain',
        'secretary' => 'bis2_sess_secretary',
        'resident'  => 'bis2_sess_resident',
        'sk'        => 'bis2_sess_sk',
        'council'   => 'bis2_sess_council',
    ];

    /** Fallback used when the role is unknown or not in the map. */
    private const DEFAULT_COOKIE = 'bis2_ci_session';

    /** How long a checked Remember me box keeps the account signed in. */
    public const REMEMBER_SECONDS = 2592000;

    /** Server lifetime for a login that ends when the browser closes. */
    public const BROWSER_SESSION_GC = 43200;

    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Returns the session cookie name for a given role.
     */
    public static function cookieNameForRole(string $role): string
    {
        return self::COOKIE_NAMES[strtolower(trim($role))] ?? self::DEFAULT_COOKIE;
    }

    /**
     * Patches the Session config so the correct cookie name is used for
     * the given role, then warms up the session singleton.
     *
     * Must be called BEFORE any code accesses session() on this request.
     *
     * @param  string $role  e.g. 'captain', 'secretary', 'resident', 'sk'
     */
    public static function configureForRole(string $role): void
    {
        $cookieName = self::cookieNameForRole($role);

        // Patch the live config object.  CodeIgniter reads $config->cookieName
        // when it initialises the Session service, so we must do this before
        // the service is first resolved.
        /** @var \Config\Session $sessionConfig */
        $sessionConfig = config('Session');
        $sessionConfig->cookieName = $cookieName;
    }

    /**
     * Infers the role from the current URL path and configures the session.
     *
     * Route groups are prefixed with the role name:
     *   /captain/...   /secretary/...   /resident/...   /sk/...
     *
     * If no known role is found in the path the session is left with its
     * default configuration (the login page, public pages, etc.).
     * MIGRATION FALLBACK
     * ──────────────────
     * If the role-scoped cookie (e.g. bis2_sess_secretary) is not present
     * but the legacy default cookie (bis2_ci_session) IS present, we fall
     * back to the default cookie so that users who were already logged in
     * before this change was deployed don't get silently logged out.
     * On their next login they'll receive the new scoped cookie automatically.
     *
     * @param  string $uriPath  e.g. '/captain/dashboard'
     * @return string|null      The detected role, or null if not found.
     */
    /**
     * Select the session cookie for this request before CodeIgniter starts
     * the session. Role pages use that role's cookie. Logout uses ?role=
     * so one account can sign out without clearing the others.
     */
    public static function configureForRequest(string $requestUri): void
    {
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $path = preg_replace('#/index\.php#', '', $path) ?? $path;
        $path = trim($path, '/');

        $query = [];
        parse_str((string) (parse_url($requestUri, PHP_URL_QUERY) ?? ''), $query);
        $role = strtolower(trim((string) ($query['role'] ?? '')));

        $detected = null;

        if (
            ($path === 'logout' || str_ends_with($path, '/logout'))
            && isset(self::COOKIE_NAMES[$role])
        ) {
            self::configureForRole($role);
            $detected = $role;
        } else {
            $detected = self::configureFromPath('/' . $path);
        }

        self::applyLifetime($detected);
    }

    public static function rememberCookieName(string $role): string
    {
        return 'bis_remember_' . strtolower(trim($role));
    }

    /**
     * Checked Remember me keeps the session cookie for 30 days.
     * An unchecked box ends the sign-in when the browser closes.
     */
    public static function setLifetime(bool $remember): void
    {
        /** @var \Config\Session $sessionConfig */
        $sessionConfig = config('Session');

        if ($remember) {
            $sessionConfig->expiration = self::REMEMBER_SECONDS;

            return;
        }

        $sessionConfig->expiration = 0;
        ini_set('session.gc_maxlifetime', (string) self::BROWSER_SESSION_GC);
    }

    public static function applyLifetime(?string $role): void
    {
        $remembered = $role !== null
            && isset(self::COOKIE_NAMES[$role])
            && (($_COOKIE[self::rememberCookieName($role)] ?? '') === '1');

        self::setLifetime($remembered);
    }

    public static function configureFromPath(string $uriPath): ?string
    {
        $path = ltrim($uriPath, '/');

        foreach (array_keys(self::COOKIE_NAMES) as $role) {
            // Match "/captain/..." or "/captain" exactly
            if ($path === $role || str_starts_with($path, $role . '/')) {
                $scopedCookie = self::cookieNameForRole($role);

                // If the scoped cookie exists, use it.
                if (isset($_COOKIE[$scopedCookie])) {
                    self::configureForRole($role);
                    return $role;
                }

                // The admin account can open every role's pages with the admin session.
                if ($role !== 'admin' && isset($_COOKIE[self::COOKIE_NAMES['admin']])) {
                    self::configureForRole('admin');

                    return 'admin';
                }

                // Migration fallback: if only the legacy default cookie exists,
                // use that so existing sessions remain valid until re-login.
                if (isset($_COOKIE[self::DEFAULT_COOKIE])) {
                    // Leave config at its default — already set to DEFAULT_COOKIE.
                    return $role;
                }

                // No cookie at all (fresh visit or already expired) — use the
                // scoped name so login will create the correct new cookie.
                self::configureForRole($role);
                return $role;
            }
        }

        return null;
    }

    /**
     * Detects the role from whichever role-scoped cookie is present in the
     * current request, and configures the session for that role.
     *
     * Used during logout when the URL path may not carry the role prefix
     * (e.g. GET /logout) and for routes like /uploads/... that have no
     * role prefix.
     *
     * Falls back to the default cookie name if no scoped cookie is found,
     * so users whose session was created before the scoped-cookie scheme
     * was introduced continue to work.
     *
     * @return string|null  The detected role, or null if none found.
     */
    public static function configureFromCookie(): ?string
    {
        // First try every role-scoped cookie.
        foreach (self::COOKIE_NAMES as $role => $cookieName) {
            if (isset($_COOKIE[$cookieName])) {
                self::configureForRole($role);
                return $role;
            }
        }

        // Fallback: legacy default cookie (session created before scoped scheme,
        // or login happened on a path where configureForRole was not called).
        if (isset($_COOKIE[self::DEFAULT_COOKIE])) {
            // Leave the Session config at its default — already points to DEFAULT_COOKIE.
            return null;  // role unknown, but session will be found
        }

        return null;
    }

    /**
     * Returns all defined cookie names (useful for "log out everywhere").
     *
     * @return string[]
     */
    public static function allCookieNames(): array
    {
        return array_values(self::COOKIE_NAMES);
    }
}
