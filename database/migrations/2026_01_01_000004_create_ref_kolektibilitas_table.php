<?php

declare(strict_types=1);

use App\Enums\Kolektibilitas;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_kolektibilitas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->smallInteger('kode')->unique();
            $table->string('nama', 50);
            $table->text('deskripsi')->nullable();
            $table->smallInteger('min_hari_tunggakan')->nullable();
            $table->smallInteger('max_hari_tunggakan')->nullable();
            $table->string('sumber', 150);
            $table->timestamps();
        });

        CheckConstraint::enum('ref_kolektibilitas', 'kode', Kolektibilitas::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_kolektibilitas');
    }
};
