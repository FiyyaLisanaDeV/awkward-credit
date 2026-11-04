<?php

declare(strict_types=1);

namespace App\Services\Calculation;

use App\Enums\SistemSukuBunga;
use App\Support\Konstanta;
use App\Support\Rounding;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

final class AngsuranCalculator
{
    /**
     * @return array{pokok: float, bunga: float, total: float}
     */
    public function hitung(float $plafon, float $rateTahunan, int $tenor, SistemSukuBunga $sistem): array
    {
        $this->guard($plafon, $tenor);

        $rateBulan = $rateTahunan / Konstanta::BULAN_PER_TAHUN;

        $pokok = match ($sistem) {
            SistemSukuBunga::Flat => $plafon / $tenor,
            SistemSukuBunga::Anuitas => $this->pmt($rateBulan, $tenor, $plafon) - ($plafon * $rateBulan),
            SistemSukuBunga::GracePeriod => 0.0,
        };

        $bunga = $plafon * $rateBulan;

        return [
            'pokok' => Rounding::money($pokok),
            'bunga' => Rounding::money($bunga),
            'total' => Rounding::money($pokok + $bunga),
        ];
    }

    /**
     * @return list<array{periode: int, tanggal_jatuh_tempo: string, saldo_awal: float, angsuran_pokok: float, bunga: float, total_angsuran: float, saldo_akhir: float}>
     */
    public function generateJadwal(
        float $plafon,
        float $rateTahunan,
        int $tenor,
        SistemSukuBunga $sistem,
        DateTimeInterface $tanggalMulai,
    ): array {
        $this->guard($plafon, $tenor);

        $rateBulan = $rateTahunan / Konstanta::BULAN_PER_TAHUN;
        $angsuran = $this->pmt($rateBulan, $tenor, $plafon);
        $saldo = $plafon;
        $start = Carbon::instance($tanggalMulai);
        $jadwal = [];

        for ($periode = 1; $periode <= $tenor; $periode++) {
            $saldoAwal = $saldo;
            $terakhir = $periode === $tenor;

            $bunga = match ($sistem) {
                SistemSukuBunga::Flat, SistemSukuBunga::GracePeriod => $plafon * $rateBulan,
                SistemSukuBunga::Anuitas => $saldoAwal * $rateBulan,
            };

            $pokok = match (true) {
                $sistem === SistemSukuBunga::GracePeriod => $terakhir ? $saldoAwal : 0.0,
                $terakhir => $saldoAwal,
                $sistem === SistemSukuBunga::Flat => $plafon / $tenor,
                default => $angsuran - $bunga,
            };

            $pokok = max(0.0, $pokok);
            $saldo = max(0.0, $saldoAwal - $pokok);

            $jadwal[] = [
                'periode' => $periode,
                'tanggal_jatuh_tempo' => $start->copy()->addMonths($periode)->format('Y-m-d'),
                'saldo_awal' => Rounding::schedule($saldoAwal),
                'angsuran_pokok' => Rounding::schedule($pokok),
                'bunga' => Rounding::schedule($bunga),
                'total_angsuran' => Rounding::schedule($pokok + $bunga),
                'saldo_akhir' => Rounding::schedule($saldo),
            ];
        }

        $selisih = Rounding::schedule($plafon - array_sum(array_column($jadwal, 'angsuran_pokok')));
        $terakhirIndex = count($jadwal) - 1;

        /** @var array{periode: int, tanggal_jatuh_tempo: string, saldo_awal: float, angsuran_pokok: float, bunga: float, total_angsuran: float, saldo_akhir: float} $baris */
        $baris = $jadwal[$terakhirIndex];
        $baris['angsuran_pokok'] = Rounding::schedule($baris['angsuran_pokok'] + $selisih);
        $baris['total_angsuran'] = Rounding::schedule($baris['total_angsuran'] + $selisih);
        $jadwal[$terakhirIndex] = $baris;

        return $jadwal;
    }

    private function pmt(float $rate, int $nper, float $pv): float
    {
        if ($rate === 0.0) {
            return -$pv / $nper;
        }

        return ($pv * $rate) / (1 - (1 + $rate) ** -$nper);
    }

    private function guard(float $plafon, int $tenor): void
    {
        if ($plafon <= 0) {
            throw new InvalidArgumentException('Plafon harus lebih besar dari nol.');
        }

        if ($tenor < 1) {
            throw new InvalidArgumentException('Tenor minimal satu bulan.');
        }
    }
}
