<?php

declare(strict_types=1);

namespace App\Services\Setting;

use App\Models\AuditLog;
use App\Models\SettingRevision;
use App\Models\User;
use Illuminate\Support\Carbon;

final class SettingAudit
{
    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public function record(SettingRevision $revision, string $aksi, ?array $old, ?array $new, User $actor): AuditLog
    {
        return AuditLog::query()->create([
            'cabang_id' => null,
            'user_id' => $actor->id,
            'entity_type' => 'setting:'.$revision->section->value,
            'entity_id' => $revision->target_id ?? $revision->id,
            'aksi' => $aksi,
            'old_value' => $old,
            'new_value' => $new,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : request()->userAgent(),
            'timestamp' => Carbon::now(),
        ]);
    }
}
