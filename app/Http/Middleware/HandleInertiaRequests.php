<?php

namespace App\Http\Middleware;

use App\Services\CartHoldService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;
use Laravel\Fortify\Features;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $canAccessAdmin = $request->user()?->isAdmin() ?? false;

        $publicUrls = [
            'eventsIndex' => Route::has('events.index') ? route('events.index') : '/events',
            'cart' => Route::has('cart.index') ? route('cart.index') : '/cart',
            'cartHoldsStore' => Route::has('cart.holds.store') ? route('cart.holds.store') : '/cart/hold',
            'cartHoldsBase' => '/cart/hold',
            'checkout' => Route::has('checkout.index') ? route('checkout.index') : '/checkout',
            'orders' => Route::has('orders.index') ? route('orders.index') : '/orders',
        ];

        $adminUrls = [];
        // Evitiamo di esporre le route admin al frontend e a utenti non admin
        if ($canAccessAdmin) {
            $adminUrls = [
                'adminDashboard' => Route::has('admin.dashboard') ? route('admin.dashboard') : '/admin/dashboard',
                'adminStatistics' => Route::has('admin.statistics') ? route('admin.statistics') : '/admin/statistics',
                'adminUsers' => Route::has('admin.users') ? route('admin.users') : '/admin/users',
                'adminEventsBase' => Route::has('admin.events.index') ? route('admin.events.index') : '/admin/events',
            ];
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'canRegister' => Features::enabled(Features::registration()),
            'auth' => [
                'user' => $request->user(),
                'canAccessAdmin' => $canAccessAdmin,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'urls' => array_merge($publicUrls, $adminUrls),
            'flash' => [
                'message' => $request->session()->get('message'),
            ],
            'cart' => $request->user() === null
                ? null
                : fn () => app(CartHoldService::class)->buildCartPayload($request->user()),
        ];
    }
}
