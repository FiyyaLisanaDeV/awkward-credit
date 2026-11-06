<?php

declare(strict_types=1);

namespace App\Livewire\Analisis;

use App\Enums\LedgerEvent;
use App\Enums\PolicyParameterKey;
use App\Enums\SistemSukuBunga;
use App\Http\Controllers\Concerns\ScopesByCabang;
use App\Models\AnalisisLabaRugi;
use App\Models\AnalisisModalKerja;
use App\Models\AnalisisNeraca;
use App\Models\AnalisisRasio;
use App\Models\AnalisisScoring5c;
use App\Models\AnalisisSensitivitas;
use App\Models\PermohonanKredit;
use App\Models\User;
use App\Services\Analisis\AnalisisRuleAuditService;
use App\Services\Analisis\KapasitasService;
use App\Services\Calculation\AngsuranCalculator;
use App\Services\Calculation\ModalKerjaCalculator;
use App\Services\Calculation\RasioKeuanganCalculator;
use App\Services\Ledger\LedgerService;
use App\Services\Policy\PolicyParameterResolver;
use App\Services\Register\BukuBesarService;
use App\Services\Scoring\Scoring5CService;
use App\Support\Rounding;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use RuntimeException;

final class Workbench extends Component
{
    use ScopesByCabang;

    public PermohonanKredit $permohonan;

    public string $tab = 'ringkasan';

    public string $total_aktiva = '0';

    public string $total_pasiva = '0';

    public string $jumlah_aktiva_lancar = '0';

    public string $kas_inventaris = '0';

    public string $persediaan = '0';

    public string $hutang_pendek = '0';

    public string $jumlah_hutang = '0';

    public string $jumlah_modal = '0';

    public string $total_pendapatan = '0';

    public string $total_pengeluaran = '0';

    public string $biaya_operasional_tahunan = '0';

    public string $periode_perputaran_hari = '0';

    public string $pembelian_bulanan = '0';

    public string $hari_persediaan = '0';

    public string $mk_tersedia = '0';

    public string $omzet_bulanan = '0';

    public string $skor_condition = '3';

    public string $status = '';

    public function mount(PermohonanKredit $permohonan): void
    {
        $this->permohonan = $permohonan;
        $user = auth()->user();
        abort_unless($user instanceof User, 401);
        $this->ensureSameCabang($this->permohonan, $user);
        $this->hydrateForms();
    }

    public function saveNeraca(LedgerService $ledger, BukuBesarService $bukuBesar): void
    {
        $this->validate([
            'total_aktiva' => ['required', 'numeric', 'min:0'],
            'total_pasiva' => ['required', 'numeric', 'min:0'],
            'jumlah_aktiva_lancar' => ['required', 'numeric', 'min:0'],
            'kas_inventaris' => ['required', 'numeric', 'min:0'],
            'persediaan' => ['required', 'numeric', 'min:0'],
            'hutang_pendek' => ['required', 'numeric', 'min:0'],
            'jumlah_hutang' => ['required', 'numeric', 'min:0'],
            'jumlah_modal' => ['required', 'numeric', 'min:0'],
        ]);

        $totalAktiva = (float) $this->total_aktiva;
        $totalPasiva = (float) $this->total_pasiva;
        $selisih = round($totalAktiva - $totalPasiva, 2);

        AnalisisNeraca::query()->updateOrCreate(
            ['permohonan_id' => $this->permohonan->id],
            [
                'parameter_kebijakan_id' => $this->permohonan->parameter_kebijakan_id,
                'total_aktiva' => $totalAktiva,
                'total_pasiva' => $totalPasiva,
                'selisih_cek' => $selisih,
                'jumlah_aktiva_lancar' => (float) $this->jumlah_aktiva_lancar,
                'kas_inventaris' => (float) $this->kas_inventaris,
                'persediaan' => (float) $this->persediaan,
                'hutang_pendek' => (float) $this->hutang_pendek,
                'jumlah_hutang' => (float) $this->jumlah_hutang,
                'jumlah_modal' => (float) $this->jumlah_modal,
            ],
        );

        $this->recordAnalysis($ledger, $bukuBesar, 'neraca', ['selisih' => $selisih], 'Neraca disimpan.');
    }

    public function saveLabaRugi(LedgerService $ledger, BukuBesarService $bukuBesar): void
    {
        $this->validate([
            'total_pendapatan' => ['required', 'numeric', 'min:0'],
            'total_pengeluaran' => ['required', 'numeric', 'min:0'],
        ]);

        $pendapatan = (float) $this->total_pendapatan;
        $pengeluaran = (float) $this->total_pengeluaran;
        $surplus = round($pendapatan - $pengeluaran, 2);

        AnalisisLabaRugi::query()->updateOrCreate(
            ['permohonan_id' => $this->permohonan->id],
            [
                'parameter_kebijakan_id' => $this->permohonan->parameter_kebijakan_id,
                'total_pendapatan' => $pendapatan,
                'total_pengeluaran' => $pengeluaran,
                'surplus_per_bulan' => $surplus,
            ],
        );

        $this->recordAnalysis($ledger, $bukuBesar, 'laba_rugi', ['surplus' => $surplus], 'Laba rugi disimpan.');
    }

    public function hitungKapasitas(KapasitasService $service, AnalisisRuleAuditService $ruleAudit, LedgerService $ledger, BukuBesarService $bukuBesar): void
    {
        try {
            $kapasitas = $service->compute($this->permohonan);
        } catch (RuntimeException $exception) {
            $this->status = $exception->getMessage();

            return;
        }

        $user = auth()->user();
        abort_unless($user instanceof User, 401);
        $ruleAudit->evaluate($this->permohonan->fresh() ?? $this->permohonan, $user);
        $this->recordAnalysis($ledger, $bukuBesar, 'kapasitas', [
            'dscr' => (float) $kapasitas->dscr_rekomendasi,
            'idir' => $kapasitas->idir,
        ], 'Kapasitas dihitung.');
    }

    public function hitungRasio(RasioKeuanganCalculator $calculator, LedgerService $ledger, BukuBesarService $bukuBesar): void
    {
        $neraca = $this->permohonan->neraca;
        $labaRugi = $this->permohonan->labaRugi;

        if ($neraca === null || $labaRugi === null) {
            $this->status = 'Neraca dan laba rugi harus disimpan terlebih dahulu.';

            return;
        }

        $hasil = $calculator->semua([
            'aktiva_lancar' => (float) $neraca->getAttribute('jumlah_aktiva_lancar'),
            'hutang_pendek' => (float) $neraca->getAttribute('hutang_pendek'),
            'persediaan' => (float) $neraca->getAttribute('persediaan'),
            'kas' => (float) $neraca->getAttribute('kas_inventaris'),
            'total_hutang' => (float) $neraca->getAttribute('jumlah_hutang'),
            'total_aktiva' => (float) $neraca->getAttribute('total_aktiva'),
            'ekuitas' => (float) $neraca->getAttribute('jumlah_modal'),
            'laba_bersih' => (float) $labaRugi->getAttribute('surplus_per_bulan'),
            'pendapatan' => (float) $labaRugi->getAttribute('total_pendapatan'),
        ]);

        AnalisisRasio::query()->updateOrCreate(
            ['permohonan_id' => $this->permohonan->id],
            array_merge($hasil, [
                'parameter_kebijakan_id' => $this->permohonan->parameter_kebijakan_id,
            ]),
        );

        $this->recordAnalysis($ledger, $bukuBesar, 'rasio', $hasil, 'Rasio dihitung.');
    }

    public function saveModalKerja(ModalKerjaCalculator $calculator, PolicyParameterResolver $parameters, LedgerService $ledger, BukuBesarService $bukuBesar): void
    {
        $this->validate([
            'biaya_operasional_tahunan' => ['required', 'numeric', 'min:0'],
            'periode_perputaran_hari' => ['required', 'numeric', 'min:0'],
            'pembelian_bulanan' => ['required', 'numeric', 'min:0'],
            'hari_persediaan' => ['required', 'numeric', 'min:0'],
            'mk_tersedia' => ['required', 'numeric', 'min:0'],
            'omzet_bulanan' => ['required', 'numeric', 'min:0'],
        ]);

        $params = $parameters->forPermohonan($this->permohonan);
        $hasil = $calculator->hitung(
            (float) $this->biaya_operasional_tahunan,
            (float) $this->periode_perputaran_hari,
            (float) $this->pembelian_bulanan,
            (float) $this->hari_persediaan,
            (float) $this->mk_tersedia,
            $params->int(PolicyParameterKey::HariKerjaPerTahun),
        );

        $omzet = (float) $this->omzet_bulanan;

        AnalisisModalKerja::query()->updateOrCreate(
            ['permohonan_id' => $this->permohonan->id],
            [
                'parameter_kebijakan_id' => $this->permohonan->parameter_kebijakan_id,
                'periode_perputaran_hari' => (float) $this->periode_perputaran_hari,
                'biaya_operasional_tahunan' => (float) $this->biaya_operasional_tahunan,
                'perputaran_kas_per_tahun' => $hasil['perputaran_kas_per_tahun'],
                'kebutuhan_mk_cash_turnover' => $hasil['kebutuhan_cash_turnover'],
                'pembelian_bulanan' => (float) $this->pembelian_bulanan,
                'hari_persediaan' => (float) $this->hari_persediaan,
                'kebutuhan_mk_wcd' => $hasil['kebutuhan_wcd'],
                'mk_tersedia' => (float) $this->mk_tersedia,
                'rata_rata_kebutuhan_mk' => $hasil['rata_rata_kebutuhan'],
                'gap_mk' => $hasil['gap'],
                'omzet_bulanan' => $omzet,
                'kebutuhan_mk_persen_omzet' => $omzet > 0 ? Rounding::ratio($hasil['rata_rata_kebutuhan'] / $omzet) : 0.0,
            ],
        );

        $this->recordAnalysis($ledger, $bukuBesar, 'modal_kerja', $hasil, 'Modal kerja dihitung.');
    }

    public function generateSensitivitas(
        AngsuranCalculator $angsuran,
        PolicyParameterResolver $parameters,
        LedgerService $ledger,
        BukuBesarService $bukuBesar,
    ): void {
        $labaRugi = $this->permohonan->labaRugi;
        $kapasitas = $this->permohonan->kapasitasBayar;

        if ($labaRugi === null || $kapasitas === null) {
            $this->status = 'Laba rugi dan kapasitas harus dihitung terlebih dahulu.';

            return;
        }

        $params = $parameters->forPermohonan($this->permohonan);
        $plafon = (float) ($this->permohonan->plafon_rekomendasi ?? $this->permohonan->plafon_diajukan);
        $rate = (float) $this->permohonan->suku_bunga_tahunan;
        $tenor = (int) ($this->permohonan->tenor_rekomendasi ?? $this->permohonan->tenor_diajukan);
        $sistem = SistemSukuBunga::from((string) $this->permohonan->sistem_suku_bunga);
        $angsuranBaru = $angsuran->hitung($plafon, $rate, $tenor, $sistem);
        $totalKewajiban = (float) $angsuranBaru['total'] + (float) $kapasitas->getAttribute('total_angsuran_slik');
        $margin = $params->float(PolicyParameterKey::MarginKapasitasBayar);
        $minimum = $params->float(PolicyParameterKey::MinimumDscr);
        $pendapatan = (float) $labaRugi->getAttribute('total_pendapatan');
        $pengeluaran = (float) $labaRugi->getAttribute('total_pengeluaran');
        $ringan = $params->float(PolicyParameterKey::SensitivitasOmzetRingan);
        $berat = $params->float(PolicyParameterKey::SensitivitasOmzetBerat);
        $biaya = $params->float(PolicyParameterKey::SensitivitasBiaya);

        $skenario = [
            ['Baseline', 0.0, 0.0],
            ['Omzet turun ringan', $ringan, 0.0],
            ['Omzet turun berat', $berat, 0.0],
            ['Biaya naik', 0.0, $biaya],
            ['Omzet ringan + biaya naik', $ringan, $biaya],
            ['Omzet berat + biaya naik', $berat, $biaya],
        ];

        foreach ($skenario as $index => [$nama, $deltaOmzet, $deltaBiaya]) {
            $pendapatanBaru = Rounding::money($pendapatan * (1 - $deltaOmzet));
            $pengeluaranBaru = Rounding::money($pengeluaran * (1 + $deltaBiaya));
            $surplus = Rounding::money($pendapatanBaru - $pengeluaranBaru);
            $rpc = Rounding::money($surplus * $margin);
            $dscr = $totalKewajiban > 0 ? Rounding::ratio($rpc / $totalKewajiban) : 0.0;

            AnalisisSensitivitas::query()->updateOrCreate(
                ['permohonan_id' => $this->permohonan->id, 'urutan' => $index + 1],
                [
                    'parameter_kebijakan_id' => $this->permohonan->parameter_kebijakan_id,
                    'nama_skenario' => $nama,
                    'delta_omzet' => $deltaOmzet,
                    'delta_biaya' => $deltaBiaya,
                    'total_pendapatan' => $pendapatanBaru,
                    'total_pengeluaran_non_angsuran' => $pengeluaranBaru,
                    'surplus_baru' => $surplus,
                    'rpc_baru' => $rpc,
                    'dscr' => $dscr,
                    'status_layak' => $dscr >= $minimum,
                ],
            );
        }

        $this->recordAnalysis($ledger, $bukuBesar, 'sensitivitas', ['jumlah_skenario' => count($skenario)], 'Sensitivitas dihitung.');
    }

    public function hitungScoring(Scoring5CService $service, LedgerService $ledger, BukuBesarService $bukuBesar): void
    {
        $this->validate(['skor_condition' => ['required', 'numeric', 'between:1,5']]);

        $hasil = $service->hitung($this->permohonan->fresh() ?? $this->permohonan, (float) $this->skor_condition);

        AnalisisScoring5c::query()->updateOrCreate(
            ['permohonan_id' => $this->permohonan->id],
            [
                'parameter_kebijakan_id' => $this->permohonan->parameter_kebijakan_id,
                'skor_character' => $hasil['skor']['character'],
                'skor_capacity' => $hasil['skor']['capacity'],
                'skor_capital' => $hasil['skor']['capital'],
                'skor_collateral' => $hasil['skor']['collateral'],
                'skor_condition' => $hasil['skor']['condition'],
                'bobot_character' => $hasil['bobot']['character'],
                'bobot_capacity' => $hasil['bobot']['capacity'],
                'bobot_capital' => $hasil['bobot']['capital'],
                'bobot_collateral' => $hasil['bobot']['collateral'],
                'bobot_condition' => $hasil['bobot']['condition'],
                'skor_tertimbang' => $hasil['skor_tertimbang'],
                'skor_total' => $hasil['skor_total'],
                'rating' => $hasil['rating'],
                'overridden' => $hasil['overridden'],
                'override_reason' => $hasil['override_reason'],
            ],
        );

        $this->recordAnalysis($ledger, $bukuBesar, 'scoring', [
            'skor_total' => $hasil['skor_total'],
            'rating' => $hasil['rating'],
        ], 'Scoring 5C dihitung.');
    }

    public function render(): View
    {
        $permohonan = $this->permohonan->fresh([
            'debitur', 'produk', 'neraca', 'labaRugi', 'kapasitasBayar', 'rasio',
            'modalKerja', 'sensitivitas', 'scoring5c',
        ]) ?? $this->permohonan;

        return view('livewire.analisis.workbench', ['permohonan' => $permohonan]);
    }

    private function recordAnalysis(LedgerService $ledger, BukuBesarService $bukuBesar, string $bagian, array $payload, string $status): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 401);

        $ledger->record(LedgerEvent::AnalisisDisimpan, 'permohonan_kredit', (string) $this->permohonan->id, array_merge(['bagian' => $bagian], $payload), $user);
        $bukuBesar->catat('ANALISIS', $this->permohonan, $user, 'Analisis '.$bagian.' disimpan', array_merge(['bagian' => $bagian], $payload));

        $this->status = $status;
    }

    private function hydrateForms(): void
    {
        $neraca = $this->permohonan->neraca;
        $labaRugi = $this->permohonan->labaRugi;
        $modalKerja = $this->permohonan->modalKerja;

        $this->total_aktiva = (string) ($neraca?->getAttribute('total_aktiva') ?? '0');
        $this->total_pasiva = (string) ($neraca?->getAttribute('total_pasiva') ?? '0');
        $this->jumlah_aktiva_lancar = (string) ($neraca?->getAttribute('jumlah_aktiva_lancar') ?? '0');
        $this->kas_inventaris = (string) ($neraca?->getAttribute('kas_inventaris') ?? '0');
        $this->persediaan = (string) ($neraca?->getAttribute('persediaan') ?? '0');
        $this->hutang_pendek = (string) ($neraca?->getAttribute('hutang_pendek') ?? '0');
        $this->jumlah_hutang = (string) ($neraca?->getAttribute('jumlah_hutang') ?? '0');
        $this->jumlah_modal = (string) ($neraca?->getAttribute('jumlah_modal') ?? '0');

        $this->total_pendapatan = (string) ($labaRugi?->getAttribute('total_pendapatan') ?? '0');
        $this->total_pengeluaran = (string) ($labaRugi?->getAttribute('total_pengeluaran') ?? '0');

        $this->biaya_operasional_tahunan = (string) ($modalKerja?->getAttribute('biaya_operasional_tahunan') ?? '0');
        $this->periode_perputaran_hari = (string) ($modalKerja?->getAttribute('periode_perputaran_hari') ?? '0');
        $this->pembelian_bulanan = (string) ($modalKerja?->getAttribute('pembelian_bulanan') ?? '0');
        $this->hari_persediaan = (string) ($modalKerja?->getAttribute('hari_persediaan') ?? '0');
        $this->mk_tersedia = (string) ($modalKerja?->getAttribute('mk_tersedia') ?? '0');
        $this->omzet_bulanan = (string) ($modalKerja?->getAttribute('omzet_bulanan') ?? '0');

        $this->skor_condition = (string) ($this->permohonan->scoring5c?->getAttribute('skor_condition') ?? '3');
    }
}
