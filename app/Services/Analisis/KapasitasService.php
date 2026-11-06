<?php

declare(strict_types=1);

namespace App\Services\Analisis;

use App\Enums\PolicyParameterKey;
use App\Enums\ProdukTipe;
use App\Enums\SistemSukuBunga;
use App\Enums\StatusKelayakan;
use App\Models\AnalisisKapasitasBayar;
use App\Models\PermohonanKredit;
use App\Services\Calculation\AngsuranCalculator;
use App\Services\Calculation\DscrCalculator;
use App\Services\Calculation\IdirCalculator;
use App\Services\Calculation\LtvCalculator;
use App\Services\Calculation\PlafonMaxCalculator;
use App\Services\Policy\PolicyParameterResolver;
use App\Services\Slik\SlikAggregationService;
use App\Support\Konstanta;
use App\Support\Rounding;
use RuntimeException;

final class KapasitasService
{
    public function __construct(
        private readonly PolicyParameterResolver $parameters,
        private readonly SlikAggregationService $slik,
    ) {}

    public function compute(PermohonanKredit $permohonan): AnalisisKapasitasBayar
    {
        $labaRugi = $permohonan->labaRugi;

        if ($labaRugi === null || $labaRugi->getAttribute('surplus_per_bulan') === null) {
            throw new RuntimeException('Laba rugi / surplus belum diisi.');
        }

        $params = $this->parameters->forPermohonan($permohonan);
        $plafon = (float) ($permohonan->plafon_rekomendasi ?? $permohonan->plafon_diajukan);
        $rate = (float) $permohonan->suku_bunga_tahunan;
        $tenor = (int) ($permohonan->tenor_rekomendasi ?? $permohonan->tenor_diajukan);
        $sistem = SistemSukuBunga::from((string) $permohonan->sistem_suku_bunga);

        $angsuran = (new AngsuranCalculator)->hitung($plafon, $rate, $tenor, $sistem);
        $slik = $this->slik->aggregateActiveObligations($permohonan);
        $totalKewajiban = Rounding::money((float) $angsuran['total'] + (float) $slik['total_angsuran_slik']);
        $surplus = (float) $labaRugi->getAttribute('surplus_per_bulan');
        $rpc = (new DscrCalculator)->rpc($surplus, $params->float(PolicyParameterKey::MarginKapasitasBayar));
        $dscr = (new DscrCalculator)->dscr($rpc, $totalKewajiban);

        $nilaiJaminan = $permohonan->debitur === null
            ? 0.0
            : (float) $permohonan->debitur->aset()->sum('nilai_diperhitungkan');
        $ltv = (new LtvCalculator)->hitung($plafon, $nilaiJaminan);

        $idir = $permohonan->produk?->tipe === ProdukTipe::TPP->value
            ? (new IdirCalculator)->hitung($angsuran['total'], $surplus)
            : null;

        $statusIdir = null;

        if ($idir !== null) {
            $hard = $params->has(PolicyParameterKey::IdirHardStopTpp)
                ? $params->float(PolicyParameterKey::IdirHardStopTpp)
                : Konstanta::IDIR_HARD_STOP_DEFAULT;
            $statusIdir = (new IdirCalculator)
                ->evaluasi($idir, $params->float(PolicyParameterKey::IdirThresholdTpp), $hard)
                ->value;
        }

        $plafonMax = (new PlafonMaxCalculator)->semua($rpc, $tenor, $rate);

        $balloonTotal = null;
        $balloonRatio = null;

        if ($sistem === SistemSukuBunga::GracePeriod) {
            $balloonTotal = Rounding::money($plafon + $angsuran['bunga']);
            $perkiraan = (float) ($permohonan->perkiraan_penerimaan_balloon ?? 0);
            $balloonRatio = $balloonTotal > 0 ? Rounding::ratio($perkiraan / $balloonTotal) : 0.0;
        }

        return AnalisisKapasitasBayar::query()->updateOrCreate(
            ['permohonan_id' => $permohonan->id],
            [
                'parameter_kebijakan_id' => $permohonan->parameter_kebijakan_id,
                'rpc_bulanan' => $rpc,
                'dscr_diajukan' => $dscr,
                'status_dscr_diajukan' => $dscr >= $params->float(PolicyParameterKey::MinimumDscr) ? StatusKelayakan::Layak->value : StatusKelayakan::TidakLayak->value,
                'dscr_rekomendasi' => $dscr,
                'status_dscr_rekomendasi' => $dscr >= $params->float(PolicyParameterKey::MinimumDscr) ? StatusKelayakan::Layak->value : StatusKelayakan::TidakLayak->value,
                'plafon_max_flat' => $plafonMax['flat'],
                'plafon_max_anuitas' => $plafonMax['anuitas'],
                'plafon_max_grace' => $plafonMax['grace'],
                'ltv' => $ltv,
                'idir' => $idir,
                'status_idir' => $statusIdir,
                'angsuran_slik_debitur' => $slik['angsuran_debitur'],
                'angsuran_slik_pasangan' => $slik['angsuran_pasangan'],
                'total_angsuran_slik' => $slik['total_angsuran_slik'],
                'jumlah_fasilitas_slik_aktif' => $slik['jumlah_fasilitas'],
                'total_pembayaran_jatuh_tempo' => $balloonTotal ?? 0.0,
                'rasio_kecukupan_balloon' => $balloonRatio,
                'status_kecukupan_balloon' => $balloonRatio === null
                    ? null
                    : ($balloonRatio >= $params->float(PolicyParameterKey::RasioBalloonLayak) ? StatusKelayakan::Layak->value : ($balloonRatio >= $params->float(PolicyParameterKey::RasioBalloonPerhatian) ? StatusKelayakan::Perhatian->value : StatusKelayakan::TidakLayak->value)),
            ],
        );
    }
}
