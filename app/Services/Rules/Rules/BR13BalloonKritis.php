<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Enums\PolicyParameterKey;
use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR13BalloonKritis extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-13';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        $rasio = $permohonan->kapasitasBayar?->rasio_kecukupan_balloon;

        if ($rasio === null) {
            return $this->pass('Rasio balloon belum dihitung.');
        }

        $batas = $this->params($permohonan)->float(PolicyParameterKey::RasioBalloonPerhatian);

        return (float) $rasio < $batas
            ? $this->fail('Rasio kecukupan balloon di bawah ambang kritis.', ['rasio' => (float) $rasio, 'batas' => $batas])
            : $this->pass('Rasio balloon mencukupi.', ['rasio' => (float) $rasio]);
    }
}
