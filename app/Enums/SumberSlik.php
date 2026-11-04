<?php

declare(strict_types=1);

namespace App\Enums;

enum SumberSlik: string
{
    case Portal = 'PORTAL';
    case Import = 'IMPORT';
    case Manual = 'MANUAL';
}
