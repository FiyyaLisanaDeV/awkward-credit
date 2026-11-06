<?php

declare(strict_types=1);

use App\Livewire\Analisis\Workbench as AnalisisWorkbench;
use App\Models\AnalisisKapasitasBayar;
use App\Models\AnalisisModalKerja;
use App\Models\AnalisisNeraca;
use App\Models\AnalisisRasio;
use App\Models\AnalisisScoring5c;
use App\Models\AnalisisSensitivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed();
    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $admin->forceFill(['must_change_password' => false])->save();
    $this->actingAs($admin);
});

it('menampilkan workbench analisis', function () {
    $permohonan = fixturePermohonan();

    $this->get(route('permohonan.analisis', $permohonan))
        ->assertOk()
        ->assertSee('Analisis '.$permohonan->no_permohonan)
        ->assertSee('Neraca');
});

it('menyimpan neraca dan menghitung kapasitas dari workbench', function () {
    $permohonan = fixturePermohonan();

    Livewire::test(AnalisisWorkbench::class, ['permohonan' => $permohonan])
        ->set('total_aktiva', '1000000')
        ->set('total_pasiva', '900000')
        ->set('jumlah_aktiva_lancar', '600000')
        ->set('kas_inventaris', '100000')
        ->set('persediaan', '200000')
        ->set('hutang_pendek', '300000')
        ->set('jumlah_hutang', '400000')
        ->set('jumlah_modal', '600000')
        ->call('saveNeraca')
        ->assertSet('status', 'Neraca disimpan.')
        ->set('total_pendapatan', '10000000')
        ->set('total_pengeluaran', '5000000')
        ->call('saveLabaRugi')
        ->call('hitungRasio')
        ->set('biaya_operasional_tahunan', '120000000')
        ->set('periode_perputaran_hari', '60')
        ->set('pembelian_bulanan', '10000000')
        ->set('hari_persediaan', '15')
        ->set('mk_tersedia', '20000000')
        ->set('omzet_bulanan', '30000000')
        ->call('saveModalKerja')
        ->call('hitungKapasitas')
        ->call('generateSensitivitas')
        ->set('skor_condition', '4')
        ->call('hitungScoring')
        ->assertSet('status', 'Scoring 5C dihitung.');

    $neraca = AnalisisNeraca::query()->where('permohonan_id', $permohonan->id)->firstOrFail();

    expect((float) $neraca->selisih_cek)->toBe(100000.0)
        ->and(AnalisisKapasitasBayar::query()->where('permohonan_id', $permohonan->id)->exists())->toBeTrue()
        ->and(AnalisisRasio::query()->where('permohonan_id', $permohonan->id)->exists())->toBeTrue()
        ->and(AnalisisModalKerja::query()->where('permohonan_id', $permohonan->id)->exists())->toBeTrue()
        ->and(AnalisisSensitivitas::query()->where('permohonan_id', $permohonan->id)->count())->toBe(6)
        ->and(AnalisisScoring5c::query()->where('permohonan_id', $permohonan->id)->exists())->toBeTrue();
});

it('menolak workbench lintas cabang', function () {
    $permohonan = fixturePermohonan();
    $bomId = DB::table('cabang')->where('kode', 'BOM')->value('id');
    $aoBom = workflowUser('AO', null, $bomId);

    $this->actingAs($aoBom)
        ->get(route('permohonan.analisis', $permohonan))
        ->assertForbidden();
});
