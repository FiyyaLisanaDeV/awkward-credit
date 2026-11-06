<?php

declare(strict_types=1);

use App\Models\Debitur;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('menolak masker tanpa konfirmasi', function () {
    $this->artisan('pii:masker')
        ->expectsOutput('Gunakan --confirm untuk melanjutkan masker.')
        ->assertFailed();
});

it('menutupi PII debitur tanpa mengubah jumlah baris atau relasi', function () {
    $debitur = fixtureDebitur();
    $nikAsli = (string) $debitur->no_ktp;
    $cabangAsli = (string) $debitur->cabang_id;
    $namaAsli = (string) $debitur->nama_ktp;

    $this->artisan('pii:masker --confirm')
        ->expectsOutput('PII termaskir: '.Debitur::query()->count().' debitur.')
        ->assertSuccessful();

    $fresh = $debitur->fresh();

    expect(Debitur::query()->count())->toBe(1)
        ->and($fresh->cabang_id)->toBe($cabangAsli)
        ->and($fresh->no_ktp)->not->toBe($nikAsli)
        ->and($fresh->nama_ktp)->not->toBe($namaAsli)
        ->and(Debitur::query()->where('no_ktp', $nikAsli)->exists())->toBeFalse();
});
