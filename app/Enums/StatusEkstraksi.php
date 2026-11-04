<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusEkstraksi: string
{
    case DRAFT = 'DRAFT';
    case VERIFIED = 'VERIFIED';
    case REJECTED = 'REJECTED';
}
