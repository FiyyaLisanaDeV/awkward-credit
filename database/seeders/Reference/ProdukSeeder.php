<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use App\Enums\ProdukTipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $produk = [
            ['kode' => 'TPP', 'nama' => 'TPP Konsumtif', 'tipe' => ProdukTipe::TPP],
            ['kode' => 'MK', 'nama' => 'Modal Kerja', 'tipe' => ProdukTipe::ModalKerja],
            ['kode' => 'GRACE', 'nama' => 'Grace Period', 'tipe' => ProdukTipe::GracePeriod],
        ];

        $cabang = DB::table('cabang')->pluck('id', 'kode');

        foreach ($cabang as $cabangId) {
            foreach ($produk as $item) {
                $exists = DB::table('produk')
                    ->where('cabang_id', $cabangId)
                    ->where('kode', $item['kode'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('produk')->insert([
                    'id' => (string) Str::uuid(),
                    'cabang_id' => $cabangId,
                    'kode' => $item['kode'],
                    'nama' => $item['nama'],
                    'tipe' => $item['tipe']->value,
                    'rate_default' => 0.18,
                    'tenor_min' => 1,
                    'tenor_max' => 60,
                    'biaya_default' => json_encode([]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
