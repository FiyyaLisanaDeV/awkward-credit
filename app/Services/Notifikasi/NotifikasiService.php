<?php

declare(strict_types=1);

namespace App\Services\Notifikasi;

use App\Models\Notifikasi;
use App\Models\User;
use App\Support\Konstanta;
use Illuminate\Database\Eloquent\Collection;

final class NotifikasiService
{
    public function kirim(
        int $userId,
        string $tipe,
        string $judul,
        string $pesan,
        ?string $entityType = null,
        ?string $entityId = null,
        ?string $cabangId = null,
    ): Notifikasi {
        return Notifikasi::query()->create([
            'cabang_id' => $cabangId,
            'user_id' => $userId,
            'tipe' => $tipe,
            'judul' => $judul,
            'pesan' => $pesan,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }

    public function unreadCount(User $user): int
    {
        return Notifikasi::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    /** @return Collection<int, Notifikasi> */
    public function terbaru(User $user, int $limit = Konstanta::NOTIFIKASI_TERBARU): Collection
    {
        return Notifikasi::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function markRead(Notifikasi $notifikasi, User $user): void
    {
        abort_unless((int) $notifikasi->user_id === (int) $user->id, 403, 'Notifikasi bukan milik Anda.');

        if (! $notifikasi->is_read) {
            $notifikasi->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function markAllRead(User $user): int
    {
        return Notifikasi::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }
}
