<?php

declare(strict_types=1);

use App\Enums\StatusPermohonan;
use App\Models\Debitur;
use App\Models\Kredit;
use App\Models\PermohonanKredit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function approvedPermohonanUi(User $maker, string $kodeCabang = 'KDI'): PermohonanKredit
{
    $cabangId = DB::table('cabang')->where('kode', $kodeCabang)->value('id');

    $debitur = Debitur::query()->create([
        'cabang_id' => $cabangId,
        'nama_ktp' => 'Debitur Cairkan '.$kodeCabang,
        'no_ktp' => '7371'.str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT),
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

    return PermohonanKredit::query()->create([
        'cabang_id' => $cabangId,
        'no_permohonan' => $kodeCabang.'/UAT/'.strtoupper(Str::random(6)),
        'debitur_id' => $debitur->id,
        'produk_id' => DB::table('produk')->where('cabang_id', $cabangId)->value('id'),
        'plafon_diajukan' => 5000000,
        'suku_bunga_tahunan' => 0.18,
        'tenor_diajukan' => 12,
        'sistem_suku_bunga' => 'Flat',
        'kegunaan_pinjaman' => 'Konsumsi',
        'status' => StatusPermohonan::APPROVED->value,
        'ao_id' => $maker->id,
        'parameter_kebijakan_id' => DB::table('ref_parameter_kebijakan')->where('status', 'ACTIVE')->value('id'),
        'tanggal_permohonan' => '2026-11-06',
        'kelengkapan_persen' => 100,
    ]);
}

it('mencairkan lewat UI dan mengarahkan ke detail kredit', function () {
    $admin = workflowUser('ADMIN');
    $permohonan = approvedPermohonanUi($admin);

    $this->actingAs($admin)
        ->get(route('kredit.cairkan.create', $permohonan))
        ->assertOk();

    $this->actingAs($admin)
        ->post(route('kredit.cairkan.store', $permohonan), [
            'tanggal_cair' => '2026-11-06',
            'rekening_pencairan' => '1234567890',
            'no_akad' => 'AKAD/UAT/1',
        ])
        ->assertRedirect(route('kredit.show', Kredit::query()->firstOrFail()));

    expect(Kredit::query()->count())->toBe(1)
        ->and($permohonan->fresh()->status)->toBe(StatusPermohonan::ACTIVE->value);
});

it('menolak pencairan lewat UI bila belum disetujui', function () {
    $admin = workflowUser('ADMIN');
    $permohonan = approvedPermohonanUi($admin);
    $permohonan->update(['status' => StatusPermohonan::SUBMITTED->value]);

    $this->actingAs($admin)
        ->post(route('kredit.cairkan.store', $permohonan), [
            'tanggal_cair' => '2026-11-06',
            'rekening_pencairan' => '1234567890',
        ])
        ->assertStatus(409);
});

it('menolak pencairan tanpa permission kredit.cairkan', function () {
    $ao = workflowUser('AO');
    $permohonan = approvedPermohonanUi($ao);

    $this->actingAs($ao)
        ->get(route('kredit.cairkan.create', $permohonan))
        ->assertForbidden();
});

it('menolak pencairan lintas cabang', function () {
    $admin = workflowUser('ADMIN');
    $permohonan = approvedPermohonanUi($admin, 'BOM');

    $this->actingAs($admin)
        ->get(route('kredit.cairkan.create', $permohonan))
        ->assertForbidden();
});
