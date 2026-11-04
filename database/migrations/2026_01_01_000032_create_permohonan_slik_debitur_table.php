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
        Schema::create('permohonan_slik_debitur', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('slik_permintaan_id')->nullable()->constrained('slik_permintaan')->cascadeOnDelete();
            $table->smallInteger('urutan');

            $table->string('nama_lembaga', 100);
            $table->string('jenis_fasilitas', 30);
            $table->string('jenis_penggunaan', 50)->nullable();
            $table->string('no_fasilitas', 50)->nullable();

            $table->decimal('plafon', 15, 2);
            $table->decimal('baki_debet', 15, 2);
            $table->decimal('suku_bunga', 5, 4)->nullable();
            $table->smallInteger('jangka_waktu_bulan')->nullable();
            $table->decimal('angsuran_bulanan', 15, 2)->nullable();
            $table->foreignUuid('kolektibilitas_id')->constrained('ref_kolektibilitas')->restrictOnDelete();
            $table->string('kondisi', 20)->nullable();

            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_tunggakan')->nullable();
            $table->smallInteger('hari_tunggakan')->nullable();
            $table->smallInteger('frekuensi_tunggakan_12m')->nullable();
            $table->decimal('tunggakan_pokok', 15, 2)->default(0);
            $table->decimal('tunggakan_bunga', 15, 2)->default(0);

            $table->string('jenis_jaminan', 50)->nullable();
            $table->decimal('nilai_jaminan', 15, 2)->nullable();
            $table->string('penjamin', 100)->nullable();
            $table->date('tanggal_data')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('permohonan_id');
        });

        DB::statement('ALTER TABLE permohonan_slik_debitur ADD CONSTRAINT permohonan_slik_debitur_nilai_check CHECK (urutan >= 1 AND plafon >= 0 AND baki_debet >= 0 AND tunggakan_pokok >= 0 AND tunggakan_bunga >= 0)');
        DB::statement('ALTER TABLE permohonan_slik_debitur ADD CONSTRAINT permohonan_slik_debitur_rate_check CHECK (suku_bunga IS NULL OR (suku_bunga >= 0 AND suku_bunga <= 1))');
        DB::statement('CREATE UNIQUE INDEX permohonan_slik_debitur_urutan_unique ON permohonan_slik_debitur (permohonan_id, urutan) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_slik_debitur');
    }
};
