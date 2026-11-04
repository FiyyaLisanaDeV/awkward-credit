<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignUuid('cabang_id')->nullable()->after('id')->constrained('cabang')->nullOnDelete();
            $table->string('username', 50)->nullable()->unique()->after('name');
            $table->string('jabatan', 100)->nullable()->after('email');
            $table->string('no_hp', 20)->nullable()->after('jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cabang_id');
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'jabatan', 'no_hp']);
        });
    }
};
