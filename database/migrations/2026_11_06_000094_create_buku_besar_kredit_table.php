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
        Schema::create('buku_besar_kredit', function (Blueprint $table): void {
            $table->bigIncrements('seq');
            $table->uuid('id')->unique();

            $table->uuid('permohonan_id')->nullable()->index();
            $table->uuid('kredit_id')->nullable()->index();
            $table->uuid('cabang_id')->nullable()->index();

            $table->string('tahap', 30)->index();
            $table->bigInteger('aktor_id')->nullable();
            $table->string('aktor_nama', 200)->nullable();
            $table->string('aktor_role', 100)->nullable();
            $table->timestampTz('occurred_at')->index();
            $table->text('ringkasan')->nullable();
            $table->jsonb('snapshot')->nullable();

            $table->char('prev_hash', 64);
            $table->char('hash', 64);
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['permohonan_id', 'occurred_at']);
            $table->index(['kredit_id', 'occurred_at']);
            $table->index(['cabang_id', 'tahap']);
        });

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION buku_besar_kredit_immutable() RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'buku_besar_kredit immutable';
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        DB::statement('CREATE TRIGGER buku_besar_kredit_immutable_trigger BEFORE UPDATE OR DELETE ON buku_besar_kredit FOR EACH ROW EXECUTE FUNCTION buku_besar_kredit_immutable()');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS buku_besar_kredit_immutable_trigger ON buku_besar_kredit');
        DB::statement('DROP FUNCTION IF EXISTS buku_besar_kredit_immutable()');
        Schema::dropIfExists('buku_besar_kredit');
    }
};
