<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('memiliki tabel kredit, workflow, dan audit', function () {
    $expected = [
        'kredit' => ['permohonan_id', 'no_rekening_kredit', 'no_akad', 'tanggal_cair', 'plafon', 'rate', 'tenor', 'sistem_suku_bunga', 'angsuran_bulanan', 'status', 'closed_at'],
        'jadwal_angsuran' => ['id', 'kredit_id', 'periode', 'tanggal_jatuh_tempo', 'saldo_awal', 'angsuran_pokok', 'bunga', 'total_angsuran', 'saldo_akhir', 'status', 'tanggal_bayar', 'nominal_dibayar', 'denda'],
        'biaya_kredit' => ['kredit_id', 'komponen', 'nilai', 'tipe', 'rate'],
        'asuransi_kredit' => ['kredit_id', 'jenis', 'perusahaan', 'no_polis', 'premi', 'manfaat', 'usia_peserta'],
        'kredit_agunan' => ['kredit_id', 'debitur_aset_id', 'nilai_jaminan', 'pengikatan', 'status'],
        'dokumen' => ['cabang_id', 'permohonan_id', 'template_id', 'jenis_dokumen', 'nomor_dokumen', 'file_path', 'file_hash', 'status_ttd', 'tanggal_ttd', 'sumber', 'deleted_at'],
        'approval_log' => ['permohonan_id', 'level', 'role_id', 'user_id', 'aksi', 'alasan', 'timestamp'],
        'signature_log' => ['dokumen_id', 'pihak', 'nama', 'ttd_hash', 'ip_address', 'timestamp'],
        'checklist_status' => ['permohonan_id', 'checklist_id', 'status', 'nomor_dokumen', 'verified_by', 'verified_at'],
        'memo_pencairan' => ['kredit_id', 'no_memo', 'tanggal_memo', 'dana_bersih', 'terbilang', 'rekening_tujuan', 'status'],
        'pemblokiran_tabungan' => ['kredit_id', 'no_rekening', 'nilai_blokir', 'jangka_waktu_bulan', 'tanggal_blokir', 'status'],
        'audit_log' => ['user_id', 'cabang_id', 'entity_type', 'entity_id', 'aksi', 'old_value', 'new_value', 'ip_address', 'timestamp'],
        'notifikasi' => ['user_id', 'tipe', 'judul', 'pesan', 'is_read', 'read_at'],
    ];

    foreach ($expected as $table => $columns) {
        expect(Schema::hasTable($table))->toBeTrue("tabel {$table} tidak ada");

        foreach ($columns as $column) {
            expect(Schema::hasColumn($table, $column))->toBeTrue("kolom {$table}.{$column} tidak ada");
        }
    }
});

it('mengizinkan insert audit_log', function () {
    DB::table('audit_log')->insert([
        'id' => (string) Str::uuid(),
        'entity_type' => 'uji',
        'aksi' => 'TEST',
    ]);

    expect(DB::table('audit_log')->count())->toBe(1);
});

it('menolak update audit_log', function () {
    $id = (string) Str::uuid();

    DB::table('audit_log')->insert([
        'id' => $id,
        'entity_type' => 'uji',
        'aksi' => 'TEST',
    ]);

    expect(fn () => DB::table('audit_log')->where('id', $id)->update(['aksi' => 'DIUBAH']))
        ->toThrow(QueryException::class);
});

it('menolak delete audit_log', function () {
    $id = (string) Str::uuid();

    DB::table('audit_log')->insert([
        'id' => $id,
        'entity_type' => 'uji',
        'aksi' => 'TEST',
    ]);

    expect(fn () => DB::table('audit_log')->where('id', $id)->delete())
        ->toThrow(QueryException::class);
});
