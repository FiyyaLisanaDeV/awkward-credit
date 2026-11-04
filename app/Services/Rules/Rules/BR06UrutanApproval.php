<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Enums\AksiApproval;
use App\Enums\StatusVersi;
use App\Models\ApprovalLog;
use App\Models\PermohonanKredit;
use App\Models\RefApprovalMatrix;
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
        $matrix = RefApprovalMatrix::query()->where('status', StatusVersi::ACTIVE->value)->first();

        if ($matrix === null || ! is_array($matrix->matrix)) {
            return $this->fail('Matriks persetujuan aktif tidak tersedia.');
        }

        $plafon = (float) ($permohonan->plafon_rekomendasi ?? $permohonan->plafon_diajukan);
        $levels = [];

        foreach ($matrix->matrix as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $min = (float) ($entry['plafon_min'] ?? 0);
            $max = $entry['plafon_max'] ?? null;

            if ($plafon >= $min && ($max === null || $plafon <= (float) $max)) {
                $levels[] = (int) $entry['level'];
            }
        }

        $wajib = $levels === [] ? 0 : max($levels);

        if ($wajib === 0) {
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

        $harus = range(1, $wajib);
        $kurang = array_values(array_diff($harus, $sudah));

        return $kurang === []
            ? $this->pass('Urutan persetujuan lengkap.', ['level' => $wajib])
            : $this->fail('Persetujuan belum berurutan.', ['kurang' => $kurang, 'wajib' => $wajib]);
    }
}
