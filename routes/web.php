<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HoldController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EventQueueController;
use App\Http\Controllers\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Account\TicketController as AccountTicketController;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/featured', [EventController::class, 'featured'])->name('events.featured');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
Route::post('/holds', [HoldController::class, 'store'])
    ->middleware('auth')
    ->name('holds.store');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('auth')
    ->name('checkout.store');

Route::middleware('auth')->prefix('account')->group(function () {
    Route::get('/orders', [AccountOrderController::class, 'index'])->name('account.orders.index');
    Route::get('/orders/{order}', [AccountOrderController::class, 'show'])->name('account.orders.show');
    Route::get('/tickets', [AccountTicketController::class, 'index'])->name('account.tickets.index');
});

require __DIR__ . '/settings.php';
