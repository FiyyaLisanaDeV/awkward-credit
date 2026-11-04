<?php

declare(strict_types=1);

namespace App\Enums;

enum SistemSukuBunga: string
{
    case Flat = 'Flat';
    case Anuitas = 'Anuitas';
    case GracePeriod = 'Grace Period';
}
