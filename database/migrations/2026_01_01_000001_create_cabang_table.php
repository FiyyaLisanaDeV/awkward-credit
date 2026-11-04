<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cabang', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('kode', 10)->unique();
            $table->string('nama', 100);
            $table->text('alamat');
            $table->string('telepon', 20)->nullable();
            $table->string('npwp', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabang');
    }
};
