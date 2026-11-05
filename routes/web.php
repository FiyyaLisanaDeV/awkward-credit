<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Web\DebiturController;
use App\Http\Controllers\Web\PermohonanController;
use App\Http\Controllers\Web\SimulasiController;
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

    Route::middleware('permission:permohonan.create')->group(function (): void {
        Route::get('/debitur/create', [DebiturController::class, 'create'])->name('debitur.create');
        Route::get('/permohonan/create', [PermohonanController::class, 'create'])->name('permohonan.create');
    });

    Route::middleware('permission:permohonan.view')->group(function (): void {
        Route::get('/debitur', [DebiturController::class, 'index'])->name('debitur.index');
        Route::get('/debitur/{debitur}', [DebiturController::class, 'show'])->name('debitur.show');
        Route::get('/permohonan', [PermohonanController::class, 'index'])->name('permohonan.index');
        Route::get('/permohonan/{permohonan}', [PermohonanController::class, 'show'])->name('permohonan.show');
    });

    Route::middleware('permission:simulasi.create')->group(function (): void {
        Route::get('/simulasi', [SimulasiController::class, 'index'])->name('simulasi.index');
        Route::get('/simulasi/create', [SimulasiController::class, 'create'])->name('simulasi.create');
        Route::get('/simulasi/bandingkan', [SimulasiController::class, 'bandingkan'])->name('simulasi.bandingkan');
        Route::get('/simulasi/{simulasi}', [SimulasiController::class, 'show'])->name('simulasi.show');
    });
});
