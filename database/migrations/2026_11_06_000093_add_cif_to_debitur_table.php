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
        Schema::table('debitur', function (Blueprint $table): void {
            $table->string('cif', 20)->nullable();
        });

        DB::statement('CREATE UNIQUE INDEX debitur_cif_unique ON debitur (cif)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS debitur_cif_unique');
        Schema::table('debitur', function (Blueprint $table): void {
            $table->dropColumn('cif');
        });
    }
};
