<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusPermohonan: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case ANALYZED = 'ANALYZED';
    case RECOMMENDED = 'RECOMMENDED';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case RETURNED = 'RETURNED';
    case SIGNED = 'SIGNED';
    case ACTIVE = 'ACTIVE';
    case CLOSED = 'CLOSED';
    case NPL = 'NPL';
}
