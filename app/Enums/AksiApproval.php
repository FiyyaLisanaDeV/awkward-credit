<?php

declare(strict_types=1);

namespace App\Enums;

enum AksiApproval: string
{
    case Approve = 'APPROVE';
    case Reject = 'REJECT';
    case Return = 'RETURN';
    case Hold = 'HOLD';
}
