<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tarif asuransi belum tersedia (Q3). Seeder hanya mengisi bila berkas
 * sumber disediakan operator: database/seeders/data/rate_asuransi.json.
 */
final class RateAsuransiSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/rate_asuransi.json');

        if (! is_file($path)) {
            $this->command?->warn('RateAsuransiSeeder dilewati: Q3 — berkas rate_asuransi.json belum tersedia.');

            return;
        }

        $data = json_decode((string) file_get_contents($path), true);

        if (! is_array($data)) {
            $this->command?->error('rate_asuransi.json tidak valid.');

            return;
        }

        foreach ($data['jiwa'] ?? [] as $row) {
            DB::table('ref_rate_asuransi_jiwa')->insert([
                'id' => (string) Str::uuid(),
                'perusahaan' => $row['perusahaan'],
                'usia_min' => $row['usia_min'],
                'usia_max' => $row['usia_max'],
                'tenor_min' => $row['tenor_min'],
                'tenor_max' => $row['tenor_max'],
                'rate_permil' => $row['rate_permil'],
                'effective_date' => $row['effective_date'],
                'sumber' => $row['sumber'] ?? '[H] kontrak rekanan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($data['kebakaran'] ?? [] as $row) {
            DB::table('ref_rate_asuransi_kebakaran')->insert([
                'id' => (string) Str::uuid(),
                'perusahaan' => $row['perusahaan'],
                'usia_min' => $row['usia_min'],
                'usia_max' => $row['usia_max'],
                'masa_asuransi' => $row['masa_asuransi'],
                'rate_permil' => $row['rate_permil'],
                'effective_date' => $row['effective_date'],
                'sumber' => $row['sumber'] ?? '[H] kontrak rekanan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($data['kendaraan'] ?? [] as $row) {
            DB::table('ref_rate_kendaraan')->insert([
                'id' => (string) Str::uuid(),
                'perusahaan' => $row['perusahaan'],
                'jenis_pertanggungan' => $row['jenis_pertanggungan'],
                'harga_min' => $row['harga_min'],
                'harga_max' => $row['harga_max'],
                'rate' => $row['rate'],
                'effective_date' => $row['effective_date'],
                'sumber' => $row['sumber'] ?? '[H] kontrak rekanan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
