<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ScopesByCabang;
use App\Http\Controllers\Controller;
use App\Models\PermohonanKredit;
use App\Models\PermohonanSlikDebitur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SlikController extends Controller
{
    use ScopesByCabang;

    public function index(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        return response()->json([
            'permintaan' => $permohonan->slikPermintaan()->latest('tanggal_permintaan')->get(),
            'fasilitas' => $permohonan->slikDebitur()->with('kolektibilitas')->orderBy('urutan')->get(),
        ]);
    }

    public function store(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        $data = $request->validate([
            'nama_lembaga' => ['required', 'string', 'max:100'],
            'jenis_fasilitas' => ['required', 'string', 'max:30'],
            'no_fasilitas' => ['nullable', 'string', 'max:50'],
            'plafon' => ['required', 'numeric', 'min:0'],
            'baki_debet' => ['required', 'numeric', 'min:0'],
            'suku_bunga' => ['nullable', 'numeric', 'between:0,1'],
            'jangka_waktu_bulan' => ['nullable', 'integer', 'min:1'],
            'angsuran_bulanan' => ['nullable', 'numeric', 'min:0'],
            'kolektibilitas_id' => ['required', 'uuid', 'exists:ref_kolektibilitas,id'],
            'kondisi' => ['nullable', 'string', 'max:20'],
            'hari_tunggakan' => ['nullable', 'integer', 'min:0'],
            'tanggal_data' => ['nullable', 'date'],
        ]);

        $urutan = (int) $permohonan->slikDebitur()->max('urutan') + 1;

        $fasilitas = $permohonan->slikDebitur()->create(array_merge($data, ['urutan' => $urutan]));

        return response()->json($fasilitas, 201);
    }

    public function destroy(Request $request, PermohonanSlikDebitur $slik): JsonResponse
    {
        $this->ensureSameCabang($slik->permohonan, $request->user());
        $slik->delete();

        return response()->json(null, 204);
    }
}
