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
        Schema::create('ref_rate_asuransi_jiwa', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('perusahaan', 50);
            $table->smallInteger('usia_min');
            $table->smallInteger('usia_max');
            $table->smallInteger('tenor_min');
            $table->smallInteger('tenor_max');
            $table->decimal('rate_permil', 10, 4);
            $table->date('effective_date');
            $table->string('sumber', 150);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE ref_rate_asuransi_jiwa ADD CONSTRAINT ref_rate_jiwa_usia_check CHECK (usia_min >= 0 AND usia_min <= usia_max)');
        DB::statement('ALTER TABLE ref_rate_asuransi_jiwa ADD CONSTRAINT ref_rate_jiwa_tenor_check CHECK (tenor_min >= 1 AND tenor_max >= tenor_min)');
        DB::statement('ALTER TABLE ref_rate_asuransi_jiwa ADD CONSTRAINT ref_rate_jiwa_rate_check CHECK (rate_permil >= 0)');
        DB::statement('CREATE INDEX ref_rate_jiwa_lookup ON ref_rate_asuransi_jiwa (perusahaan, effective_date)');
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_rate_asuransi_jiwa');
    }
};
