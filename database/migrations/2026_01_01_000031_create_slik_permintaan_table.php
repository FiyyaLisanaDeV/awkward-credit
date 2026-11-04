<?php

declare(strict_types=1);

use App\Enums\JenisPermintaanSlik;
use App\Enums\SumberSlik;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slik_permintaan', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('debitur_id')->constrained('debitur')->restrictOnDelete();

            $table->date('tanggal_permintaan');
            $table->string('jenis', 20);
            $table->string('tujuan', 50);
            $table->string('no_referensi', 50)->nullable();
            $table->foreignId('operator_id')->constrained('users')->restrictOnDelete();

            $table->string('file_path', 500)->nullable();
            $table->char('file_hash', 64)->nullable();
            $table->string('sumber', 20);

            $table->smallInteger('jumlah_fasilitas')->default(0);
            $table->decimal('total_plafon', 15, 2)->default(0);
            $table->decimal('total_baki', 15, 2)->default(0);
            $table->smallInteger('kualitas_terburuk')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index('permohonan_id');
        });

        CheckConstraint::enum('slik_permintaan', 'jenis', JenisPermintaanSlik::class);
        CheckConstraint::enum('slik_permintaan', 'sumber', SumberSlik::class);

        DB::statement('ALTER TABLE slik_permintaan ADD CONSTRAINT slik_permintaan_nilai_check CHECK (jumlah_fasilitas >= 0 AND total_plafon >= 0 AND total_baki >= 0)');
        DB::statement('ALTER TABLE slik_permintaan ADD CONSTRAINT slik_permintaan_kualitas_check CHECK (kualitas_terburuk IS NULL OR (kualitas_terburuk >= 1 AND kualitas_terburuk <= 5))');
    }

    public function down(): void
    {
        Schema::dropIfExists('slik_permintaan');
    }
};
