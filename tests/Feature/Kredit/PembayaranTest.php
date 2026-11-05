<?php

declare(strict_types=1);

use App\Enums\LedgerEvent;
use App\Enums\StatusPermohonan;
use App\Models\JadwalAngsuran;
use App\Models\Kredit;
use App\Models\Ledger;
use App\Models\User;
use App\Services\Kredit\KreditService;
use App\Services\Kredit\PembayaranService;
use App\Support\Konstanta;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function kreditAktif(): Kredit
{
    $maker = workflowUser('AO');
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $permohonan = fixturePermohonan([
        'plafon_diajukan' => 5000000,
        'ao_id' => $maker->id,
        'status' => StatusPermohonan::APPROVED->value,
    ]);

    return app(KreditService::class)->cairkan($permohonan, $admin, ['rekening_pencairan' => '123']);
}

it('mencatat pembayaran angsuran dan menandai jadwal lunas', function () {
    $kredit = kreditAktif();
    $admin = User::query()->where('username', 'admin')->firstOrFail();

    $pembayaran = app(PembayaranService::class)->catat($kredit, $admin, [
        'periode' => 1,
        'tanggal_bayar' => '2026-03-01',
        'metode' => 'TUNAI',
    ]);

    $jadwal = JadwalAngsuran::query()->where('kredit_id', $kredit->id)->where('periode', 1)->firstOrFail();

    expect((float) $pembayaran->total)->toBeGreaterThan(0.0)
        ->and($jadwal->status)->toBe(Konstanta::JADWAL_SUDAH_BAYAR)
        ->and(Ledger::query()->where('event', LedgerEvent::AngsuranDibayar->value)->count())->toBe(1);
});

it('menolak pembayaran ganda pada periode sama', function () {
    $kredit = kreditAktif();
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $service = app(PembayaranService::class);

    $service->catat($kredit, $admin, ['periode' => 1, 'metode' => 'TUNAI']);

    assertHttpStatus(fn () => $service->catat($kredit, $admin, ['periode' => 1, 'metode' => 'TUNAI']), 409);
});

it('membatalkan pembayaran lewat reversal dan mengembalikan jadwal', function () {
    $kredit = kreditAktif();
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $service = app(PembayaranService::class);

    $pembayaran = $service->catat($kredit, $admin, ['periode' => 1, 'metode' => 'TUNAI']);
    $reversal = $service->reversal($pembayaran, $admin, 'salah input');

    $jadwal = JadwalAngsuran::query()->where('kredit_id', $kredit->id)->where('periode', 1)->firstOrFail();

    expect($reversal->reversal_of)->toBe($pembayaran->id)
        ->and($pembayaran->fresh()->dibatalkan_at)->not->toBeNull()
        ->and($jadwal->status)->toBe(Konstanta::JADWAL_BELUM_BAYAR)
        ->and(Ledger::query()->where('event', LedgerEvent::AngsuranDireversal->value)->count())->toBe(1);
});
