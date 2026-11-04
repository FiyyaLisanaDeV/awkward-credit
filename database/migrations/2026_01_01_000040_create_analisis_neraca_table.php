<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_neraca', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->unique()->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('parameter_kebijakan_id')->constrained('ref_parameter_kebijakan')->restrictOnDelete();

            $columns = [
                'kas_inventaris', 'bank_simpanan', 'piutang', 'persediaan',
                'sewa_dibayar_dimuka', 'biaya_dibayar_dimuka', 'pajak_dibayar_dimuka',
                'investasi_jangka_panjang', 'tanah_bangunan', 'kebun', 'kendaraan', 'aset_lain',
                'jumlah_aktiva_lancar', 'jumlah_aktiva_tetap', 'total_aktiva',
                'hutang_pendek', 'hutang_pajak', 'hutang_gaji', 'hutang_panjang',
                'pinjaman_bank', 'pinjaman_lain', 'prive',
                'jumlah_hutang', 'rugi_laba_ditahan', 'ekuitas_modal_disetor',
                'jumlah_modal', 'total_pasiva', 'selisih_cek',
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
        Schema::dropIfExists('analisis_neraca');
    }
};
