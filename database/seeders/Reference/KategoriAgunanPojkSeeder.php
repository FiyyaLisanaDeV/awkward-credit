<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class KategoriAgunanPojkSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [34, 'Agunan tunai memenuhi Pasal 17 (tabungan/deposito/logam mulia/SB BI atau Pemerintah)', 1.0, 'Pasal 17'],
            [35, 'Emas perhiasan', 0.85, 'Pasal 20 (1) a'],
            [36, 'Tanah dan/atau bangunan bersertifikat dengan Hak Tanggungan/Fidusia', 0.80, 'Pasal 20 (1) b'],
            [37, 'Resi gudang - penilaian <= 12 bulan', 0.70, 'Pasal 20 (1) c'],
            [38, 'Tanah dan/atau bangunan bersertifikat tanpa Hak Tanggungan/Fidusia', 0.60, 'Pasal 20 (1) d'],
            [39, 'Tanah dan/atau bangunan dengan bukti kepemilikan tanah adat', 0.50, 'Pasal 20 (1) e'],
            [40, 'Tempat usaha dengan bukti/izin/hak pakai + kuasa menjual/pengalihan', 0.50, 'Pasal 20 (1) f'],
            [41, 'Kendaraan/kapal/perahu bermotor/alat berat/mesin dengan Hipotek/Fidusia', 0.50, 'Pasal 20 (1) g'],
            [42, 'Resi gudang - penilaian >12 s.d. 18 bulan', 0.50, 'Pasal 20 (1) h'],
            [43, 'Bagian Kredit yang dijamin BUMN/BUMD sebagai penjamin yang memenuhi kriteria', 0.50, 'Pasal 20 (1) i'],
            [44, 'Resi gudang - penilaian >18 s.d. <24 bulan', 0.30, 'Pasal 20 (1) j'],
            [45, 'Agunan lain yang dinilai penilai independen dalam 1 tahun terakhir', 0.20, 'Pasal 20 (1) k'],
            [46, 'Aset informatif/tidak memenuhi kategori Pasal 20', 0.00, 'Pasal 20 (2)'],
        ];

        foreach ($rows as [$row, $nama, $persen, $pasal]) {
            if (DB::table('ref_kategori_agunan_pojk')->where('nama', $nama)->exists()) {
                continue;
            }

            DB::table('ref_kategori_agunan_pojk')->insert([
                'id' => (string) Str::uuid(),
                'nama' => $nama,
                'persen_max' => $persen,
                'pasal_pojk' => $pasal,
                'catatan' => null,
                'sumber' => 'Sheet2!'.$row,
                'effective_date' => '2025-01-01',
                'is_active' => true,
                'approved_by' => null,
                'approved_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
