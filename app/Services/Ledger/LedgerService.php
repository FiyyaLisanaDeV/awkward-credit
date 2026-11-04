<?php

declare(strict_types=1);

namespace App\Services\Ledger;

use App\Enums\LedgerEvent;
use App\Models\Ledger;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class LedgerService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function record(
        LedgerEvent $event,
        string $entityType,
        ?string $entityId = null,
        array $payload = [],
        ?Authenticatable $actor = null,
        ?string $correlationId = null,
        ?string $idempotencyKey = null,
        ?Carbon $occurredAt = null,
    ): Ledger {
        return DB::transaction(function () use ($event, $entityType, $entityId, $payload, $actor, $correlationId, $idempotencyKey, $occurredAt): Ledger {
            if ($idempotencyKey !== null) {
                $existing = Ledger::query()->where('idempotency_key', $idempotencyKey)->first();

                if ($existing instanceof Ledger) {
                    return $existing;
                }
            }

            DB::select('select pg_advisory_xact_lock(hashtext(?))', [(string) config('ledger.lock_key')]);

            $last = Ledger::query()->orderByDesc('seq')->first();
            $prevHash = $last instanceof Ledger ? $last->hash : ChainHasher::genesis();
            $occurred = ($occurredAt ?? Carbon::now())->copy()->startOfSecond();

            $hash = ChainHasher::hash(
                $prevHash,
                $payload,
                ChainHasher::time($occurred),
                $event->value,
                $entityType,
                $entityId,
            );

            return Ledger::query()->create([
                'occurred_at' => $occurred,
                'cabang_id' => $actor instanceof User ? $actor->cabang_id : null,
                'actor_id' => $actor?->getAuthIdentifier(),
                'event' => $event->value,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'correlation_id' => $correlationId ?? (string) Str::uuid(),
                'idempotency_key' => $idempotencyKey,
                'payload' => $payload,
                'prev_hash' => $prevHash,
                'hash' => $hash,
                'ip_address' => app()->runningInConsole() ? null : request()->ip(),
                'user_agent' => app()->runningInConsole() ? null : request()->userAgent(),
            ]);
        });
    }
}
