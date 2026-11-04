<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class StatusPelunasanSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['L', 'Lunas', false],
            ['LT', 'Lunas dengan Tunggakan', true],
            ['BL', 'Berjalan Lancar', false],
            ['BM', 'Berjalan Menunggak', true],
            ['MB', 'Macet/Bermasalah', true],
        ];

        foreach ($rows as [$kode, $nama, $flag]) {
            if (DB::table('ref_status_pelunasan_internal')->where('kode', $kode)->exists()) {
                continue;
            }

            DB::table('ref_status_pelunasan_internal')->insert([
                'id' => (string) Str::uuid(),
                'kode' => $kode,
                'nama' => $nama,
                'flag_bermasalah' => $flag,
                'sumber' => 'DOKUMEN v2.4 / [H] BPR',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
