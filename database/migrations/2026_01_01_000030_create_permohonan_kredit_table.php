<?php

declare(strict_types=1);

use App\Enums\SistemSukuBunga;
use App\Enums\StatusPermohonan;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_kredit', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang')->restrictOnDelete();
            $table->string('no_permohonan', 50)->unique();
            $table->foreignUuid('debitur_id')->constrained('debitur')->restrictOnDelete();
            $table->foreignUuid('produk_id')->constrained('produk')->restrictOnDelete();

            $table->string('sektor', 100)->nullable();
            $table->string('tujuan_penggunaan', 200)->nullable();
            $table->decimal('plafon_diajukan', 15, 2);
            $table->decimal('plafon_rekomendasi', 15, 2)->nullable();
            $table->decimal('suku_bunga_tahunan', 5, 4);
            $table->smallInteger('tenor_diajukan');
            $table->smallInteger('tenor_rekomendasi')->nullable();
            $table->string('sistem_suku_bunga', 20);
            $table->text('kegunaan_pinjaman');
            $table->smallInteger('pinjaman_ke')->default(1);

            $table->text('sumber_pelunasan_balloon')->nullable();
            $table->decimal('perkiraan_penerimaan_balloon', 15, 2)->nullable();
            $table->string('sumber_pembayaran_angsuran', 100)->nullable();

            $table->date('tanggal_target_realisasi')->nullable();
            $table->string('prioritas', 10)->default('NORMAL');
            $table->string('status', 30)->default(StatusPermohonan::DRAFT->value);

            $table->foreignId('ao_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('parameter_kebijakan_id')->constrained('ref_parameter_kebijakan')->restrictOnDelete();

            $table->date('tanggal_permohonan');
            $table->text('catatan_ao')->nullable();
            $table->decimal('kelengkapan_persen', 5, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('debitur_id');
            $table->index('ao_id');
        });

        CheckConstraint::enum('permohonan_kredit', 'status', StatusPermohonan::class);
        CheckConstraint::enum('permohonan_kredit', 'sistem_suku_bunga', SistemSukuBunga::class);

        $grace = SistemSukuBunga::GracePeriod->value;

        DB::statement(sprintf(
            "ALTER TABLE permohonan_kredit ADD CONSTRAINT permohonan_kredit_balloon_check CHECK ((sistem_suku_bunga = '%s' AND sumber_pelunasan_balloon IS NOT NULL AND perkiraan_penerimaan_balloon IS NOT NULL) OR sistem_suku_bunga <> '%s')",
            $grace,
            $grace
        ));

        DB::statement('ALTER TABLE permohonan_kredit ADD CONSTRAINT permohonan_kredit_plafon_check CHECK (plafon_diajukan > 0 AND (plafon_rekomendasi IS NULL OR plafon_rekomendasi <= plafon_diajukan))');
        DB::statement('ALTER TABLE permohonan_kredit ADD CONSTRAINT permohonan_kredit_rate_check CHECK (suku_bunga_tahunan > 0 AND suku_bunga_tahunan <= 0.5)');
        DB::statement('ALTER TABLE permohonan_kredit ADD CONSTRAINT permohonan_kredit_tenor_check CHECK (tenor_diajukan >= 1 AND tenor_diajukan <= 360 AND (tenor_rekomendasi IS NULL OR (tenor_rekomendasi >= 1 AND tenor_rekomendasi <= 360)))');
        DB::statement('ALTER TABLE permohonan_kredit ADD CONSTRAINT permohonan_kredit_pinjaman_check CHECK (pinjaman_ke >= 1)');
        DB::statement('ALTER TABLE permohonan_kredit ADD CONSTRAINT permohonan_kredit_kelengkapan_check CHECK (kelengkapan_persen >= 0 AND kelengkapan_persen <= 100)');

        DB::statement('CREATE INDEX permohonan_cabang_status_idx ON permohonan_kredit (cabang_id, status, tanggal_permohonan DESC) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_kredit');
    }
};
