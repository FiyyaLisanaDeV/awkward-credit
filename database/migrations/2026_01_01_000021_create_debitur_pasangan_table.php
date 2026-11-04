<?php

declare(strict_types=1);

use App\Enums\JenisKelamin;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debitur_pasangan', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('debitur_id')->unique()->constrained('debitur')->cascadeOnDelete();

            $table->string('nama_ktp', 100);
            $table->char('jenis_kelamin', 1);
            $table->date('tanggal_lahir');
            $table->string('pekerjaan', 100)->nullable();
            $table->string('jenis_usaha', 100)->nullable();
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('tpp', 15, 2)->default(0);
            $table->decimal('penghasilan_lain', 15, 2)->default(0);
            $table->char('no_ktp', 16)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->boolean('is_ahli_waris')->default(true);
            $table->timestampTz('consent_at')->nullable();
            $table->timestamps();
        });

        CheckConstraint::enum('debitur_pasangan', 'jenis_kelamin', JenisKelamin::class);

        DB::statement('ALTER TABLE debitur_pasangan ADD CONSTRAINT debitur_pasangan_penghasilan_check CHECK (gaji_pokok >= 0 AND tpp >= 0 AND penghasilan_lain >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('debitur_pasangan');
    }
};
