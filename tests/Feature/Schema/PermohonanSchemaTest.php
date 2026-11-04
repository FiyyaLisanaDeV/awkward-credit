<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('memiliki tabel dan kolom permohonan serta analisis', function () {
    $expected = [
        'permohonan_kredit' => ['no_permohonan', 'debitur_id', 'produk_id', 'plafon_diajukan', 'plafon_rekomendasi', 'suku_bunga_tahunan', 'tenor_diajukan', 'sistem_suku_bunga', 'sumber_pelunasan_balloon', 'perkiraan_penerimaan_balloon', 'status', 'ao_id', 'parameter_kebijakan_id', 'kelengkapan_persen', 'deleted_at'],
        'slik_permintaan' => ['permohonan_id', 'debitur_id', 'tanggal_permintaan', 'jenis', 'sumber', 'operator_id', 'file_hash', 'jumlah_fasilitas', 'total_baki', 'kualitas_terburuk'],
        'permohonan_slik_debitur' => ['permohonan_id', 'slik_permintaan_id', 'urutan', 'nama_lembaga', 'plafon', 'baki_debet', 'kolektibilitas_id', 'hari_tunggakan', 'frekuensi_tunggakan_12m', 'tunggakan_pokok', 'deleted_at'],
        'permohonan_slik_pasangan' => ['permohonan_id', 'urutan', 'plafon', 'baki_debet', 'kolektibilitas_id', 'deleted_at'],
        'permohonan_riwayat_internal' => ['permohonan_id', 'no_rekening_pinjaman', 'status_pelunasan_id'],
        'analisis_neraca' => ['permohonan_id', 'parameter_kebijakan_id', 'total_aktiva', 'total_pasiva', 'selisih_cek'],
        'analisis_laba_rugi' => ['jumlah_bulan_berjalan', 'total_pendapatan', 'surplus_per_bulan', 'surplus_6_bulan'],
        'analisis_rasio' => ['equity_to_asset', 'roa_annualized', 'roe_annualized'],
        'analisis_modal_kerja' => ['kebutuhan_mk_cash_turnover', 'kebutuhan_mk_wcd', 'gap_mk', 'hari_persediaan'],
        'analisis_kapasitas_bayar' => ['dscr_diajukan', 'dscr_rekomendasi', 'plafon_max_flat', 'plafon_max_anuitas', 'plafon_max_grace', 'ltv', 'idir', 'rasio_kecukupan_balloon', 'biaya_lain_bulanan'],
        'analisis_sensitivitas' => ['urutan', 'nama_skenario', 'dscr', 'status_layak'],
        'analisis_scoring_5c' => ['skor_total', 'rating', 'overridden', 'justifikasi_capacity', 'bobot_character'],
        'analisis_red_flags' => ['kode', 'level', 'teratasi', 'mitigasi'],
        'analisis_rekomendasi' => ['plafon_final', 'tenor_final', 'angsuran_final', 'narasi'],
        'analisis_7aspek' => ['aspek_manfaat', 'aspek_keuangan', 'kesimpulan', 'usul'],
    ];

    foreach ($expected as $table => $columns) {
        expect(Schema::hasTable($table))->toBeTrue("tabel {$table} tidak ada");

        foreach ($columns as $column) {
            expect(Schema::hasColumn($table, $column))->toBeTrue("kolom {$table}.{$column} tidak ada");
        }
    }
});

it('menegakkan BR-04 (grace period wajib sumber pelunasan balloon)', function () {
    $definition = constraintDefinition('permohonan_kredit', 'permohonan_kredit_balloon_check');

    expect($definition)->not->toBeNull()
        ->and($definition)->toContain('Grace Period')
        ->and($definition)->toContain('sumber_pelunasan_balloon')
        ->and($definition)->toContain('perkiraan_penerimaan_balloon');
});

it('membatasi satu baris analisis tunggal per permohonan', function () {
    foreach (['analisis_neraca', 'analisis_laba_rugi', 'analisis_kapasitas_bayar', 'analisis_scoring_5c'] as $table) {
        $sql = (string) DB::table('pg_indexes')
            ->where('tablename', $table)
            ->where('indexdef', 'like', '%UNIQUE%permohonan_id%')
            ->value('indexdef');

        expect($sql)->not->toBe('');
    }
});
