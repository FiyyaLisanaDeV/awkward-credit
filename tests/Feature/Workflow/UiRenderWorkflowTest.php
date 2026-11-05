<?php

declare(strict_types=1);

use App\Enums\StatusPermohonan;
use App\Models\User;
use App\Services\Kredit\KreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $admin->forceFill(['must_change_password' => false])->save();
    $this->actingAs($admin);
});

it('menampilkan dashboard dengan KPI', function () {
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee(__('dashboard.title'))
        ->assertSee(__('dashboard.pipeline'));
});

it('menampilkan inbox persetujuan', function () {
    $this->get(route('approval.inbox'))
        ->assertOk()
        ->assertSee(__('approval.title'));
});

it('menampilkan detail persetujuan dengan timeline', function () {
    $permohonan = fixturePermohonan(['status' => StatusPermohonan::SUBMITTED->value]);

    $this->get(route('approval.show', $permohonan))
        ->assertOk()
        ->assertSee(__('approval.timeline'));
});

it('menampilkan daftar dan detail kredit dengan jadwal', function () {
    $maker = workflowUser('AO');
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $permohonan = fixturePermohonan([
        'plafon_diajukan' => 5000000,
        'ao_id' => $maker->id,
        'status' => StatusPermohonan::APPROVED->value,
    ]);

    $kredit = app(KreditService::class)->cairkan($permohonan, $admin, ['rekening_pencairan' => '123']);

    $this->get(route('kredit.index'))
        ->assertOk()
        ->assertSee(__('kredit.title'));

    $this->get(route('kredit.show', $kredit))
        ->assertOk()
        ->assertSee(__('kredit.jadwal'));
});
