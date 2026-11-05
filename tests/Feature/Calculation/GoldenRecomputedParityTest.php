<?php

declare(strict_types=1);

use App\Enums\SistemSukuBunga;
use App\Services\Calculation\AngsuranCalculator;
use App\Services\Calculation\DscrCalculator;
use App\Services\Calculation\IdirCalculator;
use App\Services\Calculation\LtvCalculator;
use App\Services\Calculation\ModalKerjaCalculator;
use App\Services\Calculation\PlafonMaxCalculator;
use App\Services\Calculation\SukuBungaEfektifCalculator;
use Illuminate\Support\Carbon;

/**
 * Paritas engine vs golden recomputed (best judgment, DRAFT).
 * Fixture = RECOMPUTED_DRAFT_NOT_VERIFIED; tes ini mengunci angka, bukan mengesahkan.
 */
function c002Fixture(): array
{
    $path = base_path('tests/Fixtures/golden/c002-recomputed.json');

    expect(is_file($path))->toBeTrue('fixture C-002 recomputed tidak ditemukan');

    return json_decode((string) file_get_contents($path), true);
}

it('C-002 recomputed: angsuran, IDIR, dan net income sesuai koreksi', function () {
    $fixture = c002Fixture();
    $in = $fixture['input'];
    $ex = $fixture['expected'];

    $hasil = (new AngsuranCalculator)->hitung(
        (float) $in['plafon'],
        (float) $in['rate'],
        (int) $in['tenor'],
        SistemSukuBunga::Flat,
    );

    expect(abs($hasil['total'] - (float) $ex['angsuran']))->toBeLessThan(0.5);

    $idir = (new IdirCalculator)->hitung($hasil['total'], (float) $ex['penghasilan_bersih']);

    expect(abs($idir - (float) $ex['idir']))->toBeLessThan(0.0001);

    expect(abs((float) $ex['penghasilan_bersih'] - 2948080.0))->toBeLessThan(0.01);
});

it('C-002 recomputed: EIR numeric x12 (bukan faktor 1,81x)', function () {
    $fixture = c002Fixture();
    $in = $fixture['input'];
    $ex = $fixture['expected'];

    $eir = (new SukuBungaEfektifCalculator)->hitung(
        (float) $in['plafon'],
        (float) $ex['angsuran'],
        (int) $in['tenor'],
    );

    expect(abs($eir - (float) $ex['eir_irr_pa']))->toBeLessThan(0.0002)
        ->and($ex['eir_effective_annual'])->toBeGreaterThan((float) $ex['eir_irr_pa']);
});

it('C-002 recomputed: jadwal flat invarian (sum pokok = plafon, saldo akhir 0)', function () {
    $fixture = c002Fixture();
    $in = $fixture['input'];

    $jadwal = (new AngsuranCalculator)->generateJadwal(
        (float) $in['plafon'],
        (float) $in['rate'],
        (int) $in['tenor'],
        SistemSukuBunga::Flat,
        Carbon::parse('2026-01-01'),
    );

    expect($jadwal)->toHaveCount(60);

    $sumPokok = array_sum(array_column($jadwal, 'angsuran_pokok'));

    expect(abs($sumPokok - (float) $in['plafon']))->toBeLessThan(0.05)
        ->and($jadwal[59]['saldo_akhir'])->toBe(0.0);
});

it('C-002 recomputed: RPC, plafon maksimum flat/anuitas, DSCR', function () {
    $fixture = c002Fixture();
    $c = $fixture['capacity'];

    $dscrCalc = new DscrCalculator;
    $rpc = $dscrCalc->rpc((float) $c['surplus_bulanan'], (float) $c['margin_kapasitas']);

    expect(abs($rpc - (float) $c['rpc']))->toBeLessThan(0.5);

    $plafon = new PlafonMaxCalculator;
    $flatMax = $plafon->flat($rpc, (int) $c['tenor'], (float) $c['rate']);
    $anuitasMax = $plafon->anuitas($rpc, (int) $c['tenor'], (float) $c['rate']);

    expect(abs($flatMax - (float) $c['max_plafon_flat']))->toBeLessThan(2.0)
        ->and(abs($anuitasMax - (float) $c['max_plafon_anuitas']))->toBeLessThan(2.0);

    $dscrAtMax = $dscrCalc->dscr($rpc, $rpc);

    expect($dscrAtMax)->toBeGreaterThanOrEqual(1.0);
});

it('C-002 recomputed: modal kerja cash turnover dan LTV (basis PPKA)', function () {
    $fixture = c002Fixture();
    $mk = $fixture['modal_kerja'];

    $hasil = (new ModalKerjaCalculator)->hitung(
        (float) $mk['biaya_operasional_tahunan'],
        (float) $mk['periode_perputaran_hari'],
        0.0,
        0.0,
        0.0,
        (int) $mk['hari_kerja_per_tahun'],
    );

    expect(abs($hasil['kebutuhan_cash_turnover'] - (float) $mk['kebutuhan_cash_turnover']))->toBeLessThan(0.5);

    $ltv = $fixture['ltv'];
    $nilai = (new LtvCalculator)->hitung((float) $ltv['exposure'], (float) $ltv['nilai_ppka']);

    expect(abs($nilai - (float) $ltv['ltv']))->toBeLessThan(0.0001);
});
