<?php

declare(strict_types=1);

use App\Enums\SettingSection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('menampilkan halaman pengaturan untuk admin', function () {
    $admin = workflowUser('ADMIN');

    $this->actingAs($admin)
        ->get(route('admin.index'))
        ->assertOk()
        ->assertSee('Pengaturan')
        ->assertSee('Parameter kebijakan');
});

it('menampilkan halaman section parameter kebijakan', function () {
    $admin = workflowUser('ADMIN');

    $this->actingAs($admin)
        ->get(route('admin.section', 'POLICY_PARAMETER'))
        ->assertOk()
        ->assertSee('Parameter kebijakan')
        ->assertSee('Simpan draft');
});

it('menampilkan seluruh halaman section', function () {
    $admin = workflowUser('ADMIN');

    foreach (SettingSection::cases() as $section) {
        $this->actingAs($admin)
            ->get(route('admin.section', $section->value))
            ->assertOk()
            ->assertSee(__('setting.save_draft'));
    }
});

it('menampilkan inbox persetujuan untuk komite', function () {
    $komite = workflowUser('KOMITE');

    $this->actingAs($komite)
        ->get(route('admin.inbox'))
        ->assertOk();
});

it('menolak pengguna tanpa izin melihat pengaturan', function () {
    $ao = workflowUser('AO');

    $this->actingAs($ao)
        ->get(route('admin.index'))
        ->assertForbidden();
});
