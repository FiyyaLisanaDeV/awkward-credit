<?php

declare(strict_types=1);

namespace App\Services\Approval;

use App\Enums\AksiApproval;
use App\Enums\LedgerEvent;
use App\Enums\StatusPermohonan;
use App\Enums\StatusVersi;
use App\Models\ApprovalLog;
use App\Models\PermohonanKredit;
use App\Models\RefApprovalMatrix;
use App\Models\User;
use App\Services\Ledger\LedgerService;
use App\Services\Notifikasi\NotifikasiService;
use App\Services\Register\BukuBesarService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

final class ApprovalService
{
    public function __construct(
        private readonly LedgerService $ledger,
        private readonly NotifikasiService $notifikasi,
        private readonly BukuBesarService $bukuBesar,
    ) {}

    /**
     * Level persetujuan yang wajib untuk plafon permohonan (level dari entri
     * matriks yang band plafonnya memuat plafon). Satu sumber kebenaran untuk
     * ApprovalService dan BR-06.
     *
     * @return list<int>
     */
    public function requiredLevels(PermohonanKredit $permohonan): array
    {
        $plafon = $this->plafon($permohonan);
        $levels = [];

        foreach ($this->matrixEntries() as $entry) {
            $min = (float) ($entry['plafon_min'] ?? 0);
            $max = $entry['plafon_max'] ?? null;

            if ($plafon >= $min && ($max === null || $plafon <= (float) $max)) {
                $levels[(int) ($entry['level'] ?? 0)] = true;
            }
        }

        $levels = array_keys($levels);
        sort($levels);

        return $levels;
    }

    public function maxLevel(PermohonanKredit $permohonan): int
    {
        $levels = $this->requiredLevels($permohonan);

        return $levels === [] ? 0 : (int) max($levels);
    }

    /** Level wajib terkecil yang belum memiliki log persetujuan. */
    public function nextLevel(PermohonanKredit $permohonan): ?int
    {
        $sudah = $this->approvedLevels($permohonan);

        foreach ($this->requiredLevels($permohonan) as $level) {
            if (! in_array($level, $sudah, true)) {
                return $level;
            }
        }

        return null;
    }

    public function roleForLevel(PermohonanKredit $permohonan, int $level): string
    {
        foreach ($this->matrixEntries() as $entry) {
            if ((int) ($entry['level'] ?? 0) === $level) {
                return (string) ($entry['role'] ?? 'ADMIN');
            }
        }

        return 'ADMIN';
    }

    public function permissionForLevel(int $level): string
    {
        return 'permohonan.approve_level_'.$level;
    }

    /** Kewenangan ganda: role matriks DAN permission level (ADMIN bypass). */
    public function authorize(User $user, PermohonanKredit $permohonan, int $level): void
    {
        if ($user->hasRole('ADMIN')) {
            return;
        }

        abort_unless(
            $user->hasRole($this->roleForLevel($permohonan, $level)),
            403,
            'Peran Anda tidak berwenang pada level ini.',
        );

        abort_unless(
            $user->hasPermissionTo($this->permissionForLevel($level)),
            403,
            'Permission level persetujuan tidak dimiliki.',
        );
    }

    /** Segregation of duties: pembuat tidak boleh menyetujui miliknya sendiri (ADMIN bypass). */
    public function assertMakerChecker(User $user, PermohonanKredit $permohonan): void
    {
        if ($user->hasRole('ADMIN')) {
            return;
        }

        abort_if(
            (int) $permohonan->ao_id === (int) $user->id,
            403,
            'Pembuat permohonan tidak boleh menyetujui pengajuannya sendiri.',
        );
    }

    public function approve(PermohonanKredit $permohonan, User $user, ?string $alasan = null): PermohonanKredit
    {
        $level = $this->nextLevel($permohonan);

        abort_if($level === null, 409, 'Persetujuan sudah lengkap atau matriks tidak tersedia.');

        $this->authorize($user, $permohonan, $level);
        $this->assertMakerChecker($user, $permohonan);

        return DB::transaction(function () use ($permohonan, $user, $level, $alasan): PermohonanKredit {
            $roleName = $this->roleForLevel($permohonan, $level);

            ApprovalLog::query()->create([
                'cabang_id' => $permohonan->cabang_id,
                'permohonan_id' => $permohonan->id,
                'level' => $level,
                'role_id' => Role::query()->where('name', $roleName)->value('id'),
                'user_id' => $user->id,
                'aksi' => AksiApproval::Approve->value,
                'alasan' => $alasan,
            ]);

            $final = $this->nextLevel($permohonan->refresh()) === null;

            $permohonan->update([
                'status' => $final ? StatusPermohonan::APPROVED->value : StatusPermohonan::RECOMMENDED->value,
            ]);

            $this->ledger->record(
                LedgerEvent::PermohonanDisetujui,
                'permohonan_kredit',
                (string) $permohonan->id,
                ['level' => $level, 'role' => $roleName, 'final' => $final],
                $user,
            );

            $this->bukuBesar->catat('PERSETUJUAN', $permohonan, $user, 'Persetujuan level '.$level.' ('.$roleName.')', [
                'level' => $level,
                'role' => $roleName,
                'final' => $final,
                'alasan' => $alasan,
            ]);

            $this->notifikasi->kirim(
                (int) $permohonan->ao_id,
                'APPROVAL',
                'Permohonan disetujui level '.$level,
                'Permohonan '.$permohonan->no_permohonan.' disetujui'.($final ? ' (final).' : '.'),
                'permohonan_kredit',
                (string) $permohonan->id,
                (string) $permohonan->cabang_id,
            );

            return $permohonan->fresh() ?? $permohonan;
        });
    }

    public function reject(PermohonanKredit $permohonan, User $user, string $alasan): PermohonanKredit
    {
        return $this->decide($permohonan, $user, $alasan, AksiApproval::Reject, StatusPermohonan::REJECTED, LedgerEvent::PermohonanDitolak);
    }

    public function returnToAo(PermohonanKredit $permohonan, User $user, string $alasan): PermohonanKredit
    {
        return $this->decide($permohonan, $user, $alasan, AksiApproval::Return, StatusPermohonan::RETURNED, LedgerEvent::PermohonanDikembalikan);
    }

    private function decide(
        PermohonanKredit $permohonan,
        User $user,
        string $alasan,
        AksiApproval $aksi,
        StatusPermohonan $status,
        LedgerEvent $event,
    ): PermohonanKredit {
        abort_if(trim($alasan) === '', 422, 'Alasan wajib diisi.');

        $this->assertMakerChecker($user, $permohonan);

        return DB::transaction(function () use ($permohonan, $user, $alasan, $aksi, $status, $event): PermohonanKredit {
            $level = $this->nextLevel($permohonan) ?? $this->maxLevel($permohonan);

            ApprovalLog::query()->create([
                'cabang_id' => $permohonan->cabang_id,
                'permohonan_id' => $permohonan->id,
                'level' => max(1, $level),
                'role_id' => Role::query()->where('name', $this->roleForLevel($permohonan, max(1, $level)))->value('id'),
                'user_id' => $user->id,
                'aksi' => $aksi->value,
                'alasan' => $alasan,
            ]);

            $permohonan->update(['status' => $status->value]);

            $this->ledger->record(
                $event,
                'permohonan_kredit',
                (string) $permohonan->id,
                ['alasan' => $alasan],
                $user,
            );

            $this->bukuBesar->catat('PERUBAHAN', $permohonan, $user, $aksi->value.': '.$alasan, [
                'aksi' => $aksi->value,
                'status' => $status->value,
                'alasan' => $alasan,
            ]);

            $this->notifikasi->kirim(
                (int) $permohonan->ao_id,
                'APPROVAL',
                'Permohonan '.strtolower($aksi->value),
                'Permohonan '.$permohonan->no_permohonan.' '.strtolower($aksi->value).': '.$alasan,
                'permohonan_kredit',
                (string) $permohonan->id,
                (string) $permohonan->cabang_id,
            );

            return $permohonan->fresh() ?? $permohonan;
        });
    }

    /** @return list<int> */
    private function approvedLevels(PermohonanKredit $permohonan): array
    {
        return array_values(ApprovalLog::query()
            ->where('permohonan_id', $permohonan->id)
            ->where('aksi', AksiApproval::Approve->value)
            ->pluck('level')
            ->map(static fn ($level): int => (int) $level)
            ->unique()
            ->all());
    }

    private function plafon(PermohonanKredit $permohonan): float
    {
        return (float) ($permohonan->plafon_rekomendasi ?? $permohonan->plafon_diajukan);
    }

    /** @return list<array<mixed>> */
    private function matrixEntries(): array
    {
        $raw = RefApprovalMatrix::query()->where('status', StatusVersi::ACTIVE->value)->first()?->matrix;

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter($raw, static fn ($entry): bool => is_array($entry)));
    }
}
