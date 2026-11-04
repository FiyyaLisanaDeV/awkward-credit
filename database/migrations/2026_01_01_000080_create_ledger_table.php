<?php

declare(strict_types=1);

use App\Enums\LedgerEvent;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger', function (Blueprint $table): void {
            $table->bigIncrements('seq');
            $table->uuid('id')->primary();
            $table->timestampTz('occurred_at');
            $table->timestampTz('dibuat_pada')->useCurrent();

            $table->foreignUuid('cabang_id')->nullable()->constrained('cabang')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('event', 40);
            $table->string('entity_type', 50);
            $table->uuid('entity_id')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->string('idempotency_key', 100)->nullable()->unique();

            $table->jsonb('payload');
            $table->char('prev_hash', 64);
            $table->char('hash', 64);

            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->index(['entity_type', 'entity_id', 'occurred_at']);
            $table->index(['event', 'occurred_at']);
            $table->index(['cabang_id', 'occurred_at']);
            $table->index('correlation_id');
        });

        CheckConstraint::enum('ledger', 'event', LedgerEvent::class);

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION ledger_immutable() RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'ledger immutable (BR-09)';
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        DB::statement('CREATE TRIGGER ledger_immutable_trigger BEFORE UPDATE OR DELETE ON ledger FOR EACH ROW EXECUTE FUNCTION ledger_immutable()');
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger');
        DB::statement('DROP FUNCTION IF EXISTS ledger_immutable()');
    }
};
