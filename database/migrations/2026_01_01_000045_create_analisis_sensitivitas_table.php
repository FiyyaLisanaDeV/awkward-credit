<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_sensitivitas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('parameter_kebijakan_id')->constrained('ref_parameter_kebijakan')->restrictOnDelete();

            $table->smallInteger('urutan');
            $table->string('nama_skenario', 100);
            $table->decimal('delta_omzet', 5, 4)->default(0);
            $table->decimal('delta_biaya', 5, 4)->default(0);

            $table->decimal('total_pendapatan', 15, 2)->default(0);
            $table->decimal('total_pengeluaran_non_angsuran', 15, 2)->default(0);
            $table->decimal('surplus_baru', 15, 2)->default(0);
            $table->decimal('rpc_baru', 15, 2)->default(0);
            $table->decimal('dscr', 10, 4)->nullable();
            $table->boolean('status_layak')->nullable();

            $table->timestamps();

            $table->unique(['permohonan_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_sensitivitas');
    }
};
