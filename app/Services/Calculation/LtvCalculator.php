<?php

declare(strict_types=1);

namespace App\Services\Calculation;

use App\Support\Rounding;

final class LtvCalculator
{
    public function hitung(float $plafon, float $nilaiJaminan): float
    {
        if ($nilaiJaminan <= 0) {
            return 0.0;
        }

        return Rounding::ratio($plafon / $nilaiJaminan);
    }

    public function melebihi(float $ltv, float $maksimum): bool
    {
        return $ltv > $maksimum;
    }
}
