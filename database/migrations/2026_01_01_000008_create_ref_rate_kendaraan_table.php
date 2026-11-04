<?php

declare(strict_types=1);

use App\Enums\JenisPertanggungan;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_rate_kendaraan', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('perusahaan', 50);
            $table->string('jenis_pertanggungan', 10);
            $table->decimal('harga_min', 15, 2);
            $table->decimal('harga_max', 15, 2);
            $table->decimal('rate', 10, 6);
            $table->date('effective_date');
            $table->string('sumber', 150);
            $table->timestamps();
        });

        CheckConstraint::enum('ref_rate_kendaraan', 'jenis_pertanggungan', JenisPertanggungan::class);

        DB::statement('ALTER TABLE ref_rate_kendaraan ADD CONSTRAINT ref_rate_kendaraan_harga_check CHECK (harga_min >= 0 AND harga_max >= harga_min)');
        DB::statement('ALTER TABLE ref_rate_kendaraan ADD CONSTRAINT ref_rate_kendaraan_rate_check CHECK (rate >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_rate_kendaraan');
    }
};
