<?php

declare(strict_types=1);

use App\Enums\JenisImport;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_profil', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('kode', 40)->unique();
            $table->string('nama', 100);
            $table->string('jenis', 20);
            $table->smallInteger('versi')->default(1);
            $table->jsonb('mapping');
            $table->string('delimiter', 5)->nullable();
            $table->smallInteger('header_row')->default(1);
            $table->string('sheet_name', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        CheckConstraint::enum('import_profil', 'jenis', JenisImport::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('import_profil');
    }
};
