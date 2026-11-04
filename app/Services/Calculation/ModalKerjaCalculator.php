<?php

declare(strict_types=1);

namespace App\Services\Calculation;

use App\Support\Konstanta;
use App\Support\Rounding;

final class ModalKerjaCalculator
{
    /**
     * @return array{perputaran_kas_per_tahun: float, kebutuhan_cash_turnover: float, kebutuhan_wcd: float, rata_rata_kebutuhan: float, gap: float}
     */
    public function hitung(
        float $biayaOperasionalTahunan,
        float $periodePerputaranHari,
        float $pembelianBulanan,
        float $hariPersediaan,
        float $mkTersedia,
        int $hariKerjaPerTahun,
    ): array {
        $perputaranPerTahun = $periodePerputaranHari > 0
            ? $hariKerjaPerTahun / $periodePerputaranHari
            : 0.0;

        $kebutuhanCashTurnover = $perputaranPerTahun > 0
            ? $biayaOperasionalTahunan / $perputaranPerTahun
            : 0.0;

        $kebutuhanWcd = ($pembelianBulanan / Konstanta::HARI_PER_BULAN) * $hariPersediaan;
        $rataRata = ($kebutuhanCashTurnover + $kebutuhanWcd) / 2;
        $gap = max(0.0, $rataRata - $mkTersedia);

        return [
            'perputaran_kas_per_tahun' => Rounding::ratio($perputaranPerTahun),
            'kebutuhan_cash_turnover' => Rounding::money($kebutuhanCashTurnover),
            'kebutuhan_wcd' => Rounding::money($kebutuhanWcd),
            'rata_rata_kebutuhan' => Rounding::money($rataRata),
            'gap' => Rounding::money($gap),
        ];
    }
}
