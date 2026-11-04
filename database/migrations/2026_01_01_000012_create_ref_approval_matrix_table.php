<?php

declare(strict_types=1);

use App\Enums\StatusVersi;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_approval_matrix', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('version', 20)->unique();
            $table->date('effective_date');
            $table->jsonb('matrix');
            $table->string('status', 20)->default(StatusVersi::DRAFT->value);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('approved_at')->nullable();
            $table->timestamps();
        });

        CheckConstraint::enum('ref_approval_matrix', 'status', StatusVersi::class);

        DB::statement("CREATE UNIQUE INDEX ref_approval_active_unique ON ref_approval_matrix (status) WHERE status = 'ACTIVE'");
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_approval_matrix');
    }
};
