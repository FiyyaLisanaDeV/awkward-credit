<?php

declare(strict_types=1);

use App\Models\Debitur;
use App\Models\ImportBatch;
use App\Models\JadwalAngsuran;
use App\Models\Kredit;
use App\Models\PermohonanKredit;
use App\Models\User;
use App\Support\Konstanta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('local');
});

function kreditUntukImport(string $kode = 'KDI', ?string $noRekening = null): Kredit
{
    $cabangId = DB::table('cabang')->where('kode', $kode)->value('id');

    $debitur = Debitur::query()->create([
        'cabang_id' => $cabangId,
        'nama_ktp' => 'Debitur Import '.$kode,
        'no_ktp' => '7371'.str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT),
        'jenis_kelamin' => 'L',
        'status_perkawinan' => 'MENIKAH',
        'no_hp' => '081200000000',
        'alamat_jalan' => 'Jalan Uji',
        'alamat_rt' => '001',
        'alamat_rw' => '002',
        'kelurahan' => 'Kelurahan',
        'kecamatan' => 'Kecamatan',
        'kabupaten' => $kode === 'BOM' ? 'Bombana' : 'Kendari',
        'provinsi' => 'Sulawesi Tenggara',
        'tempat_lahir' => 'Kota',
        'tanggal_lahir' => '1990-01-01',
        'sumber_data' => 'MANUAL',
    ]);

    $permohonan = PermohonanKredit::query()->create([
        'cabang_id' => $cabangId,
        'no_permohonan' => $kode.'/IMP/'.strtoupper(Str::random(6)),
        'debitur_id' => $debitur->id,
        'produk_id' => DB::table('produk')->where('cabang_id', $cabangId)->value('id'),
        'plafon_diajukan' => 5000000,
        'suku_bunga_tahunan' => 0.18,
        'tenor_diajukan' => 12,
        'sistem_suku_bunga' => 'Flat',
        'kegunaan_pinjaman' => 'Konsumsi',
        'status' => 'APPROVED',
        'ao_id' => User::query()->where('username', 'admin')->value('id'),
        'parameter_kebijakan_id' => DB::table('ref_parameter_kebijakan')->where('status', 'ACTIVE')->value('id'),
        'tanggal_permohonan' => '2026-11-06',
        'kelengkapan_persen' => 100,
    ]);

    $no = $noRekening ?? ($kode.'/IMP/'.strtoupper(Str::random(8)));

    $kredit = Kredit::query()->create([
        'cabang_id' => $cabangId,
        'permohonan_id' => $permohonan->id,
        'no_rekening_kredit' => $no,
        'tanggal_akad' => '2026-11-06',
        'tanggal_cair' => '2026-11-06',
        'plafon' => 5000000,
        'rate' => 0.18,
        'tenor' => 12,
        'sistem_suku_bunga' => 'Flat',
        'angsuran_bulanan' => 500000,
        'total_bunga' => 1000000,
        'total_pembayaran' => 6000000,
        'rekening_pencairan' => '1234567890',
        'status' => 'ACTIVE',
    ]);

    JadwalAngsuran::query()->create([
        'kredit_id' => $kredit->id,
        'periode' => 1,
        'tanggal_jatuh_tempo' => '2026-12-06',
        'saldo_awal' => 5000000,
        'angsuran_pokok' => 400000,
        'bunga' => 100000,
        'total_angsuran' => 500000,
        'saldo_akhir' => 4600000,
        'status' => Konstanta::JADWAL_BELUM_BAYAR,
    ]);

    return $kredit;
}

it('membuat pratinjau impor pembayaran valid', function () {
    $admin = workflowUser('ADMIN');
    $kredit = kreditUntukImport();

    $csv = "no_rekening_kredit,periode,tanggal_bayar,metode,no_bukti,keterangan\n"
        .$kredit->no_rekening_kredit.",1,2026-12-06,TUNAI,BUKTI-001,ok\n";

    $this->actingAs($admin)
        ->post(route('import.store'), [
            'file' => UploadedFile::fake()->createWithContent('bayar.csv', $csv),
        ])
        ->assertRedirect();

    $batch = ImportBatch::query()->firstOrFail();

    expect($batch->total_baris)->toBe(1)
        ->and($batch->sukses)->toBe(1)
        ->and($batch->gagal)->toBe(0);
});

it('melaporkan baris tidak valid', function () {
    $admin = workflowUser('ADMIN');

    $csv = "no_rekening_kredit,periode,tanggal_bayar,metode,no_bukti,keterangan\n"
        ."TIDAK-ADA,1,2026-12-06,TUNAI,,\n";

    $this->actingAs($admin)
        ->post(route('import.store'), [
            'file' => UploadedFile::fake()->createWithContent('bayar.csv', $csv),
        ])
        ->assertRedirect();

    $batch = ImportBatch::query()->firstOrFail();

    expect($batch->gagal)->toBe(1)
        ->and($batch->sukses)->toBe(0)
        ->and($batch->baris()->first()->errors)->toContain('no_rekening_kredit tidak ditemukan di cabang ini.');
});

it('menandai duplikat kredit + periode', function () {
    $admin = workflowUser('ADMIN');
    $kredit = kreditUntukImport();

    $csv = "no_rekening_kredit,periode,tanggal_bayar,metode,no_bukti,keterangan\n"
        .$kredit->no_rekening_kredit.",1,2026-12-06,TUNAI,,\n"
        .$kredit->no_rekening_kredit.",1,2026-12-06,TUNAI,,\n";

    $this->actingAs($admin)
        ->post(route('import.store'), [
            'file' => UploadedFile::fake()->createWithContent('bayar.csv', $csv),
        ])
        ->assertRedirect();

    $batch = ImportBatch::query()->firstOrFail();

    expect($batch->sukses)->toBe(1)
        ->and($batch->gagal)->toBe(1);
});

it('menolak akses import tanpa permission', function () {
    $ao = workflowUser('AO');

    $this->actingAs($ao)
        ->get(route('import.index'))
        ->assertForbidden();
});

it('menolak detail batch lintas cabang', function () {
    $bomAdmin = workflowUser('ADMIN', null, DB::table('cabang')->where('kode', 'BOM')->value('id'));
    $kredit = kreditUntukImport('BOM');

    $csv = "no_rekening_kredit,periode,tanggal_bayar,metode,no_bukti,keterangan\n"
        .$kredit->no_rekening_kredit.",1,2026-12-06,TUNAI,,\n";

    $this->actingAs($bomAdmin)
        ->post(route('import.store'), [
            'file' => UploadedFile::fake()->createWithContent('bayar.csv', $csv),
        ])
        ->assertRedirect();

    $batch = ImportBatch::query()->firstOrFail();
    $kdiAdmin = workflowUser('ADMIN');

    $this->actingAs($kdiAdmin)
        ->get(route('import.show', $batch))
        ->assertForbidden();
});
