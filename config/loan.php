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
        'display_decimals' => 0,
        'canonical_scale' => 6,
        'ratio_scale' => 4,
        'schedule_scale' => 2,
        'currency' => 'IDR',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pola nomor dokumen (mekanisme, bukan nilai bisnis)
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'per_page' => (int) env('PAGINATION_PER_PAGE', 20),
        'ledger_per_page' => (int) env('PAGINATION_LEDGER_PER_PAGE', 50),
    ],

    'ui' => [
        'dashboard_recent' => 10,
        'dashboard_cache_ttl' => 300,
        'notifikasi_limit' => 10,
    ],

    'golden' => [
        'tpp_path' => env('GOLDEN_TPP_PATH', '../ANALISIS EDDIY (ut kredit TPP).xlsx'),
        'output_path' => 'tests/fixtures/golden/tpp.json',
    ],

    'nomor' => [
        'permohonan' => '{cabang}/{tahun}/{urut}',
        'kredit' => '{cabang}/{tahun}/K/{urut}',
        'dokumen' => '{kode}/{cabang}/{tahun}/{urut}',
        'cif' => 'CIF-{cabang}-{urut}',
        'cif_padding' => 6,
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

    /*
    |--------------------------------------------------------------------------
    | Keamanan sesi & sandi (mekanisme)
    |--------------------------------------------------------------------------
    */

    'security' => [
        'login_max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', 5),
        'login_decay_seconds' => (int) env('LOGIN_DECAY_SECONDS', 60),
        'password_min' => (int) env('PASSWORD_MIN_LENGTH', 8),
        'password_min_production' => (int) env('PASSWORD_MIN_LENGTH_PRODUCTION', 12),
        'slow_query_ms' => (int) env('SLOW_QUERY_MS', 500),
        'admin_initial_password' => env('ADMIN_INITIAL_PASSWORD'),
        'auditor_initial_password' => env('AUDITOR_INITIAL_PASSWORD'),
        'pilot_initial_password' => env('PILOT_INITIAL_PASSWORD'),
        'pii_mask_allow_production' => env('PII_MASK_ALLOW_PRODUCTION', false),
    ],

];
