<?php

declare(strict_types=1);

namespace App\Services\Ledger;

use DateTimeInterface;
use Illuminate\Support\Carbon;

final class ChainHasher
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function hash(
        string $prevHash,
        array $payload,
        string $occurredAt,
        string $event,
        string $entityType,
        ?string $entityId = null,
    ): string {
        return hash('sha256', implode("\n", [
            $prevHash,
            self::canonical($payload),
            $occurredAt,
            $event,
            $entityType,
            $entityId ?? '',
        ]));
    }

    public static function time(DateTimeInterface $value): string
    {
        return Carbon::instance($value)->utc()->format('Y-m-d\\TH:i:s\\Z');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function canonicalJson(array $payload): string
    {
        return self::canonical($payload);
    }

    public static function genesis(): string
    {
        return str_repeat('0', strlen(hash('sha256', '')));
    }

    private static function canonical(mixed $value): string
    {
        if (is_array($value)) {
            if (array_is_list($value)) {
                return '['.implode(',', array_map(self::canonical(...), $value)).']';
            }

            ksort($value);

            $parts = [];

            foreach ($value as $key => $item) {
                $parts[] = self::encodeString((string) $key).':'.self::canonical($item);
            }

            return '{'.implode(',', $parts).'}';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            $scale = (int) config('loan.money.canonical_scale');
            $formatted = rtrim(rtrim(number_format($value, $scale, '.', ''), '0'), '.');

            return $formatted === '' || $formatted === '-0' ? '0' : $formatted;
        }

        return self::encodeString((string) $value);
    }

    private static function encodeString(string $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
