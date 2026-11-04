<?php

declare(strict_types=1);

namespace App\Services\Calculation;

use App\Support\Konstanta;
use App\Support\Rounding;
use InvalidArgumentException;

final class PlafonMaxCalculator
{
    public function flat(float $rpc, int $tenor, float $rateTahunan): float
    {
        $this->guard($rpc, $tenor);

        $rateBulan = $rateTahunan / Konstanta::BULAN_PER_TAHUN;
        $penyebut = (1 / $tenor) + $rateBulan;

        if ($penyebut <= 0) {
            throw new InvalidArgumentException('Penyebut plafon maksimum tidak valid.');
        }

        return Rounding::money($rpc / $penyebut);
    }

    public function anuitas(float $rpc, int $tenor, float $rateTahunan): float
    {
        $this->guard($rpc, $tenor);

        $rateBulan = $rateTahunan / Konstanta::BULAN_PER_TAHUN;

        if ($rateBulan <= 0) {
            return Rounding::money($rpc * $tenor);
        }

        $faktor = (1 - (1 + $rateBulan) ** -$tenor) / $rateBulan;

        return Rounding::money($rpc * $faktor);
    }

    public function grace(float $rpc, float $rateTahunan): float
    {
        if ($rpc <= 0) {
            throw new InvalidArgumentException('RPC harus lebih besar dari nol.');
        }

        $rateBulan = $rateTahunan / Konstanta::BULAN_PER_TAHUN;

        if ($rateBulan <= 0) {
            throw new InvalidArgumentException('Suku bunga harus lebih besar dari nol untuk Grace Period.');
        }

        return Rounding::money($rpc / $rateBulan);
    }

    /**
     * @return array{flat: float, anuitas: float, grace: float}
     */
    public function semua(float $rpc, int $tenor, float $rateTahunan): array
    {
        return [
            'flat' => $this->flat($rpc, $tenor, $rateTahunan),
            'anuitas' => $this->anuitas($rpc, $tenor, $rateTahunan),
            'grace' => $this->grace($rpc, $rateTahunan),
        ];
    }

    private function guard(float $rpc, int $tenor): void
    {
        if ($rpc <= 0) {
            throw new InvalidArgumentException('RPC harus lebih besar dari nol.');
        }

        if ($tenor < 1) {
            throw new InvalidArgumentException('Tenor minimal satu bulan.');
        }
    }
}
