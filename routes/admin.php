<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\EventCategoryController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\QueueEntryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Settings\AppearanceController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin routes: middleware web, auth, verified, admin | prefix admin | name admin.*
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, '__invoke'])->name('dashboard');
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/event-categories', [EventCategoryController::class, 'index'])->name('event-categories.index');
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/queue-entries', [QueueEntryController::class, 'index'])->name('queue-entries.index');

        Route::get('/user/settings/profile', [ProfileController::class, 'edit'])->name('user.settings.profile');
        Route::patch('/user/settings/profile', [ProfileController::class, 'update'])->name('user.settings.profile.update');
        Route::delete('/user/settings/profile', [ProfileController::class, 'destroy'])->name('user.settings.profile.destroy');

        Route::get('/user/settings/password', [PasswordController::class, 'edit'])->name('user.settings.password');
        Route::put('/user/settings/password', [PasswordController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('user.settings.password.update');

        Route::get('/user/settings/appearance', [AppearanceController::class, 'edit'])->name('user.settings.appearance');

        Route::get('/user/settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
            ->name('user.settings.two-factor');
    });
