<?php

declare(strict_types=1);

use App\Enums\ProdukTipe;
use App\Enums\SistemSukuBunga;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulasi_kredit', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('permohonan_id')->nullable()->constrained('permohonan_kredit')->nullOnDelete();
            $table->foreignUuid('debitur_id')->nullable()->constrained('debitur')->nullOnDelete();

            $table->string('label', 100);
            $table->string('produk_tipe', 20);
            $table->string('sistem_suku_bunga', 20);

            $table->foreignUuid('parameter_kebijakan_id')->constrained('ref_parameter_kebijakan')->restrictOnDelete();
            $table->string('parameter_version', 20);
            $table->string('engine_version', 30);

            $table->jsonb('input');
            $table->char('input_hash', 64);
            $table->jsonb('output');

            $table->decimal('plafon', 15, 2)->nullable();
            $table->decimal('rate', 5, 4)->nullable();
            $table->smallInteger('tenor')->nullable();
            $table->decimal('angsuran', 15, 2)->nullable();
            $table->decimal('dscr', 10, 4)->nullable();
            $table->decimal('plafon_max', 15, 2)->nullable();
            $table->decimal('ltv', 10, 4)->nullable();
            $table->decimal('rasio_balloon', 10, 4)->nullable();
            $table->decimal('idir', 10, 4)->nullable();
            $table->decimal('skor_5c', 5, 4)->nullable();
            $table->string('rating', 50)->nullable();
            $table->string('status_kelayakan', 30)->nullable();
            $table->smallInteger('jumlah_red_flag')->default(0);

            $table->integer('durasi_ms')->default(0);
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignUuid('ledger_id')->nullable()->constrained('ledger')->nullOnDelete();
            $table->timestampTz('dibuat_pada')->useCurrent();

            $table->index(['permohonan_id', 'dibuat_pada']);
            $table->index(['debitur_id', 'dibuat_pada']);
            $table->index(['user_id', 'dibuat_pada']);
            $table->index(['cabang_id', 'dibuat_pada']);
            $table->index('input_hash');
        });

        CheckConstraint::enum('simulasi_kredit', 'produk_tipe', ProdukTipe::class);
        CheckConstraint::enum('simulasi_kredit', 'sistem_suku_bunga', SistemSukuBunga::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('simulasi_kredit');
    }
};
