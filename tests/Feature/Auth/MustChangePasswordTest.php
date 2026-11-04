<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('memaksa pengguna mengganti kata sandi', function () {
    $user = User::factory()->create(['must_change_password' => true]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('password.change'));
});

it('tidak memaksa bila flag tidak aktif', function () {
    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});
