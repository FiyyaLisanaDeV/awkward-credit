<?php

declare(strict_types=1);

use App\Enums\SettingAction;
use App\Enums\SettingSection;
use App\Models\RefChecklistDokumen;
use App\Models\User;
use App\Services\Setting\SettingRevisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('membuat dan menerapkan referensi checklist lewat revisi', function () {
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $approver = workflowUser('KOMITE');
    $service = app(SettingRevisionService::class);

    $payload = [
        'kode' => 'UJI-02',
        'nama' => 'Dokumen Uji',
        'kategori' => 'PEMOHON',
        'wajib' => true,
        'urutan' => 99,
        'is_active' => true,
        'sumber' => 'Uji',
    ];

    $revision = $service->createDraft(SettingSection::ChecklistDokumen, SettingAction::Create, $payload, null, $admin);
    $service->submit($revision, $admin);
    $service->approve($revision->fresh(), $approver);
    $service->apply($revision->fresh(), $admin);

    $row = RefChecklistDokumen::query()->where('kode', 'UJI-02')->first();

    expect($row)->not->toBeNull()
        ->and($row->is_active)->toBeTrue();
});

it('menonaktifkan referensi lewat revisi', function () {
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $approver = workflowUser('KOMITE');
    $service = app(SettingRevisionService::class);

    $row = RefChecklistDokumen::query()->create([
        'kode' => 'UJI-03',
        'nama' => 'Dokumen Uji',
        'kategori' => 'PEMOHON',
        'wajib' => true,
        'urutan' => 1,
        'is_active' => true,
        'sumber' => 'Uji',
    ]);

    $revision = $service->createDraft(SettingSection::ChecklistDokumen, SettingAction::Deactivate, [], $row->id, $admin);
    $service->submit($revision, $admin);
    $service->approve($revision->fresh(), $approver);
    $service->apply($revision->fresh(), $admin);

    expect($row->fresh()->is_active)->toBeFalse();
});

it('menolak target referensi yang tidak ada', function () {
    $admin = User::query()->where('username', 'admin')->firstOrFail();

    assertHttpStatus(fn () => app(SettingRevisionService::class)->createDraft(
        SettingSection::ChecklistDokumen,
        SettingAction::Update,
        ['kode' => 'X'],
        '00000000-0000-0000-0000-000000000000',
        $admin,
    ), 404);
});
