<?php

declare(strict_types=1);

use App\Enums\JenisDokumen;
use App\Enums\StatusTemplate;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_template_dokumen', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('kode', 20);
            $table->string('nama', 200);
            $table->string('version', 20);
            $table->string('file_path', 500)->nullable();
            $table->string('status', 20)->default(StatusTemplate::DRAFT->value);
            $table->date('effective_date');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('approved_at')->nullable();
            $table->string('sumber', 150);
            $table->timestamps();

            $table->unique(['kode', 'version']);
            $table->index(['kode', 'status']);
        });

        CheckConstraint::enum('ref_template_dokumen', 'kode', JenisDokumen::class, JenisDokumen::templateCases());
        CheckConstraint::enum('ref_template_dokumen', 'status', StatusTemplate::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_template_dokumen');
    }
};
