<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('health');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'active', 'password.changed'])->group(function (): void {
    Route::get('/', fn () => redirect()->route('dashboard'));

    Route::get('/dashboard', fn () => view('dashboard.index'))->name('dashboard');

    Route::get('/password', [PasswordController::class, 'show'])->name('password.change');
    Route::post('/password', [PasswordController::class, 'update'])->name('password.update');
});
