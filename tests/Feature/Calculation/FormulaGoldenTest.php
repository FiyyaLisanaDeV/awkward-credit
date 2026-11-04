<?php

declare(strict_types=1);

use App\Enums\SistemSukuBunga;
use App\Services\Calculation\AngsuranCalculator;
use App\Services\Calculation\IdirCalculator;
use Illuminate\Support\Carbon;

it('menghasilkan angsuran dan IDIR yang sama dengan workbook TPP', function () {
    $path = base_path('tests/fixtures/golden/tpp.json');

    expect(is_file($path))->toBeTrue('fixture golden belum diekstrak — jalankan golden:extract');

    $fixture = json_decode((string) file_get_contents($path), true);

    $hasil = (new AngsuranCalculator)->hitung(
        (float) $fixture['input']['plafon'],
        (float) $fixture['input']['rate'],
        (int) $fixture['input']['tenor'],
        SistemSukuBunga::Flat,
    );

    expect(abs($hasil['total'] - (float) $fixture['expected']['angsuran']))->toBeLessThan(0.01);

    $idir = (new IdirCalculator)->hitung($hasil['total'], (float) $fixture['expected']['penghasilan_bersih']);

    expect(abs($idir - (float) $fixture['expected']['idir']))->toBeLessThan(0.0001);
});

it('menghasilkan jadwal 60 periode dengan total pokok sama dengan plafon', function () {
    $path = base_path('tests/fixtures/golden/tpp.json');
    $fixture = json_decode((string) file_get_contents($path), true);

    $jadwal = (new AngsuranCalculator)->generateJadwal(
        (float) $fixture['input']['plafon'],
        (float) $fixture['input']['rate'],
        (int) $fixture['input']['tenor'],
        SistemSukuBunga::Flat,
        Carbon::parse('2026-01-01'),
    );

    expect($jadwal)->toHaveCount(60);

    $totalPokok = array_sum(array_column($jadwal, 'angsuran_pokok'));

    expect(abs($totalPokok - (float) $fixture['input']['plafon']))->toBeLessThan(0.05)
        ->and($jadwal[59]['saldo_akhir'])->toBe(0.0)
        ->and($jadwal[0]['total_angsuran'])->toBeGreaterThan(0);
});
