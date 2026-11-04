<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Enums\StatusTemplate;
use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR07TemplateApproved extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-07';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        $belumDisetujui = $permohonan->dokumen()
            ->whereHas('template', static function ($query): void {
                $query->where('status', '!=', StatusTemplate::APPROVED->value);
            })
            ->exists();

        return $belumDisetujui
            ? $this->fail('Ada dokumen memakai template yang belum APPROVED.')
            : $this->pass('Semua template dokumen yang dipakai sudah APPROVED.');
    }
}
