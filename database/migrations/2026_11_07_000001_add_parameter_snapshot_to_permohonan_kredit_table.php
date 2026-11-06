<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonan_kredit', function (Blueprint $table): void {
            $table->jsonb('parameter_snapshot')->nullable()->after('parameter_kebijakan_id');
        });
    }

    public function down(): void
    {
        Schema::table('permohonan_kredit', function (Blueprint $table): void {
            $table->dropColumn('parameter_snapshot');
        });
    }
};
