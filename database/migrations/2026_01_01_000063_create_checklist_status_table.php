<?php

declare(strict_types=1);

use App\Enums\StatusChecklist;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_status', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('checklist_id')->constrained('ref_checklist_dokumen')->restrictOnDelete();
            $table->string('status', 20);
            $table->string('nomor_dokumen', 50)->nullable();
            $table->date('tanggal_dokumen')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['permohonan_id', 'checklist_id']);
        });

        CheckConstraint::enum('checklist_status', 'status', StatusChecklist::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_status');
    }
};
