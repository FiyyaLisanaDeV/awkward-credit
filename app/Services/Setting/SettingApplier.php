<?php

declare(strict_types=1);

namespace App\Services\Setting;

use App\Models\SettingRevision;
use App\Models\User;

interface SettingApplier
{
    /**
     * @return array{old: array<string, mixed>|null, new: array<string, mixed>|null, version?: string}
     */
    public function apply(SettingRevision $revision, User $actor): array;
}
