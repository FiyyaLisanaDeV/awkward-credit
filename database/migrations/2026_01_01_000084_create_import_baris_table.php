<?php

declare(strict_types=1);

use App\Enums\StatusImportBaris;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_baris', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('batch_id')->constrained('import_batch')->cascadeOnDelete();
            $table->integer('nomor_baris');
            $table->jsonb('payload');
            $table->jsonb('normalized')->nullable();
            $table->jsonb('errors')->nullable();
            $table->string('status', 20)->default(StatusImportBaris::Valid->value);
            $table->string('target_type', 50)->nullable();
            $table->uuid('target_id')->nullable();
            $table->timestamps();

            $table->index('batch_id');
        });

        CheckConstraint::enum('import_baris', 'status', StatusImportBaris::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('import_baris');
    }
};
