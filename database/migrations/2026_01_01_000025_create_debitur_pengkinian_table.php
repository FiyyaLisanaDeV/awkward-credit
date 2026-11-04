<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debitur_pengkinian', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('debitur_id')->constrained('debitur')->cascadeOnDelete();

            $table->string('cif', 20)->nullable();
            $table->string('tujuan_penggunaan', 100);
            $table->string('sumber_penghasilan', 100);
            $table->decimal('pemasukan_bulanan', 15, 2);
            $table->smallInteger('frekuensi_setoran');
            $table->decimal('pengeluaran_bulanan', 15, 2);
            $table->smallInteger('frekuensi_tarikan');
            $table->date('tanggal_pengkinian');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index('debitur_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debitur_pengkinian');
    }
};
