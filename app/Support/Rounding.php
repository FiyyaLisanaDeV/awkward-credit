<?php

declare(strict_types=1);

namespace App\Support;

final class Rounding
{
    public static function money(float $value): float
    {
        return round($value, (int) config('loan.money.scale'), PHP_ROUND_HALF_UP);
    }

    public static function ratio(float $value): float
    {
        return round($value, (int) config('loan.money.ratio_scale'), PHP_ROUND_HALF_UP);
    }

    public static function schedule(float $value): float
    {
        return round($value, (int) config('loan.money.schedule_scale'), PHP_ROUND_HALF_UP);
    }
}
