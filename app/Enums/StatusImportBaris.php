<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusImportBaris: string
{
    case Valid = 'VALID';
    case Invalid = 'INVALID';
    case Committed = 'COMMITTED';
    case Skipped = 'SKIPPED';
}
