<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusKredit: string
{
    case ACTIVE = 'ACTIVE';
    case LUNAS = 'LUNAS';
    case NPL = 'NPL';
    case CLOSED = 'CLOSED';
}
