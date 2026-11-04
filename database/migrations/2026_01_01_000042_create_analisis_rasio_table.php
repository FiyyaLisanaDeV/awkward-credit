<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_rasio', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('permohonan_id')->unique()->constrained('permohonan_kredit')->cascadeOnDelete();
            $table->foreignUuid('parameter_kebijakan_id')->constrained('ref_parameter_kebijakan')->restrictOnDelete();

            foreach ([
                'current_ratio', 'quick_ratio', 'cash_ratio', 'debt_to_asset', 'debt_to_equity',
                'equity_to_asset', 'net_profit_margin', 'roa_annualized', 'roe_annualized',
            ] as $column) {
                $table->decimal($column, 10, 4)->nullable();
            }

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_rasio');
    }
};
