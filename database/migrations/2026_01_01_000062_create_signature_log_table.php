<?php

declare(strict_types=1);

use App\Enums\PihakSignature;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_log', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('dokumen_id')->constrained('dokumen')->cascadeOnDelete();
            $table->string('pihak', 20);
            $table->string('nama', 100);
            $table->string('jabatan', 100)->nullable();
            $table->char('ttd_hash', 64)->nullable();
            $table->string('ttd_file_path', 500)->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestampTz('timestamp')->useCurrent();

            $table->index('dokumen_id');
        });

        CheckConstraint::enum('signature_log', 'pihak', PihakSignature::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_log');
    }
};
