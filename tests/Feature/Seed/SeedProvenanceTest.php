<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('mengisi referensi sesuai jumlah terdokumentasi', function () {
    $this->seed();

    expect(DB::table('cabang')->count())->toBe(3)
        ->and(DB::table('produk')->count())->toBe(9)
        ->and(DB::table('ref_kategori_agunan_pojk')->count())->toBe(13)
        ->and(DB::table('ref_kolektibilitas')->count())->toBe(5)
        ->and(DB::table('ref_status_pelunasan_internal')->count())->toBe(5)
        ->and(DB::table('ref_checklist_dokumen')->count())->toBe(28)
        ->and(DB::table('ref_parameter_kebijakan')->count())->toBe(1)
        ->and(DB::table('ref_approval_matrix')->count())->toBe(1)
        ->and(DB::table('ref_template_dokumen')->count())->toBe(17)
        ->and(DB::table('import_profil')->count())->toBe(8)
        ->and(DB::table('roles')->count())->toBe(8);
});

it('idempoten saat dijalankan dua kali', function () {
    $this->seed();
    $this->seed();

    expect(DB::table('cabang')->count())->toBe(3)
        ->and(DB::table('produk')->count())->toBe(9)
        ->and(DB::table('users')->count())->toBe(1)
        ->and(DB::table('ref_checklist_dokumen')->count())->toBe(28)
        ->and(DB::table('ref_template_dokumen')->count())->toBe(17);
});

it('mengisi sumber pada semua referensi yang memiliki kolom sumber', function () {
    $this->seed();

    $tables = [
        'ref_kategori_agunan_pojk',
        'ref_kolektibilitas',
        'ref_status_pelunasan_internal',
        'ref_checklist_dokumen',
        'ref_template_dokumen',
        'ref_rate_asuransi_jiwa',
        'ref_rate_asuransi_kebakaran',
        'ref_rate_kendaraan',
        'ref_konversi_suku_bunga',
    ];

    foreach ($tables as $table) {
        $missing = DB::table($table)
            ->where(fn ($query) => $query->whereNull('sumber')->orWhere('sumber', ''))
            ->count();

        expect($missing)->toBe(0, "tabel {$table} memiliki baris tanpa sumber");
    }
});

it('mengaktifkan parameter kebijakan v1.0 dengan 34 kunci', function () {
    $this->seed();

    $row = DB::table('ref_parameter_kebijakan')->where('status', 'ACTIVE')->first();

    expect($row)->not->toBeNull();

    $params = json_decode($row->params, true);

    expect($params)->toBeArray()
        ->and(count($params))->toBe(34)
        ->and((float) $params['minimum_dscr'])->toBe(1.0)
        ->and($params['skor_band_draft'])->toBeTrue();
});
