<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function seedCabang(): string
{
    $id = (string) Str::uuid();

    DB::table('cabang')->insert([
        'id' => $id,
        'kode' => 'KDI',
        'nama' => 'BPR Bahteramas Kendari',
        'alamat' => 'Kendari',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}

/**
 * @return array<string, mixed>
 */
function debiturRow(string $cabangId, string $nik, array $overrides = []): array
{
    return array_merge([
        'id' => (string) Str::uuid(),
        'cabang_id' => $cabangId,
        'nama_ktp' => 'Debitur Uji',
        'no_ktp' => $nik,
        'jenis_kelamin' => 'L',
        'status_perkawinan' => 'MENIKAH',
        'no_hp' => '081200000000',
        'alamat_jalan' => 'Jalan Uji',
        'alamat_rt' => '001',
        'alamat_rw' => '002',
        'kelurahan' => 'Kelurahan',
        'kecamatan' => 'Kecamatan',
        'kabupaten' => 'Kabupaten',
        'provinsi' => 'Sulawesi Tenggara',
        'tempat_lahir' => 'Kendari',
        'tanggal_lahir' => '1990-01-01',
        'sumber_data' => 'MANUAL',
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides);
}

it('menolak NIK ganda pada debitur aktif', function () {
    $cabangId = seedCabang();

    DB::table('debitur')->insert(debiturRow($cabangId, '7371000000000001'));

    expect(fn () => DB::table('debitur')->insert(debiturRow($cabangId, '7371000000000001')))
        ->toThrow(QueryException::class);
});

it('mengizinkan NIK sama setelah debitur dihapus (soft delete)', function () {
    $cabangId = seedCabang();

    DB::table('debitur')->insert(debiturRow($cabangId, '7371000000000002', ['deleted_at' => now()]));

    DB::table('debitur')->insert(debiturRow($cabangId, '7371000000000002'));

    expect(DB::table('debitur')->whereNull('deleted_at')->count())->toBe(1);
});

it('menghapus data anak saat debitur dihapus', function () {
    $cabangId = seedCabang();

    $debitur = debiturRow($cabangId, '7371000000000003');
    DB::table('debitur')->insert($debitur);

    DB::table('debitur_pasangan')->insert([
        'id' => (string) Str::uuid(),
        'debitur_id' => $debitur['id'],
        'nama_ktp' => 'Pasangan Uji',
        'jenis_kelamin' => 'P',
        'tanggal_lahir' => '1992-02-02',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('debitur')->where('id', $debitur['id'])->delete();

    expect(DB::table('debitur_pasangan')->where('debitur_id', $debitur['id'])->count())->toBe(0);
});
