<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AnalisisController;
use App\Http\Controllers\Api\V1\ApprovalController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DebiturController;
use App\Http\Controllers\Api\V1\KreditController;
use App\Http\Controllers\Api\V1\LedgerController;
use App\Http\Controllers\Api\V1\PermohonanController;
use App\Http\Controllers\Api\V1\SimulasiController;
use App\Http\Controllers\Api\V1\SlikController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        Route::apiResource('debitur', DebiturController::class)->except(['destroy'])->names('api.debitur');
        Route::post('debitur/{debitur}/aset', [DebiturController::class, 'storeAset']);
        Route::patch('aset/{aset}', [DebiturController::class, 'updateAset']);
        Route::delete('aset/{aset}', [DebiturController::class, 'destroyAset']);

        Route::apiResource('permohonan', PermohonanController::class)->except(['destroy'])->names('api.permohonan');
        Route::post('permohonan/{permohonan}/submit', [PermohonanController::class, 'submit']);

        Route::get('permohonan/{permohonan}/slik', [SlikController::class, 'index']);
        Route::post('permohonan/{permohonan}/slik', [SlikController::class, 'store']);
        Route::delete('slik/{slik}', [SlikController::class, 'destroy']);

        Route::prefix('permohonan/{permohonan}/analisis')->group(function (): void {
            Route::get('neraca', [AnalisisController::class, 'neracaShow']);
            Route::put('neraca', [AnalisisController::class, 'neracaUpdate']);
            Route::get('laba-rugi', [AnalisisController::class, 'labaRugiShow']);
            Route::put('laba-rugi', [AnalisisController::class, 'labaRugiUpdate']);
            Route::get('kapasitas', [AnalisisController::class, 'kapasitasShow']);
            Route::post('kapasitas/compute', [AnalisisController::class, 'kapasitasCompute']);
            Route::get('scoring', [AnalisisController::class, 'scoringShow']);
            Route::post('scoring/compute', [AnalisisController::class, 'scoringCompute']);
            Route::get('red-flags', [AnalisisController::class, 'redFlags']);
            Route::post('rekomendasi', [AnalisisController::class, 'rekomendasiGenerate']);
        });

        Route::get('simulasi', [SimulasiController::class, 'index'])->name('api.simulasi.index');
        Route::post('simulasi', [SimulasiController::class, 'store'])->name('api.simulasi.store');
        Route::get('simulasi/{simulasi}', [SimulasiController::class, 'show'])->name('api.simulasi.show');
        Route::post('simulasi/{simulasi}/replay', [SimulasiController::class, 'replay'])->name('api.simulasi.replay');

        Route::get('approvals/inbox', [ApprovalController::class, 'inbox']);
        Route::post('permohonan/{permohonan}/approve', [ApprovalController::class, 'approve']);
        Route::post('permohonan/{permohonan}/reject', [ApprovalController::class, 'reject']);
        Route::post('permohonan/{permohonan}/return', [ApprovalController::class, 'returnToAo']);

        Route::get('kredit', [KreditController::class, 'index'])->name('api.kredit.index');
        Route::get('kredit/{kredit}', [KreditController::class, 'show']);
        Route::get('kredit/{kredit}/jadwal', [KreditController::class, 'jadwal']);

        Route::get('ledger', [LedgerController::class, 'index']);
        Route::get('ledger/verify', [LedgerController::class, 'verify']);

        Route::get('dashboard', [DashboardController::class, 'index']);
    });
});
