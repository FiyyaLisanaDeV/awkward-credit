<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('memiliki tabel dan kolom sesuai inventaris fase 04', function () {
    $expected = [
        'users' => ['cabang_id', 'username', 'jabatan', 'no_hp', 'is_active', 'must_change_password', 'last_login_at'],
        'debitur' => [
            'id', 'cabang_id', 'nama_ktp', 'nama_ibu_kandung', 'no_ktp', 'no_kk', 'npwp',
            'jenis_kelamin', 'status_perkawinan', 'agama', 'pendidikan',
            'pekerjaan_utama', 'jabatan', 'nama_perusahaan', 'lama_bekerja_tahun', 'penghasilan_bulanan',
            'email', 'no_hp', 'no_wa',
            'alamat_jalan', 'alamat_rt', 'alamat_rw', 'kelurahan', 'kecamatan', 'kabupaten', 'provinsi', 'kode_pos',
            'alamat_tinggal', 'status_tempat_tinggal', 'lama_tinggal_tahun', 'koordinat_rumah',
            'jumlah_keluarga', 'jumlah_tanggungan', 'tempat_lahir', 'tanggal_lahir',
            'sumber_data', 'verified_by', 'verified_at', 'foto_ktp_path', 'foto_debitur_path', 'catatan',
            'deleted_at',
        ],
        'debitur_pasangan' => ['debitur_id', 'nama_ktp', 'jenis_kelamin', 'tanggal_lahir', 'gaji_pokok', 'tpp', 'penghasilan_lain', 'no_ktp', 'is_ahli_waris', 'consent_at'],
        'debitur_usaha' => ['debitur_id', 'nama_usaha', 'bentuk_badan', 'nib', 'npwp_usaha', 'sektor', 'alamat_lokasi', 'koordinat_lokasi', 'lama_usaha_tahun', 'jumlah_karyawan', 'jam_operasional', 'hari_kerja_per_bulan', 'media_penjualan', 'kompetitor', 'omzet_harian', 'omzet_bulanan', 'hpp_persen'],
        'debitur_siklus_usaha' => ['debitur_id', 'frekuensi_belanja', 'satuan_periode', 'modal_kerja_per_siklus', 'pendapatan_kotor_per_siklus', 'biaya_operasional_per_siklus'],
        'debitur_aset' => ['debitur_id', 'urutan', 'jenis_aset', 'kategori_pojk_id', 'jenis_dokumen', 'no_dokumen', 'luas_jumlah', 'harga_taksasi_satuan', 'nilai_taksasi', 'persen_diperhitungkan', 'nilai_diperhitungkan', 'status_kepemilikan', 'nama_pemilik', 'lokasi', 'koordinat', 'njop', 'foto_path', 'deleted_at'],
        'debitur_pengkinian' => ['debitur_id', 'cif', 'tujuan_penggunaan', 'sumber_penghasilan', 'pemasukan_bulanan', 'frekuensi_setoran', 'pengeluaran_bulanan', 'frekuensi_tarikan', 'tanggal_pengkinian', 'processed_by'],
    ];

    foreach ($expected as $table => $columns) {
        expect(Schema::hasTable($table))->toBeTrue("tabel {$table} tidak ada");

        foreach ($columns as $column) {
            expect(Schema::hasColumn($table, $column))->toBeTrue("kolom {$table}.{$column} tidak ada");
        }
    }
});

it('memiliki tabel permission Spatie', function () {
    foreach (['roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions'] as $table) {
        expect(Schema::hasTable($table))->toBeTrue("tabel {$table} tidak ada");
    }
});
