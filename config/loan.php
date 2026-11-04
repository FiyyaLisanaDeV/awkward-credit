<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas perhitungan
    |--------------------------------------------------------------------------
    |
    | Nilai ini bukan nilai bisnis: hanya penanda versi mesin kalkulasi yang
    | ikut disimpan pada setiap simulasi agar hasil dapat direproduksi.
    |
    */

    'engine_version' => env('APP_ENGINE_VERSION', 'calc-v1.0.0'),

    'timezone' => env('APP_TIMEZONE', 'Asia/Makassar'),

    /*
    |--------------------------------------------------------------------------
    | Uang & pembulatan
    |--------------------------------------------------------------------------
    */

    'money' => [
        'scale' => 2,
        'rounding' => PHP_ROUND_HALF_UP,
        'display_decimals' => 0,
        'currency' => 'IDR',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pola nomor dokumen (mekanisme, bukan nilai bisnis)
    |--------------------------------------------------------------------------
    */

    'nomor' => [
        'permohonan' => '{cabang}/{tahun}/{urut}',
        'kredit' => '{cabang}/{tahun}/K/{urut}',
        'dokumen' => '{kode}/{cabang}/{tahun}/{urut}',
        'urut_padding' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Unggahan & penyimpanan
    |--------------------------------------------------------------------------
    */

    'upload' => [
        'max_mb' => (int) env('IMPORT_MAX_FILE_MB', 25),
        'mimes' => ['pdf', 'jpg', 'jpeg', 'png', 'xlsx', 'xls', 'csv', 'zip'],
    ],

    'dokumen' => [
        'disk' => env('FILESYSTEM_DISK', 'local'),
        'path' => 'documents',
    ],

    'pii' => [
        'disk' => env('FILESYSTEM_DISK', 'local'),
        'path' => 'pii',
        'cipher' => 'aes-256-gcm',
    ],

];
