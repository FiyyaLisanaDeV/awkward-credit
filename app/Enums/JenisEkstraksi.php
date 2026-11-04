<?php

declare(strict_types=1);

namespace App\Enums;

enum JenisEkstraksi: string
{
    case Ktp = 'KTP';
    case Kk = 'KK';
    case Npwp = 'NPWP';
}
