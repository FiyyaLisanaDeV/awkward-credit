<?php

declare(strict_types=1);

use App\Enums\JenisDokumen;
use App\Enums\LedgerEvent;
use App\Enums\PihakSignature;
use App\Enums\StatusTemplate;
use App\Enums\StatusTtd;
use App\Models\Dokumen;
use App\Models\Ledger;
use App\Models\RefTemplateDokumen;
use App\Models\User;
use App\Services\Document\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('local');
});

it('menolak generate dokumen bila template belum APPROVED (BR-07)', function () {
    $permohonan = fixturePermohonan();

    assertHttpStatus(fn () => app(DocumentService::class)->generate($permohonan, JenisDokumen::Doc01), 422);
});

it('menghasilkan DOC-01..DOC-17, file valid, dan hash idempoten', function () {
    RefTemplateDokumen::query()->update(['status' => StatusTemplate::APPROVED->value]);

    $permohonan = fixturePermohonan();
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $service = app(DocumentService::class);

    $hasil = $service->generateAll($permohonan, $admin);

    expect($hasil)->toHaveCount(17);

    foreach ($hasil as $dokumen) {
        expect(Storage::disk('local')->exists((string) $dokumen->file_path))->toBeTrue()
            ->and($dokumen->file_hash)->toHaveLength(64)
            ->and((int) $dokumen->ukuran_bytes)->toBeGreaterThan(0);
    }

    $ulang = $service->generate($permohonan, JenisDokumen::Doc01, $admin);

    expect($ulang->id)->toBe($hasil[0]->id)
        ->and(Dokumen::query()->where('permohonan_id', $permohonan->id)->count())->toBe(17)
        ->and(Ledger::query()->where('event', LedgerEvent::DokumenDibuat->value)->count())->toBe(17);
});

it('menandatangani dokumen dan mencatat ledger', function () {
    RefTemplateDokumen::query()->update(['status' => StatusTemplate::APPROVED->value]);

    $permohonan = fixturePermohonan();
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $service = app(DocumentService::class);

    $dokumen = $service->generate($permohonan, JenisDokumen::Doc04, $admin);

    $signature = $service->sign(
        $dokumen,
        $admin,
        PihakSignature::Debitur,
        'Debitur Uji',
        null,
        str_repeat('a', 64),
    );

    expect($signature->dokumen_id)->toBe($dokumen->id)
        ->and($dokumen->fresh()->status_ttd)->toBe(StatusTtd::DITANDATANGANI->value)
        ->and(Ledger::query()->where('event', LedgerEvent::DokumenDitandatangani->value)->count())->toBe(1);
});
