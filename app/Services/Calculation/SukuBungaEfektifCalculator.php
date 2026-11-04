<?php

declare(strict_types=1);

namespace App\Services\Calculation;

use App\Support\Konstanta;
use App\Support\Rounding;
use InvalidArgumentException;

final class SukuBungaEfektifCalculator
{
    public function hitung(float $plafon, float $angsuranBulanan, int $tenor): float
    {
        if ($plafon <= 0 || $angsuranBulanan <= 0) {
            throw new InvalidArgumentException('Plafon dan angsuran harus lebih besar dari nol.');
        }

        if ($tenor < 1) {
            throw new InvalidArgumentException('Tenor minimal satu bulan.');
        }

        $low = 0.0;
        $high = Konstanta::MAX_MONTHLY_RATE;

        for ($iteration = 0; $iteration < Konstanta::BISECTION_ITERATIONS; $iteration++) {
            $mid = ($low + $high) / 2;
            $presentValue = $this->presentValue($mid, $tenor, $angsuranBulanan);

            if ($presentValue > $plafon) {
                $low = $mid;
            } else {
                $high = $mid;
            }
        }

        $rateBulan = ($low + $high) / 2;

        return Rounding::ratio($rateBulan * Konstanta::BULAN_PER_TAHUN);
    }

    private function presentValue(float $rateBulan, int $tenor, float $angsuran): float
    {
        if ($rateBulan <= 0.0) {
            return $angsuran * $tenor;
        }

        return $angsuran * (1 - (1 + $rateBulan) ** -$tenor) / $rateBulan;
    }
}
