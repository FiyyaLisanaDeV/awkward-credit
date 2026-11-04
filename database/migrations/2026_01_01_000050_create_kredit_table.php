<?php

declare(strict_types=1);

use App\Enums\SistemSukuBunga;
use App\Enums\StatusKredit;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kredit', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang')->restrictOnDelete();
            $table->foreignUuid('permohonan_id')->unique()->constrained('permohonan_kredit')->cascadeOnDelete();

            $table->string('no_rekening_kredit', 50)->unique();
            $table->string('no_akad', 50)->nullable();
            $table->date('tanggal_akad')->nullable();
            $table->date('tanggal_cair')->nullable();

            $table->decimal('plafon', 15, 2);
            $table->decimal('rate', 5, 4);
            $table->smallInteger('tenor');
            $table->string('sistem_suku_bunga', 20);
            $table->decimal('angsuran_bulanan', 15, 2);
            $table->decimal('total_bunga', 15, 2)->default(0);
            $table->decimal('total_pembayaran', 15, 2)->default(0);
            $table->text('sumber_pelunasan_balloon')->nullable();

            $table->string('rekening_pencairan', 50);
            $table->string('rekening_potong_tpp', 50)->nullable();
            $table->string('tujuan_penggunaan', 200)->nullable();
            $table->string('sektor', 100)->nullable();
            $table->string('status', 30)->default(StatusKredit::ACTIVE->value);
            $table->timestampTz('closed_at')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index('cabang_id');
        });

        CheckConstraint::enum('kredit', 'status', StatusKredit::class);
        CheckConstraint::enum('kredit', 'sistem_suku_bunga', SistemSukuBunga::class);

        DB::statement('ALTER TABLE kredit ADD CONSTRAINT kredit_nilai_check CHECK (plafon > 0 AND angsuran_bulanan >= 0 AND total_bunga >= 0 AND total_pembayaran >= 0)');
        DB::statement('ALTER TABLE kredit ADD CONSTRAINT kredit_rate_check CHECK (rate > 0 AND rate <= 1)');
        DB::statement('ALTER TABLE kredit ADD CONSTRAINT kredit_tenor_check CHECK (tenor >= 1 AND tenor <= 360)');
        DB::statement("CREATE INDEX kredit_active_idx ON kredit (cabang_id, status) WHERE status = 'ACTIVE'");
    }

    public function down(): void
    {
        Schema::dropIfExists('kredit');
    }
};
