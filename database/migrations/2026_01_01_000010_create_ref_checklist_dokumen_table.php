<?php

declare(strict_types=1);

use App\Enums\KategoriChecklist;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_checklist_dokumen', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama', 200);
            $table->string('kategori', 20);
            $table->boolean('wajib')->default(true);
            $table->smallInteger('urutan');
            $table->boolean('is_active')->default(true);
            $table->string('sumber', 150);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kategori', 'urutan']);
        });

        CheckConstraint::enum('ref_checklist_dokumen', 'kategori', KategoriChecklist::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_checklist_dokumen');
    }
};
