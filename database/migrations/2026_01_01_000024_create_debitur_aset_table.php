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
        Schema::create('debitur_aset', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('debitur_id')->constrained('debitur')->cascadeOnDelete();
            $table->unsignedSmallInteger('urutan');
            $table->string('jenis_aset', 100);
            $table->foreignUuid('kategori_pojk_id')->constrained('ref_kategori_agunan_pojk')->restrictOnDelete();

            $table->string('jenis_dokumen', 20)->nullable();
            $table->string('no_dokumen', 50)->nullable();
            $table->date('tanggal_dokumen')->nullable();
            $table->date('berlaku_sampai')->nullable();

            $table->decimal('luas_jumlah', 15, 2);
            $table->string('satuan', 20)->nullable();
            $table->decimal('harga_taksasi_satuan', 15, 2);
            $table->decimal('nilai_taksasi', 15, 2);
            $table->decimal('persen_diperhitungkan', 5, 4);
            $table->decimal('nilai_diperhitungkan', 15, 2);

            $table->string('status_kepemilikan', 30);
            $table->string('nama_pemilik', 100)->nullable();
            $table->string('hubungan_pemohon', 50)->nullable();
            $table->text('lokasi')->nullable();
            $table->string('koordinat', 50)->nullable();
            $table->decimal('njop', 15, 2)->nullable();
            $table->string('penilai', 100)->nullable();
            $table->date('tanggal_penilaian')->nullable();
            $table->string('foto_path', 500)->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('debitur_id');
        });

        DB::statement('ALTER TABLE debitur_aset ADD CONSTRAINT debitur_aset_nilai_check CHECK (luas_jumlah > 0 AND harga_taksasi_satuan >= 0 AND nilai_taksasi >= 0 AND nilai_diperhitungkan >= 0 AND persen_diperhitungkan >= 0 AND persen_diperhitungkan <= 1)');
        DB::statement('CREATE UNIQUE INDEX debitur_aset_urutan_unique ON debitur_aset (debitur_id, urutan) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('debitur_aset');
    }
};
