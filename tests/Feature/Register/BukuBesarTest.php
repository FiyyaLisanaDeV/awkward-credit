<?php

declare(strict_types=1);

use App\Models\BukuBesarKredit;
use App\Models\Debitur;
use App\Models\Produk;
use App\Services\Approval\ApprovalService;
use App\Services\Kredit\KreditService;
use App\Services\Permohonan\PermohonanService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function dataPermohonanRegister(Debitur $debitur): array
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

function debiturBom(): Debitur
{
    $cabangId = DB::table('cabang')->where('kode', 'BOM')->value('id');

    return Debitur::query()->create([
        'cabang_id' => $cabangId,
        'nama_ktp' => 'Debitur BOM Register',
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

it('merekam kronologi inisiasi, pengajuan, persetujuan, dan realisasi', function () {
    $ao = workflowUser('AO');
    $debitur = fixtureDebitur();
    $service = app(PermohonanService::class);

    $permohonan = $service->create(dataPermohonanRegister($debitur), $ao);

    expect(BukuBesarKredit::query()->where('permohonan_id', $permohonan->id)->where('tahap', 'INISIASI')->count())->toBe(1);

    $service->submit($permohonan, $ao);
    $permohonan->refresh();

    expect(BukuBesarKredit::query()->where('permohonan_id', $permohonan->id)->where('tahap', 'PENGAJUAN')->count())->toBe(1);

    $admin = workflowUser('ADMIN');
    $approval = app(ApprovalService::class);
    $guard = 0;

    fixtureKapasitas($permohonan->fresh());
    fixtureChecklistLengkap($permohonan->fresh());

    while ($approval->nextLevel($permohonan->fresh()) !== null && $guard < 10) {
        $approval->approve($permohonan->fresh(), $admin);
        $guard++;
    }

    expect(BukuBesarKredit::query()->where('permohonan_id', $permohonan->id)->where('tahap', 'PERSETUJUAN')->count())->toBeGreaterThan(0);

    $kredit = app(KreditService::class)->cairkan($permohonan->fresh(), $admin, [
        'tanggal_cair' => '2026-02-01',
        'rekening_pencairan' => '1234567890',
    ]);

    $realisasi = BukuBesarKredit::query()
        ->where('permohonan_id', $permohonan->id)
        ->where('tahap', 'REALISASI')
        ->first();

    expect($realisasi)->not->toBeNull()
        ->and($realisasi->kredit_id)->toBe($kredit->id)
        ->and($realisasi->snapshot['kredit']['no_rekening_kredit'])->toBe($kredit->no_rekening_kredit);

    $tahap = BukuBesarKredit::query()->where('permohonan_id', $permohonan->id)->orderBy('seq')->pluck('tahap')->all();

    expect($tahap[0])->toBe('INISIASI')
        ->and($tahap[1])->toBe('PENGAJUAN')
        ->and($tahap)->toContain('PERSETUJUAN', 'REALISASI');
});

it('menolak update dan delete pada buku besar (immutable)', function () {
    $ao = workflowUser('AO');
    $debitur = fixtureDebitur();

    app(PermohonanService::class)->create(dataPermohonanRegister($debitur), $ao);

    expect(fn () => DB::table('buku_besar_kredit')->update(['ringkasan' => 'diubah']))
        ->toThrow(QueryException::class);

    expect(fn () => DB::table('buku_besar_kredit')->delete())
        ->toThrow(QueryException::class);
});

it('menampilkan buku besar dan menolak lintas cabang', function () {
    $admin = workflowUser('ADMIN');
    $debitur = fixtureDebitur();
    $permohonan = app(PermohonanService::class)->create(dataPermohonanRegister($debitur), $admin);

    $this->actingAs($admin)->get(route('buku-besar.index'))->assertOk();
    $this->actingAs($admin)->get(route('buku-besar.show', $permohonan))->assertOk();

    $bomCabang = DB::table('cabang')->where('kode', 'BOM')->value('id');
    $bomAo = workflowUser('AO', null, $bomCabang);
    $bomPermohonan = app(PermohonanService::class)->create(dataPermohonanRegister(debiturBom()), $bomAo);

    $this->actingAs($admin)->get(route('buku-besar.show', $bomPermohonan))->assertForbidden();
});
