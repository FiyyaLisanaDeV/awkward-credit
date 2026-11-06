<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BukuAgunan;
use App\Services\Register\BukuAgunanService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BukuAgunanController extends Controller
{
    public function index(Request $request): View
    {
        $query = BukuAgunan::query()
            ->selectRaw('agunan_key, count(*) as pemakaian, count(distinct cabang_id) as cabang_count, max(occurred_at) as terakhir')
            ->selectRaw('min(jenis_aset) as jenis_aset, min(no_dokumen) as no_dokumen, min(nama_pemilik) as nama_pemilik')
            ->where('peristiwa', 'DIPAKAI')
            ->groupBy('agunan_key')
            ->orderByDesc('terakhir');

        return view('buku-agunan.index', [
            'agunan' => $query->paginate((int) config('loan.pagination.per_page'))->withQueryString(),
        ]);
    }

    public function show(Request $request, string $key, BukuAgunanService $service): View
    {
        $riwayat = $service->riwayat($key);

        abort_if($riwayat->isEmpty(), 404);

        $cabangIds = $riwayat->pluck('cabang_id')->unique();

        return view('buku-agunan.show', [
            'key' => $key,
            'riwayat' => $riwayat,
            'lintasCabang' => $cabangIds->count() > 1,
            'jumlahCabang' => $cabangIds->count(),
        ]);
    }
}
