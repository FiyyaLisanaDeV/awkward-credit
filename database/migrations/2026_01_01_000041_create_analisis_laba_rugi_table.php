<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_laba_rugi', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->unique()->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('parameter_kebijakan_id')->constrained('ref_parameter_kebijakan')->restrictOnDelete();

            $table->smallInteger('jumlah_bulan_berjalan')->default(6);

            $columns = [
                'penghasilan_pemohon', 'penghasilan_pasangan_gaji',
                'penghasilan_lain_pemohon', 'penghasilan_pasangan_tpp',
                'hpp', 'biaya_listrik_air', 'biaya_transport', 'biaya_sewa', 'biaya_gaji',
                'biaya_pendidikan', 'biaya_kesehatan', 'biaya_konsumsi', 'biaya_cicilan_lain',
                'biaya_telepon', 'biaya_kebersihan', 'biaya_lain',
                'total_pendapatan', 'total_pengeluaran', 'laba_kotor', 'penyusutan', 'pajak',
                'laba_bersih', 'angsuran_kredit_berjalan', 'surplus_per_bulan', 'surplus_6_bulan',
            ];

            foreach ($columns as $column) {
                $table->decimal($column, 15, 2)->default(0);
            }

            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_laba_rugi');
    }
};
