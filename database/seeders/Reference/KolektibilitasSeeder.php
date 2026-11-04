<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class KolektibilitasSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [1, 'Lancar', 'Tidak ada tunggakan', 0, 0],
            [2, 'Dalam Perhatian Khusus', 'Tunggakan 1-90 hari', 1, 90],
            [3, 'Kurang Lancar', 'Tunggakan 91-120 hari', 91, 120],
            [4, 'Diragukan', 'Tunggakan 121-180 hari', 121, 180],
            [5, 'Macet', 'Tunggakan lebih dari 180 hari', 181, null],
        ];

        foreach ($rows as [$kode, $nama, $deskripsi, $min, $max]) {
            if (DB::table('ref_kolektibilitas')->where('kode', $kode)->exists()) {
                continue;
            }

            DB::table('ref_kolektibilitas')->insert([
                'id' => (string) Str::uuid(),
                'kode' => $kode,
                'nama' => $nama,
                'deskripsi' => $deskripsi,
                'min_hari_tunggakan' => $min,
                'max_hari_tunggakan' => $max,
                'sumber' => 'POJK1/2024 Pasal 12 (perlu verifikasi Legal)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
