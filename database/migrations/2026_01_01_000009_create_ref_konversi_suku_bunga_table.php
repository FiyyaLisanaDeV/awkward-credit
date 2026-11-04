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
        Schema::create('ref_konversi_suku_bunga', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->smallInteger('tenor_bulan');
            $table->string('kategori', 20);
            $table->decimal('flat', 8, 6);
            $table->decimal('efektif', 8, 6);
            $table->decimal('anuitas', 8, 6);
            $table->date('effective_date');
            $table->string('sumber', 150);
            $table->timestamps();

            $table->unique(['tenor_bulan', 'kategori', 'effective_date'], 'ref_konversi_unique');
        });

        DB::statement('ALTER TABLE ref_konversi_suku_bunga ADD CONSTRAINT ref_konversi_tenor_check CHECK (tenor_bulan >= 1)');
        DB::statement('ALTER TABLE ref_konversi_suku_bunga ADD CONSTRAINT ref_konversi_rate_check CHECK (flat >= 0 AND flat <= 1 AND efektif >= 0 AND efektif <= 1 AND anuitas >= 0 AND anuitas <= 1)');
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_konversi_suku_bunga');
    }
};
