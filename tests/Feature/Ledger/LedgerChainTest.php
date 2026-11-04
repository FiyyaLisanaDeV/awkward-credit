<?php

declare(strict_types=1);

use App\Enums\LedgerEvent;
use App\Models\Ledger;
use App\Services\Ledger\ChainHasher;
use App\Services\Ledger\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('mencatat ledger dengan rantai hash', function () {
    $service = app(LedgerService::class);

    $first = $service->record(LedgerEvent::PermohonanDibuat, 'permohonan_kredit', (string) Str::uuid(), ['no' => 'KDI/2026/0001']);
    $second = $service->record(LedgerEvent::PermohonanDiajukan, 'permohonan_kredit', $first->entity_id, ['level' => 1]);

    expect($first->prev_hash)->toBe(ChainHasher::genesis())
        ->and($second->prev_hash)->toBe($first->hash)
        ->and(strlen($first->hash))->toBe(64)
        ->and($first->hash)->not->toBe($second->hash);
});

it('idempoten bila kunci yang sama dipakai', function () {
    $service = app(LedgerService::class);

    $first = $service->record(LedgerEvent::AnalisisDisimpan, 'permohonan_kredit', null, ['bagian' => 'neraca'], null, null, 'kunci-uji');
    $second = $service->record(LedgerEvent::AnalisisDisimpan, 'permohonan_kredit', null, ['bagian' => 'neraca'], null, null, 'kunci-uji');

    expect($first->id)->toBe($second->id)
        ->and(DB::table('ledger')->count())->toBe(1);
});

it('lulus verifikasi rantai', function () {
    $service = app(LedgerService::class);

    for ($i = 0; $i < 5; $i++) {
        $service->record(LedgerEvent::DataDiimpor, 'import_batch', (string) Str::uuid(), ['baris' => $i]);
    }

    $this->artisan('ledger:verify')->assertSuccessful();
});

it('mendeteksi baris ledger yang tidak konsisten', function () {
    $service = app(LedgerService::class);
    $last = $service->record(LedgerEvent::DataDiimpor, 'import_batch', null, ['baris' => 1]);

    DB::table('ledger')->insert([
        'id' => (string) Str::uuid(),
        'occurred_at' => now()->startOfSecond(),
        'event' => 'DATA_DIIMPOR',
        'entity_type' => 'import_batch',
        'payload' => json_encode(['baris' => 2]),
        'prev_hash' => $last->hash,
        'hash' => str_repeat('a', 64),
    ]);

    $this->artisan('ledger:verify')->assertFailed();
});

it('menyimpan payload dan idempotency key', function () {
    $row = app(LedgerService::class)->record(
        LedgerEvent::SimulasiDijalankan,
        'simulasi_kredit',
        null,
        ['input_hash' => 'abc', 'nilai' => 1.5],
        null,
        null,
        'kunci-payload',
    );

    $fresh = Ledger::query()->whereKey($row->id)->firstOrFail();

    expect($fresh->payload)->toEqual(['input_hash' => 'abc', 'nilai' => 1.5])
        ->and($fresh->idempotency_key)->toBe('kunci-payload');
});
