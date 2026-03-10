<?php

namespace App\Http\Middleware;

use App\Services\CartHoldService;
use App\Http\Resources\UserResource;
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
        $user = $request->user();
        $canAccessAdmin = $user?->isAdmin() ?? false;

        $publicUrls = [
            'eventsIndex' => Route::has('events.index') ? route('events.index') : '/events',
        ];
        
        $authUrls = [];
        if ($user !== null) {
            $authUrls = [
                'cart' => Route::has('cart.index') ? route('cart.index') : '/cart',
                'cartHoldsStore' => Route::has('cart.holds.store') ? route('cart.holds.store') : '/cart/hold',
                'cartHoldsBase' => '/cart/hold',
                'cartHoldsUpdateBase' => '/cart/hold',
                'checkout' => Route::has('checkout.index') ? route('checkout.index') : '/checkout',
                'orders' => Route::has('orders.index') ? route('orders.index') : '/orders',
                'profile' => $canAccessAdmin
                    ? (Route::has('admin.user.settings.profile') ? route('admin.user.settings.profile') : '/admin/user/settings/profile')
                    : (Route::has('profile.edit') ? route('profile.edit') : '/user/settings/profile'),
                'settingsProfile' => $canAccessAdmin
                    ? (Route::has('admin.user.settings.profile') ? route('admin.user.settings.profile') : '/admin/user/settings/profile')
                    : (Route::has('profile.edit') ? route('profile.edit') : '/user/settings/profile'),
                'settingsPassword' => $canAccessAdmin
                    ? (Route::has('admin.user.settings.password') ? route('admin.user.settings.password') : '/admin/user/settings/password')
                    : (Route::has('user-password.edit') ? route('user-password.edit') : '/user/settings/password'),
                'settingsAppearance' => $canAccessAdmin
                    ? (Route::has('admin.user.settings.appearance') ? route('admin.user.settings.appearance') : '/admin/user/settings/appearance')
                    : (Route::has('appearance.edit') ? route('appearance.edit') : '/user/settings/appearance'),
                'settingsTwoFactor' => $canAccessAdmin
                    ? (Route::has('admin.user.settings.two-factor') ? route('admin.user.settings.two-factor') : '/admin/user/settings/two-factor')
                    : (Route::has('two-factor.show') ? route('two-factor.show') : '/user/settings/two-factor'),
                'settingsProfileUpdate' => $canAccessAdmin
                    ? (Route::has('admin.user.settings.profile.update') ? route('admin.user.settings.profile.update') : '/admin/user/settings/profile')
                    : (Route::has('profile.update') ? route('profile.update') : '/user/settings/profile'),
                'settingsProfileDelete' => $canAccessAdmin
                    ? (Route::has('admin.user.settings.profile.destroy') ? route('admin.user.settings.profile.destroy') : '/admin/user/settings/profile')
                    : (Route::has('profile.destroy') ? route('profile.destroy') : '/user/settings/profile'),
                'settingsPasswordUpdate' => $canAccessAdmin
                    ? (Route::has('admin.user.settings.password.update') ? route('admin.user.settings.password.update') : '/admin/user/settings/password')
                    : (Route::has('user-password.update') ? route('user-password.update') : '/user/settings/password'),
            ];
        }

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
                'user' => $user === null ? null : new UserResource($user),
                'canAccessAdmin' => $canAccessAdmin,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'urls' => array_merge($publicUrls, $adminUrls, $authUrls),
            'flash' => [
                'message' => $request->session()->get('message'),
            ],
            'cart' => $user === null
                ? null
                : fn () => app(CartHoldService::class)->buildCartPayload($user),
        ];
    }
}
