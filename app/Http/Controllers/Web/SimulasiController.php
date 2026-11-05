<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SimulasiKredit;
use App\Services\Simulation\SimulationRunner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class SimulasiController extends Controller
{
    public function index(Request $request): View
    {
        return view('simulasi.index', [
            'simulasi' => SimulasiKredit::query()
                ->where('cabang_id', $request->user()?->cabang_id)
                ->latest('dibuat_pada')
                ->paginate((int) config('loan.pagination.per_page')),
        ]);
    }

    public function create(Request $request): View
    {
        return view('simulasi.create', [
            'permohonan' => $request->string('permohonan')->toString(),
        ]);
    }

    public function show(Request $request, SimulasiKredit $simulasi): View
    {
        abort_unless($simulasi->cabang_id === $request->user()?->cabang_id, 403);

        return view('simulasi.show', ['simulasi' => $simulasi]);
    }

    public function bandingkan(Request $request, SimulationRunner $runner): View
    {
        $ids = array_values(array_filter([
            $request->string('awal')->toString(),
            $request->string('akhir')->toString(),
        ]));

        $terpilih = SimulasiKredit::query()
            ->where('cabang_id', $request->user()?->cabang_id)
            ->whereIn('id', $ids)
            ->get();

        $awal = $terpilih->firstWhere('id', $ids[0] ?? '');
        $akhir = $terpilih->firstWhere('id', $ids[1] ?? '');

        $diff = $awal !== null && $akhir !== null
            ? $runner->diff($this->arrayOf($awal->output), $this->arrayOf($akhir->output))
            : [];

        return view('simulasi.bandingkan', [
            'daftar' => SimulasiKredit::query()
                ->where('cabang_id', $request->user()?->cabang_id)
                ->latest('dibuat_pada')
                ->limit((int) config('loan.pagination.per_page'))
                ->get(),
            'awal' => $awal,
            'akhir' => $akhir,
            'diff' => $diff,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function arrayOf(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }
}
