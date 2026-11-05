<?php

declare(strict_types=1);

use App\Services\Notifikasi\NotifikasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('mencatat kirim, unread count, dan menandai dibaca', function () {
    $user = workflowUser('AO');
    $service = app(NotifikasiService::class);

    $a = $service->kirim($user->id, 'APPROVAL', 'Judul A', 'Pesan A', 'permohonan_kredit', null);
    $service->kirim($user->id, 'APPROVAL', 'Judul B', 'Pesan B', 'permohonan_kredit', null);

    expect($service->unreadCount($user))->toBe(2)
        ->and($service->terbaru($user))->toHaveCount(2);

    $service->markRead($a, $user);

    expect($service->unreadCount($user))->toBe(1);

    $service->markAllRead($user);

    expect($service->unreadCount($user))->toBe(0);
});

it('menolak menandai notifikasi milik user lain', function () {
    $owner = workflowUser('AO');
    $other = workflowUser('AO');
    $service = app(NotifikasiService::class);
    $notifikasi = $service->kirim($owner->id, 'APPROVAL', 'Judul', 'Pesan');

    assertHttpStatus(fn () => $service->markRead($notifikasi, $other), 403);
});
