<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PermohonanKredit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class PermohonanController extends Controller
{
    public function index(Request $request): View
    {
        $query = PermohonanKredit::query()
            ->with(['debitur', 'produk'])
            ->where('cabang_id', $request->user()?->cabang_id);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return view('permohonan.index', [
            'permohonan' => $query->latest('tanggal_permohonan')->paginate((int) config('loan.pagination.per_page'))->withQueryString(),
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('permohonan.create');
    }

    public function show(Request $request, PermohonanKredit $permohonan): View
    {
        abort_unless($permohonan->cabang_id === $request->user()?->cabang_id, 403);

        return view('permohonan.show', [
            'permohonan' => $permohonan->load([
                'debitur', 'produk', 'slikDebitur.kolektibilitas', 'kapasitasBayar', 'scoring5c', 'rekomendasi',
            ]),
        ]);
    }
}
