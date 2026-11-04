<?php

declare(strict_types=1);

use App\Enums\JenisAsuransi;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asuransi_kredit', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('kredit_id')->constrained('kredit')->cascadeOnDelete();

            $table->string('jenis', 30);
            $table->string('perusahaan', 100);
            $table->string('no_polis', 50)->nullable();
            $table->decimal('premi', 15, 2);
            $table->decimal('manfaat', 15, 2)->nullable();
            $table->smallInteger('usia_peserta')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_berakhir')->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->timestamps();

            $table->index('kredit_id');
        });

        CheckConstraint::enum('asuransi_kredit', 'jenis', JenisAsuransi::class);

        DB::statement('ALTER TABLE asuransi_kredit ADD CONSTRAINT asuransi_kredit_nilai_check CHECK (premi >= 0 AND (manfaat IS NULL OR manfaat >= 0))');
    }

    public function down(): void
    {
        Schema::dropIfExists('asuransi_kredit');
    }
};
