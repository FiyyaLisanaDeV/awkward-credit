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
        Schema::create('notifikasi', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->nullable()->constrained('cabang')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('tipe', 30);
            $table->string('judul', 200);
            $table->text('pesan');
            $table->string('entity_type', 50)->nullable();
            $table->uuid('entity_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestampTz('read_at')->nullable();

            $table->index(['user_id', 'created_at']);
        });

        DB::statement('CREATE INDEX notifikasi_unread_idx ON notifikasi (user_id, created_at DESC) WHERE is_read = false');
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
