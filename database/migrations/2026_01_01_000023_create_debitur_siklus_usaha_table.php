<?php

declare(strict_types=1);

use App\Enums\SatuanPeriode;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debitur_siklus_usaha', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('debitur_id')->unique()->constrained('debitur')->cascadeOnDelete();

            $table->decimal('frekuensi_belanja', 10, 2);
            $table->string('satuan_periode', 10);
            $table->decimal('modal_kerja_per_siklus', 15, 2);
            $table->decimal('pendapatan_kotor_per_siklus', 15, 2);
            $table->decimal('biaya_operasional_per_siklus', 15, 2)->default(0);
            $table->timestamps();
        });

        CheckConstraint::enum('debitur_siklus_usaha', 'satuan_periode', SatuanPeriode::class);

        DB::statement('ALTER TABLE debitur_siklus_usaha ADD CONSTRAINT debitur_siklus_nilai_check CHECK (frekuensi_belanja >= 0 AND modal_kerja_per_siklus >= 0 AND pendapatan_kotor_per_siklus >= 0 AND biaya_operasional_per_siklus >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('debitur_siklus_usaha');
    }
};
