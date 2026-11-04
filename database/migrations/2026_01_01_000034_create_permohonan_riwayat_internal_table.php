<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_riwayat_internal', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->constrained('permohonan_kredit')->cascadeOnDelete();

            $table->string('no_rekening_pinjaman', 30)->nullable();
            $table->decimal('plafon', 15, 2)->nullable();
            $table->date('tanggal_cair')->nullable();
            $table->date('tanggal_lunas_jatuh_tempo')->nullable();
            $table->foreignUuid('kolektibilitas_tertinggi_id')->nullable()->constrained('ref_kolektibilitas')->nullOnDelete();
            $table->foreignUuid('status_pelunasan_id')->nullable()->constrained('ref_status_pelunasan_internal')->nullOnDelete();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index('permohonan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_riwayat_internal');
    }
};
