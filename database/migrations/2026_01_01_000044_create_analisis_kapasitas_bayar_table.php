<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_kapasitas_bayar', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->unique()->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('parameter_kebijakan_id')->constrained('ref_parameter_kebijakan')->restrictOnDelete();

            $table->decimal('rpc_bulanan', 15, 2)->default(0);
            $table->decimal('dscr_diajukan', 10, 4)->nullable();
            $table->string('status_dscr_diajukan', 20)->nullable();
            $table->decimal('surplus_setelah_angsuran_diajukan', 15, 2)->default(0);

            $table->decimal('dscr_rekomendasi', 10, 4)->nullable();
            $table->string('status_dscr_rekomendasi', 20)->nullable();
            $table->decimal('surplus_setelah_angsuran_rekomendasi', 15, 2)->default(0);

            $table->decimal('plafon_max_flat', 15, 2)->default(0);
            $table->decimal('plafon_max_anuitas', 15, 2)->default(0);
            $table->decimal('plafon_max_grace', 15, 2)->default(0);

            $table->decimal('suku_bunga_efektif_diajukan', 10, 4)->nullable();
            $table->decimal('suku_bunga_efektif_rekomendasi', 10, 4)->nullable();
            $table->decimal('ltv', 10, 4)->nullable();
            $table->string('status_kewajaran_plafon', 30)->nullable();

            $table->decimal('total_pembayaran_jatuh_tempo', 15, 2)->default(0);
            $table->decimal('rasio_kecukupan_balloon', 10, 4)->nullable();
            $table->string('status_kecukupan_balloon', 50)->nullable();

            $table->decimal('idir', 10, 4)->nullable();
            $table->string('status_idir', 20)->nullable();
            $table->decimal('biaya_lain_bulanan', 15, 2)->default(0);
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_kapasitas_bayar');
    }
};
