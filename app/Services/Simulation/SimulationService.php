<?php

declare(strict_types=1);

namespace App\Services\Simulation;

use App\Domain\Policy\PolicyParameters;
use App\Enums\LedgerEvent;
use App\Enums\ProdukTipe;
use App\Enums\SistemSukuBunga;
use App\Models\Debitur;
use App\Models\PermohonanKredit;
use App\Models\SimulasiKredit;
use App\Models\User;
use App\Services\Ledger\ChainHasher;
use App\Services\Ledger\LedgerService;
use Illuminate\Support\Facades\DB;

final class SimulationService
{
    public function __construct(private readonly LedgerService $ledger) {}

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $output
     */
    public function record(
        User $user,
        string $label,
        ProdukTipe $produkTipe,
        SistemSukuBunga $sistem,
        PolicyParameters $parameters,
        array $input,
        array $output,
        int $durationMs = 0,
        ?PermohonanKredit $permohonan = null,
        ?Debitur $debitur = null,
    ): SimulasiKredit {
        return DB::transaction(function () use ($user, $label, $produkTipe, $sistem, $parameters, $input, $output, $durationMs, $permohonan, $debitur): SimulasiKredit {
            $simulasi = SimulasiKredit::query()->create([
                'cabang_id' => $user->cabang_id,
                'user_id' => $user->getKey(),
                'permohonan_id' => $permohonan?->getKey(),
                'debitur_id' => $debitur?->getKey(),
                'label' => $label,
                'produk_tipe' => $produkTipe->value,
                'sistem_suku_bunga' => $sistem->value,
                'parameter_kebijakan_id' => $parameters->id,
                'parameter_version' => $parameters->version,
                'engine_version' => (string) config('loan.engine_version'),
                'input' => $input,
                'input_hash' => hash('sha256', ChainHasher::canonicalJson($input)),
                'output' => $output,
                'plafon' => $input['plafon'] ?? null,
                'rate' => $input['rate'] ?? null,
                'tenor' => $input['tenor'] ?? null,
                'angsuran' => $output['angsuran']['total'] ?? null,
                'dscr' => $output['dscr'] ?? null,
                'plafon_max' => $output['plafon_max']['flat'] ?? null,
                'ltv' => $output['ltv'] ?? null,
                'idir' => $output['idir'] ?? null,
                'status_kelayakan' => $output['status_dscr'] ?? null,
                'durasi_ms' => $durationMs,
                'dibuat_pada' => now()->startOfSecond(),
            ]);

            $ledger = $this->ledger->record(
                LedgerEvent::SimulasiDijalankan,
                'simulasi_kredit',
                (string) $simulasi->id,
                [
                    'simulasi_id' => (string) $simulasi->id,
                    'input_hash' => (string) $simulasi->input_hash,
                    'engine_version' => (string) $simulasi->engine_version,
                    'parameter_version' => (string) $simulasi->parameter_version,
                    'hasil_ringkas' => [
                        'angsuran' => $simulasi->angsuran,
                        'dscr' => $simulasi->dscr,
                        'status_kelayakan' => $simulasi->status_kelayakan,
                    ],
                ],
                $user,
            );

            $simulasi->forceFill(['ledger_id' => $ledger->id])->save();

            return $simulasi;
        });
    }
}
