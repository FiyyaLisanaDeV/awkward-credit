<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Enums\JenisImport;
use App\Enums\MetodePembayaran;
use App\Enums\StatusImport;
use App\Enums\StatusImportBaris;
use App\Models\ImportBaris;
use App\Models\ImportBatch;
use App\Models\JadwalAngsuran;
use App\Models\Kredit;
use App\Models\User;
use App\Support\Konstanta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Preview/validasi impor pembayaran CSV. Tidak menulis ke tabel target —
 * hanya batch + baris dengan status VALID/INVALID dan daftar error per baris.
 */
final class PembayaranCsvImporter
{
    private const HEADER = ['no_rekening_kredit', 'periode', 'tanggal_bayar', 'metode', 'no_bukti', 'keterangan'];

    public function preview(UploadedFile $file, User $user): ImportBatch
    {
        $maxRows = (int) config('import.max_rows');
        $contents = (string) $file->get();

        abort_if(trim($contents) === '', 422, 'File kosong.');

        $hash = hash('sha256', $contents);
        $path = 'import/pembayaran-'.$hash.'.csv';
        Storage::disk('local')->put($path, $contents);

        $rows = $this->parse($contents, $maxRows);

        $cabangId = (string) $user->cabang_id;

        return DB::transaction(function () use ($rows, $user, $cabangId, $hash, $path): ImportBatch {
            $batch = ImportBatch::query()->create([
                'cabang_id' => $cabangId,
                'user_id' => $user->id,
                'jenis' => JenisImport::PembayaranCsv->value,
                'profil_id' => null,
                'file_path' => $path,
                'file_hash' => $hash,
                'status' => StatusImport::Preview->value,
                'total_baris' => count($rows),
                'sukses' => 0,
                'gagal' => 0,
            ]);

            $sukses = 0;
            $gagal = 0;
            $seen = [];

            foreach ($rows as $index => $raw) {
                $errors = $this->validateRow($raw, $cabangId, $seen);
                $status = $errors === [] ? StatusImportBaris::Valid : StatusImportBaris::Invalid;

                ImportBaris::query()->create([
                    'batch_id' => $batch->id,
                    'nomor_baris' => $index + 2,
                    'payload' => $raw,
                    'normalized' => $errors === [] ? $this->normalize($raw) : null,
                    'errors' => $errors,
                    'status' => $status->value,
                    'target_type' => 'kredit',
                    'target_id' => null,
                ]);

                if ($errors === []) {
                    $sukses++;
                } else {
                    $gagal++;
                }
            }

            $batch->update([
                'sukses' => $sukses,
                'gagal' => $gagal,
            ]);

            return $batch->fresh() ?? $batch;
        });
    }

    /**
     * @return list<array<string, string>>
     */
    private function parse(string $contents, int $maxRows): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($contents));

        if ($lines === false || $lines === []) {
            abort(422, 'File kosong.');
        }

        $header = str_getcsv((string) $lines[0]);
        $header = array_map(static fn (?string $col): string => strtolower(trim((string) $col)), $header);

        if ($header !== self::HEADER) {
            abort(422, 'Header tidak sesuai. Gunakan: '.implode(',', self::HEADER));
        }

        abort_if(count($lines) - 1 > $maxRows, 422, 'Jumlah baris melebihi batas '.$maxRows.'.');

        $rows = [];

        foreach (array_slice($lines, 1) as $line) {
            if (trim($line) === '') {
                continue;
            }

            $values = array_pad(str_getcsv($line), count(self::HEADER), '');
            $raw = [];

            foreach (self::HEADER as $index => $key) {
                $raw[$key] = trim((string) $values[$index]);
            }

            $raw['metode'] = strtoupper((string) $raw['metode']);

            $rows[] = $raw;
        }

        return $rows;
    }

    /**
     * @param  array<string, string>  $raw
     * @param  array<string, true>  $seen
     * @return list<string>
     */
    private function validateRow(array $raw, string $cabangId, array &$seen): array
    {
        $errors = [];

        if ($raw['no_rekening_kredit'] === '') {
            return ['no_rekening_kredit kosong.'];
        }

        if (! ctype_digit($raw['periode']) || (int) $raw['periode'] < 1) {
            $errors[] = 'periode tidak valid.';
        }

        if (! $this->validDate($raw['tanggal_bayar'])) {
            $errors[] = 'tanggal_bayar tidak valid (gunakan Y-m-d).';
        }

        if (MetodePembayaran::tryFrom($raw['metode']) === null) {
            $errors[] = 'metode tidak dikenali.';
        }

        $kredit = Kredit::query()
            ->where('no_rekening_kredit', $raw['no_rekening_kredit'])
            ->where('cabang_id', $cabangId)
            ->first();

        if ($kredit === null) {
            $errors[] = 'no_rekening_kredit tidak ditemukan di cabang ini.';
        } else {
            $key = $kredit->id.':'.$raw['periode'];

            if (isset($seen[$key])) {
                $errors[] = 'duplikat kredit + periode dalam file.';
            }

            $seen[$key] = true;

            if (ctype_digit($raw['periode'])) {
                $jadwal = JadwalAngsuran::query()
                    ->where('kredit_id', $kredit->id)
                    ->where('periode', (int) $raw['periode'])
                    ->first();

                if ($jadwal === null) {
                    $errors[] = 'periode tidak ditemukan pada jadwal kredit.';
                } elseif ($jadwal->status === Konstanta::JADWAL_SUDAH_BAYAR) {
                    $errors[] = 'periode sudah dibayar.';
                }
            }
        }

        return array_values(array_unique($errors));
    }

    /**
     * @param  array<string, string>  $raw
     * @return array<string, mixed>
     */
    private function normalize(array $raw): array
    {
        return [
            'no_rekening_kredit' => $raw['no_rekening_kredit'],
            'periode' => (int) $raw['periode'],
            'tanggal_bayar' => $raw['tanggal_bayar'],
            'metode' => $raw['metode'],
            'no_bukti' => $raw['no_bukti'] !== '' ? $raw['no_bukti'] : null,
            'keterangan' => $raw['keterangan'] !== '' ? $raw['keterangan'] : null,
        ];
    }

    private function validDate(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        try {
            Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return false;
        }

        return true;
    }
}
