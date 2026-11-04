<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_scoring_5c', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->unique()->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('parameter_kebijakan_id')->constrained('ref_parameter_kebijakan')->restrictOnDelete();

            $table->decimal('skor_character', 3, 2)->nullable();
            $table->decimal('skor_capacity', 3, 2)->nullable();
            $table->decimal('skor_capital', 3, 2)->nullable();
            $table->decimal('skor_collateral', 3, 2)->nullable();
            $table->decimal('skor_condition', 3, 2)->nullable();

            $table->decimal('bobot_character', 5, 4)->nullable();
            $table->decimal('bobot_capacity', 5, 4)->nullable();
            $table->decimal('bobot_capital', 5, 4)->nullable();
            $table->decimal('bobot_collateral', 5, 4)->nullable();
            $table->decimal('bobot_condition', 5, 4)->nullable();

            $table->decimal('skor_tertimbang', 5, 4)->nullable();
            $table->decimal('skor_total', 5, 4)->nullable();
            $table->string('rating', 50)->nullable();
            $table->boolean('overridden')->default(false);
            $table->text('override_reason')->nullable();

            $table->text('justifikasi_character')->nullable();
            $table->text('justifikasi_capacity')->nullable();
            $table->text('justifikasi_capital')->nullable();
            $table->text('justifikasi_collateral')->nullable();
            $table->text('justifikasi_condition')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_scoring_5c');
    }
};
