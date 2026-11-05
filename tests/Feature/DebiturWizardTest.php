<?php

declare(strict_types=1);

use App\Livewire\DebiturWizard;
use App\Models\Debitur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $admin->forceFill(['must_change_password' => false])->save();
    $this->actingAs($admin);
});

it('menyimpan debitur melalui wizard tiga langkah', function () {
    Livewire::test(DebiturWizard::class)
        ->set('nama_ktp', 'Debitur Wizard')
        ->set('no_ktp', '7371000000009999')
        ->set('status_perkawinan', 'MENIKAH')
        ->set('tempat_lahir', 'Kendari')
        ->set('tanggal_lahir', '1992-02-02')
        ->call('next')
        ->assertSet('step', 2)
        ->set('no_hp', '081200000099')
        ->set('alamat_jalan', 'Jalan Wizard')
        ->set('alamat_rt', '001')
        ->set('alamat_rw', '002')
        ->set('kelurahan', 'Kelurahan')
        ->set('kecamatan', 'Kecamatan')
        ->set('kabupaten', 'Kendari')
        ->set('provinsi', 'Sulawesi Tenggara')
        ->call('next')
        ->assertSet('step', 3)
        ->set('penghasilan_bulanan', '7500000')
        ->call('save')
        ->assertRedirect();

    $debitur = Debitur::query()->where('no_ktp', '7371000000009999')->firstOrFail();

    expect($debitur->nama_ktp)->toBe('Debitur Wizard')
        ->and($debitur->sumber_data)->toBe('MANUAL')
        ->and($debitur->cabang_id)->not->toBeNull();
});

it('menolak lanjut bila field wajib kosong', function () {
    Livewire::test(DebiturWizard::class)
        ->call('next')
        ->assertHasErrors(['nama_ktp', 'no_ktp', 'status_perkawinan', 'tempat_lahir', 'tanggal_lahir'])
        ->assertSet('step', 1);
});
