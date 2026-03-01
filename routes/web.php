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

Route::inertia('/cart', 'Cart')
    ->middleware('auth')
    ->name('cart');

Route::inertia('/account', 'Account')
    ->middleware('auth')
    ->name('account');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/featured', [EventController::class, 'featured'])->name('events.featured');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
Route::post('/holds', [HoldController::class, 'store'])
    ->middleware('auth')
    ->name('holds.store');
Route::get('/holds', [HoldController::class, 'index'])
    ->middleware('auth')
    ->name('holds.index');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('auth')
    ->name('checkout.store');
Route::post('/queue/enter', [EventQueueController::class, 'enter'])
    ->middleware('auth')
    ->name('queue.enter');
Route::get('/queue/status', [EventQueueController::class, 'status'])
    ->middleware('auth')
    ->name('queue.status');

Route::middleware('auth')->prefix('account')->group(function () {
    Route::get('/orders', [AccountOrderController::class, 'index'])->name('account.orders.index');
    Route::get('/orders/{order}', [AccountOrderController::class, 'show'])->name('account.orders.show');
    Route::get('/tickets', [AccountTicketController::class, 'index'])->name('account.tickets.index');
});

require __DIR__ . '/settings.php';
