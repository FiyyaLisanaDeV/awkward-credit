<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class GoldenExtract extends Command
{
    protected $signature = 'golden:extract {--path= : Berkas workbook sumber} {--check : Bandingkan tanpa menulis}';

    protected $description = 'Ekstrak nilai golden dari workbook TPP ke fixture test';

    public function handle(): int
    {
        $path = (string) ($this->option('path') ?: base_path((string) config('loan.golden.tpp_path')));

        if (! is_file($path)) {
            $this->error('Workbook tidak ditemukan: '.$path);

            return self::FAILURE;
        }

        try {
            $spreadsheet = IOFactory::load($path);
        } catch (\Throwable $exception) {
            $this->error('Gagal membaca workbook: '.$exception->getMessage());

            return self::FAILURE;
        }

        $hitungan = $spreadsheet->getSheetByName('hitungan');
        $analisis = $spreadsheet->getSheetByName('analisis kredit');

        if ($hitungan === null || $analisis === null) {
            $this->error('Sheet "hitungan" / "analisis kredit" tidak ditemukan.');

            return self::FAILURE;
        }

        $fixture = [
            'source' => basename($path),
            'input' => [
                'plafon' => (float) $this->cellValue($hitungan, 'E9'),
                'rate' => (float) $this->cellValue($hitungan, 'E8'),
                'tenor' => (int) $this->cellValue($hitungan, 'E12'),
                'sistem_suku_bunga' => 'Flat',
            ],
            'expected' => [
                'plafon' => (float) $this->cellValue($analisis, 'D30'),
                'tenor' => (int) $this->cellValue($analisis, 'D31'),
                'rate' => (float) $this->cellValue($analisis, 'D32'),
                'angsuran' => (float) $this->cellValue($analisis, 'D33'),
                'idir' => (float) $this->cellValue($analisis, 'D34'),
                'penghasilan_bersih' => (float) $this->cellValue($analisis, 'E28'),
            ],
        ];

        if ($fixture['input']['plafon'] <= 0 || $fixture['input']['tenor'] <= 0) {
            $this->error('Workbook tidak berisi input yang dapat dipakai (plafon/tenor kosong).');

            return self::FAILURE;
        }

        $json = json_encode($fixture, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";
        $output = base_path((string) config('loan.golden.output_path'));

        if ($this->option('check')) {
            $current = is_file($output) ? (string) file_get_contents($output) : '';

            if (trim($current) !== trim($json)) {
                $this->error('Fixture golden berbeda dari workbook.');

                return self::FAILURE;
            }

            $this->info('golden:extract --check OK');

            return self::SUCCESS;
        }

        if (! is_dir(dirname($output))) {
            mkdir(dirname($output), 0775, true);
        }

        file_put_contents($output, $json);
        $this->info('Fixture golden ditulis: '.$output);

        return self::SUCCESS;
    }

    private function cellValue(Worksheet $sheet, string $coordinate): mixed
    {
        $cell = $sheet->getCell($coordinate);
        $cached = $cell->getOldCalculatedValue();

        if (is_numeric($cached) && (float) $cached !== 0.0) {
            return $cached;
        }

        $value = $cell->getValue();

        if (is_string($value) && str_starts_with($value, '=')) {
            try {
                return $cell->getCalculatedValue();
            } catch (\Throwable) {
                return $cached ?? 0;
            }
        }

        return $cached ?? $value;
    }
}
