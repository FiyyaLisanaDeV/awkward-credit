<?php

declare(strict_types=1);

namespace App\Support;

final class Terbilang
{
    /** @var array<int, string> */
    private const SATUAN = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
        'sepuluh', 'sebelas',
    ];

    public function rupiah(float|int $nilai): string
    {
        return trim($this->kata((int) round((float) $nilai))).' rupiah';
    }

    public function kata(int $nilai): string
    {
        if ($nilai < 0) {
            return 'minus '.$this->kata(-$nilai);
        }

        if ($nilai < Konstanta::TERBILANG_DUA_BELAS) {
            return self::SATUAN[$nilai];
        }

        if ($nilai < Konstanta::TERBILANG_DUA_PULUH) {
            return self::SATUAN[$nilai - Konstanta::TERBILANG_SEPULUH].' belas';
        }

        if ($nilai < Konstanta::TERBILANG_SERATUS) {
            return self::SATUAN[intdiv($nilai, Konstanta::TERBILANG_SEPULUH)].' puluh '.$this->kata($nilai % Konstanta::TERBILANG_SEPULUH);
        }

        if ($nilai < Konstanta::TERBILANG_DUA_RATUS) {
            return 'seratus '.$this->kata($nilai - Konstanta::TERBILANG_SERATUS);
        }

        if ($nilai < Konstanta::TERBILANG_SERIBU) {
            return self::SATUAN[intdiv($nilai, Konstanta::TERBILANG_SERATUS)].' ratus '.$this->kata($nilai % Konstanta::TERBILANG_SERATUS);
        }

        if ($nilai < Konstanta::TERBILANG_DUA_RIBU) {
            return 'seribu '.$this->kata($nilai - Konstanta::TERBILANG_SERIBU);
        }

        if ($nilai < Konstanta::TERBILANG_SEJUTA) {
            return $this->kata(intdiv($nilai, Konstanta::TERBILANG_SERIBU)).' ribu '.$this->kata($nilai % Konstanta::TERBILANG_SERIBU);
        }

        if ($nilai < Konstanta::TERBILANG_SEMILYAR) {
            return $this->kata(intdiv($nilai, Konstanta::TERBILANG_SEJUTA)).' juta '.$this->kata($nilai % Konstanta::TERBILANG_SEJUTA);
        }

        return $this->kata(intdiv($nilai, Konstanta::TERBILANG_SEMILYAR)).' miliar '.$this->kata($nilai % Konstanta::TERBILANG_SEMILYAR);
    }
}
