<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_7aspek', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->unique()->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('parameter_kebijakan_id')->constrained('ref_parameter_kebijakan')->restrictOnDelete();

            $table->text('aspek_manfaat')->nullable();
            $table->text('aspek_teknis')->nullable();
            $table->text('aspek_manajemen')->nullable();
            $table->text('aspek_pemasaran')->nullable();
            $table->text('aspek_keuangan')->nullable();
            $table->text('aspek_jaminan')->nullable();
            $table->text('kesimpulan')->nullable();
            $table->text('usul')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_7aspek');
    }
};
