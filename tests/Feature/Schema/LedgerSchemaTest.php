<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('memiliki tabel ledger, simulasi, import, dan pembayaran', function () {
    $expected = [
        'ledger' => ['seq', 'id', 'occurred_at', 'cabang_id', 'actor_id', 'event', 'entity_type', 'entity_id', 'correlation_id', 'idempotency_key', 'payload', 'prev_hash', 'hash', 'ip_address', 'user_agent'],
        'simulasi_kredit' => ['cabang_id', 'user_id', 'permohonan_id', 'label', 'produk_tipe', 'sistem_suku_bunga', 'parameter_kebijakan_id', 'parameter_version', 'engine_version', 'input', 'input_hash', 'output', 'dscr', 'plafon_max', 'skor_5c', 'status_kelayakan', 'durasi_ms', 'ledger_id', 'dibuat_pada'],
        'import_profil' => ['kode', 'nama', 'jenis', 'versi', 'mapping', 'delimiter', 'header_row', 'sheet_name', 'is_active'],
        'import_batch' => ['cabang_id', 'user_id', 'jenis', 'profil_id', 'file_path', 'file_hash', 'status', 'total_baris', 'sukses', 'gagal', 'committed_at', 'ledger_id'],
        'import_baris' => ['batch_id', 'nomor_baris', 'payload', 'normalized', 'errors', 'status', 'target_type', 'target_id'],
        'ekstraksi_dokumen' => ['cabang_id', 'debitur_id', 'jenis', 'file_path', 'file_hash', 'engine', 'engine_version', 'raw_text', 'fields', 'confidence', 'status', 'verified_by'],
        'pembayaran_angsuran' => ['cabang_id', 'kredit_id', 'jadwal_angsuran_id', 'periode', 'tanggal_bayar', 'pokok', 'bunga', 'denda', 'total', 'metode', 'no_bukti', 'sumber', 'dibatalkan_at', 'reversal_of'],
    ];

    foreach ($expected as $table => $columns) {
        expect(Schema::hasTable($table))->toBeTrue("tabel {$table} tidak ada");

        foreach ($columns as $column) {
            expect(Schema::hasColumn($table, $column))->toBeTrue("kolom {$table}.{$column} tidak ada");
        }
    }
});

it('menolak update dan delete ledger', function () {
    $row = [
        'id' => (string) Str::uuid(),
        'occurred_at' => now(),
        'event' => 'SIMULASI_DIJALANKAN',
        'entity_type' => 'simulasi_kredit',
        'entity_id' => (string) Str::uuid(),
        'payload' => json_encode(['uji' => true]),
        'prev_hash' => str_repeat('0', 64),
        'hash' => str_repeat('a', 64),
    ];

    DB::table('ledger')->insert($row);

    expect(fn () => DB::table('ledger')->where('id', $row['id'])->update(['event' => 'ANALISIS_DISIMPAN']))
        ->toThrow(QueryException::class);
});

it('menolak event ledger di luar katalog', function () {
    $row = [
        'id' => (string) Str::uuid(),
        'occurred_at' => now(),
        'event' => 'EVENT_TIDAK_ADA',
        'entity_type' => 'uji',
        'payload' => json_encode([]),
        'prev_hash' => str_repeat('0', 64),
        'hash' => str_repeat('a', 64),
    ];

    expect(fn () => DB::table('ledger')->insert($row))->toThrow(QueryException::class);
});
