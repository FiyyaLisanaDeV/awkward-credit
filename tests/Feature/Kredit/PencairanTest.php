<?php

declare(strict_types=1);

use App\Enums\LedgerEvent;
use App\Enums\StatusPermohonan;
use App\Models\Ledger;
use App\Models\PermohonanKredit;
use App\Models\User;
use App\Services\Kredit\KreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function approvedPermohonan(User $maker): PermohonanKredit
{
    return fixturePermohonan([
        'plafon_diajukan' => 5000000,
        'ao_id' => $maker->id,
        'status' => StatusPermohonan::APPROVED->value,
    ]);
}

it('mencairkan permohonan APPROVED menjadi kredit + jadwal + memo + ledger', function () {
    $maker = workflowUser('AO');
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $permohonan = approvedPermohonan($maker);

    $kredit = app(KreditService::class)->cairkan($permohonan, $admin, [
        'tanggal_cair' => '2026-02-01',
        'rekening_pencairan' => '1234567890',
    ]);

    expect($kredit->no_rekening_kredit)->toStartWith('KDI/')
        ->and($kredit->jadwal()->count())->toBe(60)
        ->and($kredit->memo()->exists())->toBeTrue()
        ->and($permohonan->fresh()->status)->toBe(StatusPermohonan::ACTIVE->value)
        ->and(Ledger::query()->where('event', LedgerEvent::AkadDicatat->value)->count())->toBe(1)
        ->and(Ledger::query()->where('event', LedgerEvent::PencairanDicatat->value)->count())->toBe(1);
});

it('menolak pencairan bila permohonan belum disetujui atau sudah dicairkan', function () {
    $maker = workflowUser('AO');
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $belum = fixturePermohonan([
        'plafon_diajukan' => 5000000,
        'ao_id' => $maker->id,
        'status' => StatusPermohonan::SUBMITTED->value,
    ]);

    assertHttpStatus(fn () => app(KreditService::class)->cairkan($belum, $admin), 409);

    $permohonan = approvedPermohonan($maker);
    app(KreditService::class)->cairkan($permohonan, $admin, ['rekening_pencairan' => '123']);

    assertHttpStatus(fn () => app(KreditService::class)->cairkan($permohonan->fresh(), $admin), 409);
});
