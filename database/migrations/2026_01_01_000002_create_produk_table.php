<?php

declare(strict_types=1);

use App\Enums\ProdukTipe;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang')->restrictOnDelete();
            $table->string('kode', 20);
            $table->string('nama', 100);
            $table->string('tipe', 20);
            $table->decimal('rate_default', 5, 4);
            $table->smallInteger('tenor_min');
            $table->smallInteger('tenor_max');
            $table->jsonb('biaya_default')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        CheckConstraint::enum('produk', 'tipe', ProdukTipe::class);

        DB::statement('CREATE UNIQUE INDEX produk_cabang_kode_unique ON produk (cabang_id, kode) WHERE deleted_at IS NULL');
        DB::statement('ALTER TABLE produk ADD CONSTRAINT produk_tenor_check CHECK (tenor_min >= 1 AND tenor_max >= tenor_min)');
        DB::statement('ALTER TABLE produk ADD CONSTRAINT produk_rate_check CHECK (rate_default > 0 AND rate_default <= 1)');
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
