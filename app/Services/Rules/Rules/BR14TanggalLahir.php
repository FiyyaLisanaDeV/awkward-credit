<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR14TanggalLahir extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-14';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        return $permohonan->debitur?->tanggal_lahir !== null
            ? $this->pass('Tanggal lahir debitur tersedia.')
            : $this->fail('Tanggal lahir debitur wajib untuk SPAPP.');
    }
}
