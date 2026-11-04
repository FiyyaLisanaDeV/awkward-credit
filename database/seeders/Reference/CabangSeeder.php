<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CabangSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['kode' => 'KDI', 'nama' => 'BPR Bahteramas Kendari', 'alamat' => 'Kendari', 'telepon' => null],
            ['kode' => 'BOM', 'nama' => 'BPR Bahteramas Bombana', 'alamat' => 'Bombana', 'telepon' => null],
            ['kode' => 'KOL', 'nama' => 'BPR Bahteramas Kolaka', 'alamat' => 'Kolaka', 'telepon' => null],
        ];

        foreach ($rows as $row) {
            if (DB::table('cabang')->where('kode', $row['kode'])->exists()) {
                continue;
            }

            DB::table('cabang')->insert([
                'id' => (string) Str::uuid(),
                'kode' => $row['kode'],
                'nama' => $row['nama'],
                'alamat' => $row['alamat'],
                'telepon' => $row['telepon'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
