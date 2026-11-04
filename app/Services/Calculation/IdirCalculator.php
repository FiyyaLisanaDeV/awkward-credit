<?php

declare(strict_types=1);

namespace App\Services\Calculation;

use App\Enums\StatusKelayakan;
use App\Support\Rounding;

final class IdirCalculator
{
    public function hitung(float $angsuranBulanan, float $penghasilanBersih): float
    {
        if ($penghasilanBersih <= 0) {
            return 0.0;
        }

        return Rounding::ratio($angsuranBulanan / $penghasilanBersih);
    }

    public function status(float $idir, float $threshold): StatusKelayakan
    {
        return $idir <= $threshold ? StatusKelayakan::Layak : StatusKelayakan::TidakLayak;
    }
}
