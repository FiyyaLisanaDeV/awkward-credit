<?php

declare(strict_types=1);

namespace App\Livewire\Analisis;

use App\Enums\LedgerEvent;
use App\Http\Controllers\Concerns\ScopesByCabang;
use App\Models\AnalisisLabaRugi;
use App\Models\AnalisisNeraca;
use App\Models\PermohonanKredit;
use App\Models\User;
use App\Services\Analisis\AnalisisRuleAuditService;
use App\Services\Analisis\KapasitasService;
use App\Services\Ledger\LedgerService;
use App\Services\Register\BukuBesarService;
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

    public string $total_pendapatan = '0';

    public string $total_pengeluaran = '0';

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
            ],
        );

        $user = auth()->user();
        abort_unless($user instanceof User, 401);
        $ledger->record(LedgerEvent::AnalisisDisimpan, 'permohonan_kredit', (string) $this->permohonan->id, [
            'bagian' => 'neraca',
            'total_aktiva' => $totalAktiva,
            'total_pasiva' => $totalPasiva,
            'selisih' => $selisih,
        ], $user);
        $bukuBesar->catat('ANALISIS', $this->permohonan, $user, 'Neraca disimpan', ['bagian' => 'neraca', 'selisih' => $selisih]);

        $this->status = 'Neraca disimpan.';
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

        $user = auth()->user();
        abort_unless($user instanceof User, 401);
        $ledger->record(LedgerEvent::AnalisisDisimpan, 'permohonan_kredit', (string) $this->permohonan->id, [
            'bagian' => 'laba_rugi',
            'total_pendapatan' => $pendapatan,
            'total_pengeluaran' => $pengeluaran,
            'surplus' => $surplus,
        ], $user);
        $bukuBesar->catat('ANALISIS', $this->permohonan, $user, 'Laba rugi disimpan', ['bagian' => 'laba_rugi', 'surplus' => $surplus]);

        $this->status = 'Laba rugi disimpan.';
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
        $ledger->record(LedgerEvent::AnalisisDisimpan, 'permohonan_kredit', (string) $this->permohonan->id, [
            'bagian' => 'kapasitas',
            'dscr' => (float) $kapasitas->dscr_rekomendasi,
            'idir' => $kapasitas->idir,
        ], $user);
        $bukuBesar->catat('ANALISIS', $this->permohonan, $user, 'Analisis kapasitas disimpan', ['bagian' => 'kapasitas']);

        $this->status = 'Kapasitas dihitung.';
    }

    public function render(): View
    {
        $permohonan = $this->permohonan->fresh([
            'debitur', 'produk', 'neraca', 'labaRugi', 'kapasitasBayar', 'scoring5c',
        ]) ?? $this->permohonan;

        return view('livewire.analisis.workbench', ['permohonan' => $permohonan]);
    }

    private function hydrateForms(): void
    {
        $this->total_aktiva = (string) ($this->permohonan->neraca?->getAttribute('total_aktiva') ?? '0');
        $this->total_pasiva = (string) ($this->permohonan->neraca?->getAttribute('total_pasiva') ?? '0');
        $this->total_pendapatan = (string) ($this->permohonan->labaRugi?->getAttribute('total_pendapatan') ?? '0');
        $this->total_pengeluaran = (string) ($this->permohonan->labaRugi?->getAttribute('total_pengeluaran') ?? '0');
    }
}
