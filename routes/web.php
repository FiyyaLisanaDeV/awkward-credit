<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Web\ApprovalController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DebiturController;
use App\Http\Controllers\Web\DokumenController;
use App\Http\Controllers\Web\KreditController;
use App\Http\Controllers\Web\LedgerController;
use App\Http\Controllers\Web\PermohonanController;
use App\Http\Controllers\Web\SettingController;
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

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/password', [PasswordController::class, 'show'])->name('password.change');
    Route::post('/password', [PasswordController::class, 'update'])->name('password.update');

    Route::middleware('permission:permohonan.create')->group(function (): void {
        Route::get('/debitur/create', [DebiturController::class, 'create'])->name('debitur.create');
        Route::get('/permohonan/create', [PermohonanController::class, 'create'])->name('permohonan.create');
    });

    Route::middleware('permission:kredit.cairkan')->group(function (): void {
        Route::get('/kredit/cairkan/{permohonan}', [KreditController::class, 'create'])->name('kredit.cairkan.create');
        Route::post('/kredit/cairkan/{permohonan}', [KreditController::class, 'store'])->name('kredit.cairkan.store');
    });

    Route::middleware('permission:dokumen.view')->group(function (): void {
        Route::get('/dokumen', [DokumenController::class, 'index'])->name('dokumen.index');
        Route::get('/dokumen/{dokumen}', [DokumenController::class, 'show'])->name('dokumen.show');
    });

    Route::middleware('permission:dokumen.generate_draft')->group(function (): void {
        Route::post('/dokumen/{permohonan}/generate', [DokumenController::class, 'generate'])->name('dokumen.generate');
    });

    Route::middleware('permission:dokumen.sign')->group(function (): void {
        Route::post('/dokumen/{dokumen}/sign', [DokumenController::class, 'sign'])->name('dokumen.sign');
    });

    Route::middleware('permission:ledger.view')->group(function (): void {
        Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger.index');
        Route::get('/ledger/verify', [LedgerController::class, 'verify'])->name('ledger.verify');
        Route::get('/ledger/{ledger}', [LedgerController::class, 'show'])->name('ledger.show');
    });

    Route::middleware('permission:permohonan.view')->group(function (): void {
        Route::get('/debitur', [DebiturController::class, 'index'])->name('debitur.index');
        Route::get('/debitur/{debitur}', [DebiturController::class, 'show'])->name('debitur.show');
        Route::get('/permohonan', [PermohonanController::class, 'index'])->name('permohonan.index');
        Route::get('/permohonan/{permohonan}', [PermohonanController::class, 'show'])->name('permohonan.show');
        Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.inbox');
        Route::get('/approval/{permohonan}', [ApprovalController::class, 'show'])->name('approval.show');
        Route::get('/kredit', [KreditController::class, 'index'])->name('kredit.index');
        Route::get('/kredit/{kredit}', [KreditController::class, 'show'])->name('kredit.show');
    });

    Route::middleware('permission:simulasi.create')->group(function (): void {
        Route::get('/simulasi', [SimulasiController::class, 'index'])->name('simulasi.index');
        Route::get('/simulasi/create', [SimulasiController::class, 'create'])->name('simulasi.create');
        Route::get('/simulasi/bandingkan', [SimulasiController::class, 'bandingkan'])->name('simulasi.bandingkan');
        Route::get('/simulasi/{simulasi}', [SimulasiController::class, 'show'])->name('simulasi.show');
    });

    Route::middleware('permission:setting.view')->group(function (): void {
        Route::get('/pengaturan', [SettingController::class, 'index'])->name('admin.index');
        Route::get('/pengaturan/persetujuan', [SettingController::class, 'inbox'])->name('admin.inbox');
        Route::get('/pengaturan/riwayat', [SettingController::class, 'history'])->name('admin.history');
        Route::get('/pengaturan/{section}', [SettingController::class, 'section'])->name('admin.section');
    });

    Route::middleware('permission:setting.edit')->group(function (): void {
        Route::post('/pengaturan/{section}', [SettingController::class, 'store'])->name('admin.store');
        Route::patch('/pengaturan/revisi/{revision}', [SettingController::class, 'update'])->name('admin.update');
        Route::post('/pengaturan/revisi/{revision}/submit', [SettingController::class, 'submit'])->name('admin.submit');
    });

    Route::middleware('permission:setting.approve')->group(function (): void {
        Route::post('/pengaturan/revisi/{revision}/approve', [SettingController::class, 'approve'])->name('admin.approve');
        Route::post('/pengaturan/revisi/{revision}/reject', [SettingController::class, 'reject'])->name('admin.reject');
    });

    Route::middleware('permission:setting.apply')->group(function (): void {
        Route::post('/pengaturan/revisi/{revision}/apply', [SettingController::class, 'apply'])->name('admin.apply');
    });
});
