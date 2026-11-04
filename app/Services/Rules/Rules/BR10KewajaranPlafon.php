<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Enums\PolicyParameterKey;
use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR10KewajaranPlafon extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-10';
    }

    public function level(): string
    {
        return 'SOFT';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        $modalKerja = $permohonan->modalKerja;

        if ($modalKerja === null || (float) $modalKerja->rata_rata_kebutuhan_mk <= 0) {
            return $this->pass('Kebutuhan modal kerja belum tersedia.');
        }

        $plafon = (float) ($permohonan->plafon_rekomendasi ?? $permohonan->plafon_diajukan);
        $batas = $this->params()->float(PolicyParameterKey::PlafonKewajaranMultiplier) * (float) $modalKerja->rata_rata_kebutuhan_mk;

        return $plafon > $batas
            ? $this->fail('Plafon melebihi kewajaran kebutuhan modal kerja.', ['plafon' => $plafon, 'batas' => $batas])
            : $this->pass('Plafon wajar terhadap kebutuhan modal kerja.', ['batas' => $batas]);
    }
}
