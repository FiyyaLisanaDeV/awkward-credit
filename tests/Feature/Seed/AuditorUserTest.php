<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('membuat user auditor dengan role AUDITOR yang read-only', function () {
    $this->seed();

    $auditor = User::query()->where('username', 'auditor')->first();

    expect($auditor)->not->toBeNull()
        ->and($auditor->hasRole('AUDITOR'))->toBeTrue()
        ->and($auditor->can('audit.view'))->toBeTrue()
        ->and($auditor->can('ledger.view'))->toBeTrue()
        ->and($auditor->can('report.view'))->toBeTrue()
        ->and($auditor->can('permohonan.view'))->toBeTrue()
        ->and($auditor->can('permohonan.approve_level_2'))->toBeFalse()
        ->and($auditor->can('permohonan.create'))->toBeFalse()
        ->and($auditor->must_change_password)->toBeTrue();
});

it('idempoten saat seeder auditor dijalankan dua kali', function () {
    $this->seed();
    $this->seed();

    expect(User::query()->where('username', 'auditor')->count())->toBe(1);
});
