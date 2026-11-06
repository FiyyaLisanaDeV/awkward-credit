<?php

declare(strict_types=1);

use App\Livewire\Analisis\Workbench as AnalisisWorkbench;
use App\Models\AnalisisKapasitasBayar;
use App\Models\AnalisisNeraca;
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
        ->call('saveNeraca')
        ->assertSet('status', 'Neraca disimpan.')
        ->set('total_pendapatan', '10000000')
        ->set('total_pengeluaran', '5000000')
        ->call('saveLabaRugi')
        ->call('hitungKapasitas')
        ->assertSet('status', 'Kapasitas dihitung.');

    $neraca = AnalisisNeraca::query()->where('permohonan_id', $permohonan->id)->firstOrFail();

    expect((float) $neraca->selisih_cek)->toBe(100000.0)
        ->and(AnalisisKapasitasBayar::query()->where('permohonan_id', $permohonan->id)->exists())->toBeTrue();
});

it('menolak workbench lintas cabang', function () {
    $permohonan = fixturePermohonan();
    $bomId = DB::table('cabang')->where('kode', 'BOM')->value('id');
    $aoBom = workflowUser('AO', null, $bomId);

    $this->actingAs($aoBom)
        ->get(route('permohonan.analisis', $permohonan))
        ->assertForbidden();
});
