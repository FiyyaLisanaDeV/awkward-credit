<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ImportProfilSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['SLIK_XLSX_IDEB', 'IDEB Excel/CSV', 'SLIK_XLSX'],
            ['SLIK_PDF_IDEB', 'IDEB PDF teks', 'SLIK_PDF'],
            ['SLIK_TEKS', 'Tempel teks SLIK', 'SLIK_TEKS'],
            ['KTP_OCR', 'KTP/KK/NPWP', 'KTP'],
            ['DEBITUR_XLSX', 'Debitur + aset (Excel)', 'DEBITUR_XLSX'],
            ['DOKUMEN_ZIP', 'Dokumen massal (ZIP)', 'DOKUMEN_ZIP'],
            ['WORKBOOK_LAMA', 'Workbook MASTER v12', 'WORKBOOK_LAMA'],
            ['PEMBAYARAN_CSV', 'Pembayaran angsuran (CSV)', 'PEMBAYARAN_CSV'],
        ];

        foreach ($rows as [$kode, $nama, $jenis]) {
            if (DB::table('import_profil')->where('kode', $kode)->exists()) {
                continue;
            }

            DB::table('import_profil')->insert([
                'id' => (string) Str::uuid(),
                'kode' => $kode,
                'nama' => $nama,
                'jenis' => $jenis,
                'versi' => 1,
                'mapping' => json_encode([]),
                'delimiter' => null,
                'header_row' => 1,
                'sheet_name' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
