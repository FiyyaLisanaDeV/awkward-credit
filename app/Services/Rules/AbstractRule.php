<?php

declare(strict_types=1);

namespace App\Services\Rules;

use App\Domain\Policy\PolicyParameters;
use App\Services\Policy\PolicyParameterResolver;

abstract class AbstractRule implements BusinessRule
{
    public function __construct(protected readonly PolicyParameterResolver $parameters) {}

    protected function params(): PolicyParameters
    {
        return $this->parameters->active();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function pass(string $message, array $data = []): RuleResult
    {
        return new RuleResult(true, $message, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function fail(string $message, array $data = []): RuleResult
    {
        return new RuleResult(false, $message, $data);
    }
}
