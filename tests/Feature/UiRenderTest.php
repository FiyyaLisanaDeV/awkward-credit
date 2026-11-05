<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $admin->forceFill(['must_change_password' => false])->save();

    $this->actingAs($admin);
});

it('menampilkan daftar debitur', function () {
    $this->get(route('debitur.index'))
        ->assertOk()
        ->assertSee(__('debitur.title'))
        ->assertSee(__('debitur.new'));
});

it('menampilkan formulir debitur baru dengan wizard', function () {
    $this->get(route('debitur.create'))
        ->assertOk()
        ->assertSee(__('debitur.step_identitas'))
        ->assertSee(__('debitur.step_pekerjaan'));
});

it('menampilkan daftar dan formulir permohonan', function () {
    $this->get(route('permohonan.index'))->assertOk()->assertSee(__('permohonan.title'));
    $this->get(route('permohonan.create'))->assertOk()->assertSee(__('permohonan.step_slik'));
});

it('menampilkan simulator dan halaman bandingkan', function () {
    $this->get(route('simulasi.index'))->assertOk()->assertSee(__('simulasi.title'));
    $this->get(route('simulasi.create'))->assertOk()->assertSee(__('simulasi.input'));
    $this->get(route('simulasi.bandingkan'))->assertOk()->assertSee(__('simulasi.pilih_dua'));
});

it('menolak pengguna tanpa izin', function () {
    $user = User::query()->where('username', 'admin')->firstOrFail();
    $user->forceFill(['must_change_password' => false])->save();
    $user->syncRoles([]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->actingAs($user->fresh());
    $this->get(route('debitur.index'))->assertForbidden();
});
