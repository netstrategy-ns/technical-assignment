<?php

use App\Http\Controllers\Events\EventController;
use App\Http\Controllers\Checkout\CartController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Events\EventQueueController;
use App\Http\Controllers\Checkout\HoldController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Orders\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('admin')->get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->name('dashboard');

Route::get('events', [EventController::class, 'index'])->name('events.index');
Route::get('events/{event:slug}', [EventController::class, 'show'])->name('events.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('events/{eventId}/queue/join', [EventQueueController::class, 'join'])->whereNumber('eventId')->name('events.queue.join');
    Route::get('events/{eventId}/queue/status', [EventQueueController::class, 'status'])->whereNumber('eventId')->name('events.queue.status');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('cart/hold', [HoldController::class, 'store'])->name('cart.holds.store');
    Route::patch('cart/hold/{hold}', [HoldController::class, 'update'])->name('cart.holds.update');
    Route::delete('cart/hold/{hold}', [HoldController::class, 'destroy'])->name('cart.holds.destroy');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});


require __DIR__.'/settings.php';
