<?php

declare(strict_types=1);

use App\Services\Debitur\DebiturService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function dataDebiturCif(): array
{
    return [
        'nama_ktp' => 'Debitur CIF Uji',
        'no_ktp' => fixtureNik(),
        'jenis_kelamin' => 'L',
        'status_perkawinan' => 'MENIKAH',
        'no_hp' => '081200000000',
        'alamat_jalan' => 'Jalan Uji',
        'alamat_rt' => '001',
        'alamat_rw' => '002',
        'kelurahan' => 'Kelurahan',
        'kecamatan' => 'Kecamatan',
        'kabupaten' => 'Kendari',
        'provinsi' => 'Sulawesi Tenggara',
        'tempat_lahir' => 'Kendari',
        'tanggal_lahir' => '1990-01-01',
    ];
}

it('memberi CIF unik berurutan saat debitur dibuat', function () {
    $ao = workflowUser('AO');
    $service = app(DebiturService::class);

    $satu = $service->create(dataDebiturCif(), $ao);
    $dua = $service->create(dataDebiturCif(), $ao);

    expect($satu->cif)->toMatch('/^CIF-KDI-\d{6}$/')
        ->and($dua->cif)->toMatch('/^CIF-KDI-\d{6}$/')
        ->and($satu->cif)->not->toBe($dua->cif)
        ->and((int) substr($dua->cif, -6))->toBe((int) substr($satu->cif, -6) + 1);
});

it('menolak perubahan CIF (immutable)', function () {
    $ao = workflowUser('AO');
    $debitur = app(DebiturService::class)->create(dataDebiturCif(), $ao);

    expect(fn () => $debitur->update(['cif' => 'CIF-KDI-999999']))
        ->toThrow(RuntimeException::class, 'CIF immutable');
});

it('mengisi CIF debitur lama secara idempotent', function () {
    $debitur = fixtureDebitur();
    expect($debitur->cif)->toBeNull();

    $this->artisan('cif:backfill')->assertSuccessful();
    expect($debitur->fresh()->cif)->toMatch('/^CIF-KDI-\d{6}$/');

    $this->artisan('cif:backfill')
        ->expectsOutput('CIF diisi: 0 debitur.')
        ->assertSuccessful();
});
