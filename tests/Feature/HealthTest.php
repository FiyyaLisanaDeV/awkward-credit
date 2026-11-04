<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengembalikan status sehat', function () {
    $this->getJson('/health')
        ->assertOk()
        ->assertJsonPath('app', 'ok')
        ->assertJsonPath('db', 'ok')
        ->assertJsonPath('cache', 'ok')
        ->assertJsonPath('storage', 'ok')
        ->assertJsonPath('queue', (string) config('queue.default'));
});
