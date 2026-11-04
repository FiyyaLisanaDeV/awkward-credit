<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_modal_kerja', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->unique()->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('parameter_kebijakan_id')->constrained('ref_parameter_kebijakan')->restrictOnDelete();

            $table->decimal('periode_perputaran_hari', 10, 2)->default(0);
            $table->decimal('biaya_operasional_tahunan', 15, 2)->default(0);
            $table->decimal('perputaran_kas_per_tahun', 10, 4)->default(0);
            $table->decimal('kebutuhan_mk_cash_turnover', 15, 2)->default(0);
            $table->decimal('pembelian_bulanan', 15, 2)->default(0);
            $table->decimal('hari_persediaan', 10, 2)->default(0);
            $table->decimal('kebutuhan_mk_wcd', 15, 2)->default(0);
            $table->decimal('mk_tersedia', 15, 2)->default(0);
            $table->decimal('rata_rata_kebutuhan_mk', 15, 2)->default(0);
            $table->decimal('gap_mk', 15, 2)->default(0);
            $table->decimal('omzet_bulanan', 15, 2)->default(0);
            $table->decimal('kebutuhan_mk_persen_omzet', 10, 4)->default(0);
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_modal_kerja');
    }
};
