<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    /**
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        // Se is_admin è true redirect verso la admin dashboard
        if ($request->user()?->isAdmin()) {
            return redirect()->to(Route::has('admin.dashboard') ? route('admin.dashboard') : '/admin/dashboard');
        }

        $requestedRedirect = $this->resolveRequestedRedirect($request);
        if ($requestedRedirect !== null) {
            return redirect()->to($requestedRedirect);
        }

        return redirect()->intended(route('home'));
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
        if (
            $parsed === false ||
            !array_key_exists('path', $parsed) ||
            array_key_exists('scheme', $parsed) ||
            array_key_exists('host', $parsed)
        ) {
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
