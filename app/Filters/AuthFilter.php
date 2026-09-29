<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter
 *
 * Ensures the user is logged in (has an active session).
 * Apply to any route group that requires authentication.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('user_id')) {
            // Session found — all good.
            return;
        }

        // Truly unauthenticated.
        if (
            $request->isAJAX() ||
            strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest' ||
            str_contains(strtolower($request->getHeaderLine('Accept')), 'application/json')
        ) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Session expired. Please log in again.',
                ]);
        }

        return redirect()->to('/login')->with('error', 'Please log in to continue.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing needed after
    }
}
