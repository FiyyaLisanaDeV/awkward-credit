<?php

declare(strict_types=1);

use App\Enums\MetodePembayaran;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_angsuran', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang')->restrictOnDelete();
            $table->foreignUuid('kredit_id')->constrained('kredit')->cascadeOnDelete();
            $table->unsignedBigInteger('jadwal_angsuran_id')->nullable();
            $table->foreign('jadwal_angsuran_id')->references('id')->on('jadwal_angsuran')->nullOnDelete();

            $table->smallInteger('periode')->nullable();
            $table->date('tanggal_bayar');
            $table->decimal('pokok', 15, 2)->default(0);
            $table->decimal('bunga', 15, 2)->default(0);
            $table->decimal('denda', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->string('metode', 20);
            $table->string('no_bukti', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('sumber', 20)->default('MANUAL');
            $table->timestampTz('dibatalkan_at')->nullable();
            $table->uuid('reversal_of')->nullable();

            $table->timestamps();

            $table->index(['kredit_id', 'tanggal_bayar']);
        });

        CheckConstraint::enum('pembayaran_angsuran', 'metode', MetodePembayaran::class);

        DB::statement('ALTER TABLE pembayaran_angsuran ADD CONSTRAINT pembayaran_angsuran_reversal_of_foreign FOREIGN KEY (reversal_of) REFERENCES pembayaran_angsuran (id) ON DELETE SET NULL');

        DB::statement('ALTER TABLE pembayaran_angsuran ADD CONSTRAINT pembayaran_angsuran_nilai_check CHECK (pokok >= 0 AND bunga >= 0 AND denda >= 0 AND total >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_angsuran');
    }
};
