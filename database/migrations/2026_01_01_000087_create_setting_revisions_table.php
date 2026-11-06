<?php

declare(strict_types=1);

use App\Enums\SettingAction;
use App\Enums\SettingSection;
use App\Enums\StatusRevision;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_revisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('section', 40);
            $table->string('action', 20);
            $table->uuid('target_id')->nullable();
            $table->jsonb('payload');
            $table->string('status', 20)->default(StatusRevision::Draft->value);
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestampTz('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('rejected_at')->nullable();
            $table->timestampTz('applied_at')->nullable();
            $table->timestamps();

            $table->index(['section', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('created_by');
            $table->index('target_id');
        });

        CheckConstraint::enum('setting_revisions', 'section', SettingSection::class);
        CheckConstraint::enum('setting_revisions', 'action', SettingAction::class);
        CheckConstraint::enum('setting_revisions', 'status', StatusRevision::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_revisions');
    }
};
