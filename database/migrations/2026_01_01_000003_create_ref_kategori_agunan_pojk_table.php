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
        Schema::create('ref_kategori_agunan_pojk', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('nama', 100);
            $table->decimal('persen_max', 5, 4);
            $table->string('pasal_pojk', 50);
            $table->text('catatan')->nullable();
            $table->string('sumber', 150);
            $table->date('effective_date');
            $table->boolean('is_active')->default(true);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE ref_kategori_agunan_pojk ADD CONSTRAINT ref_kategori_agunan_persen_check CHECK (persen_max >= 0 AND persen_max <= 1)');
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_kategori_agunan_pojk');
    }
};
