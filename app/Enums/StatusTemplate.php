<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusTemplate: string
{
    case DRAFT = 'DRAFT';
    case REVIEW = 'REVIEW';
    case APPROVED = 'APPROVED';
    case DEPRECATED = 'DEPRECATED';
}
