<?php

declare(strict_types=1);

namespace App\Enums;

enum RatingKredit: string
{
    case SangatLayak = 'SANGAT LAYAK';
    case Layak = 'LAYAK';
    case LayakBersyarat = 'LAYAK BERSYARAT';
    case TidakLayak = 'TIDAK LAYAK';
}
