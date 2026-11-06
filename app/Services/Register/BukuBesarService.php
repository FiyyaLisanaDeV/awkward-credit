<?php

declare(strict_types=1);

namespace App\Services\Register;

use App\Models\BukuBesarKredit;
use App\Models\Kredit;
use App\Models\PermohonanKredit;
use App\Models\User;
use App\Services\Ledger\ChainHasher;

final class BukuBesarService
{
    /**
     * @param  array<string, mixed>  $snapshot
     */
    public function catat(
        string $tahap,
        PermohonanKredit $permohonan,
        User $aktor,
        string $ringkasan,
        array $snapshot = [],
        ?Kredit $kredit = null,
    ): BukuBesarKredit {
        $occurred = now();
        $prev = (string) (BukuBesarKredit::query()->orderByDesc('seq')->value('hash') ?? ChainHasher::genesis());

        $payload = [
            'tahap' => $tahap,
            'ringkasan' => $ringkasan,
            'snapshot' => $snapshot,
        ];

        $hash = ChainHasher::hash(
            $prev,
            $payload,
            ChainHasher::time($occurred),
            $tahap,
            'buku_besar_kredit',
            (string) $permohonan->id,
        );

        return BukuBesarKredit::query()->create([
            'permohonan_id' => $permohonan->id,
            'kredit_id' => $kredit?->id,
            'cabang_id' => $permohonan->cabang_id,
            'tahap' => $tahap,
            'aktor_id' => $aktor->id,
            'aktor_nama' => $aktor->name,
            'aktor_role' => $aktor->getRoleNames()->implode(','),
            'occurred_at' => $occurred,
            'ringkasan' => $ringkasan,
            'snapshot' => $snapshot,
            'prev_hash' => $prev,
            'hash' => $hash,
        ]);
    }
}
