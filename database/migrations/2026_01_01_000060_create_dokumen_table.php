<?php

declare(strict_types=1);

use App\Enums\JenisDokumen;
use App\Enums\StatusTtd;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang')->restrictOnDelete();
            $table->foreignUuid('permohonan_id')->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('template_id')->nullable()->constrained('ref_template_dokumen')->nullOnDelete();

            $table->string('jenis_dokumen', 20);
            $table->string('nomor_dokumen', 50)->nullable();
            $table->date('tanggal_dokumen')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->char('file_hash', 64)->nullable();
            $table->string('status_ttd', 20)->default(StatusTtd::DRAFT->value);
            $table->date('tanggal_ttd')->nullable();
            $table->integer('ukuran_bytes')->nullable();
            $table->string('sumber', 20)->default('GENERATE');
            $table->text('catatan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['jenis_dokumen', 'status_ttd']);
        });

        CheckConstraint::enum('dokumen', 'jenis_dokumen', JenisDokumen::class);
        CheckConstraint::enum('dokumen', 'status_ttd', StatusTtd::class);

        DB::statement('ALTER TABLE dokumen ADD CONSTRAINT dokumen_ukuran_check CHECK (ukuran_bytes IS NULL OR ukuran_bytes >= 0)');
        DB::statement('CREATE UNIQUE INDEX dokumen_permohonan_jenis_hash_unique ON dokumen (permohonan_id, jenis_dokumen, file_hash) WHERE file_hash IS NOT NULL AND deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen');
    }
};
