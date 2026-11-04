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
        Schema::create('jadwal_angsuran', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignUuid('kredit_id')->constrained('kredit')->cascadeOnDelete();
            $table->smallInteger('periode');
            $table->date('tanggal_jatuh_tempo');

            $table->decimal('saldo_awal', 15, 2);
            $table->decimal('angsuran_pokok', 15, 2);
            $table->decimal('bunga', 15, 2);
            $table->decimal('total_angsuran', 15, 2);
            $table->decimal('saldo_akhir', 15, 2);

            $table->string('status', 20)->default('BELUM_BAYAR');
            $table->date('tanggal_bayar')->nullable();
            $table->decimal('nominal_dibayar', 15, 2)->default(0);
            $table->decimal('denda', 15, 2)->default(0);

            $table->timestamps();

            $table->unique(['kredit_id', 'periode']);
            $table->index(['kredit_id', 'tanggal_jatuh_tempo']);
        });

        DB::statement('ALTER TABLE jadwal_angsuran ADD CONSTRAINT jadwal_angsuran_nilai_check CHECK (periode >= 1 AND saldo_awal >= 0 AND angsuran_pokok >= 0 AND bunga >= 0 AND total_angsuran >= 0 AND saldo_akhir >= 0 AND nominal_dibayar >= 0 AND denda >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_angsuran');
    }
};
