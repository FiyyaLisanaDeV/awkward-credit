# INVENTARIS DATA — Loan Analytic BPR

| Meta | Nilai |
|---|---|
| Versi | 2.4 |
| Tanggal | 2026-11-05 |
| Cakupan | 60 tabel bisnis + 9 tabel sistem, 34 enum domain/teknis, 23 event ledger, 8 adapter import |
| Sumber angka | workbook (sheet+baris) / pasal POJK / `[H]` persetujuan manusia — lihat §12 |

> Kolom di dokumen ini adalah **kontrak**. Implementasi wajib sama; perbedaan harus lewat ADR + pembaruan dokumen ini. Dijaga `SchemaInventoryTest`.

## §0. Konvensi kolom

- `id uuid` PK (`HasUuids`) kecuali `users` (bigint bawaan Laravel), `jadwal_angsuran` (`bigserial`), dan `ledger` (`seq bigserial` + `id uuid`).
- Semua tabel bisnis: `created_at`, `updated_at`; soft delete (`deleted_at`) hanya pada entitas yang bisa dibatalkan.
- `cabang_id uuid NOT NULL` + FK `cabang` pada tabel bisnis (kecuali `ref_*` global dan `users` yang boleh null untuk admin pusat).
- Uang `numeric(15,2)`; rasio `numeric(10,4)`; persen tarif `numeric(5,4)` (0,18 = 18%); tanggal `date`; waktu peristiwa `timestamptz`.
- Enum disimpan sebagai string pendek berbahasa Indonesia sesuai enum (§8); CHECK dibangkitkan dari enum.
- Semua FK `restrictOnDelete` kecuali relasi anak yang eksplisit `cascade` (permohonan → analisis/SLIK/dokumen/checklist, kredit → jadwal/biaya/asuransi).
- Unique parsial (`WHERE deleted_at IS NULL`) untuk NIK debitur, `produk(cabang_id,kode)`, `debitur_aset(debitur_id,urutan)`.
- Setiap tabel `ref_*` wajib punya `sumber varchar(150) NOT NULL` (§12).

## §1. Master / Referensi (13)

### cabang
```
id              uuid pk
kode            varchar(10) unique
nama            varchar(100)
alamat          text
telepon         varchar(20) null
npwp            varchar(20) null
is_active       boolean default true
```

### produk
```
id              uuid pk
cabang_id       uuid fk cabang
kode            varchar(20)
nama            varchar(100)
tipe            varchar(20)          -- enum ProdukTipe: TPP|MODAL_KERJA|GRACE_PERIOD
rate_default    numeric(5,4)
tenor_min       smallint
tenor_max       smallint
biaya_default   jsonb                -- {"provisi":0.01,...} dari parameter
is_active       boolean default true
unique (cabang_id, kode) where deleted_at is null
```

### ref_kategori_agunan_pojk
```
id              uuid pk
nama            varchar(100)
persen_max      numeric(5,4)  check (persen_max between 0 and 1)
pasal_pojk      varchar(50)   -- "Pasal 20 ayat (1) huruf b"
catatan         text null
sumber          varchar(150)  -- "Sheet 2!34-46"
effective_date  date
is_active       boolean default true
approved_by     bigint null fk users
approved_at     timestamptz null
```

### ref_kolektibilitas
```
id, kode smallint unique check (kode between 1 and 5), nama varchar(50),
deskripsi text, min_hari_tunggakan smallint, max_hari_tunggakan smallint, sumber
```

### ref_status_pelunasan_internal
```
id, kode varchar(10) unique, nama varchar(50), flag_bermasalah boolean default false, sumber
```

### ref_rate_asuransi_jiwa
```
id, perusahaan varchar(50), usia_min smallint, usia_max smallint,
tenor_min smallint, tenor_max smallint, rate_permil numeric(10,4),
effective_date date, sumber;  check (usia_min <= usia_max)
```

### ref_rate_asuransi_kebakaran
```
id, perusahaan varchar(50), usia_min smallint, usia_max smallint,
masa_asuransi smallint, rate_permil numeric(10,4), effective_date date, sumber
```

### ref_rate_kendaraan
```
id, perusahaan varchar(50),
jenis_pertanggungan varchar(10) check in ('TLO','ALLRISK'),
harga_min numeric(15,2), harga_max numeric(15,2),
rate numeric(10,6), effective_date date, sumber
```

### ref_konversi_suku_bunga
```
id, tenor_bulan smallint, kategori varchar(20),
flat numeric(8,6), efektif numeric(8,6), anuitas numeric(8,6),
effective_date date, sumber;  unique (tenor_bulan, kategori, effective_date)
```

### ref_checklist_dokumen
```
id, kode varchar(20) unique, nama varchar(200),
kategori varchar(20) check in ('PEMOHON','JAMINAN','VERIFIKASI'),
wajib boolean default true, urutan smallint, is_active boolean default true, sumber
```

### ref_parameter_kebijakan (versioned)
```
id, version varchar(20) unique, effective_date date,
params jsonb, status varchar(20) check in ('DRAFT','ACTIVE','ARCHIVED'),
created_by bigint fk users, approved_by bigint null, approved_at timestamptz null
partial unique index: only one row with status='ACTIVE'
```
Kunci `params` (34): `margin_kapasitas_bayar`, `minimum_dscr`, `dscr_ideal`, `maksimum_ltv`,
`hari_kerja_per_tahun`, `sensitivitas_omzet_ringan`, `sensitivitas_omzet_berat`, `sensitivitas_biaya`,
`bobot_character`, `bobot_capacity`, `bobot_capital`, `bobot_collateral`, `bobot_condition`,
`rating_sangat_layak_min`, `rating_layak_min`, `rating_bersyarat_min`,
`threshold_slik_macet`, `threshold_slik_kol3`, `threshold_slik_nominal`,
`rasio_balloon_layak`, `rasio_balloon_perhatian`, `idir_threshold_tpp`,
`plafon_kewajaran_multiplier`, `biaya_provisi_default`, `biaya_administrasi_default`,
`biaya_survey_default`, `biaya_lain_bulanan`, `hari_persediaan_default`,
`skor_band_dscr`, `skor_band_equity_asset`, `skor_band_ltv`, `skor_band_slik`,
`bobot_kelengkapan` (map).
Empat kunci bertanda `DRAFT` sampai `[H]`: `skor_band_*`. Nilai awal dari workbook: 0,5 · 1,0 · 1,25 · 0,7 · 360 · 0,10 · 0,20 · 0,10 · 0,25 · 0,30 · 0,15 · 0,20 · 0,10 · 4,0 · 3,0 · 2,0 · 2 · 1 · 1.000.000 · 1,10 · 1,00 · 0,35 · 1,20 · 0,01 · 0,01 · 0,02.

### ref_approval_matrix (versioned)
```
id, version varchar(20) unique, effective_date date, matrix jsonb,
status check in ('DRAFT','ACTIVE','ARCHIVED'), created_by, approved_by, approved_at
matrix: [{"plafon_min":0,"plafon_max":10000000,"level":1,"role":"AO"}, ...]
```

### ref_template_dokumen
```
id, kode varchar(20) check kode in ('DOC-01'..'DOC-17'), nama varchar(200),
version varchar(20), file_path varchar(500),
status varchar(20) check in ('DRAFT','REVIEW','APPROVED','DEPRECATED'),
effective_date date, approved_by uuid null, approved_at timestamptz null, sumber
unique (kode, version)
```

## §2. Auth (6)

### users
```
id bigint pk (bawaan Laravel), cabang_id uuid null fk cabang, name varchar(100), username varchar(50) unique,
email varchar(100) unique, password varchar(255), jabatan varchar(100) null,
no_hp varchar(20) null, is_active boolean default true,
must_change_password boolean default false, last_login_at timestamptz null,
email_verified_at timestamptz null, remember_token, deleted_at
```
### permissions
```
id bigint pk, name varchar(125) unique, guard_name varchar(125), timestamps
```

### roles
```
id bigint pk, name varchar(125) unique, guard_name varchar(125), timestamps
```

### model_has_permissions
```
permission_id fk, model_type varchar, model_id uuid/bigint — PK gabungan
```

### model_has_roles
```
role_id fk, model_type varchar, model_id uuid/bigint — PK gabungan
```

### role_has_permissions
```
permission_id fk, role_id fk — PK gabungan
```

8 role di-seed: AO, MGR_PEMASARAN, KOORDINATOR, DIRUT, KOMITE, ADMIN, LEGAL, AUDITOR.

## §3. Debitur (6)

### debitur
```
id, cabang_id
nama_ktp varchar(100), nama_ibu_kandung varchar(100) null
no_ktp char(16), no_kk char(16) null, npwp varchar(20) null
jenis_kelamin char(1) check in ('L','P'), status_perkawinan varchar(20),
agama varchar(20) null, pendidikan varchar(10) null
pekerjaan_utama varchar(100) null, jabatan varchar(100) null,
nama_perusahaan varchar(100) null, lama_bekerja_tahun smallint null,
penghasilan_bulanan numeric(15,2) null
email varchar(100) null, no_hp varchar(20), no_wa varchar(20) null
alamat_jalan text, alamat_rt varchar(5), alamat_rw varchar(5),
kelurahan varchar(100), kecamatan varchar(100), kabupaten varchar(100),
provinsi varchar(100), kode_pos varchar(10) null
alamat_tinggal text null, status_tempat_tinggal varchar(20) null,
lama_tinggal_tahun smallint null, koordinat_rumah varchar(50) null
jumlah_keluarga smallint default 0, jumlah_tanggungan smallint default 0
tempat_lahir varchar(50), tanggal_lahir date
sumber_data varchar(10) check in ('MANUAL','IMPORT','OCR') default 'MANUAL'
verified_by bigint null fk users, verified_at timestamptz null
foto_ktp_path varchar(500) null, foto_debitur_path varchar(500) null
catatan text null
unique (no_ktp) where deleted_at is null
```
Usia ≥ 17 tahun divalidasi di FormRequest (bukan CHECK; menjaga imutabilitas constraint).

### debitur_pasangan
```
id, debitur_id unique fk cascade, nama_ktp, jenis_kelamin, tanggal_lahir date,
pekerjaan varchar(100) null, jenis_usaha varchar(100) null,
gaji_pokok numeric(15,2) default 0, tpp numeric(15,2) default 0,
penghasilan_lain numeric(15,2) default 0, no_ktp char(16) null, no_hp varchar(20) null,
is_ahli_waris boolean default true, consent_at timestamptz null
```

### debitur_usaha
```
id, debitur_id unique fk cascade, nama_usaha varchar(100) null,
bentuk_badan varchar(30) null, nib varchar(30) null, npwp_usaha varchar(20) null,
sektor varchar(100) null, alamat_lokasi text null, koordinat_lokasi varchar(50) null,
lama_usaha_tahun smallint null, jumlah_karyawan smallint null,
jam_operasional varchar(50) null, hari_kerja_per_bulan smallint null,
media_penjualan varchar(100) null, kompetitor text null,
omzet_harian numeric(15,2) null, omzet_bulanan numeric(15,2) null,
hpp_persen numeric(5,4) null, latar_belakang text, siklus_usaha_desc text
```

### debitur_siklus_usaha
```
id, debitur_id unique fk cascade,
frekuensi_belanja numeric(10,2) check (frekuensi_belanja >= 0),
satuan_periode varchar(10) check in ('MINGGUAN','BULANAN'),
modal_kerja_per_siklus numeric(15,2) check (modal_kerja_per_siklus >= 0),
pendapatan_kotor_per_siklus numeric(15,2) check (pendapatan_kotor_per_siklus >= 0),
biaya_operasional_per_siklus numeric(15,2) default 0
```

### debitur_aset
```
id, debitur_id fk cascade, urutan smallint, jenis_aset varchar(100),
kategori_pojk_id fk ref_kategori_agunan_pojk,
jenis_dokumen varchar(20) null, no_dokumen varchar(50) null,
tanggal_dokumen date null, berlaku_sampai date null,
luas_jumlah numeric(15,2) check (luas_jumlah > 0), satuan varchar(20) null,
harga_taksasi_satuan numeric(15,2) check (>= 0),
nilai_taksasi numeric(15,2) check (>= 0),
persen_diperhitungkan numeric(5,4) check (between 0 and 1),
nilai_diperhitungkan numeric(15,2),
status_kepemilikan varchar(30), nama_pemilik varchar(100) null,
hubungan_pemohon varchar(50) null, lokasi text null, koordinat varchar(50) null,
njop numeric(15,2) null, penilai varchar(100) null, tanggal_penilaian date null,
foto_path varchar(500) null, catatan text null
unique (debitur_id, urutan) where deleted_at is null
```
`nilai_diperhitungkan` dihitung di aplikasi (bukan generated column).

### debitur_pengkinian
```
id, debitur_id fk, cif varchar(20) null, tujuan_penggunaan varchar(100),
sumber_penghasilan varchar(100), pemasukan_bulanan numeric(15,2),
frekuensi_setoran smallint, pengeluaran_bulanan numeric(15,2),
frekuensi_tarikan smallint, tanggal_pengkinian date,
processed_by bigint null fk users, catatan text null
```

## §4. Permohonan & SLIK (5)

### permohonan_kredit
```
id, cabang_id, no_permohonan varchar(50) unique, debitur_id fk, produk_id fk,
sektor varchar(100) null, tujuan_penggunaan varchar(200) null,
plafon_diajukan numeric(15,2) check (> 0), plafon_rekomendasi numeric(15,2) null,
suku_bunga_tahunan numeric(5,4) check (> 0 and <= 0.5),
tenor_diajukan smallint check (1..360), tenor_rekomendasi smallint null,
sistem_suku_bunga varchar(20) check in ('Flat','Anuitas','Grace Period'),
kegunaan_pinjaman text, pinjaman_ke smallint default 1,
sumber_pelunasan_balloon text null, perkiraan_penerimaan_balloon numeric(15,2) null,
sumber_pembayaran_angsuran varchar(100) null,
tanggal_target_realisasi date null, prioritas varchar(10) default 'NORMAL',
status varchar(30) default 'DRAFT', ao_id fk users,
parameter_kebijakan_id fk ref_parameter_kebijakan,
tanggal_permohonan date, catatan_ao text null,
kelengkapan_persen numeric(5,2) default 0
check br04: (sistem_suku_bunga = 'Grace Period' and sumber_pelunasan_balloon is not null
             and perkiraan_penerimaan_balloon is not null) or sistem_suku_bunga <> 'Grace Period'
check: plafon_rekomendasi is null or plafon_rekomendasi <= plafon_diajukan
index: (cabang_id, status, tanggal_permohonan desc) where deleted_at is null; (debitur_id); (ao_id)
```

### slik_permintaan
```
id, permohonan_id fk cascade, debitur_id fk, tanggal_permintaan date,
jenis varchar(20) check in ('INDIVIDU','BADAN'), tujuan varchar(50),
no_referensi varchar(50) null, operator_id fk users,
file_path varchar(500) null, file_hash char(64) null,
sumber varchar(20) check in ('PORTAL','IMPORT','MANUAL'),
jumlah_fasilitas smallint default 0, total_plafon numeric(15,2) default 0,
total_baki numeric(15,2) default 0, kualitas_terburuk smallint null, catatan text null
```

### permohonan_slik_debitur
```
id, permohonan_id fk cascade, slik_permintaan_id fk null, urutan smallint,
nama_lembaga varchar(100), jenis_fasilitas varchar(30), jenis_penggunaan varchar(50) null,
no_fasilitas varchar(50) null, plafon numeric(15,2) check (>= 0),
baki_debet numeric(15,2) check (>= 0),               -- TIDAK dipaksa <= plafon (lihat ADR-16)
suku_bunga numeric(5,4) null, jangka_waktu_bulan smallint null,
angsuran_bulanan numeric(15,2) null, kolektibilitas_id fk ref_kolektibilitas,
kondisi varchar(20) null, tanggal_mulai date null, tanggal_tunggakan date null,
hari_tunggakan smallint null, frekuensi_tunggakan_12m smallint null,
tunggakan_pokok numeric(15,2) default 0, tunggakan_bunga numeric(15,2) default 0,
jenis_jaminan varchar(50) null, nilai_jaminan numeric(15,2) null,
penjamin varchar(100) null, tanggal_data date null, catatan text null
unique (permohonan_id, urutan) where deleted_at is null
```
Anomali `baki > plafon` dicatat sebagai peringatan data-quality, bukan blokir.

### permohonan_slik_pasangan
```
id, permohonan_id fk cascade, slik_permintaan_id fk null, urutan,
nama_lembaga, jenis_fasilitas, no_fasilitas, plafon, baki_debet,
kolektibilitas_id, kondisi, tanggal_data, catatan
unique (permohonan_id, urutan) where deleted_at is null
```

### permohonan_riwayat_internal
```
id, permohonan_id fk cascade, no_rekening_pinjaman varchar(30) null,
plafon numeric(15,2) null, tanggal_cair date null,
tanggal_lunas_jatuh_tempo date null,
kolektibilitas_tertinggi_id fk null, status_pelunasan_id fk null, catatan text null
```

## §5. Analisis (10)

Semua tabel analisis: `permohonan_id UUID UNIQUE FK cascade`, `parameter_kebijakan_id FK` (BR-08), `timestamps`. Uang `numeric(15,2) default 0`; rasio `numeric(10,4)`.

### analisis_neraca
`kas_inventaris, bank_simpanan, piutang, persediaan, sewa_dibayar_dimuka, biaya_dibayar_dimuka, pajak_dibayar_dimuka, investasi_jangka_panjang, tanah_bangunan, kebun, kendaraan, aset_lain, jumlah_aktiva_lancar, jumlah_aktiva_tetap, total_aktiva, hutang_pendek, hutang_pajak, hutang_gaji, hutang_panjang, pinjaman_bank, pinjaman_lain, prive, jumlah_hutang, rugi_laba_ditahan, ekuitas_modal_disetor, jumlah_modal, total_pasiva, selisih_cek, catatan text null`

### analisis_laba_rugi
`jumlah_bulan_berjalan smallint default 6, penghasilan_pemohon, penghasilan_pasangan_gaji, penghasilan_lain_pemohon, penghasilan_pasangan_tpp, hpp, biaya_listrik_air, biaya_transport, biaya_sewa, biaya_gaji, biaya_pendidikan, biaya_kesehatan, biaya_konsumsi, biaya_cicilan_lain, biaya_telepon, biaya_kebersihan, biaya_lain, total_pendapatan, total_pengeluaran, laba_kotor, penyusutan, pajak, laba_bersih, angsuran_kredit_berjalan, surplus_per_bulan, surplus_6_bulan, catatan text null`

### analisis_rasio
`current_ratio, quick_ratio, cash_ratio, debt_to_asset, debt_to_equity, equity_to_asset, net_profit_margin, roa_annualized, roe_annualized` (numeric(10,4)).

### analisis_modal_kerja
`periode_perputaran_hari numeric(10,2), biaya_operasional_tahunan, perputaran_kas_per_tahun numeric(10,4), kebutuhan_mk_cash_turnover, pembelian_bulanan, hari_persediaan numeric(10,2), kebutuhan_mk_wcd, mk_tersedia, rata_rata_kebutuhan_mk, gap_mk, omzet_bulanan, kebutuhan_mk_persen_omzet numeric(10,4), catatan`

### analisis_kapasitas_bayar
`rpc_bulanan, dscr_diajukan numeric(10,4), status_dscr_diajukan varchar(20), surplus_setelah_angsuran_diajukan, dscr_rekomendasi numeric(10,4), status_dscr_rekomendasi, surplus_setelah_angsuran_rekomendasi, plafon_max_flat, plafon_max_anuitas, plafon_max_grace, suku_bunga_efektif_diajukan numeric(10,4), suku_bunga_efektif_rekomendasi, ltv numeric(10,4), status_kewajaran_plafon varchar(30), total_pembayaran_jatuh_tempo, rasio_kecukupan_balloon numeric(10,4), status_kecukupan_balloon varchar(50), idir numeric(10,4), status_idir varchar(20), biaya_lain_bulanan numeric(15,2) default 0, catatan`

### analisis_sensitivitas
`urutan smallint, nama_skenario varchar(100), delta_omzet numeric(5,4) default 0, delta_biaya numeric(5,4) default 0, total_pendapatan, total_pengeluaran_non_angsuran, surplus_baru, rpc_baru, dscr numeric(10,4), status_layak boolean`; `unique(permohonan_id, urutan)`; 3 baris default dari parameter sensitivitas.

### analisis_scoring_5c
`skor_character numeric(3,2), skor_capacity, skor_capital, skor_collateral, skor_condition, bobot_character numeric(5,4), bobot_capacity, bobot_capital, bobot_collateral, bobot_condition, skor_tertimbang numeric(5,4), skor_total numeric(5,4), rating varchar(50), overridden boolean default false, override_reason text null, justifikasi_character text null, justifikasi_capacity text null, justifikasi_capital text null, justifikasi_collateral text null, justifikasi_condition text null`

### analisis_red_flags
`kode varchar(50), level varchar(20) check in ('INFO','WARNING','CRITICAL'), pesan text, sumber varchar(30) default 'SISTEM', teratasi boolean default false, mitigasi text null, pemilik_mitigasi varchar(100) null, tenggat date null`; `unique(permohonan_id, kode)`; index `level where level='CRITICAL'`.

### analisis_rekomendasi
`rekomendasi_sistem varchar(100), narasi text, plafon_final, tenor_final smallint, rate_final numeric(5,4), angsuran_final, syarat text null, covenant text null, catatan text null`

### analisis_7aspek
`aspek_manfaat, aspek_teknis, aspek_manajemen, aspek_pemasaran, aspek_keuangan, aspek_jaminan (text null), kesimpulan text, usul text`

## §6. Kredit, Workflow, Audit (13)

### kredit
```
id, cabang_id, permohonan_id fk unique, no_rekening_kredit varchar(50) unique,
no_akad varchar(50) null, tanggal_akad date null, tanggal_cair date null,
plafon numeric(15,2), rate numeric(5,4), tenor smallint,
sistem_suku_bunga varchar(20), angsuran_bulanan numeric(15,2),
total_bunga numeric(15,2), total_pembayaran numeric(15,2),
sumber_pelunasan_balloon text null, rekening_pencairan varchar(50),
rekening_potong_tpp varchar(50) null, tujuan_penggunaan varchar(200) null,
sektor varchar(100) null, status varchar(30) default 'ACTIVE',
closed_at timestamptz null, catatan text null
index (status) where status='ACTIVE'
```

### jadwal_angsuran
```
id bigserial pk, kredit_id fk cascade, periode smallint,
tanggal_jatuh_tempo date, saldo_awal numeric(15,2),
angsuran_pokok numeric(15,2), bunga numeric(15,2),
total_angsuran numeric(15,2), saldo_akhir numeric(15,2),
status varchar(20) default 'BELUM_BAYAR', tanggal_bayar date null,
nominal_dibayar numeric(15,2) default 0, denda numeric(15,2) default 0
unique (kredit_id, periode); index (kredit_id, tanggal_jatuh_tempo)
```

### biaya_kredit
`kredit_id fk cascade, komponen varchar(50), nilai numeric(15,2), tipe varchar(20) check in ('PERSEN','NOMINAL'), rate numeric(10,4) null, keterangan text null`; `unique(kredit_id, komponen)`.

### asuransi_kredit
`kredit_id fk cascade, jenis varchar(30) check (JIWA_AL_AMIN|JIWA_JASINDO|KEBAKARAN_BUMIDA|KENDARAAN_JASINDO|LAINNYA), perusahaan varchar(100), no_polis varchar(50) null, premi numeric(15,2), manfaat numeric(15,2) null, usia_peserta smallint null, tanggal_mulai date null, tanggal_berakhir date null, status varchar(20) default 'DRAFT'`.

### kredit_agunan
`kredit_id fk cascade, debitur_aset_id fk, nilai_jaminan numeric(15,2), pengikatan varchar(50) null, no_pengikatan varchar(50) null, tanggal_ikatan date null, status varchar(20) default 'AKTIF'`; `unique(kredit_id, debitur_aset_id)`.

### dokumen
```
id, cabang_id, permohonan_id fk cascade, template_id fk ref_template_dokumen null,
jenis_dokumen varchar(20), nomor_dokumen varchar(50) null,
tanggal_dokumen date null, file_path varchar(500) null, file_hash char(64) null,
status_ttd varchar(20) default 'DRAFT', tanggal_ttd date null,
ukuran_bytes integer null, sumber varchar(20) default 'GENERATE',
catatan text null
unique (permohonan_id, jenis_dokumen, file_hash) where file_hash is not null
index (jenis_dokumen, status_ttd)
```

### approval_log
`id, cabang_id, permohonan_id fk cascade, level smallint, role_id fk, user_id fk, aksi varchar(20) check in ('APPROVE','REJECT','RETURN','HOLD'), alasan text null, timestamp timestamptz default now()`; index `(permohonan_id, timestamp desc)`.

### signature_log
`id, dokumen_id fk cascade, pihak varchar(20) check in ('DEBITUR','BPR','SAKSI'), nama varchar(100), jabatan varchar(100) null, ttd_hash char(64) null, ttd_file_path varchar(500) null, ip_address inet null, timestamp timestamptz default now()`.

### checklist_status
`id, permohonan_id fk cascade, checklist_id fk ref_checklist_dokumen, status varchar(20) check in ('ADA','TIDAK_ADA','NA','TIDAK_WAJIB'), nomor_dokumen varchar(50) null, tanggal_dokumen date null, catatan text null, verified_by uuid null, verified_at timestamptz null`; `unique(permohonan_id, checklist_id)`.

### memo_pencairan
`id, kredit_id fk unique cascade, no_memo varchar(50) null, tanggal_memo date, dana_bersih numeric(15,2), terbilang varchar(500), rekening_tujuan varchar(50), atas_nama varchar(100), status varchar(20) default 'DRAFT', approved_by uuid null, approved_at timestamptz null`.

### pemblokiran_tabungan
`id, kredit_id fk cascade, no_rekening varchar(50), nilai_blokir numeric(15,2), jangka_waktu_bulan smallint, alasan text null, tanggal_blokir date, tanggal_buka date null, status varchar(20) default 'AKTIF'`; index `(status) where status='AKTIF'`.

### audit_log
`id uuid, cabang_id null, user_id null, entity_type varchar(50), entity_id uuid null, aksi varchar(50), old_value jsonb null, new_value jsonb null, ip_address inet null, user_agent text null, timestamp timestamptz default now()`; index `(entity_type, entity_id, timestamp desc)`, `(user_id, timestamp desc)`, GIN `old_value/new_value`. **Immutable via trigger.**

### notifikasi
`id, cabang_id, user_id fk cascade, tipe varchar(30), judul varchar(200), pesan text, entity_type varchar(50) null, entity_id uuid null, is_read boolean default false, created_at, read_at timestamptz null`; index `(user_id, is_read, created_at desc) where is_read=false`.

## §7. Simulasi, Ledger, Import, Pembayaran (7)

### ledger
```
seq bigserial unique, id uuid pk, occurred_at timestamptz not null,
dibuat_pada timestamptz default now(), cabang_id uuid null, actor_id uuid null,
event varchar(40) not null, entity_type varchar(50) not null, entity_id uuid null,
correlation_id uuid null, idempotency_key varchar(100) null unique,
payload jsonb not null, prev_hash char(64) not null, hash char(64) not null,
ip_address inet null, user_agent text null
index (entity_type, entity_id, occurred_at desc), (event, occurred_at desc),
      (cabang_id, occurred_at desc), (correlation_id)
trigger: before update or delete -> raise exception
```

### simulasi_kredit
```
id uuid, cabang_id, user_id fk, permohonan_id null fk, debitur_id null fk,
label varchar(100), produk_tipe varchar(20), sistem_suku_bunga varchar(20),
parameter_kebijakan_id fk, parameter_version varchar(20),
engine_version varchar(30), input jsonb, input_hash char(64),
output jsonb, plafon numeric(15,2) null, rate numeric(5,4) null, tenor smallint null,
angsuran numeric(15,2) null, dscr numeric(10,4) null, plafon_max numeric(15,2) null,
ltv numeric(10,4) null, rasio_balloon numeric(10,4) null, idir numeric(10,4) null,
skor_5c numeric(5,4) null, rating varchar(50) null, status_kelayakan varchar(30) null,
jumlah_red_flag smallint default 0, durasi_ms integer, ip_address inet null,
user_agent text null, ledger_id uuid null, dibuat_pada timestamptz default now()
index (permohonan_id, dibuat_pada desc), (debitur_id, ...), (user_id, ...), (cabang_id, ...), (input_hash)
```

### import_profil
`id, kode varchar(40) unique, nama varchar(100), jenis varchar(20), versi smallint default 1, mapping jsonb, delimiter varchar(5) null, header_row smallint default 1, sheet_name varchar(100) null, is_active boolean default true`.

### import_batch
`id, cabang_id, user_id, jenis varchar(20), profil_id fk null, file_path varchar(500), file_hash char(64), status varchar(20) check in ('PREVIEW','COMMITTED','FAILED','REVERTED'), total_baris int default 0, sukses int default 0, gagal int default 0, committed_at timestamptz null, ledger_id uuid null, catatan text null`; `unique(jenis, file_hash)`.

### import_baris
`id, batch_id fk cascade, nomor_baris int, payload jsonb, normalized jsonb null, errors jsonb null, status varchar(20) check in ('VALID','INVALID','COMMITTED','SKIPPED'), target_type varchar(50) null, target_id uuid null`.

### ekstraksi_dokumen
`id, cabang_id, debitur_id null fk, permohonan_id null fk, jenis varchar(20) check in ('KTP','KK','NPWP'), file_path varchar(500), file_hash char(64), engine varchar(30), engine_version varchar(30) null, raw_text text null, fields jsonb, confidence jsonb null, status varchar(20) check in ('DRAFT','VERIFIED','REJECTED'), verified_by uuid null, verified_at timestamptz null`.

### pembayaran_angsuran
`id, cabang_id, kredit_id fk, jadwal_angsuran_id fk null, periode smallint null, tanggal_bayar date, pokok numeric(15,2), bunga numeric(15,2), denda numeric(15,2) default 0, total numeric(15,2), metode varchar(20) check in ('TUNAI','TRANSFER','POTONG_TPP','LAINNYA'), no_bukti varchar(50) null, keterangan text null, sumber varchar(20) default 'MANUAL', dibatalkan_at timestamptz null, reversal_of uuid null fk pembayaran_angsuran`; index `(kredit_id, tanggal_bayar)`.

## §8. Enum (34)

| Enum | Nilai |
|---|---|
| ProdukTipe | TPP, MODAL_KERJA, GRACE_PERIOD |
| SistemSukuBunga | Flat, Anuitas, Grace Period |
| StatusPermohonan | DRAFT, SUBMITTED, ANALYZED, RECOMMENDED, APPROVED, REJECTED, RETURNED, SIGNED, ACTIVE, CLOSED, NPL |
| StatusKredit | ACTIVE, LUNAS, NPL, CLOSED |
| Kolektibilitas | 1 Lancar, 2 Dalam Perhatian Khusus, 3 Kurang Lancar, 4 Diragukan, 5 Macet |
| JenisKelamin | L, P |
| StatusPerkawinan | BELUM_MENIKAH, MENIKAH, CERAI_HIDUP, CERAI_MATI, LAINNYA |
| SatuanPeriode | MINGGUAN, BULANAN |
| StatusPelunasanInternal | L, LT, BL, BM, MB |
| JenisPertanggungan | TLO, ALLRISK |
| LevelRedFlag | INFO, WARNING, CRITICAL |
| StatusTtd | DRAFT, MENUNGGU, DITANDATANGANI, DIBATALKAN |
| AksiApproval | APPROVE, REJECT, RETURN, HOLD |
| PihakSignature | DEBITUR, BPR, SAKSI |
| JenisAsuransi | JIWA_AL_AMIN, JIWA_JASINDO, KEBAKARAN_BUMIDA, KENDARAAN_JASINDO, LAINNYA |
| TipeBiaya | PERSEN, NOMINAL |
| JenisDokumen | DOC-01 … DOC-17, LAINNYA |
| KategoriChecklist | PEMOHON, JAMINAN, VERIFIKASI |
| StatusChecklist | ADA, TIDAK_ADA, NA, TIDAK_WAJIB |
| JenisImport | SLIK_XLSX, SLIK_PDF, SLIK_TEKS, KTP, DEBITUR_XLSX, DOKUMEN_ZIP, WORKBOOK_LAMA, PEMBAYARAN_CSV |
| StatusImport | PREVIEW, COMMITTED, FAILED, REVERTED |
| StatusImportBaris | VALID, INVALID, COMMITTED, SKIPPED |
| JenisEkstraksi | KTP, KK, NPWP |
| StatusEkstraksi | DRAFT, VERIFIED, REJECTED |
| MetodePembayaran | TUNAI, TRANSFER, POTONG_TPP, LAINNYA |
| StatusVersi | DRAFT, ACTIVE, ARCHIVED |
| StatusKelayakan | LAYAK, PERHATIAN, TIDAK LAYAK |
| StatusTemplate | DRAFT, REVIEW, APPROVED, DEPRECATED |
| JenisPermintaanSlik | INDIVIDU, BADAN |
| SumberSlik | PORTAL, IMPORT, MANUAL |
| SumberData | MANUAL, IMPORT, OCR |
| LedgerEvent | 23 nilai, lihat §9 |
| RatingKredit | SANGAT LAYAK, LAYAK, LAYAK BERSYARAT, TIDAK LAYAK |
| RekomendasiSistem | DISETUJUI, DISETUJUI DENGAN SYARAT, DITOLAK |

## §9. Katalog event ledger (23)

| # | Event | entity_type | Payload wajib |
|---|---|---|---|
| 1 | SIMULASI_DIJALANKAN | simulasi_kredit | simulasi_id, input_hash, engine_version, parameter_version, hasil_ringkas |
| 2 | ANALISIS_DISIMPAN | permohonan_kredit | bagian, parameter_kebijakan_id, nilai_kunci |
| 3 | PERMOHONAN_DIBUAT | permohonan_kredit | no_permohonan, debitur_id, produk_id, plafon_diajukan |
| 4 | PERMOHONAN_DIAJUKAN | permohonan_kredit | level_berikut, kelengkapan_persen |
| 5 | PERMOHONAN_DIKEMBALIKAN | permohonan_kredit | level, alasan |
| 6 | PERMOHONAN_DISETUJUI | permohonan_kredit | level, role, plafon |
| 7 | PERMOHONAN_DITOLAK | permohonan_kredit | level, alasan |
| 8 | DOKUMEN_DIBUAT | dokumen | kode, versi_template, file_hash |
| 9 | DOKUMEN_DITANDATANGANI | dokumen | pihak, ttd_hash |
| 10 | DOKUMEN_DIUNGGAH | dokumen | file_hash, sumber |
| 11 | CHECKLIST_DIVERIFIKASI | checklist_status | checklist_id, status |
| 12 | PARAMETER_DIAKTIFKAN | ref_parameter_kebijakan | version, effective_date, selisih_kunci |
| 13 | TEMPLATE_DISETUJUI | ref_template_dokumen | kode, version |
| 14 | SLIK_DIIMPOR | slik_permintaan | batch_id, jumlah_fasilitas, total_plafon, kualitas_terburuk |
| 15 | KTP_DIEKSTRAKSI | ekstraksi_dokumen | jenis, engine, field_terisi, confidence_min |
| 16 | DATA_DIIMPOR | import_batch | batch_id, jenis, sukses, gagal |
| 17 | IMPORT_DIBATALKAN | import_batch | batch_id, alasan, jumlah_target |
| 18 | AKAD_DICATAT | kredit | no_akad, tanggal_akad |
| 19 | PENCAIRAN_DICATAT | kredit | tanggal_cair, plafon, dana_bersih |
| 20 | ANGSURAN_DIBAYAR | pembayaran_angsuran | kredit_id, periode, pokok, bunga, denda, total |
| 21 | ANGSURAN_DIREVERSAL | pembayaran_angsuran | pembayaran_id, alasan |
| 22 | AKSES_PII | dokumen/ekstraksi_dokumen | jenis_file, aksi (view/download), alasan |
| 23 | PENGGUNA_DIAKTIFKAN | users | user_id, aktif |

Setiap event punya konstanta di `App\Enums\LedgerEvent`; `LedgerEventCatalogTest` memastikan enum = katalog ini = event yang di-emit test.

## §10. Mapping import

### 10.1 SLIK Excel/CSV, PDF teks, tempel-teks → `permohonan_slik_debitur`
Sumber (kolom iDeb/judul tabel disesuaikan profil): `Nama Pelapor/Pelapor` → `nama_lembaga`; `Jenis Fasilitas` → `jenis_fasilitas`; `Nomor Fasilitas` → `no_fasilitas`; `Plafon` → `plafon`; `Baki Debet` → `baki_debet`; `Suku Bunga` → `suku_bunga`; `Jangka Waktu` → `jangka_waktu_bulan`; `Angsuran` → `angsuran_bulanan`; `Kualitas/Kolektibilitas` → `kolektibilitas_id` (1–5); `Kondisi` → `kondisi`; `Tanggal Mulai` → `tanggal_mulai`; `Tanggal Tunggakan` → `tanggal_tunggakan`; `Jumlah Tunggakan (hari)` → `hari_tunggakan`; `Frekuensi Tunggakan` → `frekuensi_tunggakan_12m`; `Tunggakan Pokok/Bunga` → kolom terkait; `Status/Jaminan` → `jenis_jaminan`; `Tanggal Data` → `tanggal_data`.
Header iDeb: `tanggal_permintaan`, `no_referensi`, `operator_id`, `file_hash` → `slik_permintaan`.

### 10.2 KTP/KK/NPWP → `ekstraksi_dokumen` + `debitur`
`NIK`/`NIK/Nomor KK` → `no_ktp`/`no_kk`; `Nama` → `nama_ktp`; `Tempat/Tgl Lahir` → `tempat_lahir`/`tanggal_lahir`; `Jenis Kelamin` → `jenis_kelamin`; `Alamat`, `RT/RW`, `Kel/Desa`, `Kecamatan`, `Kabupaten`, `Provinsi`, `Kode Pos`; `Agama`, `Status Perkawinan`, `Pekerjaan` → field debitur; `Berlaku Hingga` → catatan verifikasi. NIK→(provinsi, tanggal lahir, jenis kelamin) tanpa OCR.

### 10.3 Debitur + aset XLSX
Sheet `debitur` (NIK, nama, tanggal lahir, alamat terstruktur, pekerjaan, penghasilan, no HP), `pasangan`, `usaha`, `siklus_usaha`, `aset` (jenis, kategori, luas, taksasi, persen, nilai). Kolom opsional boleh kosong; validasi NIK unik & numerik.

### 10.4 Dokumen ZIP
Konvensi `NIK__KODE_DOK__nomor.ext` (mis. `7371xxxxxxxxxxxx__DOC-06__001.pdf`) → `dokumen` (jenis_dokumen, nomor, file_path, hash; status DRAFT). Nama tidak sesuai → masuk `import_baris` INVALID.

### 10.5 Workbook analisis lama → permohonan + analisis
Sheet `1.Profil Debitur`, `2.Data Kredit & Asumsi`, `3.Neraca & Laba Rugi`, `4.Rasio`, `5.Modal Kerja`, `6.Kapasitas`, `7.Sensitivitas`, `8.5C`, `9.Rekomendasi`, `13.Perhitungan`, `14.Checklist` → entitas terkait. Best-effort + review manusia; dipakai juga sebagai fixture regresi.

### 10.6 Pembayaran angsuran CSV
`rekening` → `kredit.no_rekening_kredit`; `periode`; `tanggal_bayar`; `pokok`; `bunga`; `denda`; `total`; `metode`; `no_bukti`. Rekonsiliasi selisih → antrean.

### 10.7 Referensi
`cabang`, `produk`, `ref_*` (CSV) dengan kolom `sumber` wajib.

## §11. Wajib/opsional & bobot kelengkapan

| Entitas | Wajib untuk submit | Opsional (meningkatkan skor) | Bobot kelengkapan |
|---|---|---|---|
| debitur | no_ktp, nama_ktp, jenis_kelamin, tanggal_lahir, no_hp, alamat, provinsi | nama ibu, KK, NPWP, email, koordinat, foto | 25% |
| permohonan | produk, plafon, rate, tenor, sistem, tujuan, parameter | target realisasi, prioritas, catatan | 15% |
| SLIK | minimal 1 fasilitas ATAU centang "tidak ada fasilitas" | lengkap 12 kolom/fasilitas | 15% |
| neraca/laba-rugi | total aktiva, total pasiva, pendapatan, pengeluaran | rincian aset/hutang/biaya | 20% |
| kapasitas | surplus, angsuran, dscr | plafon max, balloon, idir | 10% |
| scoring | 5 skor + justifikasi | catatan | 10% |
| dokumen | checklist wajib (BR-05) | lampiran | 5% |

`kelengkapan_persen` dihitung dari bobot di atas + `bobot_kelengkapan` parameter; **tidak** memblokir submit kecuali item wajib & BR-05. Nilai bobot awal `[A]` sampai `[H]` di Fase 07.

## §12. Sumber data & provenance

Aturan: setiap nilai referensi/parameter punya `sumber` yang dapat diperiksa.

| Nilai | Sumber | Contoh format |
|---|---|---|
| 13 kategori agunan + persen | Sheet `2.Data Kredit & Asumsi` baris 34–46 | `Sheet2!34-46` |
| Checklist 28 baris | Sheet `14.Checklist Dok & Verifikasi` | `Sheet14!A4-D26` |
| Parameter kebijakan v1.0 | Sheet `2.Data Kredit & Asumsi` baris 17–24 | `Sheet2!17-24` |
| Bobot 5C & rating | Sheet `8.Penilaian 5C` | `Sheet8!C4-G8` |
| Kolektibilitas 1–5 | POJK 1/2024 + iDebKu | `POJK1/2024 Pasal 20` |
| Tarif asuransi | kontrak rekanan BPR | `[H] <nama>, <tanggal>` |
| Band skor 5C (skor_band_*) | belum diputuskan | `DRAFT` sampai `[H]` |
| Batas/format teknis | config | `config/loan.php` |

`SeedProvenanceTest`: `sumber` kosong pada baris `ref_*` = gagal. Nilai `[H]` wajib menyebut nama & tanggal persetujuan.

## §13. Traceability

| Kebutuhan | Tabel | Service | Route | Test | Dokumen |
|---|---|---|---|---|---|
| Angsuran 3 sistem | jadwal_angsuran, kredit | AngsuranCalculator | `/kredit/{id}/jadwal` | FormulaGoldenTest | B §8 |
| DSCR & plafon max | analisis_kapasitas_bayar | DscrCalculator, PlafonMaxCalculator | `/permohonan/{id}/analisis/kapasitas` | DscrCalculatorTest | B §8 |
| Skor 5C + override | analisis_scoring_5c | Scoring5CService | `/permohonan/{id}/analisis/scoring` | Scoring5CTest | B §9 |
| Grace balloon | permohonan_kredit, analisis_kapasitas_bayar | BR04, BR13 | `/permohonan/{id}/analisis/kapasitas` | BR04/BR13 test | A §21 |
| Approval berjenjang | approval_log, ref_approval_matrix | ApprovalService | `/approval*` | ApprovalFlowTest | B §10 |
| Checklist wajib | checklist_status, ref_checklist_dokumen | ChecklistService | `/permohonan/{id}` | BR05Test | B §9 |
| 17 dokumen | dokumen, ref_template_dokumen | DocumentService | `/dokumen*` | DocumentGenerationTest | A §21 |
| Simulasi & jejak | simulasi_kredit, ledger | SimulationService, LedgerService | `/simulasi*`, `/ledger*` | SimulationReplayTest, LedgerChainTest | B §11 |
| Import SLIK/KTP | import_* , slik_permintaan, ekstraksi_dokumen | ImportPipeline + adapter | `/import*` | ImportAdapterTest | B §12 |
| Pembayaran | pembayaran_angsuran, jadwal_angsuran | PembayaranService | `/jadwal/{id}/bayar` | PembayaranTest | B §16 |

## §14. Checklist verifikasi inventaris

- [ ] Setiap tabel di RENCANA §7 punya bab di dokumen ini.
- [ ] `SchemaInventoryTest` membandingkan kolom dokumen vs `Schema::getColumnListing`.
- [ ] Setiap kolom `ref_*` punya `sumber` terisi (bukan null).
- [ ] Setiap enum di §8 punya CHECK DB yang identik (`EnumCheckConstraintTest`).
- [ ] Setiap event §9 punya konstanta enum dan minimal satu test pemancar.
- [ ] Setiap adapter §10 punya fixture anonim dan test.
- [ ] Bobot kelengkapan §11 berjumlah 100%.
- [ ] Tidak ada PII nyata di contoh/fixture.
