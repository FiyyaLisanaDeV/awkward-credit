<?php

declare(strict_types=1);

use App\Enums\AksiApproval;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_log', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang')->restrictOnDelete();
            $table->foreignUuid('permohonan_id')->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->smallInteger('level');
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('aksi', 20);
            $table->text('alasan')->nullable();
            $table->timestampTz('timestamp')->useCurrent();

            $table->index(['permohonan_id', 'timestamp']);
        });

        CheckConstraint::enum('approval_log', 'aksi', AksiApproval::class);

        DB::statement('ALTER TABLE approval_log ADD CONSTRAINT approval_log_level_check CHECK (level >= 1)');
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_log');
    }
};
