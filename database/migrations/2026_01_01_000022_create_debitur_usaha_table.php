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
        Schema::create('debitur_usaha', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('debitur_id')->unique()->constrained('debitur')->cascadeOnDelete();

            $table->string('nama_usaha', 100)->nullable();
            $table->string('bentuk_badan', 30)->nullable();
            $table->string('nib', 30)->nullable();
            $table->string('npwp_usaha', 20)->nullable();
            $table->string('sektor', 100)->nullable();
            $table->text('alamat_lokasi')->nullable();
            $table->string('koordinat_lokasi', 50)->nullable();
            $table->smallInteger('lama_usaha_tahun')->nullable();
            $table->smallInteger('jumlah_karyawan')->nullable();
            $table->string('jam_operasional', 50)->nullable();
            $table->smallInteger('hari_kerja_per_bulan')->nullable();
            $table->string('media_penjualan', 100)->nullable();
            $table->text('kompetitor')->nullable();
            $table->decimal('omzet_harian', 15, 2)->nullable();
            $table->decimal('omzet_bulanan', 15, 2)->nullable();
            $table->decimal('hpp_persen', 5, 4)->nullable();
            $table->text('latar_belakang')->nullable();
            $table->text('siklus_usaha_desc')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE debitur_usaha ADD CONSTRAINT debitur_usaha_nilai_check CHECK (omzet_harian >= 0 AND omzet_bulanan >= 0 AND (hpp_persen IS NULL OR (hpp_persen >= 0 AND hpp_persen <= 1)))');
    }

    public function down(): void
    {
        Schema::dropIfExists('debitur_usaha');
    }
};
