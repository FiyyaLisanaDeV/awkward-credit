<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Batas import
    |--------------------------------------------------------------------------
    |
    | Batas mekanis untuk melindungi server. Nilai bisnis (mis. threshold
    | SLIK, bobot skor) TIDAK boleh berada di sini — letaknya di
    | ref_parameter_kebijakan.
    |
    */

    'max_rows' => (int) env('IMPORT_MAX_ROWS', 5000),

    'max_file_mb' => (int) env('IMPORT_MAX_FILE_MB', 25),

    'allowed_mimes' => [
        'xlsx',
        'xls',
        'csv',
        'pdf',
        'jpg',
        'jpeg',
        'png',
        'zip',
    ],

    'staging_ttl_hours' => 24,

    /*
    |--------------------------------------------------------------------------
    | Profil default
    |--------------------------------------------------------------------------
    |
    | Kode profil disimpan di tabel import_profil (bukan di kode). Daftar ini
    | hanya dipakai seeder untuk membuat profil awal.
    |
    */

    'profiles' => [
        'SLIK_XLSX_IDEB',
        'SLIK_PDF_IDEB',
        'SLIK_TEKS',
        'KTP_OCR',
        'DEBITUR_XLSX',
        'DOKUMEN_ZIP',
        'WORKBOOK_LAMA',
        'PEMBAYARAN_CSV',
    ],

    /*
    |--------------------------------------------------------------------------
    | OCR (opsional; aplikasi tetap berjalan tanpa tesseract)
    |--------------------------------------------------------------------------
    */

    'ocr' => [
        'driver' => env('OCR_DRIVER', 'auto'),
        'lang' => env('OCR_LANG', 'ind'),
        'binary' => env('TESSERACT_PATH', ''),
    ],

];
