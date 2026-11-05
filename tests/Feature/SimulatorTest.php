<?php

declare(strict_types=1);

use App\Livewire\Simulator;
use App\Models\SimulasiKredit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    $admin = User::query()->where('username', 'admin')->firstOrFail();
    $admin->forceFill(['must_change_password' => false])->save();
    $this->actingAs($admin);
});

it('menghitung simulasi tanpa menyimpan', function () {
    $component = Livewire::test(Simulator::class)
        ->set('label', 'Uji Hitung')
        ->set('plafon', '80000000')
        ->set('rate', '0.18')
        ->set('tenor', '60')
        ->set('surplus_bulanan', '5000000')
        ->call('hitung')
        ->assertHasNoErrors();

    expect($component->get('hasil'))->toHaveKey('angsuran')
        ->and($component->get('jadwal'))->toHaveCount(60);

    expect(SimulasiKredit::query()->count())->toBe(0);
});

it('menyimpan simulasi dan mencatat ledger', function () {
    Livewire::test(Simulator::class)
        ->set('label', 'Uji Simpan')
        ->set('plafon', '80000000')
        ->set('rate', '0.18')
        ->set('tenor', '60')
        ->set('surplus_bulanan', '5000000')
        ->call('simpan')
        ->assertRedirect();

    $simulasi = SimulasiKredit::query()->firstOrFail();

    expect($simulasi->label)->toBe('Uji Simpan')
        ->and($simulasi->ledger_id)->not->toBeNull()
        ->and((float) $simulasi->angsuran)->toBeGreaterThan(0.0);
});

it('menolak input tidak valid', function () {
    Livewire::test(Simulator::class)
        ->set('label', '')
        ->set('plafon', '0')
        ->set('rate', '2')
        ->set('tenor', '0')
        ->call('hitung')
        ->assertHasErrors(['label', 'plafon', 'rate', 'tenor']);
});
