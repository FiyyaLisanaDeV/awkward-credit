<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusPerkawinan: string
{
    case BelumMenikah = 'BELUM_MENIKAH';
    case Menikah = 'MENIKAH';
    case CeraiHidup = 'CERAI_HIDUP';
    case CeraiMati = 'CERAI_MATI';
    case Lainnya = 'LAINNYA';
}
