<?php

declare(strict_types=1);

use App\Enums\ProdukTipe;
use App\Enums\SistemSukuBunga;
use App\Models\SimulasiKredit;
use App\Models\User;
use App\Services\Policy\PolicyParameterResolver;
use App\Services\Simulation\SimulationEngine;
use App\Services\Simulation\SimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

/**
 * @return array<string, mixed>
 */
function inputSimulasi(): array
{
    return [
        'produk_tipe' => ProdukTipe::TPP->value,
        'sistem_suku_bunga' => SistemSukuBunga::Flat->value,
        'plafon' => 80000000,
        'rate' => 0.18,
        'tenor' => 60,
        'surplus_bulanan' => 5000000,
        'penghasilan_bersih' => 3048080,
    ];
}

it('merekam simulasi, mencatat ledger, dan dapat direplay', function () {
    $user = User::query()->where('username', 'admin')->firstOrFail();
    $parameters = app(PolicyParameterResolver::class)->active();
    $input = inputSimulasi();
    $output = app(SimulationEngine::class)->run($input, $parameters);

    $simulasi = app(SimulationService::class)->record(
        $user,
        'Uji TPP',
        ProdukTipe::TPP,
        SistemSukuBunga::Flat,
        $parameters,
        $input,
        $output,
        12,
    );

    expect($simulasi->input_hash)->toHaveLength(64)
        ->and($simulasi->ledger_id)->not->toBeNull()
        ->and(DB::table('ledger')->where('event', 'SIMULASI_DIJALANKAN')->count())->toBe(1)
        ->and((float) $simulasi->angsuran)->toBeGreaterThan(0);

    $this->artisan('simulasi:replay', ['id' => $simulasi->id])->assertSuccessful();
});

it('mendeteksi output simulasi yang berubah', function () {
    $user = User::query()->where('username', 'admin')->firstOrFail();
    $parameters = app(PolicyParameterResolver::class)->active();
    $input = inputSimulasi();
    $output = app(SimulationEngine::class)->run($input, $parameters);

    $simulasi = app(SimulationService::class)->record(
        $user,
        'Uji TPP',
        ProdukTipe::TPP,
        SistemSukuBunga::Flat,
        $parameters,
        $input,
        $output,
    );

    SimulasiKredit::query()->whereKey($simulasi->id)->update([
        'output' => json_encode(['angsuran' => ['total' => 1]]),
    ]);

    $this->artisan('simulasi:replay', ['id' => $simulasi->id])->assertFailed();
});
