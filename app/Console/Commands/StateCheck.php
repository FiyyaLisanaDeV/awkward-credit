<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Throwable;

final class StateCheck extends Command
{
    protected $signature = 'state:check';

    protected $description = 'Reality check: versi, database, migrasi, rute, test, dokumen, dan git';

    public function handle(): int
    {
        $rows = [];
        $ok = true;

        $rows[] = ['php', PHP_VERSION, version_compare(PHP_VERSION, '8.3.0', '>=') ? 'OK' : 'GAGAL'];
        $rows[] = ['laravel', app()->version(), 'OK'];
        $rows[] = ['timezone', (string) config('app.timezone'), 'OK'];

        try {
            DB::connection()->getPdo();
            $rows[] = ['database', (string) config('database.default'), 'OK'];
            $rows[] = ['migrasi', (string) $this->migrationCount(), 'OK'];
        } catch (Throwable $e) {
            $rows[] = ['database', $e->getMessage(), 'GAGAL'];
            $ok = false;
        }

        $rows[] = ['rute', (string) count(Route::getRoutes()->getRoutes()), 'OK'];
        $rows[] = ['file test', (string) $this->testFileCount(), 'OK'];
        $rows[] = ['composer.lock', $this->lockHash(), 'OK'];
        $rows[] = ['git', $this->gitHead(), 'OK'];

        foreach ($this->requiredDocs() as $doc) {
            $exists = is_file(base_path($doc));
            $rows[] = ['dokumen: '.$doc, $exists ? 'ada' : 'HILANG', $exists ? 'OK' : 'GAGAL'];
            if (! $exists) {
                $ok = false;
            }
        }

        $this->table(['Item', 'Nilai', 'Status'], $rows);

        return $ok ? self::SUCCESS : self::FAILURE;
    }

    private function migrationCount(): int
    {
        return DB::table('migrations')->count();
    }

    private function testFileCount(): int
    {
        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(base_path('tests'))
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), 'Test.php')) {
                $count++;
            }
        }

        return $count;
    }

    private function lockHash(): string
    {
        $path = base_path('composer.lock');

        return is_file($path) ? substr((string) hash_file('sha256', $path), 0, 12) : '-';
    }

    private function gitHead(): string
    {
        $output = [];
        @exec('git rev-parse --short HEAD 2>/dev/null', $output);

        return $output[0] ?? '-';
    }

    /**
     * @return list<string>
     */
    private function requiredDocs(): array
    {
        return [
            'docs/RUANG-LINGKUP-PRODUK.md',
            'docs/RENCANA-PRODUKSI-FINAL.md',
            'docs/TEKNIS.md',
            'docs/INVENTARIS_DATA.md',
            'docs/PROGRESS.md',
            'docs/DECISIONS.md',
            'docs/VERIFIKASI.md',
        ];
    }
}
