<?php

declare(strict_types=1);

namespace App\Services\Rules;

use App\Models\PermohonanKredit;

final class BusinessRuleEngine
{
    /**
     * @param  iterable<BusinessRule>  $rules
     */
    public function __construct(private readonly iterable $rules) {}

    /**
     * @return list<array{kode: string, level: string, result: RuleResult}>
     */
    public function validateAll(PermohonanKredit $permohonan): array
    {
        $results = [];

        foreach ($this->rules as $rule) {
            $results[] = [
                'kode' => $rule->kode(),
                'level' => $rule->level(),
                'result' => $rule->validate($permohonan),
            ];
        }

        return $results;
    }

    /**
     * @param  list<array{kode: string, level: string, result: RuleResult}>  $results
     * @return list<array{kode: string, level: string, result: RuleResult}>
     */
    public function hardViolations(array $results): array
    {
        return array_values(array_filter(
            $results,
            static fn (array $row): bool => $row['level'] === 'HARD' && ! $row['result']->passed
        ));
    }

    /**
     * @param  list<array{kode: string, level: string, result: RuleResult}>  $results
     */
    public function hasHardViolations(array $results): bool
    {
        return $this->hardViolations($results) !== [];
    }
}
