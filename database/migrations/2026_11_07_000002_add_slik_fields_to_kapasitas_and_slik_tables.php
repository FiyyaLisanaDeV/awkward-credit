<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisis_kapasitas_bayar', function (Blueprint $table): void {
            $table->decimal('angsuran_slik_debitur', 15, 2)->default(0);
            $table->decimal('angsuran_slik_pasangan', 15, 2)->default(0);
            $table->decimal('total_angsuran_slik', 15, 2)->default(0);
            $table->integer('jumlah_fasilitas_slik_aktif')->default(0);
        });

        Schema::table('permohonan_slik_debitur', function (Blueprint $table): void {
            $table->boolean('is_angsuran_estimated')->default(false);
        });

        Schema::table('permohonan_slik_pasangan', function (Blueprint $table): void {
            $table->decimal('angsuran_bulanan', 15, 2)->nullable();
            $table->decimal('suku_bunga', 5, 4)->nullable();
            $table->smallInteger('jangka_waktu_bulan')->nullable();
            $table->boolean('is_angsuran_estimated')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('permohonan_slik_pasangan', function (Blueprint $table): void {
            $table->dropColumn(['angsuran_bulanan', 'suku_bunga', 'jangka_waktu_bulan', 'is_angsuran_estimated']);
        });

        Schema::table('permohonan_slik_debitur', function (Blueprint $table): void {
            $table->dropColumn('is_angsuran_estimated');
        });

        Schema::table('analisis_kapasitas_bayar', function (Blueprint $table): void {
            $table->dropColumn(['angsuran_slik_debitur', 'angsuran_slik_pasangan', 'total_angsuran_slik', 'jumlah_fasilitas_slik_aktif']);
        });
    }
};
