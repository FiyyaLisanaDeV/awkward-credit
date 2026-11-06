<?php

declare(strict_types=1);

use App\Models\PermohonanSlikDebitur;
use App\Models\PermohonanSlikPasangan;
use App\Services\Slik\SlikAggregationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('mengagregasi fasilitas aktif, dedupe, dan estimasi angsuran', function () {
    $permohonan = fixturePermohonan();
    $kolektibilitas = DB::table('ref_kolektibilitas')->value('id');

    PermohonanSlikDebitur::query()->create([
        'permohonan_id' => $permohonan->id,
        'urutan' => 1,
        'nama_lembaga' => 'Bank A',
        'jenis_fasilitas' => 'Kredit',
        'no_fasilitas' => 'F-1',
        'plafon' => 5000000,
        'baki_debet' => 4000000,
        'angsuran_bulanan' => 1000000,
        'kolektibilitas_id' => $kolektibilitas,
    ]);

    // Duplikat fasilitas yang sama di pasangan harus diabaikan.
    PermohonanSlikPasangan::query()->create([
        'permohonan_id' => $permohonan->id,
        'urutan' => 1,
        'nama_lembaga' => 'Bank A',
        'jenis_fasilitas' => 'Kredit',
        'no_fasilitas' => 'F-1',
        'plafon' => 5000000,
        'baki_debet' => 4000000,
        'angsuran_bulanan' => 999999,
        'kolektibilitas_id' => $kolektibilitas,
    ]);

    // Fasilitas tanpa angsuran harus diestimasi flat.
    PermohonanSlikPasangan::query()->create([
        'permohonan_id' => $permohonan->id,
        'urutan' => 2,
        'nama_lembaga' => 'Bank B',
        'jenis_fasilitas' => 'Kredit',
        'no_fasilitas' => 'F-2',
        'plafon' => 10000000,
        'baki_debet' => 8000000,
        'suku_bunga' => 0.12,
        'jangka_waktu_bulan' => 10,
        'angsuran_bulanan' => null,
        'kolektibilitas_id' => $kolektibilitas,
    ]);

    $hasil = app(SlikAggregationService::class)->aggregateActiveObligations($permohonan);

    expect($hasil['angsuran_debitur'])->toBe(1000000.0)
        ->and($hasil['angsuran_pasangan'])->toBe(1100000.0)
        ->and($hasil['total_angsuran_slik'])->toBe(2100000.0)
        ->and($hasil['jumlah_fasilitas'])->toBe(2);

    expect(PermohonanSlikPasangan::query()->where('no_fasilitas', 'F-2')->value('is_angsuran_estimated'))->toBeTrue();
});
