<?php

declare(strict_types=1);

use App\Enums\SistemSukuBunga;
use App\Services\Calculation\AngsuranCalculator;
use Illuminate\Support\Carbon;

it('menghitung angsuran flat, anuitas, dan grace', function () {
    $calculator = new AngsuranCalculator;

    $flat = $calculator->hitung(120000000, 0.18, 12, SistemSukuBunga::Flat);
    $anuitas = $calculator->hitung(120000000, 0.18, 12, SistemSukuBunga::Anuitas);
    $grace = $calculator->hitung(120000000, 0.18, 12, SistemSukuBunga::GracePeriod);

    expect($flat['total'])->toBeGreaterThan(0)
        ->and($anuitas['total'])->toBeGreaterThan(0)
        ->and($grace['pokok'])->toBe(0.0)
        ->and($grace['total'])->toBe($grace['bunga'])
        ->and(abs($flat['pokok'] + $flat['bunga'] - $flat['total']))->toBeLessThan(0.01);
});

it('menjaga total anuitas konstan dan saldo akhir nol', function () {
    $jadwal = (new AngsuranCalculator)->generateJadwal(
        120000000,
        0.18,
        24,
        SistemSukuBunga::Anuitas,
        Carbon::parse('2026-01-01'),
    );

    expect($jadwal)->toHaveCount(24);

    $totals = array_column($jadwal, 'total_angsuran');

    expect(abs(max($totals) - min($totals)))->toBeLessThan(1.0)
        ->and($jadwal[23]['saldo_akhir'])->toBe(0.0);
});

it('menerapkan true-up pada periode terakhir', function () {
    $jadwal = (new AngsuranCalculator)->generateJadwal(
        100000000,
        0.18,
        60,
        SistemSukuBunga::Flat,
        Carbon::parse('2026-01-01'),
    );

    $totalPokok = array_sum(array_column($jadwal, 'angsuran_pokok'));

    expect(abs($totalPokok - 100000000))->toBeLessThan(0.05)
        ->and($jadwal[59]['saldo_akhir'])->toBe(0.0);
});

it('membayar pokok sekaligus pada grace period', function () {
    $jadwal = (new AngsuranCalculator)->generateJadwal(
        90000000,
        0.18,
        12,
        SistemSukuBunga::GracePeriod,
        Carbon::parse('2026-01-01'),
    );

    expect($jadwal[0]['angsuran_pokok'])->toBe(0.0)
        ->and($jadwal[11]['angsuran_pokok'])->toBe(90000000.0)
        ->and($jadwal[11]['saldo_akhir'])->toBe(0.0);
});
