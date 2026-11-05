<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Enums\AksiApproval;
use App\Models\ApprovalLog;
use App\Models\PermohonanKredit;
use App\Services\Approval\ApprovalService;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;

final class BR06UrutanApproval extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-06';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        $wajib = app(ApprovalService::class)->requiredLevels($permohonan);

        if ($wajib === []) {
            return $this->fail('Tidak ada level persetujuan untuk plafon ini.');
        }

        $sudah = ApprovalLog::query()
            ->where('permohonan_id', $permohonan->getKey())
            ->where('aksi', AksiApproval::Approve->value)
            ->orderBy('level')
            ->pluck('level')
            ->map(static fn ($level): int => (int) $level)
            ->unique()
            ->values()
            ->all();

        $kurang = array_values(array_diff($wajib, $sudah));

        return $kurang === []
            ? $this->pass('Urutan persetujuan lengkap.', ['level' => $wajib])
            : $this->fail('Persetujuan belum berurutan.', ['kurang' => $kurang, 'wajib' => $wajib]);
    }
}
