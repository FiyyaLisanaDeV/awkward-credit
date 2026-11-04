<?php

declare(strict_types=1);

namespace App\Enums;

enum JenisAsuransi: string
{
    case JiwaAlAmin = 'JIWA_AL_AMIN';
    case JiwaJasindo = 'JIWA_JASINDO';
    case KebakaranBumida = 'KEBAKARAN_BUMIDA';
    case KendaraanJasindo = 'KENDARAAN_JASINDO';
    case Lainnya = 'LAINNYA';
}
