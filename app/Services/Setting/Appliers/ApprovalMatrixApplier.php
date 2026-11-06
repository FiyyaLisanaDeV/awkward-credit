<?php

declare(strict_types=1);

namespace App\Services\Setting\Appliers;

use App\Enums\LedgerEvent;
use App\Enums\StatusVersi;
use App\Models\RefApprovalMatrix;
use App\Models\SettingRevision;
use App\Models\User;
use App\Services\Ledger\LedgerService;
use App\Services\Setting\SettingApplier;
use Illuminate\Support\Facades\DB;

final class ApprovalMatrixApplier implements SettingApplier
{
    public function __construct(private readonly LedgerService $ledger) {}

    public function apply(SettingRevision $revision, User $actor): array
    {
        return DB::transaction(function () use ($revision, $actor): array {
            $old = RefApprovalMatrix::query()->where('status', StatusVersi::ACTIVE->value)->first();
            $oldSnapshot = $old?->toArray();

            if ($old instanceof RefApprovalMatrix) {
                $old->update(['status' => StatusVersi::ARCHIVED->value]);
            }

            $version = $this->nextVersion();

            $new = RefApprovalMatrix::query()->create([
                'version' => $version,
                'effective_date' => $revision->payload['effective_date'] ?? now()->toDateString(),
                'matrix' => $revision->payload['matrix'],
                'status' => StatusVersi::ACTIVE->value,
                'created_by' => $revision->created_by,
                'approved_by' => $revision->approved_by,
                'approved_at' => $revision->approved_at,
            ]);

            $this->ledger->record(
                LedgerEvent::ParameterDiaktifkan,
                'ref_approval_matrix',
                (string) $new->id,
                [
                    'section' => $revision->section->value,
                    'version' => $version,
                    'revision_id' => $revision->id,
                    'previous_version' => $old?->version,
                ],
                $actor,
            );

            return [
                'old' => $oldSnapshot,
                'new' => $new->toArray(),
                'version' => $version,
            ];
        });
    }

    private function nextVersion(): string
    {
        $major = 1;
        $minor = 0;

        foreach (RefApprovalMatrix::query()->pluck('version') as $version) {
            if (! preg_match('/^v(\d+)\.(\d+)$/', (string) $version, $matches)) {
                continue;
            }

            $candidateMajor = (int) $matches[1];
            $candidateMinor = (int) $matches[2];

            if ($candidateMajor > $major || ($candidateMajor === $major && $candidateMinor > $minor)) {
                $major = $candidateMajor;
                $minor = $candidateMinor;
            }
        }

        return 'v'.$major.'.'.($minor + 1);
    }
}
