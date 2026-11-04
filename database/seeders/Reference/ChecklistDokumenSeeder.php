<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ChecklistDokumenSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['PEM-01', 'KTP Pemohon (berlaku)', 'PEMOHON', true],
            ['PEM-02', 'Kartu Keluarga (KK)', 'PEMOHON', true],
            ['PEM-03', 'Surat Nikah / Cerai (jika menikah/cerai)', 'PEMOHON', false],
            ['PEM-04', 'KTP Suami/Istri (jika menikah)', 'PEMOHON', true],
            ['PEM-05', 'NPWP (untuk plafon sesuai ketentuan berlaku)', 'PEMOHON', false],
            ['PEM-06', 'Surat Keterangan Usaha (Desa/Kelurahan) / NIB / SIUP', 'PEMOHON', true],
            ['PEM-07', 'Surat Persetujuan Pasangan atas Agunan', 'PEMOHON', false],
            ['PEM-08', 'Pas Foto Pemohon & Pasangan', 'PEMOHON', true],
            ['PEM-09', 'Surat Keterangan Domisili (jika alamat KTP berbeda)', 'PEMOHON', false],
            ['PEM-10', 'Slip/Bukti Penghasilan Tambahan (jika ada Suami/Istri PNS/Karyawan)', 'PEMOHON', true],
            ['JAM-01', 'Sertifikat Tanah/Bangunan (SHM/SHGB) atau bukti kepemilikan lain', 'JAMINAN', true],
            ['JAM-02', 'BPKB Kendaraan Bermotor', 'JAMINAN', true],
            ['JAM-03', 'STNK Kendaraan Bermotor (masih berlaku)', 'JAMINAN', true],
            ['JAM-04', 'Bukti Pengikatan Jaminan (APHT/Fidusia/Kuasa Jual)', 'JAMINAN', true],
            ['JAM-05', 'Hasil Taksasi/Penilaian Agunan (internal atau independen)', 'JAMINAN', true],
            ['JAM-06', 'Foto Agunan (kondisi terkini)', 'JAMINAN', true],
            ['JAM-07', 'Bukti Pembayaran PBB/Pajak Kendaraan Terakhir', 'JAMINAN', true],
            ['VER-01', 'Tanggal Kunjungan Lapangan', 'VERIFIKASI', true],
            ['VER-02', 'Nama Petugas / Account Officer', 'VERIFIKASI', true],
            ['VER-03', 'Verifikasi Tempat Tinggal (sesuai KTP)', 'VERIFIKASI', true],
            ['VER-04', 'Verifikasi Tempat Usaha (lokasi, kondisi, keramaian)', 'VERIFIKASI', true],
            ['VER-05', 'Verifikasi Kepemilikan & Fisik Agunan', 'VERIFIKASI', true],
            ['VER-06', 'Trade Checking / Cross-check ke Tetangga, Pemasok, atau Pembeli', 'VERIFIKASI', true],
            ['VER-07', 'Estimasi Omzet Hasil Observasi Lapangan (Rp/bulan)', 'VERIFIKASI', true],
            ['VER-08', 'Kesesuaian Data Lapangan dengan Data yang Diisi Pemohon', 'VERIFIKASI', true],
            ['KES-01', 'Catatan/Kesimpulan Hasil Verifikasi Karakter & Kondisi Usaha', 'VERIFIKASI', true],
            ['KES-02', 'Rekomendasi Petugas Lapangan', 'VERIFIKASI', true],
            ['KES-03', 'Tanda Tangan Petugas Lapangan / Tanggal', 'VERIFIKASI', true],
        ];

        $urutan = 0;

        foreach ($rows as [$kode, $nama, $kategori, $wajib]) {
            $urutan++;

            if (DB::table('ref_checklist_dokumen')->where('kode', $kode)->exists()) {
                continue;
            }

            DB::table('ref_checklist_dokumen')->insert([
                'id' => (string) Str::uuid(),
                'kode' => $kode,
                'nama' => $nama,
                'kategori' => $kategori,
                'wajib' => $wajib,
                'urutan' => $urutan,
                'is_active' => true,
                'sumber' => 'Sheet14',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
