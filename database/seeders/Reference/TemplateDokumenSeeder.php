<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class TemplateDokumenSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['DOC-01', 'Analisis Kredit'],
            ['DOC-02', 'Perhitungan Kredit & Jadwal Angsuran'],
            ['DOC-03', 'Memo Pencairan'],
            ['DOC-04', 'Perjanjian Kredit'],
            ['DOC-05', 'Surat Kuasa'],
            ['DOC-06', 'Surat Pernyataan'],
            ['DOC-07', 'Pemblokiran Tabungan'],
            ['DOC-08', 'Persetujuan SLIK'],
            ['DOC-09', 'SPK / SPAPP'],
            ['DOC-10', 'Pernyataan Asuransi'],
            ['DOC-11', 'Penyerahan Jaminan'],
            ['DOC-12', 'Lembar Konfirmasi'],
            ['DOC-13', 'Form Pengkinian Data'],
            ['DOC-14', 'Checklist Dokumen & Verifikasi'],
            ['DOC-15', 'Asuransi Jiwa'],
            ['DOC-16', 'Asuransi Kebakaran'],
            ['DOC-17', 'Appraisal / Penilaian Agunan'],
        ];

        foreach ($rows as [$kode, $nama]) {
            $exists = DB::table('ref_template_dokumen')
                ->where('kode', $kode)
                ->where('version', 'v1.0')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('ref_template_dokumen')->insert([
                'id' => (string) Str::uuid(),
                'kode' => $kode,
                'nama' => $nama,
                'version' => 'v1.0',
                'file_path' => null,
                'status' => 'DRAFT',
                'effective_date' => '2026-01-01',
                'approved_by' => null,
                'approved_at' => null,
                'sumber' => 'DOKUMEN v2.4 §21',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
