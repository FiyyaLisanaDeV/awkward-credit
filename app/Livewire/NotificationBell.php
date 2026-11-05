<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\Notifikasi\NotifikasiService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class NotificationBell extends Component
{
    public bool $terbuka = false;

    public function toggle(): void
    {
        $this->terbuka = ! $this->terbuka;
    }

    public function tandaiSemua(NotifikasiService $service): void
    {
        $user = auth()->user();

        if ($user !== null) {
            $service->markAllRead($user);
        }
    }

    public function render(NotifikasiService $service): View
    {
        $user = auth()->user();

        return view('components.notification-bell', [
            'jumlah' => $user === null ? 0 : $service->unreadCount($user),
            'daftar' => $user === null ? collect() : $service->terbaru($user, (int) config('loan.ui.notifikasi_limit')),
        ]);
    }
}
