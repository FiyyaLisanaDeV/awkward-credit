<?php

declare(strict_types=1);

use App\Enums\JenisKelamin;
use App\Enums\StatusPerkawinan;
use App\Enums\SumberData;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debitur', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang')->restrictOnDelete();

            $table->string('nama_ktp', 100);
            $table->string('nama_ibu_kandung', 100)->nullable();
            $table->char('no_ktp', 16);
            $table->char('no_kk', 16)->nullable();
            $table->string('npwp', 20)->nullable();

            $table->char('jenis_kelamin', 1);
            $table->string('status_perkawinan', 20);
            $table->string('agama', 20)->nullable();
            $table->string('pendidikan', 10)->nullable();

            $table->string('pekerjaan_utama', 100)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('nama_perusahaan', 100)->nullable();
            $table->smallInteger('lama_bekerja_tahun')->nullable();
            $table->decimal('penghasilan_bulanan', 15, 2)->nullable();

            $table->string('email', 100)->nullable();
            $table->string('no_hp', 20);
            $table->string('no_wa', 20)->nullable();

            $table->text('alamat_jalan');
            $table->string('alamat_rt', 5);
            $table->string('alamat_rw', 5);
            $table->string('kelurahan', 100);
            $table->string('kecamatan', 100);
            $table->string('kabupaten', 100);
            $table->string('provinsi', 100);
            $table->string('kode_pos', 10)->nullable();

            $table->text('alamat_tinggal')->nullable();
            $table->string('status_tempat_tinggal', 20)->nullable();
            $table->smallInteger('lama_tinggal_tahun')->nullable();
            $table->string('koordinat_rumah', 50)->nullable();

            $table->smallInteger('jumlah_keluarga')->default(0);
            $table->smallInteger('jumlah_tanggungan')->default(0);

            $table->string('tempat_lahir', 50);
            $table->date('tanggal_lahir');

            $table->string('sumber_data', 10)->default(SumberData::Manual->value);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('verified_at')->nullable();
            $table->string('foto_ktp_path', 500)->nullable();
            $table->string('foto_debitur_path', 500)->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('cabang_id');
        });

        CheckConstraint::enum('debitur', 'jenis_kelamin', JenisKelamin::class);
        CheckConstraint::enum('debitur', 'status_perkawinan', StatusPerkawinan::class);
        CheckConstraint::enum('debitur', 'sumber_data', SumberData::class);

        DB::statement('ALTER TABLE debitur ADD CONSTRAINT debitur_tanggungan_check CHECK (jumlah_keluarga >= 0 AND jumlah_tanggungan >= 0)');
        DB::statement('CREATE UNIQUE INDEX debitur_no_ktp_unique ON debitur (no_ktp) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('debitur');
    }
};
