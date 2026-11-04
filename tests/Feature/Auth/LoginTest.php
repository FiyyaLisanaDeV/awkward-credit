<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('menampilkan halaman login', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee(__('auth.login_title'));
});

it('menolak tamu mengakses dasbor', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('masuk dengan kredensial benar', function () {
    $user = User::factory()->create(['password' => Hash::make('rahasia123')]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'rahasia123',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->last_login_at)->not->toBeNull();
});

it('menolak kata sandi yang salah', function () {
    $user = User::factory()->create(['password' => Hash::make('rahasia123')]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'salah',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('menolak akun yang tidak aktif', function () {
    $user = User::factory()->create([
        'password' => Hash::make('rahasia123'),
        'is_active' => false,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'rahasia123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('membatasi percobaan login berulang', function () {
    $user = User::factory()->create(['password' => Hash::make('rahasia123')]);

    $max = (int) config('loan.security.login_max_attempts');

    for ($attempt = 0; $attempt < $max; $attempt++) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'salah',
        ]);
    }

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'salah',
    ])->assertStatus(429);
});

it('keluar dari sesi', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));

    $this->assertGuest();
});
