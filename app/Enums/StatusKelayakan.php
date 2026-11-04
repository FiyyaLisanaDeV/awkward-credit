<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusKelayakan: string
{
    case Layak = 'LAYAK';
    case Perhatian = 'PERHATIAN';
    case TidakLayak = 'TIDAK LAYAK';
}
