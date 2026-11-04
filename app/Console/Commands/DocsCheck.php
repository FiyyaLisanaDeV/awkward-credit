<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class DocsCheck extends Command
{
    protected $signature = 'docs:check {--strict : Perlakukan peringatan sebagai kegagalan}';

    protected $description = 'Konsistensi dokumen: berkas wajib, jumlah fase/event/enum/tabel, tautan, istilah terlarang';

    /** @var list<string> */
    private array $errors = [];

    /** @var list<string> */
    private array $warnings = [];

    public function handle(): int
    {
        $this->checkRequiredDocs();
        $this->checkCounts();
        $this->checkLinks();
        $this->checkForbiddenTerms();

        foreach ($this->warnings as $warning) {
            $this->warn('PERINGATAN: '.$warning);
        }

        foreach ($this->errors as $error) {
            $this->error('GAGAL: '.$error);
        }

        $this->line(sprintf('temuan: %d error, %d peringatan', count($this->errors), count($this->warnings)));

        if ($this->errors !== []) {
            return self::FAILURE;
        }

        if ($this->option('strict') && $this->warnings !== []) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function checkRequiredDocs(): void
    {
        $docs = [
            'RENCANA_IMPLEMENTASI.md',
            'TEKNIS.md',
            'INVENTARIS_DATA.md',
            'DSH_PROMPTS_SIMPLE.md',
            'PROGRESS.md',
            'DECISIONS.md',
            'VERIFIKASI.md',
        ];

        foreach ($docs as $doc) {
            if (! is_file(base_path('docs/'.$doc))) {
                $this->errors[] = 'dokumen hilang: docs/'.$doc;
            }
        }
    }

    private function checkCounts(): void
    {
        $prompts = $this->read('docs/DSH_PROMPTS_SIMPLE.md');
        $progress = $this->read('docs/PROGRESS.md');
        $inventaris = $this->read('docs/INVENTARIS_DATA.md');

        $this->expect('prompt fase (DSH)', preg_match_all('/^## FASE /m', $prompts) ?: 0, 22);
        $this->expect(
            'baris papan (PROGRESS)',
            preg_match_all('/^\| (?:0[0-9]|1[0-8]|06B|08B|12B) \|/m', $progress) ?: 0,
            22
        );

        $events = $this->section($inventaris, '## §9. Katalog event ledger', '## §10.');
        $this->expect('event ledger (INVENTARIS)', preg_match_all('/^\| \d+ \| [A-Z_]+ \|/m', $events) ?: 0, 23);

        $enums = $this->section($inventaris, '## §8. Enum', '## §9.');
        $enumRows = preg_match_all('/^\| [A-Za-z]+ \|/m', $enums) ?: 0;
        $this->expect('enum (INVENTARIS)', max(0, $enumRows - 1), 31);

        $tables = $this->section($inventaris, '## §1. Master', '## §8.');
        $this->expect('tabel bisnis (INVENTARIS)', preg_match_all('/^### /m', $tables) ?: 0, 60);
    }

    private function checkLinks(): void
    {
        foreach (glob(base_path('docs/*.md')) ?: [] as $file) {
            $content = (string) file_get_contents($file);

            preg_match_all('/\]\(([^)]+)\)/', $content, $matches);

            foreach ($matches[1] as $target) {
                if (str_starts_with($target, 'http') || str_starts_with($target, '#') || str_starts_with($target, 'mailto:')) {
                    continue;
                }

                $path = strtok($target, '#');
                $full = dirname($file).'/'.$path;

                if (! file_exists($full)) {
                    $this->errors[] = sprintf('tautan rusak: %s → %s', basename($file), $target);
                }
            }
        }
    }

    private function checkForbiddenTerms(): void
    {
        $file = base_path('docs/DSH_PROMPTS_SIMPLE.md');
        $lines = is_file($file) ? file($file, FILE_IGNORE_NEW_LINES) ?: [] : [];

        foreach ($lines as $number => $line) {
            $lineNumber = $number + 1;

            if (str_contains($line, 'jq ') && ! str_contains($line, 'tidak ada')) {
                $this->warnings[] = 'DSH_PROMPTS_SIMPLE.md:'.$lineNumber.' memakai jq (tidak terpasang)';
            }

            if (preg_match('/\b(docker|redis-cli|redis-server|minio|puppeteer|pg_partman)\b/i', $line) !== 1) {
                continue;
            }

            $explanatory = str_contains($line, 'command -v')
                || str_contains($line, 'pg_available_extensions')
                || str_contains($line, 'KETIADAAN')
                || stripos($line, 'tanpa') !== false
                || stripos($line, 'tidak ada') !== false;

            if (! $explanatory) {
                $this->warnings[] = 'DSH_PROMPTS_SIMPLE.md:'.$lineNumber.' menyebut dependensi terlarang';
            }
        }
    }

    private function expect(string $label, int $actual, int $expected): void
    {
        if ($actual !== $expected) {
            $this->errors[] = sprintf('%s: %d (harus %d)', $label, $actual, $expected);
        }
    }

    private function read(string $relative): string
    {
        $path = base_path($relative);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }

    private function section(string $content, string $start, string $end): string
    {
        $from = strpos($content, $start);

        if ($from === false) {
            return '';
        }

        $to = strpos($content, $end, $from);

        return $to === false ? substr($content, $from) : substr($content, $from, $to - $from);
    }
}
