<?php

declare(strict_types=1);

use App\Enums\TipeBiaya;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biaya_kredit', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('kredit_id')->constrained('kredit')->cascadeOnDelete();
            $table->string('komponen', 50);
            $table->decimal('nilai', 15, 2);
            $table->string('tipe', 20);
            $table->decimal('rate', 10, 4)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['kredit_id', 'komponen']);
        });

        CheckConstraint::enum('biaya_kredit', 'tipe', TipeBiaya::class);

        DB::statement('ALTER TABLE biaya_kredit ADD CONSTRAINT biaya_kredit_nilai_check CHECK (nilai >= 0 AND (rate IS NULL OR (rate >= 0 AND rate <= 1)))');
    }

    public function down(): void
    {
        Schema::dropIfExists('biaya_kredit');
    }
};
