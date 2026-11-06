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
        Schema::create('buku_agunan', function (Blueprint $table): void {
            $table->bigIncrements('seq');
            $table->uuid('id')->unique();

            $table->char('agunan_key', 64)->index();

            $table->uuid('debitur_id')->nullable()->index();
            $table->uuid('debitur_aset_id')->nullable()->index();
            $table->uuid('permohonan_id')->nullable()->index();
            $table->uuid('kredit_id')->nullable()->index();
            $table->uuid('cabang_id')->nullable()->index();

            $table->string('jenis_aset', 100);
            $table->string('no_dokumen', 50)->nullable();
            $table->string('nama_pemilik', 100)->nullable();
            $table->decimal('nilai_taksasi', 15, 2)->nullable();
            $table->decimal('persen_diperhitungkan', 5, 4)->nullable();
            $table->decimal('nilai_diperhitungkan', 15, 2)->nullable();

            $table->string('peristiwa', 20)->default('DIPAKAI')->index();
            $table->bigInteger('aktor_id')->nullable();
            $table->string('aktor_nama', 200)->nullable();
            $table->timestampTz('occurred_at')->index();
            $table->jsonb('snapshot')->nullable();

            $table->char('prev_hash', 64);
            $table->char('hash', 64);
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['agunan_key', 'occurred_at']);
            $table->index(['cabang_id', 'peristiwa']);
        });

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION buku_agunan_immutable() RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'buku_agunan immutable';
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        DB::statement('CREATE TRIGGER buku_agunan_immutable_trigger BEFORE UPDATE OR DELETE ON buku_agunan FOR EACH ROW EXECUTE FUNCTION buku_agunan_immutable()');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS buku_agunan_immutable_trigger ON buku_agunan');
        DB::statement('DROP FUNCTION IF EXISTS buku_agunan_immutable()');
        Schema::dropIfExists('buku_agunan');
    }
};
