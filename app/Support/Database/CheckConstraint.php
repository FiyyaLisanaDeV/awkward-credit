<?php

declare(strict_types=1);

namespace App\Support\Database;

use BackedEnum;
use Illuminate\Support\Facades\DB;

final class CheckConstraint
{
    /**
     * Tambahkan CHECK berdasarkan himpunan nilai enum (satu sumber kebenaran).
     *
     * @param  class-string<BackedEnum>  $enum
     * @param  list<BackedEnum>|null  $cases
     */
    public static function enum(string $table, string $column, string $enum, ?array $cases = null, ?string $name = null): void
    {
        $cases ??= $enum::cases();

        $values = array_map(
            static fn (BackedEnum $case): string => is_int($case->value)
                ? (string) $case->value
                : "'".$case->value."'",
            $cases
        );

        $constraint = $name ?? $table.'_'.$column.'_check';

        DB::statement(sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s CHECK (%s IN (%s))',
            $table,
            $constraint,
            $column,
            implode(', ', $values)
        ));
    }

    /**
     * @param  list<string>  $values
     */
    public static function allowed(string $table, string $column, array $values, ?string $name = null): void
    {
        $quoted = array_map(static fn (string $value): string => "'".$value."'", $values);
        $constraint = $name ?? $table.'_'.$column.'_check';

        DB::statement(sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s CHECK (%s IN (%s))',
            $table,
            $constraint,
            $column,
            implode(', ', $quoted)
        ));
    }

    public static function expression(string $table, string $name, string $expression): void
    {
        DB::statement(sprintf('ALTER TABLE %s ADD CONSTRAINT %s CHECK (%s)', $table, $name, $expression));
    }
}
