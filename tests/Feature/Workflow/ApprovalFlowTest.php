<?php

declare(strict_types=1);

use App\Enums\AksiApproval;
use App\Enums\LedgerEvent;
use App\Enums\StatusPermohonan;
use App\Models\ApprovalLog;
use App\Models\Ledger;
use App\Models\Notifikasi;
use App\Models\User;
use App\Services\Approval\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('menyetujui berjenjang sampai APPROVED dan mencatat log, ledger, notifikasi', function () {
    $maker = workflowUser('AO');
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $permohonan = fixturePermohonan([
        'plafon_diajukan' => 5000000,
        'ao_id' => $maker->id,
        'status' => StatusPermohonan::SUBMITTED->value,
    ]);

    $service = app(ApprovalService::class);

    fixtureKapasitas($permohonan);
    fixtureChecklistLengkap($permohonan);

    $service->approve($permohonan, $admin, 'level 1 ok');
    expect($permohonan->fresh()->status)->toBe(StatusPermohonan::RECOMMENDED->value);

    $service->approve($permohonan->fresh(), $admin, 'level 2 ok');
    expect($permohonan->fresh()->status)->toBe(StatusPermohonan::APPROVED->value);

    expect(ApprovalLog::query()->where('permohonan_id', $permohonan->id)->where('aksi', AksiApproval::Approve->value)->count())->toBe(2)
        ->and(Ledger::query()->where('event', LedgerEvent::PermohonanDisetujui->value)->where('entity_id', $permohonan->id)->count())->toBe(2)
        ->and(Notifikasi::query()->where('user_id', $maker->id)->where('tipe', 'APPROVAL')->count())->toBe(2);
});

it('menolak (reject) menulis log, ledger, dan notifikasi', function () {
    $maker = workflowUser('AO');
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $permohonan = fixturePermohonan([
        'plafon_diajukan' => 5000000,
        'ao_id' => $maker->id,
        'status' => StatusPermohonan::SUBMITTED->value,
    ]);

    app(ApprovalService::class)->reject($permohonan, $admin, 'agunan kurang');

    expect($permohonan->fresh()->status)->toBe(StatusPermohonan::REJECTED->value)
        ->and(ApprovalLog::query()->where('permohonan_id', $permohonan->id)->where('aksi', AksiApproval::Reject->value)->count())->toBe(1)
        ->and(Ledger::query()->where('event', LedgerEvent::PermohonanDitolak->value)->where('entity_id', $permohonan->id)->count())->toBe(1)
        ->and(Notifikasi::query()->where('user_id', $maker->id)->count())->toBe(1);
});

it('mengembalikan (return) menulis log, ledger, dan notifikasi', function () {
    $maker = workflowUser('AO');
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $permohonan = fixturePermohonan([
        'plafon_diajukan' => 5000000,
        'ao_id' => $maker->id,
        'status' => StatusPermohonan::SUBMITTED->value,
    ]);

    app(ApprovalService::class)->returnToAo($permohonan, $admin, 'lengkapi dokumen');

    expect($permohonan->fresh()->status)->toBe(StatusPermohonan::RETURNED->value)
        ->and(ApprovalLog::query()->where('permohonan_id', $permohonan->id)->where('aksi', AksiApproval::Return->value)->count())->toBe(1)
        ->and(Ledger::query()->where('event', LedgerEvent::PermohonanDikembalikan->value)->where('entity_id', $permohonan->id)->count())->toBe(1)
        ->and(Notifikasi::query()->where('user_id', $maker->id)->count())->toBe(1);
});
