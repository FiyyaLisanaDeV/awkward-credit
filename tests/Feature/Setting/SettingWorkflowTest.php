<?php

declare(strict_types=1);

use App\Enums\SettingAction;
use App\Enums\SettingSection;
use App\Enums\StatusRevision;
use App\Models\AuditLog;
use App\Models\Ledger;
use App\Models\RefParameterKebijakan;
use App\Models\SettingRevision;
use App\Models\User;
use App\Services\Setting\SettingRevisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function policyPayload(float $margin = 0.45): array
{
    $active = RefParameterKebijakan::query()->where('status', 'ACTIVE')->firstOrFail();
    $params = $active->params;
    $params['margin_kapasitas_bayar'] = $margin;

    return [
        'params' => $params,
        'effective_date' => '2026-06-01',
    ];
}

it('menjalankan alur draft-submit-approve-apply untuk parameter kebijakan', function () {
    $maker = User::query()->where('username', 'admin')->firstOrFail();
    $approver = workflowUser('KOMITE');
    $old = RefParameterKebijakan::query()->where('status', 'ACTIVE')->firstOrFail();
    $service = app(SettingRevisionService::class);

    $revision = $service->createDraft(
        SettingSection::PolicyParameter,
        SettingAction::Create,
        policyPayload(),
        null,
        $maker,
        'Uji perubahan margin',
    );

    expect($revision->status)->toBe(StatusRevision::Draft);

    $service->submit($revision, $maker);
    expect($revision->fresh()->status)->toBe(StatusRevision::Pending);

    $service->approve($revision->fresh(), $approver);
    expect($revision->fresh()->status)->toBe(StatusRevision::Approved)
        ->and($revision->fresh()->approved_by)->toBe($approver->id);

    $result = $service->apply($revision->fresh(), $maker);

    expect($result['version'])->toBe('v1.1')
        ->and($revision->fresh()->status)->toBe(StatusRevision::Applied);

    $new = RefParameterKebijakan::query()->where('status', 'ACTIVE')->firstOrFail();

    expect($new->version)->toBe('v1.1')
        ->and((float) $new->params['margin_kapasitas_bayar'])->toBe(0.45)
        ->and(RefParameterKebijakan::query()->find($old->id)?->status)->toBe('ARCHIVED')
        ->and(Ledger::query()->where('event', 'PARAMETER_DIAKTIFKAN')->count())->toBeGreaterThan(0)
        ->and(AuditLog::query()->where('entity_type', 'setting:POLICY_PARAMETER')->count())->toBeGreaterThan(0);
});

it('menolak pembuat menyetujui revisinya sendiri', function () {
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $service = app(SettingRevisionService::class);

    $revision = $service->createDraft(SettingSection::PolicyParameter, SettingAction::Create, policyPayload(), null, $admin);
    $service->submit($revision, $admin);

    assertHttpStatus(fn () => $service->approve($revision->fresh(), $admin), 403);
});

it('menolak pengguna tanpa izin approve', function () {
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $ao = workflowUser('AO');
    $service = app(SettingRevisionService::class);

    $revision = $service->createDraft(SettingSection::PolicyParameter, SettingAction::Create, policyPayload(), null, $admin);
    $service->submit($revision, $admin);

    assertHttpStatus(fn () => $service->approve($revision->fresh(), $ao), 403);
});

it('tidak menerapkan revisi dua kali', function () {
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $approver = workflowUser('KOMITE');
    $service = app(SettingRevisionService::class);

    $revision = $service->createDraft(SettingSection::PolicyParameter, SettingAction::Create, policyPayload(), null, $admin);
    $service->submit($revision, $admin);
    $service->approve($revision->fresh(), $approver);
    $service->apply($revision->fresh(), $admin);

    assertHttpStatus(fn () => $service->apply($revision->fresh(), $admin), 422);
});

it('menolak revisi dengan catatan alasan', function () {
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $approver = workflowUser('KOMITE');
    $service = app(SettingRevisionService::class);

    $revision = $service->createDraft(SettingSection::PolicyParameter, SettingAction::Create, policyPayload(), null, $admin);
    $service->submit($revision, $admin);
    $service->reject($revision->fresh(), $approver, 'Belum sesuai kebijakan');

    expect($revision->fresh()->status)->toBe(StatusRevision::Rejected)
        ->and($revision->fresh()->note)->toContain('Belum sesuai kebijakan');
});

it('menyimpan revisi sebagai draft yang dapat disunting', function () {
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $service = app(SettingRevisionService::class);

    $revision = $service->createDraft(SettingSection::PolicyParameter, SettingAction::Create, policyPayload(0.40), null, $admin);
    $updated = $service->updateDraft($revision, policyPayload(0.45), 'revisi nilai', $admin);

    expect((float) $updated->payload['params']['margin_kapasitas_bayar'])->toBe(0.45)
        ->and($updated->note)->toBe('revisi nilai');

    $submitted = $service->submit($updated, $admin);
    expect($submitted->status)->toBe(StatusRevision::Pending);

    $applied = SettingRevision::query()->find($revision->id);
    expect($applied?->submitted_at)->not->toBeNull();
});
