<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR08ParameterTersimpan extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-08';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        return $permohonan->parameter_kebijakan_id !== null
            ? $this->pass('Parameter kebijakan tersimpan pada permohonan.')
            : $this->fail('Permohonan tidak mereferensikan versi parameter kebijakan.');
    }
}
