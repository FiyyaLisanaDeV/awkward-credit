<?php

declare(strict_types=1);

use App\Enums\LevelRedFlag;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_red_flags', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('parameter_kebijakan_id')->constrained('ref_parameter_kebijakan')->restrictOnDelete();

            $table->string('kode', 50);
            $table->string('level', 20);
            $table->text('pesan');
            $table->string('sumber', 30)->default('SISTEM');
            $table->boolean('teratasi')->default(false);
            $table->text('mitigasi')->nullable();
            $table->string('pemilik_mitigasi', 100)->nullable();
            $table->date('tenggat')->nullable();

            $table->timestamps();

            $table->unique(['permohonan_id', 'kode']);
        });

        CheckConstraint::enum('analisis_red_flags', 'level', LevelRedFlag::class);

        DB::statement("CREATE INDEX analisis_red_flags_critical_idx ON analisis_red_flags (permohonan_id) WHERE level = 'CRITICAL'");
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_red_flags');
    }
};
