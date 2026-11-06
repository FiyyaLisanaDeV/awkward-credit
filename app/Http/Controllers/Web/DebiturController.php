<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Debitur;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DebiturController extends Controller
{
    public function index(Request $request): View
    {
        $query = Debitur::query()
            ->withCount('permohonan')
            ->where('cabang_id', $request->user()?->cabang_id);

        if ($keyword = $request->string('q')->toString()) {
            $query->where(static function ($sub) use ($keyword): void {
                $sub->where('nama_ktp', 'ilike', '%'.$keyword.'%')
                    ->orWhere('no_ktp', 'like', '%'.$keyword.'%')
                    ->orWhere('cif', 'ilike', '%'.$keyword.'%');
            });
        }

        return view('debitur.index', [
            'debitur' => $query->orderBy('nama_ktp')->paginate((int) config('loan.pagination.per_page'))->withQueryString(),
            'q' => $keyword,
        ]);
    }

    public function create(): View
    {
        return view('debitur.create');
    }

    public function show(Request $request, Debitur $debitur): View
    {
        abort_unless($debitur->cabang_id === $request->user()?->cabang_id, 403);

        return view('debitur.show', [
            'debitur' => $debitur->load(['pasangan', 'usaha', 'aset', 'permohonan' => fn ($q) => $q->latest('tanggal_permohonan')]),
        ]);
    }
}
