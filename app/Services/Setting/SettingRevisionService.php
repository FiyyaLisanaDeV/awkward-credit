<?php

declare(strict_types=1);

namespace App\Services\Setting;

use App\Enums\SettingAction;
use App\Enums\SettingSection;
use App\Enums\StatusRevision;
use App\Models\SettingRevision;
use App\Models\User;
use App\Services\Setting\Appliers\ApprovalMatrixApplier;
use App\Services\Setting\Appliers\PolicyParameterApplier;
use App\Services\Setting\Appliers\ReferenceUpsertApplier;
use App\Services\Setting\Validators\SettingPayloadValidator;
use App\Support\Konstanta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class SettingRevisionService
{
    public function __construct(
        private readonly SettingSectionRegistry $registry,
        private readonly SettingPayloadValidator $validator,
        private readonly SettingAudit $audit,
        private readonly SettingNotifier $notifier,
        private readonly PolicyParameterApplier $policyApplier,
        private readonly ApprovalMatrixApplier $matrixApplier,
        private readonly ReferenceUpsertApplier $referenceApplier,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function createDraft(
        SettingSection $section,
        SettingAction $action,
        array $payload,
        ?string $targetId,
        User $user,
        ?string $note = null,
    ): SettingRevision {
        abort_unless($user->can('setting.edit'), 403);

        if ($this->registry->isVersioned($section)) {
            $action = SettingAction::Create;
            $targetId = null;
        } else {
            $this->assertTarget($section, $action, $targetId);
        }

        return SettingRevision::query()->create([
            'section' => $section->value,
            'action' => $action->value,
            'target_id' => $targetId,
            'payload' => $payload,
            'status' => StatusRevision::Draft->value,
            'note' => $note,
            'created_by' => $user->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateDraft(SettingRevision $revision, array $payload, ?string $note, User $user): SettingRevision
    {
        abort_unless($revision->isEditable(), 422);
        abort_unless($revision->created_by === $user->id || $user->hasRole('ADMIN'), 403);

        $revision->update([
            'payload' => $payload,
            'note' => $note,
        ]);

        return $revision->refresh();
    }

    public function submit(SettingRevision $revision, User $user): SettingRevision
    {
        abort_unless($revision->status === StatusRevision::Draft, 422);
        abort_unless($revision->created_by === $user->id || $user->hasRole('ADMIN'), 403);

        $this->validateRevision($revision);

        $revision->update([
            'status' => StatusRevision::Pending->value,
            'submitted_at' => now(),
        ]);

        $this->audit->record($revision, 'SUBMIT', null, $revision->payload, $user);
        $this->notifier->submitted($revision->refresh());

        return $revision->refresh();
    }

    public function approve(SettingRevision $revision, User $user): SettingRevision
    {
        abort_unless($revision->status === StatusRevision::Pending, 422);
        abort_unless($user->can('setting.approve'), 403);
        abort_if($revision->created_by === $user->id, 403, 'Pembuat tidak boleh menyetujui revisinya sendiri.');

        $this->validateRevision($revision);

        $revision->update([
            'status' => StatusRevision::Approved->value,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $this->audit->record($revision, 'APPROVE', null, $revision->payload, $user);
        $this->notifier->decided($revision->refresh(), StatusRevision::Approved);

        return $revision->refresh();
    }

    public function reject(SettingRevision $revision, User $user, string $reason): SettingRevision
    {
        abort_unless(in_array($revision->status, [StatusRevision::Pending, StatusRevision::Approved], true), 422);
        abort_unless($user->can('setting.approve'), 403);

        $note = trim(($revision->note ?? '')."\nDitolak: ".$reason);

        $revision->update([
            'status' => StatusRevision::Rejected->value,
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'note' => $note,
        ]);

        $this->audit->record($revision, 'REJECT', null, ['reason' => $reason], $user);
        $this->notifier->decided($revision->refresh(), StatusRevision::Rejected);

        return $revision->refresh();
    }

    /**
     * @return array{old: array<string, mixed>|null, new: array<string, mixed>|null, version?: string}
     */
    public function apply(SettingRevision $revision, User $user): array
    {
        abort_unless($revision->status === StatusRevision::Approved, 422);
        abort_unless($user->can('setting.apply'), 403);

        return DB::transaction(function () use ($revision, $user): array {
            $locked = SettingRevision::query()->whereKey($revision->id)->lockForUpdate()->firstOrFail();

            abort_unless($locked->status === StatusRevision::Approved, 409);

            $result = $this->applier($locked->section)->apply($locked, $user);

            $locked->update([
                'status' => StatusRevision::Applied->value,
                'applied_at' => now(),
            ]);

            $this->audit->record($locked, 'APPLY', $result['old'], $result['new'], $user);
            $this->notifier->decided($locked->refresh(), StatusRevision::Applied);

            return $result;
        });
    }

    /**
     * @return LengthAwarePaginator<int, SettingRevision>
     */
    public function history(?SettingSection $section = null, ?StatusRevision $status = null): LengthAwarePaginator
    {
        $sectionValue = $section?->value;
        $statusValue = $status?->value;

        return SettingRevision::query()
            ->with(['creator', 'approver', 'rejector'])
            ->when($sectionValue !== null, fn ($query) => $query->where('section', $sectionValue))
            ->when($statusValue !== null, fn ($query) => $query->where('status', $statusValue))
            ->orderByDesc('created_at')
            ->paginate(Konstanta::SETTING_PER_PAGE);
    }

    public function pendingCount(SettingSection $section): int
    {
        return SettingRevision::query()
            ->where('section', $section->value)
            ->where('status', StatusRevision::Pending->value)
            ->count();
    }

    public function activeVersion(SettingSection $section): ?string
    {
        $model = $this->registry->model($section);

        if (! $this->registry->isVersioned($section)) {
            return null;
        }

        return $model::query()->where('status', 'ACTIVE')->value('version');
    }

    private function applier(SettingSection $section): SettingApplier
    {
        return match ($section) {
            SettingSection::PolicyParameter => $this->policyApplier,
            SettingSection::ApprovalMatrix => $this->matrixApplier,
            default => $this->referenceApplier,
        };
    }

    private function validateRevision(SettingRevision $revision): void
    {
        if (in_array($revision->action, [SettingAction::Deactivate, SettingAction::Activate], true)) {
            abort_if($revision->target_id === null, 422, 'Aksi ini membutuhkan target.');

            return;
        }

        $this->validator->validate($revision->section, $revision->payload, $revision->target_id);
    }

    private function assertTarget(SettingSection $section, SettingAction $action, ?string $targetId): void
    {
        if ($action === SettingAction::Create) {
            abort_if($targetId !== null, 422, 'Aksi CREATE tidak boleh memiliki target.');

            return;
        }

        abort_if($targetId === null, 422, 'Aksi ini membutuhkan target.');

        $model = $this->registry->model($section);

        abort_unless($model::query()->whereKey($targetId)->exists(), 404);
    }
}
