<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Enums\PolicyParameterKey;
use App\Enums\ProdukTipe;
use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR11IdirTpp extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-11';
    }

    public function level(): string
    {
        return 'SOFT';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        if ($permohonan->produk?->tipe !== ProdukTipe::TPP->value) {
            return $this->pass('Bukan produk TPP.');
        }

        $idir = $permohonan->kapasitasBayar?->idir;

        if ($idir === null) {
            return $this->pass('IDIR belum dihitung.');
        }

        $batas = $this->params($permohonan)->float(PolicyParameterKey::IdirThresholdTpp);

        return (float) $idir > $batas
            ? $this->fail('IDIR TPP melebihi ambang.', ['idir' => (float) $idir, 'batas' => $batas])
            : $this->pass('IDIR TPP dalam ambang.', ['idir' => (float) $idir]);
    }
}
