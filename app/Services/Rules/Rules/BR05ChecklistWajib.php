<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Enums\StatusChecklist;
use App\Models\ChecklistStatus;
use App\Models\PermohonanKredit;
use App\Models\RefChecklistDokumen;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR05ChecklistWajib extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-05';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        $wajib = RefChecklistDokumen::query()
            ->where('wajib', true)
            ->where('is_active', true)
            ->pluck('id');

        if ($wajib->isEmpty()) {
            return $this->pass('Tidak ada checklist wajib.');
        }

        $terpenuhi = ChecklistStatus::query()
            ->where('permohonan_id', $permohonan->getKey())
            ->whereIn('checklist_id', $wajib)
            ->whereIn('status', [
                StatusChecklist::Ada->value,
                StatusChecklist::Na->value,
                StatusChecklist::TidakWajib->value,
            ])
            ->pluck('checklist_id')
            ->unique();

        $kurang = $wajib->diff($terpenuhi)->count();

        return $kurang === 0
            ? $this->pass('Checklist wajib lengkap.')
            : $this->fail('Checklist wajib belum lengkap.', ['kurang' => $kurang]);
    }
}
