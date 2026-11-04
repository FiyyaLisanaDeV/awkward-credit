<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusKredit;
use App\Enums\StatusPermohonan;
use App\Http\Controllers\Controller;
use App\Models\Kredit;
use App\Models\PermohonanKredit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cabangId = $request->user()->cabang_id;

        $perStatus = PermohonanKredit::query()
            ->where('cabang_id', $cabangId)
            ->select('status', DB::raw('count(*) as jumlah'), DB::raw('coalesce(sum(plafon_diajukan),0) as total_plafon'))
            ->groupBy('status')
            ->get();

        return response()->json([
            'per_status' => $perStatus,
            'recent' => PermohonanKredit::query()
                ->with('debitur')
                ->where('cabang_id', $cabangId)
                ->latest('tanggal_permohonan')
                ->limit((int) config('loan.ui.dashboard_recent'))
                ->get(),
            'kredit_aktif' => Kredit::query()->where('cabang_id', $cabangId)->where('status', StatusKredit::ACTIVE->value)->count(),
            'draft' => PermohonanKredit::query()->where('cabang_id', $cabangId)->where('status', StatusPermohonan::DRAFT->value)->count(),
        ]);
    }
}
