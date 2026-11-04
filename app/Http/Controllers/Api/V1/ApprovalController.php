<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\AksiApproval;
use App\Enums\LedgerEvent;
use App\Enums\StatusPermohonan;
use App\Enums\StatusVersi;
use App\Http\Controllers\Concerns\ScopesByCabang;
use App\Http\Controllers\Controller;
use App\Models\ApprovalLog;
use App\Models\Notifikasi;
use App\Models\PermohonanKredit;
use App\Models\RefApprovalMatrix;
use App\Services\Ledger\LedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

final class ApprovalController extends Controller
{
    use ScopesByCabang;

    public function __construct(private readonly LedgerService $ledger) {}

    public function inbox(Request $request): JsonResponse
    {
        $query = PermohonanKredit::query()
            ->with('debitur')
            ->where('cabang_id', $request->user()->cabang_id)
            ->whereIn('status', [
                StatusPermohonan::SUBMITTED->value,
                StatusPermohonan::ANALYZED->value,
                StatusPermohonan::RECOMMENDED->value,
            ]);

        return response()->json($query->latest('tanggal_permohonan')->paginate((int) config('loan.pagination.per_page')));
    }

    public function approve(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        $data = $request->validate(['alasan' => ['nullable', 'string']]);
        $level = $this->nextLevel($permohonan);

        abort_if($level === null, 409, 'Persetujuan sudah lengkap atau matriks tidak tersedia.');

        $roleName = $this->roleForLevel($permohonan, $level);

        abort_unless($request->user()->hasRole($roleName) || $request->user()->hasRole('ADMIN'), 403, 'Peran Anda tidak berwenang pada level ini.');

        $roleId = Role::query()->where('name', $roleName)->value('id');

        ApprovalLog::query()->create([
            'cabang_id' => $permohonan->cabang_id,
            'permohonan_id' => $permohonan->id,
            'level' => $level,
            'role_id' => $roleId,
            'user_id' => $request->user()->id,
            'aksi' => AksiApproval::Approve->value,
            'alasan' => $data['alasan'] ?? null,
        ]);

        $final = $level === $this->maxLevel($permohonan);

        $permohonan->update([
            'status' => $final ? StatusPermohonan::APPROVED->value : StatusPermohonan::RECOMMENDED->value,
        ]);

        $this->ledger->record(
            LedgerEvent::PermohonanDisetujui,
            'permohonan_kredit',
            (string) $permohonan->id,
            ['level' => $level, 'role' => $roleName, 'final' => $final],
            $request->user(),
        );

        Notifikasi::query()->create([
            'cabang_id' => $permohonan->cabang_id,
            'user_id' => $permohonan->ao_id,
            'tipe' => 'APPROVAL',
            'judul' => 'Permohonan disetujui level '.$level,
            'pesan' => 'Permohonan '.$permohonan->no_permohonan.' disetujui.',
            'entity_type' => 'permohonan_kredit',
            'entity_id' => $permohonan->id,
        ]);

        return response()->json($permohonan->fresh());
    }

    public function reject(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        $data = $request->validate(['alasan' => ['required', 'string', 'max:500']]);

        $permohonan->update(['status' => StatusPermohonan::REJECTED->value]);

        $this->ledger->record(
            LedgerEvent::PermohonanDitolak,
            'permohonan_kredit',
            (string) $permohonan->id,
            ['alasan' => $data['alasan']],
            $request->user(),
        );

        return response()->json($permohonan->fresh());
    }

    public function returnToAo(Request $request, PermohonanKredit $permohonan): JsonResponse
    {
        $this->ensureSameCabang($permohonan, $request->user());

        $data = $request->validate(['alasan' => ['required', 'string', 'max:500']]);

        $permohonan->update(['status' => StatusPermohonan::RETURNED->value]);

        $this->ledger->record(
            LedgerEvent::PermohonanDikembalikan,
            'permohonan_kredit',
            (string) $permohonan->id,
            ['alasan' => $data['alasan']],
            $request->user(),
        );

        return response()->json($permohonan->fresh());
    }

    private function matrix(PermohonanKredit $permohonan): ?RefApprovalMatrix
    {
        return RefApprovalMatrix::query()->where('status', StatusVersi::ACTIVE->value)->first();
    }

    /** @return list<array<mixed>> */
    private function matrixEntries(PermohonanKredit $permohonan): array
    {
        $raw = $this->matrix($permohonan)?->matrix;
        $entries = [];

        if (! is_array($raw)) {
            return $entries;
        }

        foreach ($raw as $entry) {
            if (is_array($entry)) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    private function maxLevel(PermohonanKredit $permohonan): int
    {
        $plafon = (float) ($permohonan->plafon_rekomendasi ?? $permohonan->plafon_diajukan);
        $level = 0;

        foreach ($this->matrixEntries($permohonan) as $entry) {
            $min = (float) ($entry['plafon_min'] ?? 0);
            $max = $entry['plafon_max'] ?? null;

            if ($plafon >= $min && ($max === null || $plafon <= (float) $max)) {
                $level = max($level, (int) $entry['level']);
            }
        }

        return $level;
    }

    private function roleForLevel(PermohonanKredit $permohonan, int $level): string
    {
        foreach ($this->matrixEntries($permohonan) as $entry) {
            if ((int) ($entry['level'] ?? 0) === $level) {
                return (string) $entry['role'];
            }
        }

        return 'ADMIN';
    }

    private function nextLevel(PermohonanKredit $permohonan): ?int
    {
        $max = $this->maxLevel($permohonan);

        if ($max === 0) {
            return null;
        }

        $sudah = (int) ApprovalLog::query()
            ->where('permohonan_id', $permohonan->id)
            ->where('aksi', AksiApproval::Approve->value)
            ->max('level');

        return $sudah + 1 <= $max ? $sudah + 1 : null;
    }
}
