<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\StatusPermohonan;
use App\Http\Controllers\Controller;
use App\Models\PermohonanKredit;
use App\Services\Approval\ApprovalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ApprovalController extends Controller
{
    public function __construct(private readonly ApprovalService $approval) {}

    public function index(Request $request): View
    {
        $query = PermohonanKredit::query()
            ->with('debitur')
            ->where('cabang_id', $request->user()?->cabang_id)
            ->whereIn('status', [
                StatusPermohonan::SUBMITTED->value,
                StatusPermohonan::ANALYZED->value,
                StatusPermohonan::RECOMMENDED->value,
            ]);

        return view('workflow.index', [
            'permohonan' => $query->latest('tanggal_permohonan')
                ->paginate((int) config('loan.pagination.per_page'))
                ->withQueryString(),
        ]);
    }

    public function show(Request $request, PermohonanKredit $permohonan): View
    {
        abort_unless($permohonan->cabang_id === $request->user()?->cabang_id, 403);

        $permohonan->load([
            'debitur', 'produk',
            'approvalLog' => fn ($query) => $query->orderBy('level')->orderBy('timestamp'),
            'approvalLog.user', 'approvalLog.role',
            'kapasitasBayar', 'scoring5c', 'rekomendasi',
        ]);

        return view('workflow.show', [
            'permohonan' => $permohonan,
            'nextLevel' => $this->approval->nextLevel($permohonan),
            'wajibLevel' => $this->approval->requiredLevels($permohonan),
        ]);
    }
}
