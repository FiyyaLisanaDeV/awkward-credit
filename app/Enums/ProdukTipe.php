<?php

declare(strict_types=1);

namespace App\Enums;

enum ProdukTipe: string
{
    case TPP = 'TPP';
    case ModalKerja = 'MODAL_KERJA';
    case GracePeriod = 'GRACE_PERIOD';
}
