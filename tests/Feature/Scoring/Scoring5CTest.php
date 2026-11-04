<?php

declare(strict_types=1);

use App\Enums\RatingKredit;
use App\Enums\RekomendasiSistem;
use App\Models\AnalisisRasio;
use App\Models\PermohonanSlikDebitur;
use App\Models\RefKolektibilitas;
use App\Services\Scoring\RedFlagEvaluator;
use App\Services\Scoring\RekomendasiService;
use App\Services\Scoring\Scoring5CService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

it('memaksa skor 1,0 bila SLIK bermasalah (BR-01)', function () {
    $permohonan = fixturePermohonan();
    fixtureKapasitas($permohonan);

    $kol3 = RefKolektibilitas::query()->where('kode', 3)->firstOrFail();

    PermohonanSlikDebitur::query()->create([
        'permohonan_id' => $permohonan->id,
        'urutan' => 1,
        'nama_lembaga' => 'Bank Uji',
        'jenis_fasilitas' => 'Kredit',
        'plafon' => 10000000,
        'baki_debet' => 5000000,
        'kolektibilitas_id' => $kol3->id,
    ]);

    $hasil = app(Scoring5CService::class)->hitung($permohonan->fresh(), 5.0);

    expect($hasil['skor_total'])->toBe(1.0)
        ->and($hasil['overridden'])->toBeTrue()
        ->and($hasil['rating'])->toBe(RatingKredit::TidakLayak->value);
});

it('menghitung skor 5C normal sesuai bobot', function () {
    $permohonan = fixturePermohonan();
    fixtureKapasitas($permohonan, ['dscr_rekomendasi' => 1.5, 'ltv' => 0.4]);

    AnalisisRasio::query()->create([
        'permohonan_id' => $permohonan->id,
        'parameter_kebijakan_id' => $permohonan->parameter_kebijakan_id,
        'equity_to_asset' => 0.6,
    ]);

    $hasil = app(Scoring5CService::class)->hitung($permohonan->fresh(), 4.0);

    expect($hasil['overridden'])->toBeFalse()
        ->and($hasil['skor_total'])->toBeGreaterThan(3.0)
        ->and($hasil['rating'])->not->toBe(RatingKredit::TidakLayak->value)
        ->and(array_sum($hasil['bobot']))->toBeGreaterThan(0.99);
});

it('menolak skor condition di luar rentang', function () {
    $permohonan = fixturePermohonan();
    fixtureKapasitas($permohonan);

    expect(fn () => app(Scoring5CService::class)->hitung($permohonan, 6.0))
        ->toThrow(InvalidArgumentException::class);
});

it('menghasilkan red flag untuk DSCR rendah dan LTV tinggi', function () {
    $permohonan = fixturePermohonan();
    fixtureKapasitas($permohonan, ['dscr_rekomendasi' => 0.5, 'ltv' => 0.95]);

    $kode = array_column(app(RedFlagEvaluator::class)->evaluate($permohonan), 'kode');

    expect($kode)->toContain('DSCR_RENDAH')
        ->and($kode)->toContain('LTV_TINGGI');
});

it('membuat rekomendasi dari skor dan kapasitas', function () {
    $permohonan = fixturePermohonan();
    fixtureKapasitas($permohonan, ['dscr_rekomendasi' => 1.5, 'ltv' => 0.4]);

    AnalisisRasio::query()->create([
        'permohonan_id' => $permohonan->id,
        'parameter_kebijakan_id' => $permohonan->parameter_kebijakan_id,
        'equity_to_asset' => 0.6,
    ]);

    $scoring = app(Scoring5CService::class)->hitung($permohonan->fresh(), 4.0);
    $rekomendasi = app(RekomendasiService::class)->generate($permohonan->fresh(), $scoring);

    expect($rekomendasi['rekomendasi_sistem'])->not->toBe(RekomendasiSistem::Ditolak->value)
        ->and($rekomendasi['plafon_final'])->toBeLessThanOrEqual(90000000)
        ->and($rekomendasi['angsuran_final'])->toBeGreaterThan(0);
});
