<?php

declare(strict_types=1);

namespace App\Enums;

enum SumberData: string
{
    case Manual = 'MANUAL';
    case Import = 'IMPORT';
    case Ocr = 'OCR';
}
