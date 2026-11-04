<?php

declare(strict_types=1);

namespace App\Enums;

enum RekomendasiSistem: string
{
    case Disetujui = 'DISETUJUI';
    case DisetujuiDenganSyarat = 'DISETUJUI DENGAN SYARAT';
    case Ditolak = 'DITOLAK';
}
