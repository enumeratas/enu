<?php

namespace App\Filters;

use App\Libraries\PiiGuard;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PiiOutputFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (! session()->get('user_id')) {
            return $response;
        }

        $path = strtolower($request->getUri()->getPath());
        foreach (['/pii/', '/uploads/', '/household-files/', '/api/', '/login', '/signup', '/forgot-password', '/verify-email', '/export', '/download', '/print'] as $skip) {
            if (str_contains($path, $skip)) {
                return $response;
            }
        }

        $contentType = strtolower($response->getHeaderLine('Content-Type'));
        if ($contentType !== '' && ! str_contains($contentType, 'text/html') && ! str_contains($contentType, 'xhtml')) {
            return $response;
        }

        if (PiiGuard::showsPlainText()) {
            return $response;
        }

        $body = $response->getBody();
        if (! is_string($body) || $body === '' || ! str_contains($body, '<')) {
            return $response;
        }

        $response->setBody(PiiGuard::redactHtml($body));

        return $response;
    }
}
