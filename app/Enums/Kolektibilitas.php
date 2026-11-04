<?php

declare(strict_types=1);

namespace App\Enums;

enum Kolektibilitas: int
{
    case Lancar = 1;
    case DalamPerhatianKhusus = 2;
    case KurangLancar = 3;
    case Diragukan = 4;
    case Macet = 5;
}
