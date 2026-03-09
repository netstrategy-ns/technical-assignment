<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\HoldController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('events', [EventController::class, 'index'])->name('events.index');
Route::get('events/{event:slug}', [EventController::class, 'show'])->name('events.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('cart/hold', [HoldController::class, 'store'])->name('cart.holds.store');
    Route::patch('cart/hold/{hold}', [HoldController::class, 'update'])->name('cart.holds.update');
    Route::delete('cart/hold/{hold}', [HoldController::class, 'destroy'])->name('cart.holds.destroy');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
