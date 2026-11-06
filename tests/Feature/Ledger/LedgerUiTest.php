<?php

declare(strict_types=1);

use App\Enums\LedgerEvent;
use App\Services\Ledger\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('menampilkan daftar dan detail ledger untuk auditor', function () {
    $auditor = workflowUser('AUDITOR');
    $row = app(LedgerService::class)->record(LedgerEvent::DataDiimpor, 'import_batch', null, ['baris' => 1], $auditor);

    $this->actingAs($auditor)
        ->get(route('ledger.index'))
        ->assertOk()
        ->assertSee('Ledger');

    $this->actingAs($auditor)
        ->get(route('ledger.show', $row))
        ->assertOk();
});

it('menjalankan verifikasi ledger dari UI dan menampilkan rantai utuh', function () {
    $auditor = workflowUser('AUDITOR');

    $this->actingAs($auditor)
        ->get(route('ledger.verify'))
        ->assertOk()
        ->assertSee('utuh');
});

it('menampilkan kegagalan verifikasi bila ada baris dipalsukan', function () {
    $auditor = workflowUser('AUDITOR');
    $last = app(LedgerService::class)->record(LedgerEvent::DataDiimpor, 'import_batch', null, ['baris' => 1], $auditor);

    DB::table('ledger')->insert([
        'id' => (string) Str::uuid(),
        'occurred_at' => now()->startOfSecond(),
        'event' => 'DATA_DIIMPOR',
        'entity_type' => 'import_batch',
        'payload' => json_encode(['baris' => 2]),
        'prev_hash' => $last->hash,
        'hash' => str_repeat('a', 64),
    ]);

    $this->actingAs($auditor)
        ->get(route('ledger.verify'))
        ->assertOk()
        ->assertSee('tidak utuh');
});

it('menolak route tulis pada ledger', function () {
    $auditor = workflowUser('AUDITOR');

    foreach (['post', 'put', 'patch', 'delete'] as $method) {
        $this->actingAs($auditor)->$method(route('ledger.index'))->assertStatus(405);
    }
});

it('menolak detail ledger lintas cabang', function () {
    $auditor = workflowUser('AUDITOR');
    $bomUser = workflowUser('AO', null, DB::table('cabang')->where('kode', 'BOM')->value('id'));
    $row = app(LedgerService::class)->record(LedgerEvent::DataDiimpor, 'import_batch', null, ['baris' => 1], $bomUser);

    $this->actingAs($auditor)
        ->get(route('ledger.show', $row))
        ->assertForbidden();
});
