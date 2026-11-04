<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ScopesByCabang;
use App\Http\Controllers\Controller;
use App\Models\Kredit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class KreditController extends Controller
{
    use ScopesByCabang;

    public function index(Request $request): JsonResponse
    {
        $query = Kredit::query()
            ->with('permohonan.debitur')
            ->where('cabang_id', $request->user()->cabang_id);

        return response()->json($query->latest('tanggal_cair')->paginate((int) config('loan.pagination.per_page')));
    }

    public function show(Request $request, Kredit $kredit): JsonResponse
    {
        $this->ensureSameCabang($kredit, $request->user());

        return response()->json($kredit->load(['permohonan.debitur', 'jadwal', 'biaya', 'asuransi', 'agunan']));
    }

    public function jadwal(Request $request, Kredit $kredit): JsonResponse
    {
        $this->ensureSameCabang($kredit, $request->user());

        return response()->json($kredit->jadwal()->orderBy('periode')->get());
    }
}
