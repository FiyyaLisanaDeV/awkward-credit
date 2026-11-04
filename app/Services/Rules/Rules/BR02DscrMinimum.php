<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Enums\PolicyParameterKey;
use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR02DscrMinimum extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-02';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        $kapasitas = $permohonan->kapasitasBayar;

        if ($kapasitas === null || $kapasitas->dscr_rekomendasi === null) {
            return $this->fail('Analisis kapasitas bayar belum tersedia.');
        }

        $dscr = (float) $kapasitas->dscr_rekomendasi;
        $minimum = $this->params()->float(PolicyParameterKey::MinimumDscr);

        $data = ['dscr' => $dscr, 'minimum' => $minimum];

        return $dscr >= $minimum
            ? $this->pass('DSCR memenuhi batas minimum.', $data)
            : $this->fail('DSCR di bawah batas minimum.', $data);
    }
}
