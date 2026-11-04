<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR15NikConsent extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-15';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        $nik = $permohonan->debitur?->no_ktp;

        return is_string($nik) && $nik !== ''
            ? $this->pass('NIK tersedia untuk consent SLIK.')
            : $this->fail('NIK wajib untuk consent SLIK.');
    }
}
