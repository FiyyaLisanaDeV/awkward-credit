<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BukuBesarKredit;
use App\Models\PermohonanKredit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BukuBesarController extends Controller
{
    public function index(Request $request): View
    {
        $query = BukuBesarKredit::query()
            ->with(['permohonan.debitur', 'aktor'])
            ->where('cabang_id', $request->user()?->cabang_id);

        if ($tahap = $request->string('tahap')->toString()) {
            $query->where('tahap', $tahap);
        }

        if ($keyword = $request->string('q')->toString()) {
            $query->whereHas('permohonan', static function ($sub) use ($keyword): void {
                $sub->where('no_permohonan', 'ilike', '%'.$keyword.'%');
            });
        }

        return view('buku-besar.index', [
            'entri' => $query->orderByDesc('seq')
                ->paginate((int) config('loan.pagination.per_page'))
                ->withQueryString(),
            'tahap' => $tahap,
            'q' => $keyword,
        ]);
    }

    public function show(Request $request, PermohonanKredit $permohonan): View
    {
        abort_unless($permohonan->cabang_id === $request->user()?->cabang_id, 403);

        return view('buku-besar.show', [
            'permohonan' => $permohonan->load(['debitur', 'produk', 'kredit']),
            'entri' => BukuBesarKredit::query()
                ->with('aktor')
                ->where('permohonan_id', $permohonan->id)
                ->orderBy('seq')
                ->get(),
        ]);
    }
}
