<?php

declare(strict_types=1);

use App\Enums\StatusPermohonan;
use App\Models\PermohonanKredit;
use App\Models\User;
use App\Services\Approval\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function workflowPermohonan(User $maker): PermohonanKredit
{
    return fixturePermohonan([
        'plafon_diajukan' => 5000000,
        'ao_id' => $maker->id,
        'status' => StatusPermohonan::SUBMITTED->value,
    ]);
}

it('menolak approver dengan role matriks yang salah (403)', function () {
    $maker = workflowUser('AO');
    $mgr = workflowUser('MGR_PEMASARAN');
    $permohonan = workflowPermohonan($maker);

    assertHttpStatus(fn () => app(ApprovalService::class)->approve($permohonan, $mgr), 403);
});

it('menolak approver tanpa permission level (double, 403)', function () {
    $role = Role::findByName('AO');
    $role->revokePermissionTo('permohonan.approve_level_1');
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $maker = workflowUser('AO');
    $approver = workflowUser('AO');
    $permohonan = workflowPermohonan($maker);

    assertHttpStatus(fn () => app(ApprovalService::class)->approve($permohonan, $approver), 403);
});

it('menolak pembuat menyetujui pengajuannya sendiri (SoD, 403)', function () {
    $maker = workflowUser('AO');
    $permohonan = workflowPermohonan($maker);

    assertHttpStatus(fn () => app(ApprovalService::class)->approve($permohonan, $maker), 403);
});

it('mengizinkan AO lain menyetujui level 1', function () {
    $maker = workflowUser('AO');
    $approver = workflowUser('AO');
    $permohonan = workflowPermohonan($maker);

    app(ApprovalService::class)->approve($permohonan, $approver);

    expect($permohonan->fresh()->status)->toBe(StatusPermohonan::RECOMMENDED->value);
});

it('mengizinkan ADMIN melewati batas cabang dan SoD', function () {
    $maker = workflowUser('AO');
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $permohonan = workflowPermohonan($maker);

    app(ApprovalService::class)->approve($permohonan, $admin);

    expect($permohonan->fresh()->status)->toBe(StatusPermohonan::RECOMMENDED->value);
});
