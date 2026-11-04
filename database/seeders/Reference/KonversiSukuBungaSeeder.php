<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tabel konversi suku bunga bergantung kebijakan BPR (Q3). Seeder hanya
 * mengisi bila berkas database/seeders/data/rate_konversi.json tersedia.
 */
final class KonversiSukuBungaSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/rate_konversi.json');

        if (! is_file($path)) {
            $this->command?->warn('KonversiSukuBungaSeeder dilewati: Q3 — berkas rate_konversi.json belum tersedia.');

            return;
        }

        $data = json_decode((string) file_get_contents($path), true);

        if (! is_array($data)) {
            $this->command?->error('rate_konversi.json tidak valid.');

            return;
        }

        foreach ($data as $row) {
            DB::table('ref_konversi_suku_bunga')->insert([
                'id' => (string) Str::uuid(),
                'tenor_bulan' => $row['tenor_bulan'],
                'kategori' => $row['kategori'],
                'flat' => $row['flat'],
                'efektif' => $row['efektif'],
                'anuitas' => $row['anuitas'],
                'effective_date' => $row['effective_date'],
                'sumber' => $row['sumber'] ?? '[H] kebijakan BPR',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
