<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\LevelRedFlag;
use App\Enums\StatusKredit;
use App\Enums\StatusPermohonan;
use App\Models\AnalisisRedFlags;
use App\Models\Kredit;
use App\Models\PermohonanKredit;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function ringkasan(User $user): array
    {
        $cabangId = (string) $user->cabang_id;

        if ($cabangId === '') {
            return $this->kosong();
        }

        $ttl = (int) config('loan.ui.dashboard_cache_ttl');

        return Cache::remember(
            'dashboard:'.$cabangId,
            $ttl,
            fn (): array => $this->hitung($cabangId),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function kosong(): array
    {
        return [
            'per_status' => [],
            'draft' => 0,
            'pipeline' => ['submitted' => 0, 'analyzed' => 0, 'recommended' => 0],
            'kredit_aktif' => 0,
            'red_flag_kritis' => 0,
            'kelengkapan_rata' => 0.0,
            'recent' => collect(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function hitung(string $cabangId): array
    {
        $perStatus = array_map(
            static fn (array $row): array => [
                'status' => (string) $row['status'],
                'jumlah' => (int) $row['jumlah'],
                'total_plafon' => (float) $row['total_plafon'],
            ],
            PermohonanKredit::query()
                ->where('cabang_id', $cabangId)
                ->selectRaw('status, count(*) as jumlah, coalesce(sum(plafon_diajukan), 0) as total_plafon')
                ->groupBy('status')
                ->get()
                ->toArray(),
        );

        $jumlah = static fn (string $status): int => PermohonanKredit::query()
            ->where('cabang_id', $cabangId)
            ->where('status', $status)
            ->count();

        return [
            'per_status' => $perStatus,
            'draft' => $jumlah(StatusPermohonan::DRAFT->value),
            'pipeline' => [
                'submitted' => $jumlah(StatusPermohonan::SUBMITTED->value),
                'analyzed' => $jumlah(StatusPermohonan::ANALYZED->value),
                'recommended' => $jumlah(StatusPermohonan::RECOMMENDED->value),
            ],
            'kredit_aktif' => Kredit::query()
                ->where('cabang_id', $cabangId)
                ->where('status', StatusKredit::ACTIVE->value)
                ->count(),
            'red_flag_kritis' => AnalisisRedFlags::query()
                ->where('level', LevelRedFlag::Critical->value)
                ->where('teratasi', false)
                ->whereHas('permohonan', static fn ($query) => $query->where('cabang_id', $cabangId))
                ->count(),
            'kelengkapan_rata' => round((float) PermohonanKredit::query()
                ->where('cabang_id', $cabangId)
                ->avg('kelengkapan_persen'), 2),
            'recent' => PermohonanKredit::query()
                ->with('debitur')
                ->where('cabang_id', $cabangId)
                ->latest('tanggal_permohonan')
                ->limit((int) config('loan.ui.dashboard_recent'))
                ->get(),
        ];
    }
}
