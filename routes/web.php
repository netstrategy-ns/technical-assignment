<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\HoldController;
use App\Http\Controllers\Frontend\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('events', [EventController::class, 'index'])->name('events.index');
Route::get('events/{event:slug}', [EventController::class, 'show'])->name('events.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('cart/hold', [HoldController::class, 'store'])->name('cart.holds.store');
    Route::patch('cart/hold/{hold}', [HoldController::class, 'update'])->name('cart.holds.update');
    Route::delete('cart/hold/{hold}', [HoldController::class, 'destroy'])->name('cart.holds.destroy');
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
