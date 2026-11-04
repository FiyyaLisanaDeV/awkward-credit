<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusTtd: string
{
    case DRAFT = 'DRAFT';
    case MENUNGGU = 'MENUNGGU';
    case DITANDATANGANI = 'DITANDATANGANI';
    case DIBATALKAN = 'DIBATALKAN';
}
