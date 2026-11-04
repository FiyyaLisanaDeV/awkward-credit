<?php

declare(strict_types=1);

namespace App\Services\Rules;

use App\Models\PermohonanKredit;

interface BusinessRule
{
    public function kode(): string;

    /**
     * @return 'HARD'|'SOFT'
     */
    public function level(): string;

    public function validate(PermohonanKredit $permohonan): RuleResult;
}
