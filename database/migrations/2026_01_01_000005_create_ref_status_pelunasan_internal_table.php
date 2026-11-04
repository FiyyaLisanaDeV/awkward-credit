<?php

declare(strict_types=1);

use App\Enums\StatusPelunasanInternal;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_status_pelunasan_internal', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('kode', 10)->unique();
            $table->string('nama', 50);
            $table->boolean('flag_bermasalah')->default(false);
            $table->string('sumber', 150);
            $table->timestamps();
        });

        CheckConstraint::enum('ref_status_pelunasan_internal', 'kode', StatusPelunasanInternal::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_status_pelunasan_internal');
    }
};
