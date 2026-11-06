<?php

declare(strict_types=1);

namespace App\Services\Simulation;

use App\Domain\Policy\PolicyParameters;
use App\Enums\PolicyParameterKey;
use App\Enums\ProdukTipe;
use App\Enums\SistemSukuBunga;
use App\Enums\StatusKelayakan;
use App\Services\Calculation\AngsuranCalculator;
use App\Services\Calculation\DscrCalculator;
use App\Services\Calculation\IdirCalculator;
use App\Services\Calculation\LtvCalculator;
use App\Services\Calculation\ModalKerjaCalculator;
use App\Services\Calculation\PlafonMaxCalculator;
use App\Services\Calculation\SukuBungaEfektifCalculator;
use App\Support\Konstanta;

final class SimulationEngine
{
    public function __construct(
        private readonly AngsuranCalculator $angsuran = new AngsuranCalculator,
        private readonly DscrCalculator $dscr = new DscrCalculator,
        private readonly PlafonMaxCalculator $plafonMax = new PlafonMaxCalculator,
        private readonly LtvCalculator $ltv = new LtvCalculator,
        private readonly IdirCalculator $idir = new IdirCalculator,
        private readonly ModalKerjaCalculator $modalKerja = new ModalKerjaCalculator,
        private readonly SukuBungaEfektifCalculator $efektif = new SukuBungaEfektifCalculator,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function run(array $input, PolicyParameters $parameters): array
    {
        $produkTipe = ProdukTipe::from((string) $input['produk_tipe']);
        $sistem = SistemSukuBunga::from((string) $input['sistem_suku_bunga']);

        $plafon = (float) $input['plafon'];
        $rate = (float) $input['rate'];
        $tenor = (int) $input['tenor'];

        $angsuran = $this->angsuran->hitung($plafon, $rate, $tenor, $sistem);

        $surplus = (float) ($input['surplus_bulanan'] ?? 0);
        $rpc = $this->dscr->rpc($surplus, $parameters->float(PolicyParameterKey::MarginKapasitasBayar));
        $dscr = $this->dscr->dscr($rpc, $angsuran['total']);
        $statusDscr = $this->dscr->status($dscr, $parameters->float(PolicyParameterKey::MinimumDscr));

        $plafonMax = $this->plafonMax->semua(
            $rpc,
            $tenor,
            $rate,
        );

        $ltv = isset($input['nilai_jaminan']) && (float) $input['nilai_jaminan'] > 0
            ? $this->ltv->hitung($plafon, (float) $input['nilai_jaminan'])
            : null;

        $idir = null;
        $statusIdir = null;

        if ($produkTipe === ProdukTipe::TPP && isset($input['penghasilan_bersih'])) {
            $idir = $this->idir->hitung($angsuran['total'], (float) $input['penghasilan_bersih']);
            $soft = $parameters->float(PolicyParameterKey::IdirThresholdTpp);
            $hard = $parameters->has(PolicyParameterKey::IdirHardStopTpp)
                ? $parameters->float(PolicyParameterKey::IdirHardStopTpp)
                : Konstanta::IDIR_HARD_STOP_DEFAULT;
            $statusIdir = $this->idir->evaluasi($idir, $soft, $hard);
        }

        $modalKerja = null;

        if ($produkTipe === ProdukTipe::ModalKerja && isset($input['biaya_operasional_tahunan'])) {
            $modalKerja = $this->modalKerja->hitung(
                (float) $input['biaya_operasional_tahunan'],
                (float) ($input['periode_perputaran_hari'] ?? 0),
                (float) ($input['pembelian_bulanan'] ?? 0),
                (float) ($input['hari_persediaan'] ?? 0),
                (float) ($input['mk_tersedia'] ?? 0),
                $parameters->int(PolicyParameterKey::HariKerjaPerTahun),
            );
        }

        $efektif = $this->efektif->hitung($plafon, $angsuran['total'], $tenor);

        return [
            'angsuran' => $angsuran,
            'rpc' => $rpc,
            'dscr' => $dscr,
            'status_dscr' => $statusDscr->value,
            'plafon_max' => $plafonMax,
            'ltv' => $ltv,
            'idir' => $idir,
            'status_idir' => $statusIdir instanceof StatusKelayakan ? $statusIdir->value : null,
            'modal_kerja' => $modalKerja,
            'suku_bunga_efektif' => $efektif,
        ];
    }
}
