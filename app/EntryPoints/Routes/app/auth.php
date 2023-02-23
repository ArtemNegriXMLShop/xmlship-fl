<?php

use App\EntryPoints\Http\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::namespace('Auth')->prefix('auth')->group(static function () {
    Route::post('logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum'])->name('auth.logout');

    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot.password');
    Route::post('new-password', [AuthController::class, 'newPassword'])->name('auth.new.password');
});
