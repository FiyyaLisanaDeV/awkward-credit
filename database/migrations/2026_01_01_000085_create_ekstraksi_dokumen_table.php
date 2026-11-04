<?php

declare(strict_types=1);

use App\Enums\JenisEkstraksi;
use App\Enums\StatusEkstraksi;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekstraksi_dokumen', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang')->restrictOnDelete();
            $table->foreignUuid('debitur_id')->nullable()->constrained('debitur')->nullOnDelete();
            $table->foreignUuid('permohonan_id')->nullable()->constrained('permohonan_kredit')->nullOnDelete();

            $table->string('jenis', 20);
            $table->string('file_path', 500);
            $table->char('file_hash', 64);
            $table->string('engine', 30);
            $table->string('engine_version', 30)->nullable();
            $table->text('raw_text')->nullable();
            $table->jsonb('fields');
            $table->jsonb('confidence')->nullable();
            $table->string('status', 20)->default(StatusEkstraksi::DRAFT->value);

            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('verified_at')->nullable();
            $table->timestamps();

            $table->index(['debitur_id', 'jenis']);
        });

        CheckConstraint::enum('ekstraksi_dokumen', 'jenis', JenisEkstraksi::class);
        CheckConstraint::enum('ekstraksi_dokumen', 'status', StatusEkstraksi::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('ekstraksi_dokumen');
    }
};
