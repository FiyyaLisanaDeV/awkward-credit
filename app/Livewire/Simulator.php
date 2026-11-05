<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\ProdukTipe;
use App\Enums\SistemSukuBunga;
use App\Models\PermohonanKredit;
use App\Services\Calculation\AngsuranCalculator;
use App\Services\Simulation\SimulationRunner;
use App\Support\Validation\SimulasiRules;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Component;

final class Simulator extends Component
{
    public string $label = '';

    public string $produk_tipe = ProdukTipe::TPP->value;

    public string $sistem_suku_bunga = SistemSukuBunga::Flat->value;

    public string $plafon = '';

    public string $rate = '';

    public string $tenor = '';

    public string $surplus_bulanan = '';

    public string $penghasilan_bersih = '';

    public string $nilai_jaminan = '';

    public string $biaya_operasional_tahunan = '';

    public string $periode_perputaran_hari = '';

    public string $pembelian_bulanan = '';

    public string $hari_persediaan = '';

    public string $mk_tersedia = '';

    public string $permohonan_id = '';

    public string $debitur_id = '';

    /** @var array<string, mixed> */
    public array $hasil = [];

    /** @var list<array<string, mixed>> */
    public array $jadwal = [];

    public function mount(?string $permohonan = null): void
    {
        $this->permohonan_id = (string) ($permohonan ?? '');

        if ($this->permohonan_id === '') {
            return;
        }

        $model = PermohonanKredit::query()->with('produk')->find($this->permohonan_id);

        if ($model === null) {
            $this->permohonan_id = '';

            return;
        }

        $this->label = 'Permohonan '.$model->no_permohonan;
        $produk = $model->produk;
        $this->produk_tipe = (string) ($produk !== null ? $produk->tipe : ProdukTipe::TPP->value);
        $this->sistem_suku_bunga = (string) $model->sistem_suku_bunga;
        $this->plafon = (string) $model->plafon_diajukan;
        $this->rate = (string) $model->suku_bunga_tahunan;
        $this->tenor = (string) $model->tenor_diajukan;
    }

    public function hitung(SimulationRunner $runner): void
    {
        $this->validate(SimulasiRules::rules());

        $this->hasil = $runner->preview($this->input());
        $this->jadwal = app(AngsuranCalculator::class)->generateJadwal(
            (float) $this->plafon,
            (float) $this->rate,
            (int) $this->tenor,
            SistemSukuBunga::from($this->sistem_suku_bunga),
            Carbon::now(),
        );
    }

    public function simpan(SimulationRunner $runner): void
    {
        $this->validate(SimulasiRules::rules());

        $simulasi = $runner->execute($this->input(), auth()->user());

        session()->flash('status', __('simulasi.saved'));

        $this->redirectRoute('simulasi.show', $simulasi);
    }

    public function render(): View
    {
        return view('livewire.simulator', [
            'produkTipeOptions' => collect(ProdukTipe::cases())
                ->mapWithKeys(static fn ($e): array => [$e->value => __('enums.produk_tipe.'.$e->value)])->all(),
            'sistemOptions' => collect(SistemSukuBunga::cases())
                ->mapWithKeys(static fn ($e): array => [$e->value => __('enums.sistem_suku_bunga.'.$e->value)])->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function input(): array
    {
        $base = [
            'label' => $this->label,
            'debitur_id' => $this->debitur_id === '' ? null : $this->debitur_id,
            'permohonan_id' => $this->permohonan_id === '' ? null : $this->permohonan_id,
            'produk_tipe' => $this->produk_tipe,
            'sistem_suku_bunga' => $this->sistem_suku_bunga,
            'plafon' => $this->plafon,
            'rate' => $this->rate,
            'tenor' => $this->tenor,
            'surplus_bulanan' => $this->surplus_bulanan,
            'penghasilan_bersih' => $this->penghasilan_bersih,
            'nilai_jaminan' => $this->nilai_jaminan,
            'biaya_operasional_tahunan' => $this->biaya_operasional_tahunan,
            'periode_perputaran_hari' => $this->periode_perputaran_hari,
            'pembelian_bulanan' => $this->pembelian_bulanan,
            'hari_persediaan' => $this->hari_persediaan,
            'mk_tersedia' => $this->mk_tersedia,
        ];

        return array_map(
            static fn (mixed $value): mixed => $value === '' ? null : $value,
            $base,
        );
    }
}
