<?php

declare(strict_types=1);

use App\Enums\JenisDokumen;
use App\Enums\StatusPermohonan;
use App\Enums\StatusTemplate;
use App\Models\RefTemplateDokumen;
use App\Models\User;
use App\Services\Document\DocumentService;
use App\Services\Kredit\KreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $admin->forceFill(['must_change_password' => false])->save();
    $this->actingAs($admin);
});

it('health merespons dalam batas wajar', function () {
    $mulai = microtime(true);

    $this->get('/health')->assertOk();

    expect((microtime(true) - $mulai) * 1000)->toBeLessThan(2000);
});

it('daftar debitur dan jadwal kredit dalam batas wajar', function () {
    $admin = User::query()->where('username', 'admin')->firstOrFail();

    $mulai = microtime(true);
    $this->get(route('debitur.index'))->assertOk();
    expect((microtime(true) - $mulai) * 1000)->toBeLessThan(3000);

    $maker = workflowUser('AO');
    $permohonan = fixturePermohonan([
        'plafon_diajukan' => 5000000,
        'ao_id' => $maker->id,
        'status' => StatusPermohonan::APPROVED->value,
    ]);
    $kredit = app(KreditService::class)->cairkan($permohonan, $admin, ['rekening_pencairan' => '123']);

    $mulai = microtime(true);
    $this->get(route('kredit.show', $kredit))->assertOk();
    expect((microtime(true) - $mulai) * 1000)->toBeLessThan(3000);
});

it('generate PDF dokumen dalam batas wajar', function () {
    Storage::fake('local');
    RefTemplateDokumen::query()->update(['status' => StatusTemplate::APPROVED->value]);

    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $permohonan = fixturePermohonan();

    $mulai = microtime(true);
    app(DocumentService::class)->generate($permohonan, JenisDokumen::Doc01, $admin);

    expect((microtime(true) - $mulai) * 1000)->toBeLessThan(5000);
});
