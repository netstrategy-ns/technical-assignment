<?php

use App\Http\Controllers\Settings\AppearanceController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/user/settings/profile');

    Route::get('user/settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('user/settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('user/settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('user/settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

    Route::put('user/settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::get('user/settings/appearance', [AppearanceController::class, 'edit'])->name('appearance.edit');

    Route::get('user/settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');
});
