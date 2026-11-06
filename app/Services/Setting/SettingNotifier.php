<?php

declare(strict_types=1);

namespace App\Services\Setting;

use App\Enums\StatusRevision;
use App\Models\SettingRevision;
use App\Models\User;
use App\Services\Notifikasi\NotifikasiService;

final class SettingNotifier
{
    public function __construct(private readonly NotifikasiService $notifikasi) {}

    public function submitted(SettingRevision $revision): void
    {
        $approvers = User::query()->permission('setting.approve')->get();
        $creatorName = $revision->creator()->value('name');

        foreach ($approvers as $approver) {
            $this->notifikasi->kirim(
                (int) $approver->id,
                'SETTING',
                __('setting.notif_submitted_title'),
                __('setting.notif_submitted_body', [
                    'section' => $revision->section->value,
                    'creator' => is_string($creatorName) ? $creatorName : '-',
                ]),
                'setting_revision',
                (string) $revision->id,
                null,
            );
        }
    }

    public function decided(SettingRevision $revision, StatusRevision $status): void
    {
        $this->notifikasi->kirim(
            (int) $revision->created_by,
            'SETTING',
            __('setting.notif_decided_title'),
            __('setting.notif_decided_body', [
                'section' => $revision->section->value,
                'status' => __('status_revision.'.$status->value),
            ]),
            'setting_revision',
            (string) $revision->id,
            null,
        );
    }
}
