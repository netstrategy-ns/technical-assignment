<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HoldController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\QueueController;
use App\Http\Middleware\EnsureQueueAccess;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Hold routes (with queue access check)
    Route::post('/events/{event:slug}/holds', [HoldController::class, 'store'])
        ->middleware(EnsureQueueAccess::class)
        ->name('holds.store');
    Route::delete('/holds/{hold}', [HoldController::class, 'destroy'])
        ->name('holds.destroy');

    // Checkout routes (with queue access check)
    Route::get('/events/{event:slug}/checkout', [CheckoutController::class, 'show'])
        ->middleware(EnsureQueueAccess::class)
        ->name('checkout.show');
    Route::post('/events/{event:slug}/checkout', [CheckoutController::class, 'store'])
        ->middleware(EnsureQueueAccess::class)
        ->name('checkout.store');

    // Order routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Queue routes
    Route::post('/events/{event:slug}/queue', [QueueController::class, 'store'])->name('queue.store');
    Route::get('/events/{event:slug}/queue/status', [QueueController::class, 'show'])->name('queue.status');
});

require __DIR__.'/settings.php';
