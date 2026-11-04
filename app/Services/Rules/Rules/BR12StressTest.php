<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR12StressTest extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-12';
    }

    public function level(): string
    {
        return 'SOFT';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        $skenario = $permohonan->sensitivitas()->get();

        if ($skenario->isEmpty()) {
            return $this->pass('Skenario sensitivitas belum diisi.');
        }

        $tidakLayak = $skenario->filter(static fn ($row): bool => $row->status_layak === false)->count();

        return $tidakLayak > 0
            ? $this->fail('Skenario terburuk tidak layak.', ['jumlah' => $tidakLayak])
            : $this->pass('Semua skenario sensitivitas layak.');
    }
}
