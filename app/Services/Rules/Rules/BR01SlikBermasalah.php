<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Enums\Kolektibilitas;
use App\Enums\PolicyParameterKey;
use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR01SlikBermasalah extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-01';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        $slik = $permohonan->slikDebitur()->with('kolektibilitas')->get();

        $kol3 = $slik->filter(static fn ($row): bool => (int) ($row->kolektibilitas->kode ?? 0) >= Kolektibilitas::KurangLancar->value)->count();
        $macet = $slik->filter(static fn ($row): bool => (int) ($row->kolektibilitas->kode ?? 0) === Kolektibilitas::Macet->value)->count();

        $params = $this->params();
        $bermasalah = $kol3 >= $params->int(PolicyParameterKey::ThresholdSlikKol3)
            || $macet >= $params->int(PolicyParameterKey::ThresholdSlikMacet);

        $data = ['kol3' => $kol3, 'macet' => $macet, 'bermasalah' => $bermasalah];

        return $bermasalah
            ? $this->fail('Riwayat SLIK bermasalah; skor 5C dipaksa 1,0.', $data)
            : $this->pass('Riwayat SLIK tidak bermasalah.', $data);
    }
}
