<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('tidak memiliki nama route duplikat', function () {
    $names = collect(Route::getRoutes()->getRoutes())
        ->map(static fn ($route): ?string => $route->getName())
        ->filter()
        ->values();

    expect($names->count())->toBe($names->unique()->count());
});

it('semua route api v1 memakai auth sanctum kecuali login', function () {
    $tanpaAuth = [];

    foreach (Route::getRoutes()->getRoutes() as $route) {
        $uri = $route->uri();

        if (! str_starts_with($uri, 'api/v1/') || $uri === 'api/v1/login') {
            continue;
        }

        $middleware = implode('|', $route->gatherMiddleware());

        if (! str_contains($middleware, 'sanctum')) {
            $tanpaAuth[] = $uri;
        }
    }

    expect($tanpaAuth)->toBe([]);
});

it('route utama tersedia', function () {
    foreach (['dashboard', 'approval.inbox', 'kredit.index', 'debitur.index', 'permohonan.index', 'simulasi.index'] as $name) {
        expect(Route::has($name))->toBeTrue("route {$name} tidak tersedia");
    }
});
