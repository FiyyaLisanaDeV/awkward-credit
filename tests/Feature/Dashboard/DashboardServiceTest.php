<?php

declare(strict_types=1);

use App\Enums\StatusPermohonan;
use App\Models\PermohonanKredit;
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

it('tidak menyimpan model Eloquent di cache dashboard', function () {
    Cache::flush();

    $admin = User::query()->where('username', 'admin')->firstOrFail();
    fixturePermohonan(['status' => StatusPermohonan::SUBMITTED->value]);

    $service = app(DashboardService::class);
    $service->ringkasan($admin);
    $kedua = $service->ringkasan($admin);

    expect($kedua['recent']->first())->toBeInstanceOf(PermohonanKredit::class)
        ->and(Cache::get('dashboard:'.$admin->cabang_id))->not->toHaveKey('recent');
});
