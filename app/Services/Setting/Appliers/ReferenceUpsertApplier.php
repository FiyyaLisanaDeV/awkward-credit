<?php

declare(strict_types=1);

namespace App\Services\Setting\Appliers;

use App\Enums\SettingAction;
use App\Enums\SettingSection;
use App\Enums\StatusTemplate;
use App\Models\SettingRevision;
use App\Models\User;
use App\Services\Setting\SettingApplier;
use App\Services\Setting\SettingSectionRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

final class ReferenceUpsertApplier implements SettingApplier
{
    public function __construct(private readonly SettingSectionRegistry $registry) {}

    public function apply(SettingRevision $revision, User $actor): array
    {
        /** @var class-string<Model> $model */
        $model = $this->registry->model($revision->section);

        return DB::transaction(function () use ($revision, $model): array {
            $payload = $this->payloadFor($revision);

            return match ($revision->action) {
                SettingAction::Create => [
                    'old' => null,
                    'new' => $model::query()->create($payload)->toArray(),
                ],
                SettingAction::Update => $this->update($model, $revision, $payload),
                SettingAction::Deactivate => $this->toggle($model, $revision, false),
                SettingAction::Activate => $this->toggle($model, $revision, true),
            };
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFor(SettingRevision $revision): array
    {
        $payload = $revision->payload;

        if ($revision->section === SettingSection::TemplateDokumen
            && ($payload['status'] ?? null) === StatusTemplate::APPROVED->value) {
            $payload['approved_by'] = $revision->approved_by;
            $payload['approved_at'] = $revision->approved_at;
        }

        return $payload;
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $payload
     * @return array{old: array<string, mixed>, new: array<string, mixed>}
     */
    private function update(string $model, SettingRevision $revision, array $payload): array
    {
        $record = $model::query()->findOrFail($revision->target_id);
        $old = $record->toArray();
        $record->update($payload);

        return ['old' => $old, 'new' => $record->refresh()->toArray()];
    }

    /**
     * @param  class-string<Model>  $model
     * @return array{old: array<string, mixed>, new: array<string, mixed>|null}
     */
    private function toggle(string $model, SettingRevision $revision, bool $active): array
    {
        $record = $model::query()->findOrFail($revision->target_id);
        $old = $record->toArray();
        $table = $record->getTable();

        if (Schema::hasColumn($table, 'is_active')) {
            $record->update(['is_active' => $active]);

            return ['old' => $old, 'new' => $record->refresh()->toArray()];
        }

        if (! $active && in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
            $record->delete();

            return ['old' => $old, 'new' => null];
        }

        throw ValidationException::withMessages([
            'action' => 'Bagian ini tidak mendukung aksi aktif/nonaktif.',
        ]);
    }
}
