<?php

declare(strict_types=1);

return [
    'jenis_kelamin' => [
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
    ],
    'status_perkawinan' => [
        'BELUM_MENIKAH' => 'Belum menikah',
        'MENIKAH' => 'Menikah',
        'CERAI_HIDUP' => 'Cerai hidup',
        'CERAI_MATI' => 'Cerai mati',
        'LAINNYA' => 'Lainnya',
    ],
    'produk_tipe' => [
        'TPP' => 'TPP (konsumtif)',
        'MODAL_KERJA' => 'Modal kerja',
        'GRACE_PERIOD' => 'Grace period (balloon)',
    ],
    'sistem_suku_bunga' => [
        'Flat' => 'Flat',
        'Anuitas' => 'Anuitas',
        'Grace Period' => 'Grace period',
    ],
    'kolektibilitas' => [
        1 => 'Lancar',
        2 => 'Dalam perhatian khusus',
        3 => 'Kurang lancar',
        4 => 'Diragukan',
        5 => 'Macet',
    ],
];
