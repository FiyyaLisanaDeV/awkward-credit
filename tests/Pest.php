<?php

declare(strict_types=1);

use App\Enums\SistemSukuBunga;
use App\Models\AnalisisKapasitasBayar;
use App\Models\Debitur;
use App\Models\PermohonanKredit;
use App\Models\Produk;
use App\Models\RefParameterKebijakan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->in('Feature', 'Unit/Guard');

function fixtureNik(): string
{
    return '7371'.str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
}

function fixtureDebitur(?string $nik = null): Debitur
{
    return Debitur::query()->create([
        'cabang_id' => DB::table('cabang')->value('id'),
        'nama_ktp' => 'Debitur Uji',
        'no_ktp' => $nik ?? fixtureNik(),
        'jenis_kelamin' => 'L',
        'status_perkawinan' => 'MENIKAH',
        'no_hp' => '081200000000',
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
}

/**
 * @param  array<string, mixed>  $overrides
 */
function fixturePermohonan(array $overrides = []): PermohonanKredit
{
    $user = User::query()->where('username', 'admin')->firstOrFail();
    $debitur = fixtureDebitur();
    $produk = Produk::query()->where('kode', 'TPP')->firstOrFail();
    $parameter = RefParameterKebijakan::query()->where('status', 'ACTIVE')->firstOrFail();

    return PermohonanKredit::query()->create(array_merge([
        'cabang_id' => $debitur->cabang_id,
        'no_permohonan' => 'KDI/2026/'.strtoupper(Str::random(6)),
        'debitur_id' => $debitur->id,
        'produk_id' => $produk->id,
        'plafon_diajukan' => 80000000,
        'suku_bunga_tahunan' => 0.18,
        'tenor_diajukan' => 60,
        'sistem_suku_bunga' => SistemSukuBunga::Flat->value,
        'kegunaan_pinjaman' => 'Konsumsi',
        'ao_id' => $user->id,
        'parameter_kebijakan_id' => $parameter->id,
        'tanggal_permohonan' => '2026-01-05',
        'status' => 'DRAFT',
    ], $overrides));
}

/**
 * @param  array<string, mixed>  $overrides
 */
function fixtureKapasitas(PermohonanKredit $permohonan, array $overrides = []): AnalisisKapasitasBayar
{
    return AnalisisKapasitasBayar::query()->create(array_merge([
        'permohonan_id' => $permohonan->id,
        'parameter_kebijakan_id' => $permohonan->parameter_kebijakan_id,
        'dscr_rekomendasi' => 1.5,
        'ltv' => 0.5,
        'idir' => 0.30,
        'rasio_kecukupan_balloon' => 1.2,
        'plafon_max_flat' => 90000000,
    ], $overrides));
}

function workflowUser(string $role, ?string $username = null, ?string $cabangId = null): User
{
    $cabang = $cabangId ?? DB::table('cabang')->where('kode', 'KDI')->value('id');

    $user = User::query()->create([
        'cabang_id' => $cabang,
        'name' => 'Uji '.$role,
        'username' => $username ?? 'uji_'.strtolower($role).'_'.Str::lower(Str::random(5)),
        'email' => Str::lower($role).'_'.Str::lower(Str::random(6)).'@bpr.local',
        'password' => 'password',
        'jabatan' => $role,
        'is_active' => true,
        'must_change_password' => false,
    ]);

    $user->assignRole($role);

    return $user;
}

function assertHttpStatus(callable $callback, int $expected): void
{
    try {
        $callback();
    } catch (HttpException $exception) {
        expect($exception->getStatusCode())->toBe($expected);

        return;
    }

    throw new RuntimeException('Diharapkan HttpException '.$expected.' tetapi tidak dilempar.');
}
