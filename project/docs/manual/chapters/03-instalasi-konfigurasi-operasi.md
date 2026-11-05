# Bab 3 — Instalasi, Konfigurasi, Environment, dan Operasi Dasar

## 3.1 Prasyarat `[E]`
Dari `README.md` dan `scripts/setup.sh`:
- **PHP ≥ 8.3** dengan ekstensi `pdo_pgsql`, `bcmath`, `mbstring`, `intl`, `gd`, `zip`.
- **Composer 2**.
- **PostgreSQL 16** native di `127.0.0.1:5432` (tanpa Docker).
- Herd opsional untuk domain `.test`.
- **Tanpa** Docker, Redis, MinIO, npm.

## 3.2 Langkah setup `[E]`
```bash
composer setup              # -> bash scripts/setup.sh
```
`scripts/setup.sh` melakukan (urutan):
1. Cek `php`, `composer`, `psql`, `createdb` ada; cek PHP ≥ 8.3 & `pdo_pgsql`.
2. Jika `.env` belum ada → copy dari `.env.example` lalu `php artisan key:generate`.
3. Jika `vendor/` belum ada → `composer install`.
4. Buat database `$DB_DATABASE` dan `${DB_DATABASE}_test` bila belum ada.
5. `php artisan migrate --force`.
6. `php artisan db:seed --force`.
7. `php artisan storage:link`; buat `storage/app/private/documents` & `storage/app/private/pii`.
8. `php artisan optimize:clear`.

Menjalankan:
```bash
php artisan serve                  # http://127.0.0.1:8000
herd link loan-analytic-bpr        # http://loan-analytic-bpr.test
```

## 3.3 Variabel environment kunci `[E]`
`.env.example` (ringkas; lihat file untuk lengkap):
| Variabel | Nilai contoh | Fungsi |
|---|---|---|
| `APP_NAME` | `Loan Analytic BPR` | Nama aplikasi |
| `APP_TIMEZONE` | `Asia/Makassar` | Zona waktu (WITA) |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | `id` / `en` | Bahasa (lang `id.php`) |
| `APP_ENGINE_VERSION` | `calc-v1.0.0` | Versi mesin kalkulasi → disimpan per simulasi |
| `DB_CONNECTION` | `pgsql` | Driver DB |
| `DB_DATABASE` | `loan_analytic_bpr` | DB utama |
| `SESSION_DRIVER` | `database` | Sesi tersimpan di DB |
| `CACHE_STORE` | `database` | Cache di DB |
| `QUEUE_CONNECTION` | `sync` | Antrean sinkron (tanpa Redis) |
| `FILESYSTEM_DISK` | `local` | Storage lokal |
| `LEDGER_CHAIN` | `true` | Aktifkan hash chain ledger |
| `IMPORT_MAX_ROWS` | `5000` | Batas baris impor |
| `IMPORT_MAX_FILE_MB` | `25` | Batas ukuran berkas impor |
| `OCR_DRIVER` | `auto` | `auto`/`none`/`tesseract` |
| `OCR_LANG` | `ind` | Bahasa OCR |
| `TESSERACT_PATH` | (kosong) | Path biner tesseract |
| `PII_STORAGE` | `encrypted` | Mode penyimpanan PII |
| `ADMIN_INITIAL_PASSWORD` | (kosong) | Sandi admin awal (jangan commit) |
| `LOGIN_MAX_ATTEMPTS` | `5` | Maks percobaan login |
| `LOGIN_DECAY_SECONDS` | `60` | Jendela throttle login |
| `PASSWORD_MIN_LENGTH` | `8` | Panjang sandi min (dev) |
| `PASSWORD_MIN_LENGTH_PRODUCTION` | `12` | Panjang sandi min (produksi) |

## 3.4 Config aplikasi `[E]`
### `config/loan.php`
- `engine_version` (`APP_ENGINE_VERSION`), `timezone` (`Asia/Makassar`).
- `money`: `scale=2`, `display_decimals=0`, `canonical_scale=6`, `ratio_scale=4`, `schedule_scale=2`, `currency=IDR`.
- `pagination`: `per_page=20`, `ledger_per_page=50`; `ui.dashboard_recent=10`.
- `golden`: `tpp_path` (workbook), `output_path=tests/fixtures/golden/tpp.json`.
- `nomor`: pola `{cabang}/{tahun}/{urut}`, kredit `{cabang}/{tahun}/K/{urut}`, dokumen `{kode}/{cabang}/{tahun}/{urut}`, `urut_padding=4`.
- `upload.max_mb=25`, `mimes=[pdf,jpg,jpeg,png,xlsx,xls,csv,zip]`.
- `dokumen` (disk/path), `pii` (disk/path, cipher `aes-256-gcm`).
- `security`: login attempts/decay, password min, `slow_query_ms=500`, `admin_initial_password`.

### `config/ledger.php`
- `enabled=true`, `chain=LEDGER_CHAIN`, `lock_key=ledger-chain`, `verify_schedule='0 1 * * *'` (harian 01:00).

### `config/import.php`
- `max_rows=5000`, `max_file_mb=25`, `allowed_mimes`, `staging_ttl_hours=24`.
- `profiles`: 8 profil default (SLIK_XLSX_IDEB, SLIK_PDF_IDEB, SLIK_TEKS, KTP_OCR, DEBITUR_XLSX, DOKUMEN_ZIP, WORKBOOK_LAMA, PEMBAYARAN_CSV) — dipakai seeder; kode profil disimpan di tabel `import_profil`.
- `ocr`: driver/lang/binary.

## 3.5 Perintah operasi harian `[E]`
| Perintah | Fungsi |
|---|---|
| `php artisan serve` | Server dev |
| `php artisan migrate` / `migrate:fresh` | Migrasi DB |
| `php artisan db:seed` | Seeder referensi |
| `php artisan state:check` | Reality check: versi, DB, migrasi, rute, test, dokumen, git |
| `php artisan docs:check [--strict]` | Konsistensi dokumen vs kode (berkas wajib, jumlah, tautan, istilah terlarang) |
| `php artisan hardcode:audit [--path=…]` | AST scan literal angka/string bisnis (lihat Bab 15) |
| `php artisan ledger:verify [--from --to]` | Verifikasi hash chain ledger |
| `php artisan simulasi:replay {id}` | Reproduksi output simulasi (kanonik) |
| `php artisan golden:extract [--path --check]` | Ekstraksi nilai golden dari workbook XLSX |
| `bash scripts/verify.sh --quick|--phase|--full` | Verifikasi bertingkat |
| `bash scripts/backup.sh` / `restore.sh <dump>` | Backup/restore DB + storage privat |
| `bash scripts/reset-db.sh` | Reset DB dev (hati-hati) |

## 3.6 Verifikasi (gate) `[E]`
`scripts/verify.sh` mode:
- `--quick`: lingkungan + DB + rute + `state:check`.
- `--phase`: + Pint `--test`, PHPStan level 5 (dan level 8 untuk `app/Services`, `app/Enums`, `app/Domain` via `phpstan-services.neon`), Pest, `hardcode:audit`, `docs:check`, `state:check`.
- `--full`: + `docs:check --strict`.

## 3.7 Troubleshooting cepat
| Gejala | Sebab umum | Tindakan |
|---|---|---|
| `pdo_pgsql tidak tersedia` | ekstensi PHP belum aktif | aktifkan ekstensi (Herd/php.ini) |
| `koneksi database / migrasi` GAGAL | PG belum jalan / DB belum dibuat | jalankan PG, `createdb`, `php artisan migrate` |
| Login ditolak walau kredensial benar | `is_active=false` atau `must_change_password` | aktifkan user / ganti sandi (Bab 4) |
| `MissingPolicyParameter` | tidak ada `ref_parameter_kebijakan` ACTIVE | jalankan `ParameterKebijakanSeeder` |
| `hardcode:audit` GAGAL | literal angka/string bisnis baru | pindahkan ke policy/param (`Konstanta` hanya mekanis) |
| `ledger:verify` GAGAL | baris ledger diubah/hash tak cocok | jangan UPDATE/DELETE ledger; investigasi via pembalik |
