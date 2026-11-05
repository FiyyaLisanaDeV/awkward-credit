<?php

declare(strict_types=1);

use App\Livewire\PermohonanWizard;
use App\Models\Debitur;
use App\Models\PermohonanKredit;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $admin->forceFill(['must_change_password' => false])->save();
    $this->actingAs($admin);

    $this->debitur = Debitur::query()->create([
        'cabang_id' => $admin->cabang_id,
        'nama_ktp' => 'Debitur Wizard Permohonan',
        'no_ktp' => '7371000000008888',
        'jenis_kelamin' => 'L',
        'status_perkawinan' => 'MENIKAH',
        'no_hp' => '081200000088',
        'alamat_jalan' => 'Jalan Uji',
        'alamat_rt' => '001',
        'alamat_rw' => '002',
        'kelurahan' => 'Kelurahan',
        'kecamatan' => 'Kecamatan',
        'kabupaten' => 'Kendari',
        'provinsi' => 'Sulawesi Tenggara',
        'tempat_lahir' => 'Kendari',
        'tanggal_lahir' => '1990-01-01',
        'sumber_data' => 'MANUAL',
    ]);

    $this->produk = Produk::query()->where('cabang_id', $admin->cabang_id)->where('kode', 'TPP')->firstOrFail();
});

it('membuat draft pada langkah plafon lalu mengajukan permohonan', function () {
    $component = Livewire::test(PermohonanWizard::class)
        ->set('debitur_id', $this->debitur->id)
        ->set('produk_id', $this->produk->id)
        ->call('next')
        ->assertSet('step', 2)
        ->set('plafon_diajukan', '80000000')
        ->set('suku_bunga_tahunan', '0.18')
        ->set('tenor_diajukan', '60')
        ->set('sistem_suku_bunga', 'Flat')
        ->set('tanggal_permohonan', '2026-01-05')
        ->set('kegunaan_pinjaman', 'Konsumsi')
        ->call('next')
        ->assertSet('step', 3)
        ->call('next')
        ->assertSet('step', 4)
        ->call('next')
        ->assertSet('step', 5)
        ->call('submit');

    $permohonan = PermohonanKredit::query()->where('debitur_id', $this->debitur->id)->firstOrFail();

    expect($permohonan->status)->toBe('SUBMITTED')
        ->and((float) $permohonan->plafon_diajukan)->toBe(80000000.0)
        ->and($permohonan->no_permohonan)->toStartWith('KDI/');

    expect($component)->not->toBeNull();
});

it('mengurai tempelan SLIK menjadi baris fasilitas', function () {
    $kode = DB::table('ref_kolektibilitas')->where('kode', '1')->value('id');

    Livewire::test(PermohonanWizard::class)
        ->set('debitur_id', $this->debitur->id)
        ->set('produk_id', $this->produk->id)
        ->call('next')
        ->set('plafon_diajukan', '50000000')
        ->set('suku_bunga_tahunan', '0.15')
        ->set('tenor_diajukan', '36')
        ->set('sistem_suku_bunga', 'Flat')
        ->set('tanggal_permohonan', '2026-01-05')
        ->set('kegunaan_pinjaman', 'Konsumsi')
        ->call('next')
        ->call('next')
        ->set('paste', "BANK ABC;KPR;150000000;120000000;1;1500000\nKOPERASI;Modal;25000000;10000000;2;500000")
        ->call('parsePaste')
        ->assertCount('slikRows', 2)
        ->assertSet('slikRows.0.kolektibilitas_id', (string) $kode);
});
