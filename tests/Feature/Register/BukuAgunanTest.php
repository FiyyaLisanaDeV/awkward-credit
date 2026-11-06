<?php

declare(strict_types=1);

use App\Models\BukuAgunan;
use App\Models\Debitur;
use App\Models\DebiturAset;
use App\Models\Produk;
use App\Models\RefKategoriAgunanPojk;
use App\Services\Permohonan\PermohonanService;
use App\Services\Register\BukuAgunanService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function buatAsetAgunan(Debitur $debitur, string $noDokumen = 'SHM-001'): DebiturAset
{
    return DebiturAset::query()->create([
        'debitur_id' => $debitur->id,
        'urutan' => 1,
        'jenis_aset' => 'Tanah dan Bangunan',
        'kategori_pojk_id' => RefKategoriAgunanPojk::query()->value('id'),
        'jenis_dokumen' => 'SHM',
        'no_dokumen' => $noDokumen,
        'luas_jumlah' => 100,
        'satuan' => 'm2',
        'harga_taksasi_satuan' => 1000000,
        'nilai_taksasi' => 100000000,
        'persen_diperhitungkan' => 0.8,
        'nilai_diperhitungkan' => 80000000,
        'status_kepemilikan' => 'MILIK_SENDIRI',
        'nama_pemilik' => 'Pemilik Uji',
    ]);
}

function dataPermohonanAgunan(Debitur $debitur): array
{
    $produk = Produk::query()->where('cabang_id', $debitur->cabang_id)->where('kode', 'TPP')->firstOrFail();

    return [
        'debitur_id' => $debitur->id,
        'produk_id' => $produk->id,
        'plafon_diajukan' => 5000000,
        'suku_bunga_tahunan' => 0.18,
        'tenor_diajukan' => 12,
        'sistem_suku_bunga' => 'Flat',
        'kegunaan_pinjaman' => 'Konsumsi',
        'tujuan_penggunaan' => 'Konsumsi',
        'tanggal_permohonan' => '2026-01-05',
    ];
}

function debiturBomAgunan(): Debitur
{
    $cabangId = DB::table('cabang')->where('kode', 'BOM')->value('id');

    return Debitur::query()->create([
        'cabang_id' => $cabangId,
        'nama_ktp' => 'Debitur BOM Agunan',
        'no_ktp' => fixtureNik(),
        'jenis_kelamin' => 'L',
        'status_perkawinan' => 'MENIKAH',
        'no_hp' => '081200000000',
        'alamat_jalan' => 'Jalan Uji',
        'alamat_rt' => '001',
        'alamat_rw' => '002',
        'kelurahan' => 'Kelurahan',
        'kecamatan' => 'Kecamatan',
        'kabupaten' => 'Bombana',
        'provinsi' => 'Sulawesi Tenggara',
        'tempat_lahir' => 'Bombana',
        'tanggal_lahir' => '1990-01-01',
    ]);
}

it('mencatat agunan saat permohonan diajukan', function () {
    $ao = workflowUser('AO');
    $debitur = fixtureDebitur();
    $aset = buatAsetAgunan($debitur);

    $permohonan = app(PermohonanService::class)->create(dataPermohonanAgunan($debitur), $ao);
    app(PermohonanService::class)->submit($permohonan, $ao);

    expect(BukuAgunan::query()->where('agunan_key', app(BukuAgunanService::class)->agunanKey($aset))->where('peristiwa', 'DIPAKAI')->count())->toBe(1);
});

it('mendeteksi agunan yang dipakai lintas cabang', function () {
    $ao = workflowUser('AO');
    $debiturKdi = fixtureDebitur();
    $asetKdi = buatAsetAgunan($debiturKdi, 'SHM-BERSAMA');

    $permohonanKdi = app(PermohonanService::class)->create(dataPermohonanAgunan($debiturKdi), $ao);
    app(PermohonanService::class)->submit($permohonanKdi, $ao);

    $bomCabang = DB::table('cabang')->where('kode', 'BOM')->value('id');
    $bomAo = workflowUser('AO', null, $bomCabang);
    $debiturBom = debiturBomAgunan();
    buatAsetAgunan($debiturBom, 'SHM-BERSAMA');

    $permohonanBom = app(PermohonanService::class)->create(dataPermohonanAgunan($debiturBom), $bomAo);
    app(PermohonanService::class)->submit($permohonanBom, $bomAo);

    $service = app(BukuAgunanService::class);
    $key = $service->agunanKey($asetKdi);

    expect(BukuAgunan::query()->where('agunan_key', $key)->count())->toBe(2)
        ->and($service->terpakaiLintasCabang($key))->toBeTrue();
});

it('menolak update dan delete pada buku agunan (immutable)', function () {
    $ao = workflowUser('AO');
    $debitur = fixtureDebitur();
    buatAsetAgunan($debitur);

    $permohonan = app(PermohonanService::class)->create(dataPermohonanAgunan($debitur), $ao);
    app(PermohonanService::class)->submit($permohonan, $ao);

    expect(fn () => DB::table('buku_agunan')->update(['nama_pemilik' => 'diubah']))
        ->toThrow(QueryException::class);

    expect(fn () => DB::table('buku_agunan')->delete())
        ->toThrow(QueryException::class);
});

it('menampilkan halaman buku agunan', function () {
    $admin = workflowUser('ADMIN');
    $debitur = fixtureDebitur();
    $aset = buatAsetAgunan($debitur);

    $permohonan = app(PermohonanService::class)->create(dataPermohonanAgunan($debitur), $admin);
    app(PermohonanService::class)->submit($permohonan, $admin);

    $this->actingAs($admin)->get(route('buku-agunan.index'))->assertOk();

    $key = app(BukuAgunanService::class)->agunanKey($aset);
    $this->actingAs($admin)->get(route('buku-agunan.show', $key))->assertOk();
});
