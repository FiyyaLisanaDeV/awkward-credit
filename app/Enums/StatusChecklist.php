<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusChecklist: string
{
    case Ada = 'ADA';
    case TidakAda = 'TIDAK_ADA';
    case Na = 'NA';
    case TidakWajib = 'TIDAK_WAJIB';
}
