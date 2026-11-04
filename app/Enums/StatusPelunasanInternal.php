<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusPelunasanInternal: string
{
    case Lunas = 'L';
    case LunasDenganTunggakan = 'LT';
    case BerjalanLancar = 'BL';
    case BerjalanMenunggak = 'BM';
    case MacetBermasalah = 'MB';

    public function bermasalah(): bool
    {
        return match ($this) {
            self::Lunas, self::BerjalanLancar => false,
            self::LunasDenganTunggakan, self::BerjalanMenunggak, self::MacetBermasalah => true,
        };
    }
}
