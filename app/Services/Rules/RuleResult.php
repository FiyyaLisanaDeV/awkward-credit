<?php

declare(strict_types=1);

namespace App\Services\Rules;

final class RuleResult
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly bool $passed,
        public readonly string $message,
        public readonly array $data = [],
    ) {}
}
