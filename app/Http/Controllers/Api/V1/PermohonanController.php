<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ScopesByCabang;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permohonan\StorePermohonanRequest;
use App\Models\Debitur;
use App\Models\PermohonanKredit;
use App\Services\Permohonan\PermohonanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PermohonanController extends Controller
{
    use ScopesByCabang;

    public function __construct(private readonly PermohonanService $service) {}

    public function index(Request $request): JsonResponse
    {
        $query = PermohonanKredit::query()
            ->with('debitur')
            ->where('cabang_id', $request->user()->cabang_id);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return response()->json($query->latest('tanggal_permohonan')->paginate((int) config('loan.pagination.per_page')));
    }

    public function store(StorePermohonanRequest $request): JsonResponse
    {
        $debitur = Debitur::query()->findOrFail($request->validated('debitur_id'));
        $this->ensureSameCabang($debitur, $request->user());

        $permohonan = $this->service->create($request->validated(), $request->user());

        return response()->json($permohonan, 201);
    }

    public function show(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        return response()->json($permohonan->load([
            'debitur', 'produk', 'slikDebitur.kolektibilitas', 'kapasitasBayar', 'scoring5c', 'rekomendasi',
        ]));
    }

    public function update(StorePermohonanRequest $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        return response()->json($this->service->update($permohonan, $request->validated()));
    }

    public function submit(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        return response()->json($this->service->submit($permohonan, $request->user()));
    }
}
