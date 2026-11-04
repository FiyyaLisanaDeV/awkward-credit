<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusImport: string
{
    case Preview = 'PREVIEW';
    case Committed = 'COMMITTED';
    case Failed = 'FAILED';
    case Reverted = 'REVERTED';
}
