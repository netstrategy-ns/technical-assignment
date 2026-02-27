<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HoldController;
use App\Http\Controllers\CheckoutController;

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


require __DIR__ . '/settings.php';
