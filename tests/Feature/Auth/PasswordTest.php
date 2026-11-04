<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('menampilkan formulir ganti kata sandi', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/password')
        ->assertOk()
        ->assertSee(__('auth.change_password_title'));
});

it('mengganti kata sandi dan menurunkan flag wajib ganti', function () {
    $user = User::factory()->create([
        'password' => Hash::make('lama12345'),
        'must_change_password' => true,
    ]);

    $this->actingAs($user)
        ->post('/password', [
            'current_password' => 'lama12345',
            'password' => 'baru12345',
            'password_confirmation' => 'baru12345',
        ])
        ->assertRedirect(route('dashboard'));

    $user->refresh();

    expect(Hash::check('baru12345', $user->password))->toBeTrue()
        ->and($user->must_change_password)->toBeFalse();
});

it('menolak kata sandi lama yang salah', function () {
    $user = User::factory()->create(['password' => Hash::make('lama12345')]);

    $this->actingAs($user)
        ->post('/password', [
            'current_password' => 'salah',
            'password' => 'baru12345',
            'password_confirmation' => 'baru12345',
        ])
        ->assertSessionHasErrors('current_password');
});
