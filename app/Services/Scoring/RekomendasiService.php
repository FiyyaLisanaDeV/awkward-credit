<?php

declare(strict_types=1);

namespace App\Services\Scoring;

use App\Enums\RatingKredit;
use App\Enums\RekomendasiSistem;
use App\Enums\SistemSukuBunga;
use App\Models\PermohonanKredit;
use App\Services\Calculation\AngsuranCalculator;
use App\Support\Konstanta;
use App\Support\Rounding;

final class RekomendasiService
{
    public function __construct(private readonly AngsuranCalculator $angsuran) {}

    /**
     * @param  array<string, mixed>  $scoring
     * @return array<string, mixed>
     */
    public function generate(PermohonanKredit $permohonan, array $scoring): array
    {
        $rating = RatingKredit::tryFrom((string) ($scoring['rating'] ?? '')) ?? RatingKredit::TidakLayak;
        $kapasitas = $permohonan->kapasitasBayar;

        $rekomendasi = match ($rating) {
            RatingKredit::SangatLayak, RatingKredit::Layak => RekomendasiSistem::Disetujui,
            RatingKredit::LayakBersyarat => RekomendasiSistem::DisetujuiDenganSyarat,
            RatingKredit::TidakLayak => RekomendasiSistem::Ditolak,
        };

        $plafonDiajukan = (float) $permohonan->plafon_diajukan;
        $plafonKandidat = (float) ($permohonan->plafon_rekomendasi ?? $plafonDiajukan);
        $plafonMaxFlat = $kapasitas === null ? 0.0 : (float) ($kapasitas->plafon_max_flat ?? 0);

        if ($plafonMaxFlat > 0) {
            $plafonKandidat = min($plafonKandidat, $plafonMaxFlat);
        }

        $plafonFinal = $rekomendasi === RekomendasiSistem::Ditolak ? 0.0 : Rounding::money($plafonKandidat);
        $tenorFinal = (int) ($permohonan->tenor_rekomendasi ?? $permohonan->tenor_diajukan);
        $rateFinal = Rounding::ratio((float) $permohonan->suku_bunga_tahunan);
        $angsuranFinal = 0.0;

        if ($plafonFinal > 0 && $tenorFinal > 0) {
            $angsuranFinal = Rounding::money(
                $this->angsuran->hitung(
                    $plafonFinal,
                    $rateFinal,
                    $tenorFinal,
                    SistemSukuBunga::from((string) $permohonan->sistem_suku_bunga),
                )['total']
            );
        }

        $narasi = sprintf(
            'Rating %s dengan skor %s. Plafon final Rp%s selama %d bulan pada suku bunga %.2f%% per tahun.',
            $rating->value,
            number_format((float) ($scoring['skor_total'] ?? 0), 2, ',', '.'),
            number_format($plafonFinal, 0, ',', '.'),
            $tenorFinal,
            $rateFinal * Konstanta::PERSEN,
        );

        return [
            'rekomendasi_sistem' => $rekomendasi->value,
            'narasi' => $narasi,
            'plafon_final' => $plafonFinal,
            'tenor_final' => $tenorFinal,
            'rate_final' => $rateFinal,
            'angsuran_final' => $angsuranFinal,
        ];
    }
}
