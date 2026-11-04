<?php

declare(strict_types=1);

use App\Enums\StatusPermohonan;
use App\Models\PermohonanKredit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

function apiUser(): User
{
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $admin->forceFill(['password' => Hash::make('rahasia123')])->save();

    return $admin;
}

it('menolak akses tanpa token', function () {
    $this->getJson('/api/v1/debitur')->assertUnauthorized();
});

it('login mengembalikan token dan me mengembalikan profil', function () {
    apiUser();

    $response = $this->postJson('/api/v1/login', [
        'email' => 'admin@bpr-bahteramas.local',
        'password' => 'rahasia123',
    ])->assertCreated()->assertJsonStructure(['token', 'user']);

    $token = (string) $response->json('token');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('email', 'admin@bpr-bahteramas.local');
});

it('membuat dan menampilkan debitur', function () {
    Sanctum::actingAs(apiUser());

    $payload = [
        'nama_ktp' => 'Debitur API',
        'no_ktp' => '7371000000001234',
        'jenis_kelamin' => 'L',
        'status_perkawinan' => 'MENIKAH',
        'no_hp' => '081200000001',
        'alamat_jalan' => 'Jalan API',
        'alamat_rt' => '001',
        'alamat_rw' => '002',
        'kelurahan' => 'Kelurahan',
        'kecamatan' => 'Kecamatan',
        'kabupaten' => 'Kendari',
        'provinsi' => 'Sulawesi Tenggara',
        'tempat_lahir' => 'Kendari',
        'tanggal_lahir' => '1990-01-01',
    ];

    $id = $this->postJson('/api/v1/debitur', $payload)
        ->assertCreated()
        ->json('id');

    $this->getJson('/api/v1/debitur/'.$id)->assertOk()->assertJsonPath('nama_ktp', 'Debitur API');
    $this->getJson('/api/v1/debitur?q=API')->assertOk();
});

it('menampilkan permohonan dan mengajukannya', function () {
    Sanctum::actingAs(apiUser());

    $permohonan = fixturePermohonan();

    $this->getJson('/api/v1/permohonan/'.$permohonan->id)->assertOk();

    $this->postJson('/api/v1/permohonan/'.$permohonan->id.'/submit')
        ->assertOk()
        ->assertJsonPath('status', StatusPermohonan::SUBMITTED->value);
});

it('menghitung kapasitas bayar dari laba rugi', function () {
    Sanctum::actingAs(apiUser());

    $permohonan = fixturePermohonan();

    $this->putJson('/api/v1/permohonan/'.$permohonan->id.'/analisis/laba-rugi', [
        'total_pendapatan' => 8000000,
        'total_pengeluaran' => 3000000,
        'surplus_per_bulan' => 5000000,
    ])->assertOk();

    $this->postJson('/api/v1/permohonan/'.$permohonan->id.'/analisis/kapasitas/compute')
        ->assertOk()
        ->assertJsonStructure(['rpc_bulanan', 'dscr_rekomendasi', 'plafon_max_flat', 'ltv']);
});

it('menyimpan simulasi dan replay identik', function () {
    Sanctum::actingAs(apiUser());

    $id = $this->postJson('/api/v1/simulasi', [
        'label' => 'Uji API',
        'produk_tipe' => 'TPP',
        'sistem_suku_bunga' => 'Flat',
        'plafon' => 80000000,
        'rate' => 0.18,
        'tenor' => 60,
        'surplus_bulanan' => 5000000,
        'penghasilan_bersih' => 3048080,
    ])->assertCreated()->json('id');

    $this->postJson('/api/v1/simulasi/'.$id.'/replay')
        ->assertOk()
        ->assertJsonPath('identik', true);
});

it('menyetujui permohonan sesuai level', function () {
    Sanctum::actingAs(apiUser());

    $permohonan = fixturePermohonan();
    $permohonan->update(['status' => StatusPermohonan::SUBMITTED->value]);

    $this->postJson('/api/v1/permohonan/'.$permohonan->id.'/approve')
        ->assertOk();

    expect(PermohonanKredit::query()->findOrFail($permohonan->id)->status)
        ->toBe(StatusPermohonan::RECOMMENDED->value);
});

it('memverifikasi ledger lewat API', function () {
    Sanctum::actingAs(apiUser());

    $this->getJson('/api/v1/ledger/verify')
        ->assertOk()
        ->assertJsonPath('exit', 0);
});
