<?php

declare(strict_types=1);

namespace App\Enums;

enum KategoriChecklist: string
{
    case Pemohon = 'PEMOHON';
    case Jaminan = 'JAMINAN';
    case Verifikasi = 'VERIFIKASI';
}
