<?php

declare(strict_types=1);

use App\Enums\JenisImport;
use App\Enums\StatusImport;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batch', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('jenis', 20);
            $table->foreignUuid('profil_id')->nullable()->constrained('import_profil')->nullOnDelete();

            $table->string('file_path', 500);
            $table->char('file_hash', 64);
            $table->string('status', 20)->default(StatusImport::Preview->value);
            $table->integer('total_baris')->default(0);
            $table->integer('sukses')->default(0);
            $table->integer('gagal')->default(0);
            $table->timestampTz('committed_at')->nullable();
            $table->foreignUuid('ledger_id')->nullable()->constrained('ledger')->nullOnDelete();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->unique(['jenis', 'file_hash']);
        });

        CheckConstraint::enum('import_batch', 'jenis', JenisImport::class);
        CheckConstraint::enum('import_batch', 'status', StatusImport::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batch');
    }
};
