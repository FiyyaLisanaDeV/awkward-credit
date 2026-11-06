<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Cabang;
use App\Models\Debitur;
use App\Services\Workflow\NomorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class CifBackfill extends Command
{
    protected $signature = 'cif:backfill {--finalize : jadikan kolom cif NOT NULL setelah backfill}';

    protected $description = 'Isi nomor CIF untuk debitur yang belum memilikinya (idempotent)';

    public function handle(NomorService $nomor): int
    {
        $total = 0;

        Debitur::query()
            ->whereNull('cif')
            ->chunkById(100, function ($rows) use (&$total, $nomor): void {
                foreach ($rows as $debitur) {
                    $cabang = Cabang::query()->find($debitur->cabang_id);

                    if ($cabang === null) {
                        continue;
                    }

                    DB::table('debitur')
                        ->where('id', $debitur->id)
                        ->update(['cif' => $nomor->cif($cabang)]);

                    $total++;
                }
            });

        $this->info("CIF diisi: {$total} debitur.");

        if ($this->option('finalize')) {
            $sisa = Debitur::query()->whereNull('cif')->count();

            if ($sisa > 0) {
                $this->error("Masih ada {$sisa} debitur tanpa CIF; finalize dibatalkan.");

                return self::FAILURE;
            }

            DB::statement('ALTER TABLE debitur ALTER COLUMN cif SET NOT NULL');
            $this->info('Kolom debitur.cif sekarang NOT NULL.');
        }

        return self::SUCCESS;
    }
}
