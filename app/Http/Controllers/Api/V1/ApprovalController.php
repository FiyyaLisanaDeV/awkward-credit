<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusPermohonan;
use App\Http\Controllers\Concerns\ScopesByCabang;
use App\Http\Controllers\Controller;
use App\Models\PermohonanKredit;
use App\Services\Approval\ApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ApprovalController extends Controller
{
    use ScopesByCabang;

    public function __construct(private readonly ApprovalService $approval) {}

    public function inbox(Request $request): JsonResponse
    {
        $query = PermohonanKredit::query()
            ->with('debitur')
            ->where('cabang_id', $request->user()->cabang_id)
            ->whereIn('status', [
                StatusPermohonan::SUBMITTED->value,
                StatusPermohonan::ANALYZED->value,
                StatusPermohonan::RECOMMENDED->value,
            ]);

        return response()->json($query->latest('tanggal_permohonan')->paginate((int) config('loan.pagination.per_page')));
    }

    public function approve(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        $data = $request->validate(['alasan' => ['nullable', 'string']]);

        return response()->json($this->approval->approve($permohonan, $request->user(), $data['alasan'] ?? null));
    }

    public function reject(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        $data = $request->validate(['alasan' => ['required', 'string', 'max:500']]);

        return response()->json($this->approval->reject($permohonan, $request->user(), $data['alasan']));
    }

    public function returnToAo(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        $data = $request->validate(['alasan' => ['required', 'string', 'max:500']]);

        return response()->json($this->approval->returnToAo($permohonan, $request->user(), $data['alasan']));
    }
}
