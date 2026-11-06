<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Enums\PolicyParameterKey;
use App\Enums\ProdukTipe;
use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;
use App\Support\Konstanta;

final class BR11IdirHardStop extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-11H';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        if ($permohonan->produk?->tipe !== ProdukTipe::TPP->value) {
            return $this->pass('Bukan produk TPP.');
        }

        $idir = $permohonan->kapasitasBayar?->idir;

        if ($idir === null) {
            return $this->fail('IDIR TPP belum dihitung.');
        }

        $hardStop = $this->params($permohonan)->has(PolicyParameterKey::IdirHardStopTpp)
            ? $this->params($permohonan)->float(PolicyParameterKey::IdirHardStopTpp)
            : Konstanta::IDIR_HARD_STOP_DEFAULT;

        return (float) $idir > $hardStop
            ? $this->fail('IDIR TPP melebihi ambang batas keras.', [
                'idir' => (float) $idir,
                'hard_stop' => $hardStop,
            ])
            : $this->pass('IDIR TPP memenuhi toleransi maksimum.', [
                'idir' => (float) $idir,
                'hard_stop' => $hardStop,
            ]);
    }
}
