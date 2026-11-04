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
        Schema::create('pemblokiran_tabungan', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('kredit_id')->constrained('kredit')->cascadeOnDelete();

            $table->string('no_rekening', 50);
            $table->decimal('nilai_blokir', 15, 2);
            $table->smallInteger('jangka_waktu_bulan');
            $table->text('alasan')->nullable();
            $table->date('tanggal_blokir');
            $table->date('tanggal_buka')->nullable();
            $table->string('status', 20)->default('AKTIF');
            $table->timestamps();

            $table->index('kredit_id');
        });

        DB::statement('ALTER TABLE pemblokiran_tabungan ADD CONSTRAINT pemblokiran_tabungan_nilai_check CHECK (nilai_blokir >= 0 AND jangka_waktu_bulan >= 1)');
        DB::statement("CREATE INDEX pemblokiran_tabungan_active_idx ON pemblokiran_tabungan (kredit_id) WHERE status = 'AKTIF'");
    }

    public function down(): void
    {
        Schema::dropIfExists('pemblokiran_tabungan');
    }
};
