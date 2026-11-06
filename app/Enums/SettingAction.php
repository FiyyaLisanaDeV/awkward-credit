<?php

declare(strict_types=1);

namespace App\Enums;

enum SettingAction: string
{
    case Create = 'CREATE';
    case Update = 'UPDATE';
    case Deactivate = 'DEACTIVATE';
    case Activate = 'ACTIVATE';
}
