<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kredit\CairkanKreditRequest;
use App\Models\Kredit;
use App\Models\PermohonanKredit;
use App\Services\Kredit\KreditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class KreditController extends Controller
{
    public function create(PermohonanKredit $permohonan): View
    {
        abort_unless($permohonan->cabang_id === auth()->user()?->cabang_id, 403);

        return view('kredit.cairkan', ['permohonan' => $permohonan->load('debitur', 'produk')]);
    }

    public function store(CairkanKreditRequest $request, PermohonanKredit $permohonan, KreditService $service): RedirectResponse
    {
        abort_unless($permohonan->cabang_id === $request->user()?->cabang_id, 403);

        $kredit = $service->cairkan($permohonan, $request->user(), $request->validated());

        return redirect()->route('kredit.show', $kredit)->with('status', __('kredit.cairkan'));
    }

    public function index(Request $request): View
    {
        $query = Kredit::query()
            ->with('permohonan.debitur')
            ->where('cabang_id', $request->user()?->cabang_id);

        return view('kredit.index', [
            'kredit' => $query->latest('tanggal_cair')
                ->paginate((int) config('loan.pagination.per_page'))
                ->withQueryString(),
        ]);
    }

    public function show(Request $request, Kredit $kredit): View
    {
        abort_unless($kredit->cabang_id === $request->user()?->cabang_id, 403);

        return view('kredit.show', [
            'kredit' => $kredit->load([
                'permohonan.debitur',
                'jadwal' => fn ($query) => $query->orderBy('periode'),
                'pembayaran' => fn ($query) => $query->latest('tanggal_bayar'),
                'biaya', 'agunan',
            ]),
        ]);
    }
}
