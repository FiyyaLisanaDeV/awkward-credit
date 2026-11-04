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
        Schema::create('kredit_agunan', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('kredit_id')->constrained('kredit')->cascadeOnDelete();
            $table->foreignUuid('debitur_aset_id')->constrained('debitur_aset')->restrictOnDelete();

            $table->decimal('nilai_jaminan', 15, 2);
            $table->string('pengikatan', 50)->nullable();
            $table->string('no_pengikatan', 50)->nullable();
            $table->date('tanggal_ikatan')->nullable();
            $table->string('status', 20)->default('AKTIF');
            $table->timestamps();

            $table->unique(['kredit_id', 'debitur_aset_id']);
        });

        DB::statement('ALTER TABLE kredit_agunan ADD CONSTRAINT kredit_agunan_nilai_check CHECK (nilai_jaminan >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('kredit_agunan');
    }
};
