<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * RoleFilter
 *
 * Checks that the logged-in user has one of the allowed roles.
 *
 * Usage in Routes.php:
 *   'filter' => 'auth|role:captain'
 *   'filter' => 'auth|role:captain,secretary'
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userRole = strtolower(trim((string) session()->get('role')));
        $allowedRoles = array_map(
            static fn($role) => strtolower(trim((string) $role)),
            (array) $arguments
        );

        if ($userRole === 'admin') {
            return;
        }

        // $arguments is an array of allowed roles passed after the colon
        if (empty($allowedRoles) || ! in_array($userRole, $allowedRoles, true)) {
            // Return JSON for AJAX / API requests instead of redirecting.
            // A redirect causes the browser to follow the 302 to /login and
            // return login-page HTML as the response body to the AJAX caller.
            if (
                $request->isAJAX() ||
                strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest' ||
                str_contains(strtolower($request->getHeaderLine('Accept')), 'application/json')
            ) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON([
                        'success' => false,
                        'message' => 'You do not have permission to access that resource.',
                    ]);
            }

            // A signed-in user who opens another role's page should stay
            // signed in. Sending them to /login looks like the session ended.
            if ($userRole !== '') {
                return redirect()->to('/' . $userRole . '/dashboard')
                    ->with('error', 'You do not have permission to access that page.');
            }

            return redirect()->to('/login')->with('error', 'You do not have permission to access that page.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing needed after
    }
}
