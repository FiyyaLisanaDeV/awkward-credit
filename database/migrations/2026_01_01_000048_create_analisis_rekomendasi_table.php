<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_rekomendasi', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->unique()->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('parameter_kebijakan_id')->constrained('ref_parameter_kebijakan')->restrictOnDelete();

            $table->string('rekomendasi_sistem', 100)->nullable();
            $table->text('narasi')->nullable();
            $table->decimal('plafon_final', 15, 2)->nullable();
            $table->smallInteger('tenor_final')->nullable();
            $table->decimal('rate_final', 5, 4)->nullable();
            $table->decimal('angsuran_final', 15, 2)->nullable();
            $table->text('syarat')->nullable();
            $table->text('covenant')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_rekomendasi');
    }
};
