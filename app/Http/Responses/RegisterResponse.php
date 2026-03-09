<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request)
    {
        $requestedRedirect = $this->resolveRequestedRedirect($request);
        if ($requestedRedirect !== null) {
            return redirect()->to($requestedRedirect);
        }

        return redirect()->to(route('home'));
    }

    private function resolveRequestedRedirect(Request $request): ?string
    {
        $redirect = $this->sanitizeAuthRedirect($request->input('auth_redirect'));
        if ($redirect !== null) {
            return $redirect;
        }

        $sessionIntended = $request->session()->pull('url.intended');
        return $this->sanitizeAuthRedirect($sessionIntended);
    }

    private function sanitizeAuthRedirect($redirect): ?string
    {
        if (!is_string($redirect)) {
            return null;
        }

        $redirect = trim($redirect);
        if ($redirect === '') {
            return null;
        }

        $parsed = parse_url($redirect);
        if ($parsed === false || !array_key_exists('path', $parsed)) {
            return null;
        }

        $path = $parsed['path'];
        if (!is_string($path) || $path === '' || !str_starts_with($path, '/')) {
            return null;
        }

        if ($path === '/login' || $path === '/register') {
            return '/';
        }

        if ($path === '/dashboard') {
            return null;
        }

        if (str_starts_with($path, '/admin')) {
            return null;
        }

        $query = array_key_exists('query', $parsed) ? (string) $parsed['query'] : '';
        return $query === '' ? $path : $path . '?' . $query;
    }
}

