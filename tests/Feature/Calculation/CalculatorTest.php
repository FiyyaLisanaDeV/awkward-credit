<?php

declare(strict_types=1);

use App\Enums\StatusKelayakan;
use App\Services\Calculation\DscrCalculator;
use App\Services\Calculation\IdirCalculator;
use App\Services\Calculation\LtvCalculator;
use App\Services\Calculation\ModalKerjaCalculator;
use App\Services\Calculation\PlafonMaxCalculator;
use App\Services\Calculation\RasioKeuanganCalculator;
use App\Services\Calculation\SukuBungaEfektifCalculator;

it('menghitung DSCR dan statusnya', function () {
    $calculator = new DscrCalculator;

    $rpc = $calculator->rpc(5000000, 0.5);

    expect($rpc)->toBe(2500000.0)
        ->and($calculator->dscr($rpc, 2500000))->toBe(1.0)
        ->and($calculator->status(1.0, 1.0))->toBe(StatusKelayakan::Layak)
        ->and($calculator->status(0.99, 1.0))->toBe(StatusKelayakan::TidakLayak)
        ->and($calculator->dscr($rpc, 0))->toBe(0.0);
});

it('menghitung plafon maksimum dan memenuhi hubungan invers', function () {
    $calculator = new PlafonMaxCalculator;
    $rpc = 2500000.0;

    $flat = $calculator->flat($rpc, 60, 0.18);
    $anuitas = $calculator->anuitas($rpc, 60, 0.18);
    $grace = $calculator->grace($rpc, 0.18);

    expect($flat)->toBeGreaterThan(0)
        ->and($anuitas)->toBeGreaterThan($flat)
        ->and($grace)->toBeGreaterThan(0);

    $rateBulan = 0.18 / 12;
    $kembali = $flat * ((1 / 60) + $rateBulan);

    expect(abs($kembali - $rpc))->toBeLessThan(1.0);
});

it('menghitung LTV dan batas maksimum', function () {
    $calculator = new LtvCalculator;

    expect($calculator->hitung(80000000, 100000000))->toBe(0.8)
        ->and($calculator->melebihi(0.8, 0.7))->toBeTrue()
        ->and($calculator->hitung(50000000, 0))->toBe(0.0);
});

it('menghitung IDIR terhadap ambang', function () {
    $calculator = new IdirCalculator;

    expect($calculator->hitung(2500000, 3000000))->toBe(0.8333)
        ->and($calculator->status(0.3333, 0.35))->toBe(StatusKelayakan::Layak)
        ->and($calculator->status(0.8333, 0.35))->toBe(StatusKelayakan::TidakLayak);
});

it('menghitung kebutuhan modal kerja', function () {
    $hasil = (new ModalKerjaCalculator)->hitung(
        biayaOperasionalTahunan: 360000000,
        periodePerputaranHari: 30,
        pembelianBulanan: 100000000,
        hariPersediaan: 15,
        mkTersedia: 20000000,
        hariKerjaPerTahun: 360,
    );

    expect($hasil['kebutuhan_cash_turnover'])->toBeGreaterThan(0)
        ->and($hasil['kebutuhan_wcd'])->toBeGreaterThan(0)
        ->and(abs($hasil['rata_rata_kebutuhan'] - (($hasil['kebutuhan_cash_turnover'] + $hasil['kebutuhan_wcd']) / 2)))->toBeLessThan(0.05)
        ->and($hasil['gap'])->toBeGreaterThanOrEqual(0);
});

it('menghitung rasio keuangan', function () {
    $hasil = (new RasioKeuanganCalculator)->semua([
        'aktiva_lancar' => 300000000,
        'persediaan' => 100000000,
        'kas' => 50000000,
        'hutang_pendek' => 150000000,
        'total_hutang' => 400000000,
        'total_aktiva' => 1000000000,
        'ekuitas' => 600000000,
        'laba_bersih' => 120000000,
        'pendapatan' => 900000000,
    ]);

    expect($hasil['current_ratio'])->toBe(2.0)
        ->and($hasil['equity_to_asset'])->toBe(0.6)
        ->and($hasil['debt_to_asset'])->toBe(0.4)
        ->and($hasil['net_profit_margin'])->toBeGreaterThan(0);
});

it('menghitung suku bunga efektif dari angsuran flat', function () {
    $efektif = (new SukuBungaEfektifCalculator)->hitung(80000000, 2533333.3333, 60);

    expect($efektif)->toBeGreaterThan(0.18)
        ->and($efektif)->toBeLessThan(0.50);
});
