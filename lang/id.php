<?php

declare(strict_types=1);

return [

    'auth' => [
        'failed' => 'Email atau kata sandi salah.',
        'inactive' => 'Akun Anda tidak aktif. Hubungi administrator.',
        'throttle' => 'Terlalu banyak percobaan masuk. Coba lagi dalam :seconds detik.',
        'must_change_password' => 'Anda harus mengganti kata sandi terlebih dahulu.',
        'email' => 'Email',
        'password' => 'Kata sandi',
        'remember' => 'Ingat saya',
        'login_title' => 'Masuk',
        'login_hint' => 'Gunakan akun yang diberikan administrator.',
        'submit_login' => 'Masuk',
        'logout' => 'Keluar',
        'change_password' => 'Ganti sandi',
        'change_password_title' => 'Ganti Kata Sandi',
        'change_password_hint' => 'Kata sandi minimal 8 karakter dan tidak boleh sama dengan sandi lama.',
        'current_password' => 'Kata sandi saat ini',
        'new_password' => 'Kata sandi baru',
        'confirm_password' => 'Konfirmasi kata sandi baru',
        'submit_password' => 'Simpan kata sandi',
    ],

    'passwords' => [
        'updated' => 'Kata sandi berhasil diubah.',
    ],

    'nav' => [
        'dashboard' => 'Dasbor',
        'debitur' => 'Debitur',
        'permohonan' => 'Permohonan',
        'simulasi' => 'Simulasi',
        'approval' => 'Persetujuan',
        'dokumen' => 'Dokumen',
        'kredit' => 'Kredit',
        'ledger' => 'Ledger',
        'import' => 'Import',
        'admin' => 'Administrasi',
    ],

    'dashboard' => [
        'title' => 'Dasbor',
        'welcome' => 'Selamat datang, :name.',
        'placeholder_title' => 'Modul permohonan belum aktif',
        'placeholder_body' => 'KPI dan pipeline akan tampil setelah modul permohonan, analisis, dan persetujuan tersedia.',
        'kpi' => [
            'draft' => 'Draft',
            'submitted' => 'Diajukan',
            'approved' => 'Disetujui',
            'active' => 'Aktif',
            'empty' => '—',
        ],
    ],

];
