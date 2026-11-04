<?php

declare(strict_types=1);

namespace App\Enums;

enum PihakSignature: string
{
    case Debitur = 'DEBITUR';
    case Bpr = 'BPR';
    case Saksi = 'SAKSI';
}
