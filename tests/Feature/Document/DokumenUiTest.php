<?php

declare(strict_types=1);

use App\Enums\JenisDokumen;
use App\Enums\LedgerEvent;
use App\Enums\PihakSignature;
use App\Enums\StatusPermohonan;
use App\Enums\StatusTemplate;
use App\Models\Debitur;
use App\Models\Dokumen;
use App\Models\Ledger;
use App\Models\PermohonanKredit;
use App\Models\RefTemplateDokumen;
use App\Models\User;
use App\Services\Document\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('local');
});

function dokumenPermohonanBom(): PermohonanKredit
{
    $cabangId = DB::table('cabang')->where('kode', 'BOM')->value('id');

    $debitur = Debitur::query()->create([
        'cabang_id' => $cabangId,
        'nama_ktp' => 'Debitur Dokumen BOM',
        'no_ktp' => '7371'.str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT),
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
        'sumber_data' => 'MANUAL',
    ]);

    return PermohonanKredit::query()->create([
        'cabang_id' => $cabangId,
        'no_permohonan' => 'BOM/UAT/'.strtoupper(Str::random(6)),
        'debitur_id' => $debitur->id,
        'produk_id' => DB::table('produk')->where('cabang_id', $cabangId)->value('id'),
        'plafon_diajukan' => 5000000,
        'suku_bunga_tahunan' => 0.18,
        'tenor_diajukan' => 12,
        'sistem_suku_bunga' => 'Flat',
        'kegunaan_pinjaman' => 'Konsumsi',
        'status' => StatusPermohonan::APPROVED->value,
        'ao_id' => User::query()->where('username', 'admin')->value('id'),
        'parameter_kebijakan_id' => DB::table('ref_parameter_kebijakan')->where('status', 'ACTIVE')->value('id'),
        'tanggal_permohonan' => '2026-11-06',
        'kelengkapan_persen' => 100,
    ]);
}

it('menampilkan daftar dokumen untuk admin', function () {
    $admin = workflowUser('ADMIN');

    $this->actingAs($admin)
        ->get(route('dokumen.index'))
        ->assertOk()
        ->assertSee('Dokumen');
});

it('menghasilkan dokumen lewat UI setelah template disetujui', function () {
    RefTemplateDokumen::query()->update(['status' => StatusTemplate::APPROVED->value]);

    $admin = workflowUser('ADMIN');
    $permohonan = fixturePermohonan(['status' => StatusPermohonan::APPROVED->value]);

    $this->actingAs($admin)
        ->post(route('dokumen.generate', $permohonan), ['jenis' => 'DOC-01'])
        ->assertRedirect();

    expect(Dokumen::query()->count())->toBe(1);
});

it('menolak generate lewat UI saat template DRAFT (BR-07)', function () {
    $admin = workflowUser('ADMIN');
    $permohonan = fixturePermohonan(['status' => StatusPermohonan::APPROVED->value]);

    $this->actingAs($admin)
        ->post(route('dokumen.generate', $permohonan), ['jenis' => 'DOC-01'])
        ->assertStatus(422);
});

it('menandatangani dokumen lewat UI dan mencatat ledger', function () {
    RefTemplateDokumen::query()->update(['status' => StatusTemplate::APPROVED->value]);

    $admin = workflowUser('ADMIN');
    $permohonan = fixturePermohonan(['status' => StatusPermohonan::APPROVED->value]);
    $dokumen = app(DocumentService::class)->generate($permohonan, JenisDokumen::Doc04, $admin);

    $this->actingAs($admin)
        ->post(route('dokumen.sign', $dokumen), [
            'pihak' => PihakSignature::Debitur->value,
            'nama' => 'Debitur Uji',
        ])
        ->assertRedirect();

    expect($dokumen->fresh()->status_ttd)->toBe('DITANDATANGANI')
        ->and(Ledger::query()->where('event', LedgerEvent::DokumenDitandatangani->value)->count())->toBe(1);
});

it('menolak sign tanpa permission dokumen.sign', function () {
    RefTemplateDokumen::query()->update(['status' => StatusTemplate::APPROVED->value]);

    $admin = workflowUser('ADMIN');
    $ao = workflowUser('AO');
    $permohonan = fixturePermohonan(['status' => StatusPermohonan::APPROVED->value]);
    $dokumen = app(DocumentService::class)->generate($permohonan, JenisDokumen::Doc04, $admin);

    $this->actingAs($ao)
        ->post(route('dokumen.sign', $dokumen), [
            'pihak' => PihakSignature::Debitur->value,
            'nama' => 'Debitur Uji',
        ])
        ->assertForbidden();
});

it('menolak akses dokumen lintas cabang', function () {
    RefTemplateDokumen::query()->update(['status' => StatusTemplate::APPROVED->value]);

    $admin = workflowUser('ADMIN');
    $permohonan = dokumenPermohonanBom();

    $this->actingAs($admin)
        ->post(route('dokumen.generate', $permohonan), ['jenis' => 'DOC-01'])
        ->assertForbidden();
});
