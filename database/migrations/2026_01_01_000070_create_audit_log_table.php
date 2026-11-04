<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->nullable()->constrained('cabang')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('entity_type', 50);
            $table->uuid('entity_id')->nullable();
            $table->string('aksi', 50);
            $table->jsonb('old_value')->nullable();
            $table->jsonb('new_value')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestampTz('timestamp')->useCurrent();

            $table->index(['entity_type', 'entity_id', 'timestamp']);
            $table->index(['user_id', 'timestamp']);
            $table->index(['cabang_id', 'timestamp']);
        });

        DB::statement('CREATE INDEX audit_log_old_value_gin ON audit_log USING GIN (old_value)');
        DB::statement('CREATE INDEX audit_log_new_value_gin ON audit_log USING GIN (new_value)');

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION audit_log_immutable() RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'audit_log immutable (BR-09)';
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        DB::statement('CREATE TRIGGER audit_log_immutable_trigger BEFORE UPDATE OR DELETE ON audit_log FOR EACH ROW EXECUTE FUNCTION audit_log_immutable()');
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
        DB::statement('DROP FUNCTION IF EXISTS audit_log_immutable()');
    }
};
