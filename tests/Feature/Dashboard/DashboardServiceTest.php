<?php

declare(strict_types=1);

use App\Enums\StatusPermohonan;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('menghitung KPI per cabang dan menyimpan cache', function () {
    Cache::flush();

    $admin = User::query()->where('username', 'admin')->firstOrFail();

    fixturePermohonan(['status' => StatusPermohonan::SUBMITTED->value]);
    fixturePermohonan(['status' => StatusPermohonan::DRAFT->value]);

    $ringkasan = app(DashboardService::class)->ringkasan($admin);

    expect($ringkasan['pipeline']['submitted'])->toBe(1)
        ->and($ringkasan['draft'])->toBe(1)
        ->and($ringkasan)->toHaveKeys(['per_status', 'kredit_aktif', 'red_flag_kritis', 'kelengkapan_rata', 'recent'])
        ->and(Cache::has('dashboard:'.$admin->cabang_id))->toBeTrue();
});
