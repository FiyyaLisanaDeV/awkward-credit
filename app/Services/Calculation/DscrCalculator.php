<?php

declare(strict_types=1);

namespace App\Services\Calculation;

use App\Enums\StatusKelayakan;
use App\Support\Rounding;

final class DscrCalculator
{
    public function rpc(float $surplusBulanan, float $marginKapasitas): float
    {
        return Rounding::money($surplusBulanan * $marginKapasitas);
    }

    public function dscr(float $rpc, float $angsuranBulanan): float
    {
        if ($angsuranBulanan <= 0) {
            return 0.0;
        }

        return Rounding::ratio($rpc / $angsuranBulanan);
    }

    public function status(float $dscr, float $minimum): StatusKelayakan
    {
        return $dscr >= $minimum ? StatusKelayakan::Layak : StatusKelayakan::TidakLayak;
    }

    /**
     * @return array{rpc: float, dscr: float, status: StatusKelayakan}
     */
    public function evaluasi(float $surplusBulanan, float $marginKapasitas, float $angsuranBulanan, float $minimum): array
    {
        $rpc = $this->rpc($surplusBulanan, $marginKapasitas);

        return [
            'rpc' => $rpc,
            'dscr' => $this->dscr($rpc, $angsuranBulanan),
            'status' => $this->status($this->dscr($rpc, $angsuranBulanan), $minimum),
        ];
    }
}
