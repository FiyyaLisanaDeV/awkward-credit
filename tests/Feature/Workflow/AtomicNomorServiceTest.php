<?php

declare(strict_types=1);

use App\Services\Workflow\NomorService;
use Database\Seeders\BackfillNomorSequenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('menghasilkan tiga format nomor dari scope yang sama dengan backfill', function () {
    $tahun = (int) now()->format('Y');
    $service = app(NomorService::class);

    expect($service->generate('permohonan', 'KDI', $tahun))->toBe("KDI/{$tahun}/0001")
        ->and($service->generate('kredit', 'KDI', $tahun))->toBe("KDI/{$tahun}/K/0001")
        ->and($service->generate('dokumen', 'KDI', $tahun, 'SPK'))->toBe("SPK/KDI/{$tahun}/0001");

    expect(DB::table('nomor_sequence')->where('scope', 'permohonan:kdi:'.$tahun)->exists())->toBeTrue()
        ->and(DB::table('nomor_sequence')->where('scope', 'kredit:kdi:'.$tahun)->exists())->toBeTrue()
        ->and(DB::table('nomor_sequence')->where('scope', 'dokumen:spk:kdi:'.$tahun)->exists())->toBeTrue();
});

it('backfill mengambil suffix tertinggi dan idempoten', function () {
    $tahun = (int) now()->format('Y');
    $permohonan = fixturePermohonan(['no_permohonan' => "KDI/{$tahun}/0042"]);

    $this->seed(BackfillNomorSequenceSeeder::class);
    $this->seed(BackfillNomorSequenceSeeder::class);

    expect((int) DB::table('nomor_sequence')->where('scope', 'permohonan:kdi:'.$tahun)->value('last_number'))->toBe(42)
        ->and(app(NomorService::class)->generate('permohonan', 'KDI', $tahun))->toBe("KDI/{$tahun}/0043");

    $permohonan->delete();
});
