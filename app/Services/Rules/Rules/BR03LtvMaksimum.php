<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Enums\PolicyParameterKey;
use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR03LtvMaksimum extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-03';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        $kapasitas = $permohonan->kapasitasBayar;

        if ($kapasitas === null || $kapasitas->ltv === null) {
            return $this->pass('LTV belum dihitung; tidak ada pelanggaran.');
        }

        $ltv = (float) $kapasitas->ltv;
        $maksimum = $this->params()->float(PolicyParameterKey::MaksimumLtv);

        $data = ['ltv' => $ltv, 'maksimum' => $maksimum];

        return $ltv > $maksimum
            ? $this->fail('LTV melebihi batas maksimum.', $data)
            : $this->pass('LTV dalam batas.', $data);
    }
}
