<?php

declare(strict_types=1);

namespace App\Services\Scoring;

use App\Enums\Kolektibilitas;
use App\Enums\LevelRedFlag;
use App\Enums\PolicyParameterKey;
use App\Enums\StatusChecklist;
use App\Models\ChecklistStatus;
use App\Models\PermohonanKredit;
use App\Models\RefChecklistDokumen;
use App\Services\Policy\PolicyParameterResolver;

final class RedFlagEvaluator
{
    public function __construct(private readonly PolicyParameterResolver $parameters) {}

    /**
     * @return list<array{kode: string, level: LevelRedFlag, pesan: string}>
     */
    public function evaluate(PermohonanKredit $permohonan): array
    {
        $params = $this->parameters->active();
        $flags = [];

        $slik = $permohonan->slikDebitur()->with('kolektibilitas')->get();
        $kol3 = $slik->filter(static fn ($row): bool => (int) ($row->kolektibilitas->kode ?? 0) >= Kolektibilitas::KurangLancar->value)->count();
        $macet = $slik->filter(static fn ($row): bool => (int) ($row->kolektibilitas->kode ?? 0) === Kolektibilitas::Macet->value)->count();

        if ($kol3 >= $params->int(PolicyParameterKey::ThresholdSlikKol3)
            || $macet >= $params->int(PolicyParameterKey::ThresholdSlikMacet)) {
            $flags[] = ['kode' => 'SLIK_BERMASALAH', 'level' => LevelRedFlag::Critical, 'pesan' => 'Riwayat SLIK bermasalah (kol 3+ atau macet).'];
        }

        $kapasitas = $permohonan->kapasitasBayar;

        if ($kapasitas !== null && $kapasitas->dscr_rekomendasi !== null
            && (float) $kapasitas->dscr_rekomendasi < $params->float(PolicyParameterKey::MinimumDscr)) {
            $flags[] = ['kode' => 'DSCR_RENDAH', 'level' => LevelRedFlag::Warning, 'pesan' => 'DSCR rekomendasi di bawah batas minimum.'];
        }

        if ($kapasitas !== null && $kapasitas->ltv !== null
            && (float) $kapasitas->ltv > $params->float(PolicyParameterKey::MaksimumLtv)) {
            $flags[] = ['kode' => 'LTV_TINGGI', 'level' => LevelRedFlag::Warning, 'pesan' => 'LTV melebihi batas maksimum.'];
        }

        if ($kapasitas !== null && $kapasitas->rasio_kecukupan_balloon !== null
            && (float) $kapasitas->rasio_kecukupan_balloon < $params->float(PolicyParameterKey::RasioBalloonPerhatian)) {
            $flags[] = ['kode' => 'BALLOON_KURANG', 'level' => LevelRedFlag::Critical, 'pesan' => 'Rasio kecukupan balloon di bawah 1,0.'];
        }

        if ($permohonan->sensitivitas()->where('status_layak', false)->exists()) {
            $flags[] = ['kode' => 'STRESS_TIDAK_LAYAK', 'level' => LevelRedFlag::Warning, 'pesan' => 'Skenario sensitivitas terburuk tidak layak.'];
        }

        $wajib = RefChecklistDokumen::query()->where('wajib', true)->where('is_active', true)->pluck('id');

        if ($wajib->isNotEmpty()) {
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

            if ($wajib->diff($terpenuhi)->isNotEmpty()) {
                $flags[] = ['kode' => 'CHECKLIST_KURANG', 'level' => LevelRedFlag::Warning, 'pesan' => 'Checklist wajib belum lengkap.'];
            }
        }

        $debitur = $permohonan->debitur;

        if ($debitur === null || $debitur->tanggal_lahir === null || $debitur->no_ktp === null) {
            $flags[] = ['kode' => 'IDENTITAS_KURANG', 'level' => LevelRedFlag::Warning, 'pesan' => 'Data identitas debitur belum lengkap.'];
        }

        return $flags;
    }
}
