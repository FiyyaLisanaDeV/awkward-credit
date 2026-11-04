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
        Schema::create('ref_rate_asuransi_kebakaran', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('perusahaan', 50);
            $table->smallInteger('usia_min');
            $table->smallInteger('usia_max');
            $table->smallInteger('masa_asuransi');
            $table->decimal('rate_permil', 10, 4);
            $table->date('effective_date');
            $table->string('sumber', 150);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE ref_rate_asuransi_kebakaran ADD CONSTRAINT ref_rate_kebakaran_usia_check CHECK (usia_min >= 0 AND usia_min <= usia_max)');
        DB::statement('ALTER TABLE ref_rate_asuransi_kebakaran ADD CONSTRAINT ref_rate_kebakaran_masa_check CHECK (masa_asuransi >= 1)');
        DB::statement('ALTER TABLE ref_rate_asuransi_kebakaran ADD CONSTRAINT ref_rate_kebakaran_rate_check CHECK (rate_permil >= 0)');
        DB::statement('CREATE INDEX ref_rate_kebakaran_lookup ON ref_rate_asuransi_kebakaran (perusahaan, effective_date)');
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_rate_asuransi_kebakaran');
    }
};
