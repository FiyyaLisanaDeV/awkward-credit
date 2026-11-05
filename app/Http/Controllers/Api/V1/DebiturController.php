<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ScopesByCabang;
use App\Http\Controllers\Controller;
use App\Http\Requests\Debitur\StoreDebiturRequest;
use App\Models\Debitur;
use App\Models\DebiturAset;
use App\Services\Debitur\DebiturService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DebiturController extends Controller
{
    use ScopesByCabang;

    public function __construct(private readonly DebiturService $debiturService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Debitur::query()->where('cabang_id', $request->user()->cabang_id);

        if ($keyword = $request->string('q')->toString()) {
            $query->where('nama_ktp', 'ilike', '%'.$keyword.'%');
        }

        return response()->json($query->orderBy('nama_ktp')->paginate((int) config('loan.pagination.per_page')));
    }

    public function store(StoreDebiturRequest $request): JsonResponse
    {
        $debitur = $this->debiturService->create($request->validated(), $request->user());

        return response()->json($debitur, 201);
    }

    public function show(Request $request, Debitur $debitur): JsonResponse
    {
        $this->ensureSameCabang($debitur, $request->user());

        return response()->json($debitur->load(['pasangan', 'usaha', 'siklusUsaha', 'aset']));
    }

    public function update(StoreDebiturRequest $request, Debitur $debitur): JsonResponse
    {
        $this->ensureSameCabang($debitur, $request->user());

        return response()->json($this->debiturService->update($debitur, $request->validated()));
    }

    public function storeAset(Request $request, Debitur $debitur): JsonResponse
    {
        $this->ensureSameCabang($debitur, $request->user());

        $data = $request->validate([
            'urutan' => ['required', 'integer', 'min:1'],
            'jenis_aset' => ['required', 'string', 'max:100'],
            'kategori_pojk_id' => ['required', 'uuid', 'exists:ref_kategori_agunan_pojk,id'],
            'luas_jumlah' => ['required', 'numeric', 'gt:0'],
            'harga_taksasi_satuan' => ['required', 'numeric', 'min:0'],
            'nilai_taksasi' => ['required', 'numeric', 'min:0'],
            'persen_diperhitungkan' => ['required', 'numeric', 'between:0,1'],
            'status_kepemilikan' => ['required', 'string', 'max:30'],
            'nama_pemilik' => ['nullable', 'string', 'max:100'],
            'hubungan_pemohon' => ['nullable', 'string', 'max:50'],
            'no_dokumen' => ['nullable', 'string', 'max:50'],
        ]);

        $data['nilai_diperhitungkan'] = (float) $data['nilai_taksasi'] * (float) $data['persen_diperhitungkan'];

        $aset = $debitur->aset()->create($data);

        return response()->json($aset, 201);
    }

    public function updateAset(Request $request, DebiturAset $aset): JsonResponse
    {
        $this->ensureSameCabang($aset->debitur, $request->user());

        $data = $request->validate([
            'jenis_aset' => ['sometimes', 'string', 'max:100'],
            'nilai_taksasi' => ['sometimes', 'numeric', 'min:0'],
            'persen_diperhitungkan' => ['sometimes', 'numeric', 'between:0,1'],
            'status_kepemilikan' => ['sometimes', 'string', 'max:30'],
        ]);

        $aset->update($data);

        if (isset($data['nilai_taksasi'], $data['persen_diperhitungkan'])) {
            $aset->update([
                'nilai_diperhitungkan' => (float) $data['nilai_taksasi'] * (float) $data['persen_diperhitungkan'],
            ]);
        }

        return response()->json($aset->fresh());
    }

    public function destroyAset(Request $request, DebiturAset $aset): JsonResponse
    {
        $this->ensureSameCabang($aset->debitur, $request->user());
        $aset->delete();

        return response()->json(null, 204);
    }
}
