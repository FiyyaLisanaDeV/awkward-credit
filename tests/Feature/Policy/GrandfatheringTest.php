<?php

declare(strict_types=1);

use App\Enums\PolicyParameterKey;
use App\Enums\SistemSukuBunga;
use App\Models\Produk;
use App\Models\RefParameterKebijakan;
use App\Models\User;
use App\Services\Permohonan\PermohonanService;
use App\Services\Policy\PolicyParameterResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('mengunci snapshot parameter dan tidak terpengaruh perubahan policy aktif', function () {
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $debitur = fixtureDebitur();
    $produk = Produk::query()->where('kode', 'TPP')->firstOrFail();
    $service = app(PermohonanService::class);

    $data = [
        'debitur_id' => $debitur->id,
        'produk_id' => $produk->id,
        'plafon_diajukan' => 80000000,
        'suku_bunga_tahunan' => 0.18,
        'tenor_diajukan' => 60,
        'sistem_suku_bunga' => SistemSukuBunga::Flat->value,
        'kegunaan_pinjaman' => 'Konsumsi',
        'tanggal_permohonan' => '2026-01-05',
    ];

    $lama = $service->create($data, $admin);
    $resolver = app(PolicyParameterResolver::class);

    expect($resolver->forPermohonan($lama)->float(PolicyParameterKey::IdirThresholdTpp))->toBe(0.35);

    $row = RefParameterKebijakan::query()->where('status', 'ACTIVE')->firstOrFail();
    $params = $row->params;
    $params['idir_threshold_tpp'] = 0.99;

    DB::table('ref_parameter_kebijakan')->where('id', $row->id)->update([
        'params' => json_encode($params, JSON_THROW_ON_ERROR),
    ]);

    expect($resolver->forPermohonan($lama->fresh())->float(PolicyParameterKey::IdirThresholdTpp))->toBe(0.35);

    $baru = $service->create($data, $admin);

    expect($resolver->forPermohonan($baru->fresh())->float(PolicyParameterKey::IdirThresholdTpp))->toBe(0.99);
});

it('menolak perubahan snapshot setelah permohonan dibuat', function () {
    $permohonan = fixturePermohonan();

    expect(fn () => $permohonan->update(['parameter_snapshot' => ['id' => 'x', 'version' => 'x', 'params' => []]]))
        ->toThrow(RuntimeException::class, 'parameter_snapshot immutable');
});
