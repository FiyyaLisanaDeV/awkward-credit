<?php

declare(strict_types=1);

namespace App\Enums;

enum LevelRedFlag: string
{
    case Info = 'INFO';
    case Warning = 'WARNING';
    case Critical = 'CRITICAL';

    public function isBlocking(): bool
    {
        return $this === self::Critical;
    }
}
