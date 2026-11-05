<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\Kolektibilitas as KolektibilitasEnum;
use App\Enums\SistemSukuBunga;
use App\Exceptions\BusinessRuleViolation;
use App\Models\Debitur;
use App\Models\PermohonanKredit;
use App\Models\Produk;
use App\Models\RefKolektibilitas;
use App\Services\Permohonan\PermohonanService;
use App\Support\Konstanta;
use App\Support\Validation\PermohonanRules;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;

final class PermohonanWizard extends Component
{
    public int $step = 1;

    public string $debitur_id = '';

    public string $produk_id = '';

    public string $plafon_diajukan = '';

    public string $plafon_rekomendasi = '';

    public string $suku_bunga_tahunan = '';

    public string $tenor_diajukan = '';

    public string $tenor_rekomendasi = '';

    public string $sistem_suku_bunga = SistemSukuBunga::Flat->value;

    public string $tanggal_permohonan = '';

    public string $tanggal_target_realisasi = '';

    public string $prioritas = 'NORMAL';

    public string $sektor = '';

    public string $tujuan_penggunaan = '';

    public string $kegunaan_pinjaman = '';

    public string $pinjaman_ke = '1';

    public string $sumber_pembayaran_angsuran = '';

    public string $sumber_pelunasan_balloon = '';

    public string $perkiraan_penerimaan_balloon = '';

    public string $catatan_ao = '';

    public string $paste = '';

    /** @var list<array<string, string>> */
    public array $slikRows = [];

    public string $nomorPermohonan = '';

    /** @var array<int, list<string>> */
    private array $fields = [
        1 => ['debitur_id', 'produk_id'],
        2 => [
            'plafon_diajukan', 'suku_bunga_tahunan', 'tenor_diajukan', 'sistem_suku_bunga',
            'tanggal_permohonan', 'tanggal_target_realisasi', 'prioritas', 'kegunaan_pinjaman',
        ],
        Konstanta::STEP_3 => [
            'sektor', 'tujuan_penggunaan', 'pinjaman_ke',
            'sumber_pembayaran_angsuran', 'sumber_pelunasan_balloon',
            'perkiraan_penerimaan_balloon', 'catatan_ao',
        ],
    ];

    public function mount(?string $debitur = null): void
    {
        $this->tanggal_permohonan = now()->toDateString();
        $this->debitur_id = (string) ($debitur ?? '');

        if ($this->debitur_id !== '' && ! Debitur::query()->whereKey($this->debitur_id)->exists()) {
            $this->debitur_id = '';
        }
    }

    public function next(PermohonanService $service): void
    {
        match ($this->step) {
            1 => $this->validate(Arr::only(PermohonanRules::rules(), $this->fields[1])),
            2 => $this->validate(Arr::only(PermohonanRules::rules(), $this->fields[2])),
            Konstanta::STEP_3 => $this->validate(Arr::only(PermohonanRules::rules(), $this->fields[Konstanta::STEP_3])),
            Konstanta::STEP_4 => $this->saveSlik(),
            default => null,
        };

        if ($this->step === 2) {
            $this->persist($service);
        }

        if ($this->step === Konstanta::STEP_3) {
            $this->persist($service);

            if ($this->draft() !== null) {
                $this->slikRows = $this->draft()->slikDebitur()
                    ->orderBy('urutan')
                    ->get()
                    ->map(static fn ($row): array => [
                        'nama_lembaga' => (string) $row->nama_lembaga,
                        'jenis_fasilitas' => (string) $row->jenis_fasilitas,
                        'plafon' => (string) $row->plafon,
                        'baki_debet' => (string) $row->baki_debet,
                        'kolektibilitas_id' => (string) $row->kolektibilitas_id,
                        'angsuran_bulanan' => (string) ($row->angsuran_bulanan ?? ''),
                    ])
                    ->all();
            }
        }

        $this->step = min(Konstanta::STEP_5, $this->step + 1);
    }

    public function previous(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function saveDraft(PermohonanService $service): void
    {
        $this->persist($service);
        session()->flash('status', __('permohonan.draft_saved'));
    }

    public function addSlikRow(): void
    {
        $this->slikRows[] = [
            'nama_lembaga' => '',
            'jenis_fasilitas' => '',
            'plafon' => '',
            'baki_debet' => '',
            'kolektibilitas_id' => '',
            'angsuran_bulanan' => '',
        ];
    }

    public function removeSlikRow(int $index): void
    {
        unset($this->slikRows[$index]);
        $this->slikRows = array_values($this->slikRows);
    }

    public function parsePaste(): void
    {
        $peta = RefKolektibilitas::query()->pluck('id', 'kode')->all();
        $baris = [];

        foreach (preg_split('/\R/', trim($this->paste)) ?: [] as $line) {
            if (trim($line) === '') {
                continue;
            }

            $kolom = array_map('trim', preg_split('/[\t;|]/', $line) ?: []);

            if (count($kolom) < Konstanta::SLIK_PASTE_MIN_COLUMNS) {
                continue;
            }

            $baris[] = [
                'nama_lembaga' => $kolom[0],
                'jenis_fasilitas' => $kolom[1],
                'plafon' => $kolom[2],
                'baki_debet' => $kolom[Konstanta::SLIK_PASTE_BAKI_INDEX],
                'kolektibilitas_id' => (string) ($peta[$kolom[Konstanta::SLIK_PASTE_KOLEKTIBILITAS_INDEX]] ?? ''),
                'angsuran_bulanan' => $kolom[Konstanta::SLIK_PASTE_ANGSURAN_INDEX] ?? '',
            ];
        }

        if ($baris === []) {
            $this->addError('paste', __('permohonan.paste_invalid'));

            return;
        }

        $this->slikRows = $baris;
        $this->paste = '';
        $this->resetErrorBag('paste');
    }

    public function submit(PermohonanService $service): void
    {
        if ($this->persist($service) === null) {
            return;
        }

        try {
            $service->submit($this->draft(), auth()->user());
        } catch (BusinessRuleViolation $e) {
            $this->addError('submit', $e->getMessage());

            return;
        }

        session()->flash('status', __('permohonan.submitted'));

        $this->redirectRoute('permohonan.show', $this->draft());
    }

    public function render(): View
    {
        $debitur = Debitur::query()
            ->where('cabang_id', auth()->user()?->cabang_id)
            ->orderBy('nama_ktp')
            ->limit((int) config('loan.pagination.per_page'))
            ->get();

        $produk = Produk::query()
            ->where('cabang_id', auth()->user()?->cabang_id)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        return view('livewire.permohonan-wizard', [
            'debiturOptions' => $debitur->mapWithKeys(static fn (Debitur $d): array => [$d->id => $d->nama_ktp.' — '.$d->no_ktp])->all(),
            'produkOptions' => $produk->mapWithKeys(static fn (Produk $p): array => [$p->id => $p->nama])->all(),
            'sistemOptions' => collect(SistemSukuBunga::cases())->mapWithKeys(static fn ($e): array => [$e->value => __('enums.sistem_suku_bunga.'.$e->value)])->all(),
            'kolektibilitasOptions' => RefKolektibilitas::query()->orderBy('kode')->get()
                ->mapWithKeys(static fn ($k): array => [$k->id => $k->kode.' — '.$k->nama])->all(),
            'kolektibilitasKode' => collect(KolektibilitasEnum::cases())->mapWithKeys(static fn ($e): array => [$e->value => $e->value.' — '.__('enums.kolektibilitas.'.$e->value)])->all(),
            'draft' => $this->draft(),
        ]);
    }

    private function draft(): ?PermohonanKredit
    {
        if ($this->nomorPermohonan === '') {
            return null;
        }

        return PermohonanKredit::query()->where('no_permohonan', $this->nomorPermohonan)->first();
    }

    private function persist(PermohonanService $service): ?PermohonanKredit
    {
        if ($this->step < 2) {
            return null;
        }

        $data = array_map(
            static fn (mixed $value): mixed => $value === '' ? null : $value,
            Arr::only($this->all(), array_keys(PermohonanRules::rules())),
        );

        $draft = $this->draft();

        if ($draft === null) {
            $draft = $service->create($data, auth()->user());
            $this->nomorPermohonan = (string) $draft->no_permohonan;

            return $draft;
        }

        $service->update($draft, $data);

        return $draft->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function slikRules(): array
    {
        return [
            'slikRows.*.nama_lembaga' => ['required', 'string', 'max:100'],
            'slikRows.*.jenis_fasilitas' => ['required', 'string', 'max:30'],
            'slikRows.*.plafon' => ['required', 'numeric', 'min:0'],
            'slikRows.*.baki_debet' => ['required', 'numeric', 'min:0'],
            'slikRows.*.kolektibilitas_id' => ['required', 'uuid', 'exists:ref_kolektibilitas,id'],
            'slikRows.*.angsuran_bulanan' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function saveSlik(): void
    {
        $draft = $this->draft();

        if ($draft === null) {
            return;
        }

        $this->validate($this->slikRules());

        $draft->slikDebitur()->delete();

        foreach (array_values($this->slikRows) as $index => $row) {
            $draft->slikDebitur()->create([
                'urutan' => $index + 1,
                'nama_lembaga' => $row['nama_lembaga'],
                'jenis_fasilitas' => $row['jenis_fasilitas'],
                'plafon' => $row['plafon'],
                'baki_debet' => $row['baki_debet'],
                'kolektibilitas_id' => $row['kolektibilitas_id'],
                'angsuran_bulanan' => $row['angsuran_bulanan'] === '' ? null : $row['angsuran_bulanan'],
            ]);
        }
    }
}
