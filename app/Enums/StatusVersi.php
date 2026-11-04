<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusVersi: string
{
    case DRAFT = 'DRAFT';
    case ACTIVE = 'ACTIVE';
    case ARCHIVED = 'ARCHIVED';
}
