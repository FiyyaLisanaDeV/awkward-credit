<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomor_sequence', function (Blueprint $table): void {
            $table->string('scope', 50)->primary();
            $table->bigInteger('last_number')->default(0);
            $table->timestampTz('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_sequence');
    }
};
