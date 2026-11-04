<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Enums\SistemSukuBunga;
use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR04BalloonWajib extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-04';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        if ($permohonan->sistem_suku_bunga !== SistemSukuBunga::GracePeriod->value) {
            return $this->pass('Bukan Grace Period.');
        }

        $lengkap = $permohonan->sumber_pelunasan_balloon !== null
            && $permohonan->sumber_pelunasan_balloon !== ''
            && $permohonan->perkiraan_penerimaan_balloon !== null;

        $data = ['sumber' => $permohonan->sumber_pelunasan_balloon, 'perkiraan' => $permohonan->perkiraan_penerimaan_balloon];

        return $lengkap
            ? $this->pass('Sumber pelunasan balloon tersedia.', $data)
            : $this->fail('Grace Period wajib memiliki sumber dan perkiraan penerimaan balloon.', $data);
    }
}
