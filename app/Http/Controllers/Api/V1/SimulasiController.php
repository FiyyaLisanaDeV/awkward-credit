<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Policy\PolicyParameters;
use App\Enums\ProdukTipe;
use App\Enums\SistemSukuBunga;
use App\Http\Controllers\Concerns\ScopesByCabang;
use App\Http\Controllers\Controller;
use App\Models\RefParameterKebijakan;
use App\Models\SimulasiKredit;
use App\Services\Ledger\ChainHasher;
use App\Services\Simulation\SimulationEngine;
use App\Services\Simulation\SimulationRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class SimulasiController extends Controller
{
    use ScopesByCabang;

    public function index(Request $request): JsonResponse
    {
        $query = SimulasiKredit::query()->where('cabang_id', $request->user()->cabang_id);

        if ($label = $request->string('q')->toString()) {
            $query->where('label', 'ilike', '%'.$label.'%');
        }

        return response()->json($query->latest('dibuat_pada')->paginate((int) config('loan.pagination.per_page')));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'produk_tipe' => ['required', Rule::enum(ProdukTipe::class)],
            'sistem_suku_bunga' => ['required', Rule::enum(SistemSukuBunga::class)],
            'plafon' => ['required', 'numeric', 'gt:0'],
            'rate' => ['required', 'numeric', 'gt:0', 'lte:0.5'],
            'tenor' => ['required', 'integer', 'between:1,360'],
            'surplus_bulanan' => ['required', 'numeric', 'min:0'],
            'penghasilan_bersih' => ['nullable', 'numeric', 'min:0'],
            'nilai_jaminan' => ['nullable', 'numeric', 'min:0'],
            'biaya_operasional_tahunan' => ['nullable', 'numeric', 'min:0'],
            'periode_perputaran_hari' => ['nullable', 'numeric', 'min:0'],
            'pembelian_bulanan' => ['nullable', 'numeric', 'min:0'],
            'hari_persediaan' => ['nullable', 'numeric', 'min:0'],
            'mk_tersedia' => ['nullable', 'numeric', 'min:0'],
            'permohonan_id' => ['nullable', 'uuid', 'exists:permohonan_kredit,id'],
            'debitur_id' => ['nullable', 'uuid', 'exists:debitur,id'],
        ]);

        $simulasi = app(SimulationRunner::class)->execute($data, $request->user());

        return response()->json($simulasi, 201);
    }

    public function show(Request $request, SimulasiKredit $simulasi): JsonResponse
    {
        $this->ensureSameCabang($simulasi, $request->user());

        return response()->json($simulasi);
    }

    public function replay(Request $request, SimulasiKredit $simulasi): JsonResponse
    {
        $this->ensureSameCabang($simulasi, $request->user());

        $parameter = RefParameterKebijakan::query()->findOrFail($simulasi->parameter_kebijakan_id);
        $output = app(SimulationEngine::class)->run(
            is_array($simulasi->input) ? $simulasi->input : [],
            PolicyParameters::fromModel($parameter),
        );

        $expected = ChainHasher::canonicalJson(is_array($simulasi->output) ? $simulasi->output : []);
        $actual = ChainHasher::canonicalJson($output);

        return response()->json([
            'identik' => $expected === $actual,
            'expected' => $expected,
            'actual' => $actual,
        ]);
    }
}
