<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\LedgerEvent;
use App\Http\Controllers\Concerns\ScopesByCabang;
use App\Http\Controllers\Controller;
use App\Models\AnalisisLabaRugi;
use App\Models\AnalisisNeraca;
use App\Models\AnalisisRekomendasi;
use App\Models\AnalisisScoring5c;
use App\Models\PermohonanKredit;
use App\Models\User;
use App\Services\Analisis\AnalisisRuleAuditService;
use App\Services\Analisis\KapasitasService;
use App\Services\Ledger\LedgerService;
use App\Services\Register\BukuBesarService;
use App\Services\Scoring\RedFlagEvaluator;
use App\Services\Scoring\RekomendasiService;
use App\Services\Scoring\Scoring5CService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AnalisisController extends Controller
{
    use ScopesByCabang;

    public function __construct(
        private readonly KapasitasService $kapasitas,
        private readonly AnalisisRuleAuditService $ruleAudit,
        private readonly LedgerService $ledger,
        private readonly BukuBesarService $bukuBesar,
    ) {}

    public function neracaShow(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        return response()->json($permohonan->neraca);
    }

    public function neracaUpdate(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        $data = $request->validate([
            'kas_inventaris' => ['nullable', 'numeric'],
            'bank_simpanan' => ['nullable', 'numeric'],
            'piutang' => ['nullable', 'numeric'],
            'persediaan' => ['nullable', 'numeric'],
            'tanah_bangunan' => ['nullable', 'numeric'],
            'kebun' => ['nullable', 'numeric'],
            'kendaraan' => ['nullable', 'numeric'],
            'jumlah_aktiva_lancar' => ['nullable', 'numeric'],
            'jumlah_aktiva_tetap' => ['nullable', 'numeric'],
            'total_aktiva' => ['nullable', 'numeric'],
            'hutang_pendek' => ['nullable', 'numeric'],
            'hutang_panjang' => ['nullable', 'numeric'],
            'jumlah_hutang' => ['nullable', 'numeric'],
            'jumlah_modal' => ['nullable', 'numeric'],
            'total_pasiva' => ['nullable', 'numeric'],
            'selisih_cek' => ['nullable', 'numeric'],
        ]);

        $neraca = AnalisisNeraca::query()->updateOrCreate(
            ['permohonan_id' => $permohonan->id],
            array_merge($data, ['parameter_kebijakan_id' => $permohonan->parameter_kebijakan_id])
        );

        $aktor = $request->user();
        abort_unless($aktor instanceof User, 401);
        $this->bukuBesar->catat('ANALISIS', $permohonan, $aktor, 'Neraca disimpan', ['bagian' => 'neraca']);

        return response()->json($neraca);
    }

    public function labaRugiShow(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        return response()->json($permohonan->labaRugi);
    }

    public function labaRugiUpdate(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        $data = $request->validate([
            'jumlah_bulan_berjalan' => ['nullable', 'integer', 'between:1,12'],
            'penghasilan_pemohon' => ['nullable', 'numeric'],
            'penghasilan_pasangan_gaji' => ['nullable', 'numeric'],
            'penghasilan_lain_pemohon' => ['nullable', 'numeric'],
            'penghasilan_pasangan_tpp' => ['nullable', 'numeric'],
            'biaya_listrik_air' => ['nullable', 'numeric'],
            'biaya_transport' => ['nullable', 'numeric'],
            'biaya_sewa' => ['nullable', 'numeric'],
            'biaya_gaji' => ['nullable', 'numeric'],
            'biaya_pendidikan' => ['nullable', 'numeric'],
            'biaya_kesehatan' => ['nullable', 'numeric'],
            'biaya_konsumsi' => ['nullable', 'numeric'],
            'biaya_cicilan_lain' => ['nullable', 'numeric'],
            'biaya_telepon' => ['nullable', 'numeric'],
            'biaya_kebersihan' => ['nullable', 'numeric'],
            'biaya_lain' => ['nullable', 'numeric'],
            'total_pendapatan' => ['nullable', 'numeric'],
            'total_pengeluaran' => ['nullable', 'numeric'],
            'surplus_per_bulan' => ['nullable', 'numeric'],
            'surplus_6_bulan' => ['nullable', 'numeric'],
        ]);

        $labaRugi = AnalisisLabaRugi::query()->updateOrCreate(
            ['permohonan_id' => $permohonan->id],
            array_merge($data, ['parameter_kebijakan_id' => $permohonan->parameter_kebijakan_id])
        );

        $aktor = $request->user();
        abort_unless($aktor instanceof User, 401);
        $this->bukuBesar->catat('ANALISIS', $permohonan, $aktor, 'Laba rugi disimpan', ['bagian' => 'laba_rugi']);

        return response()->json($labaRugi);
    }

    public function kapasitasShow(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        return response()->json($permohonan->kapasitasBayar);
    }

    public function kapasitasCompute(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        $labaRugi = $permohonan->labaRugi;

        if ($labaRugi === null || $labaRugi->getAttribute('surplus_per_bulan') === null) {
            return response()->json(['message' => 'Laba rugi / surplus belum diisi.'], 422);
        }

        $kapasitas = $this->kapasitas->compute($permohonan);
        $dscr = (float) $kapasitas->dscr_rekomendasi;
        $this->ruleAudit->evaluate($permohonan, $request->user() instanceof User ? $request->user() : null);

        $this->ledger->record(
            LedgerEvent::AnalisisDisimpan,
            'permohonan_kredit',
            (string) $permohonan->id,
            ['bagian' => 'kapasitas', 'parameter_kebijakan_id' => $permohonan->parameter_kebijakan_id, 'dscr' => $dscr],
            $request->user(),
        );

        $aktor = $request->user();
        abort_unless($aktor instanceof User, 401);
        $this->bukuBesar->catat('ANALISIS', $permohonan, $aktor, 'Analisis kapasitas disimpan', [
            'bagian' => 'kapasitas',
            'dscr' => $dscr,
        ]);

        return response()->json($kapasitas);
    }

    public function scoringShow(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        return response()->json($permohonan->scoring5c);
    }

    public function scoringCompute(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        $data = $request->validate([
            'skor_condition' => ['required', 'numeric', 'between:1,5'],
            'justifikasi_character' => ['nullable', 'string'],
            'justifikasi_capacity' => ['nullable', 'string'],
            'justifikasi_capital' => ['nullable', 'string'],
            'justifikasi_collateral' => ['nullable', 'string'],
            'justifikasi_condition' => ['nullable', 'string'],
        ]);

        $hasil = app(Scoring5CService::class)->hitung($permohonan, (float) $data['skor_condition']);

        $scoring = AnalisisScoring5c::query()->updateOrCreate(
            ['permohonan_id' => $permohonan->id],
            [
                'parameter_kebijakan_id' => $permohonan->parameter_kebijakan_id,
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
                'justifikasi_character' => $data['justifikasi_character'] ?? null,
                'justifikasi_capacity' => $data['justifikasi_capacity'] ?? null,
                'justifikasi_capital' => $data['justifikasi_capital'] ?? null,
                'justifikasi_collateral' => $data['justifikasi_collateral'] ?? null,
                'justifikasi_condition' => $data['justifikasi_condition'] ?? null,
            ]
        );

        $this->ledger->record(
            LedgerEvent::AnalisisDisimpan,
            'permohonan_kredit',
            (string) $permohonan->id,
            ['bagian' => 'scoring', 'skor_total' => $hasil['skor_total'], 'rating' => $hasil['rating']],
            $request->user(),
        );

        $aktor = $request->user();
        abort_unless($aktor instanceof User, 401);
        $this->bukuBesar->catat('ANALISIS', $permohonan, $aktor, 'Penilaian 5C disimpan', [
            'bagian' => 'scoring',
            'skor_total' => $hasil['skor_total'],
            'rating' => $hasil['rating'],
        ]);

        return response()->json($scoring);
    }

    public function redFlags(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        return response()->json(array_map(
            static fn (array $flag): array => [
                'kode' => $flag['kode'],
                'level' => $flag['level']->value,
                'pesan' => $flag['pesan'],
            ],
            app(RedFlagEvaluator::class)->evaluate($permohonan),
        ));
    }

    public function rekomendasiGenerate(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        $scoring = $permohonan->scoring5c;

        if ($scoring === null) {
            return response()->json(['message' => 'Scoring 5C belum dihitung.'], 422);
        }

        $hasil = app(RekomendasiService::class)->generate($permohonan, ['rating' => $scoring->rating, 'skor_total' => $scoring->skor_total]);

        $rekomendasi = AnalisisRekomendasi::query()->updateOrCreate(
            ['permohonan_id' => $permohonan->id],
            array_merge($hasil, ['parameter_kebijakan_id' => $permohonan->parameter_kebijakan_id])
        );

        $aktor = $request->user();
        abort_unless($aktor instanceof User, 401);
        $this->bukuBesar->catat('ANALISIS', $permohonan, $aktor, 'Rekomendasi dibuat', ['bagian' => 'rekomendasi']);

        return response()->json($rekomendasi);
    }
}
