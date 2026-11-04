<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\LedgerEvent;
use App\Enums\StatusPermohonan;
use App\Http\Controllers\Concerns\ScopesByCabang;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permohonan\StorePermohonanRequest;
use App\Models\Cabang;
use App\Models\Debitur;
use App\Models\PermohonanKredit;
use App\Services\Ledger\LedgerService;
use App\Services\Policy\PolicyParameterResolver;
use App\Services\Rules\Rules\BR04BalloonWajib;
use App\Services\Rules\Rules\BR08ParameterTersimpan;
use App\Services\Workflow\NomorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class PermohonanController extends Controller
{
    use ScopesByCabang;

    public function __construct(
        private readonly NomorService $nomor,
        private readonly PolicyParameterResolver $parameters,
        private readonly LedgerService $ledger,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = PermohonanKredit::query()
            ->with('debitur')
            ->where('cabang_id', $request->user()->cabang_id);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return response()->json($query->latest('tanggal_permohonan')->paginate((int) config('loan.pagination.per_page')));
    }

    public function store(StorePermohonanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $debitur = Debitur::query()->findOrFail($data['debitur_id']);
        $this->ensureSameCabang($debitur, $request->user());

        $cabang = Cabang::query()->findOrFail($debitur->cabang_id);
        $parameter = $this->parameters->active();

        $permohonan = DB::transaction(function () use ($data, $cabang, $parameter, $request): PermohonanKredit {
            $permohonan = PermohonanKredit::query()->create(array_merge($data, [
                'cabang_id' => $cabang->id,
                'no_permohonan' => $this->nomor->permohonan($cabang),
                'parameter_kebijakan_id' => $parameter->id,
                'ao_id' => $request->user()->id,
                'status' => StatusPermohonan::DRAFT->value,
                'prioritas' => $data['prioritas'] ?? 'NORMAL',
                'pinjaman_ke' => $data['pinjaman_ke'] ?? 1,
            ]));

            $this->ledger->record(
                LedgerEvent::PermohonanDibuat,
                'permohonan_kredit',
                (string) $permohonan->id,
                [
                    'no_permohonan' => $permohonan->no_permohonan,
                    'debitur_id' => $permohonan->debitur_id,
                    'produk_id' => $permohonan->produk_id,
                    'plafon_diajukan' => (float) $permohonan->plafon_diajukan,
                ],
                $request->user(),
            );

            return $permohonan;
        });

        return response()->json($permohonan, 201);
    }

    public function show(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        return response()->json($permohonan->load([
            'debitur', 'produk', 'slikDebitur.kolektibilitas', 'kapasitasBayar', 'scoring5c', 'rekomendasi',
        ]));
    }

    public function update(StorePermohonanRequest $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        abort_unless($permohonan->status === StatusPermohonan::DRAFT->value, 409, 'Hanya draft yang dapat diubah.');

        $permohonan->update($request->validated());

        return response()->json($permohonan->fresh());
    }

    public function submit(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        abort_unless($permohonan->status === StatusPermohonan::DRAFT->value, 409, 'Permohonan bukan draft.');

        $balloon = app(BR04BalloonWajib::class)->validate($permohonan);
        $parameter = app(BR08ParameterTersimpan::class)->validate($permohonan);

        if (! $balloon->passed) {
            return response()->json(['message' => $balloon->message, 'kode' => 'BR-04'], 422);
        }

        if (! $parameter->passed) {
            return response()->json(['message' => $parameter->message, 'kode' => 'BR-08'], 422);
        }

        $permohonan->update(['status' => StatusPermohonan::SUBMITTED->value]);

        $this->ledger->record(
            LedgerEvent::PermohonanDiajukan,
            'permohonan_kredit',
            (string) $permohonan->id,
            ['status' => $permohonan->status],
            $request->user(),
        );

        return response()->json($permohonan->fresh());
    }
}
