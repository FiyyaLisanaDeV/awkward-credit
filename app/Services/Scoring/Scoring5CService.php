<?php

declare(strict_types=1);

namespace App\Services\Scoring;

use App\Domain\Policy\PolicyParameters;
use App\Enums\Kolektibilitas;
use App\Enums\PolicyParameterKey;
use App\Enums\RatingKredit;
use App\Models\PermohonanKredit;
use App\Services\Policy\PolicyParameterResolver;
use App\Support\Konstanta;
use App\Support\Rounding;
use InvalidArgumentException;

final class Scoring5CService
{
    public function __construct(private readonly PolicyParameterResolver $parameters) {}

    /**
     * @return array<string, mixed>
     */
    public function hitung(PermohonanKredit $permohonan, float $skorCondition): array
    {
        if ($skorCondition < Konstanta::SKOR_MIN || $skorCondition > Konstanta::SKOR_MAKS) {
            throw new InvalidArgumentException('Skor Condition harus antara 1 dan 5.');
        }

        $params = $this->parameters->forPermohonan($permohonan);
        $kapasitas = $permohonan->kapasitasBayar;
        $rasio = $permohonan->rasio;

        $slik = $permohonan->slikDebitur()->with('kolektibilitas')->get();
        $kol3 = $slik->filter(
            static fn ($row): bool => (int) ($row->kolektibilitas->kode ?? 0) >= Kolektibilitas::KurangLancar->value
        )->count();
        $macet = $slik->filter(
            static fn ($row): bool => (int) ($row->kolektibilitas->kode ?? 0) === Kolektibilitas::Macet->value
        )->count();

        $bermasalah = $kol3 >= $params->int(PolicyParameterKey::ThresholdSlikKol3)
            || $macet >= $params->int(PolicyParameterKey::ThresholdSlikMacet);

        $dscr = $kapasitas === null ? 0.0 : (float) ($kapasitas->dscr_rekomendasi ?? 0);
        $equity = $rasio === null ? 0.0 : (float) ($rasio->equity_to_asset ?? 0);
        $ltv = $kapasitas === null ? 0.0 : (float) ($kapasitas->ltv ?? 0);

        $skor = [
            'character' => $this->bandSlik($params->array(PolicyParameterKey::SkorBandSlik), $kol3),
            'capacity' => $this->bandMin($params->array(PolicyParameterKey::SkorBandDscr), $dscr),
            'capital' => $this->bandMin($params->array(PolicyParameterKey::SkorBandEquityAsset), $equity),
            'collateral' => $this->bandMax($params->array(PolicyParameterKey::SkorBandLtv), $ltv),
            'condition' => $skorCondition,
        ];

        $bobot = [
            'character' => $params->float(PolicyParameterKey::BobotCharacter),
            'capacity' => $params->float(PolicyParameterKey::BobotCapacity),
            'capital' => $params->float(PolicyParameterKey::BobotCapital),
            'collateral' => $params->float(PolicyParameterKey::BobotCollateral),
            'condition' => $params->float(PolicyParameterKey::BobotCondition),
        ];

        $tertimbang = 0.0;

        foreach ($skor as $aspek => $nilai) {
            $tertimbang += $nilai * $bobot[$aspek];
        }

        $total = $bermasalah ? 1.0 : Rounding::ratio($tertimbang);
        $rating = $this->rating($params, $total, $bermasalah);

        return [
            'skor' => $skor,
            'bobot' => $bobot,
            'skor_tertimbang' => Rounding::ratio($tertimbang),
            'skor_total' => Rounding::ratio($total),
            'rating' => $rating->value,
            'overridden' => $bermasalah,
            'override_reason' => $bermasalah ? 'BR-01: riwayat SLIK bermasalah' : null,
        ];
    }

    private function rating(PolicyParameters $params, float $total, bool $bermasalah): RatingKredit
    {
        if ($bermasalah) {
            return RatingKredit::TidakLayak;
        }

        return match (true) {
            $total >= $params->float(PolicyParameterKey::RatingSangatLayakMin) => RatingKredit::SangatLayak,
            $total >= $params->float(PolicyParameterKey::RatingLayakMin) => RatingKredit::Layak,
            $total >= $params->float(PolicyParameterKey::RatingBersyaratMin) => RatingKredit::LayakBersyarat,
            default => RatingKredit::TidakLayak,
        };
    }

    /**
     * @param  array<int|string, mixed>  $bands
     */
    private function bandSlik(array $bands, int $kol3): float
    {
        foreach ($bands as $band) {
            if (is_array($band) && $kol3 <= (int) ($band['maks_kol3'] ?? 0)) {
                return (float) $band['skor'];
            }
        }

        return 1.0;
    }

    /**
     * @param  array<int|string, mixed>  $bands
     */
    private function bandMin(array $bands, float $value): float
    {
        foreach ($bands as $band) {
            if (is_array($band) && $value >= (float) ($band['min'] ?? 0)) {
                return (float) $band['skor'];
            }
        }

        return 1.0;
    }

    /**
     * @param  array<int|string, mixed>  $bands
     */
    private function bandMax(array $bands, float $value): float
    {
        foreach ($bands as $band) {
            if (is_array($band) && $value <= (float) ($band['max'] ?? 1.0)) {
                return (float) $band['skor'];
            }
        }

        return 1.0;
    }
}
