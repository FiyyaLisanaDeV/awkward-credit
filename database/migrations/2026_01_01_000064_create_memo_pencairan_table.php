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
        Schema::create('memo_pencairan', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('kredit_id')->unique()->constrained('kredit')->cascadeOnDelete();

            $table->string('no_memo', 50)->nullable();
            $table->date('tanggal_memo');
            $table->decimal('dana_bersih', 15, 2);
            $table->string('terbilang', 500);
            $table->string('rekening_tujuan', 50);
            $table->string('atas_nama', 100);
            $table->string('status', 20)->default('DRAFT');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('approved_at')->nullable();

            $table->timestamps();
        });

        DB::statement('ALTER TABLE memo_pencairan ADD CONSTRAINT memo_pencairan_dana_check CHECK (dana_bersih >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('memo_pencairan');
    }
};
