# Bab 15 — Perintah CLI/Artisan, Scheduler, dan Quality Tools

## 15.1 Command kustom `[E]` (`app/Console/Commands`)
| Command | Signature | Fungsi |
|---|---|---|
| `state:check` | `state:check` | Reality check: PHP, Laravel, timezone, DB+migrasi, jumlah rute, file test, hash `composer.lock`, git HEAD, keberadaan 7 dokumen wajib |
| `docs:check` | `docs:check {--strict}` | Konsistensi dokumen: berkas wajib, jumlah (prompt fase=22, baris papan=22, event=23, enum=34, tabel=60), tautan, istilah terlarang |
| `hardcode:audit` | `hardcode:audit {--path=*}` | AST scan literal angka/string bisnis (lihat 15.3) |
| `ledger:verify` | `ledger:verify {--from= --to=}` | Verifikasi hash chain ledger |
| `simulasi:replay` | `simulasi:replay {id}` | Jalankan ulang simulasi & bandingkan output kanonik |
| `golden:extract` | `golden:extract {--path= --check}` | Ekstraksi nilai golden dari workbook XLSX ke fixture |

## 15.2 Detail `state:check` `[E]`
Menampilkan tabel item/​nilai/status: `php` (≥8.3), `laravel`, `timezone`, `database`, `migrasi` (jumlah), `rute` (jumlah), `file test`, `composer.lock` (12 char hash), `git` (HEAD), dan 7 dokumen wajib (`RUANG-LINGKUP-PRODUK.md`, `RENCANA-PRODUKSI-FINAL.md`, `TEKNIS.md`, `INVENTARIS_DATA.md`, `PROGRESS.md`, `DECISIONS.md`, `VERIFIKASI.md`) → GAGAL bila ada yang hilang.

## 15.3 Detail `hardcode:audit` `[E]`
- **AST** (nikic/php-parser) memindai default: `app/Services`, `app/Http`, `app/Livewire`, `app/Support`, `app/Domain` (atau `--path`).
- **Boleh**: `ALLOWED_NUMBERS = [0,1,2,-1,200,201,204,400,401,403,404,409,422,429,500,503]`; `ALLOWED_FLOATS = [0.0,1.0,2.0,-1.0]`.
- **Denylist string**: `TPP, MODAL_KERJA, GRACE_PERIOD, Flat, Anuitas, Grace Period, Layak, TIDAK LAYAK, KDI, ChangeMe, BPR Bahteramas`.
- Dikecualikan: `app/Support/Konstanta.php`.
- Ada temuan → tabel + `FAILURE`; nol → "0 pelanggaran".

## 15.4 Detail `docs:check` `[E]`
Mengecek: (a) 7 dokumen wajib ada; (b) jumlah prompt fase=22, baris papan PROGRESS=22, event ledger=23, enum=34, tabel bisnis=60 di `INVENTARIS_DATA.md`; (c) tautan markdown tidak rusak; (d) istilah/dependensi terlarang (`jq`, `docker`, `redis`, `minio`, `puppeteer`, `pg_partman`) memicu peringatan kecuali konteks penjelasan. `--strict` → peringatan jadi kegagalan.

## 15.5 Scheduler `[E]`
`config/ledger.php` `verify_schedule='0 1 * * *'` (verifikasi rantai harian `[A]`). Untuk aktif, perlu entri `routes/console.php`/cron `schedule:run` (verifikasi konfigurasi aktual `[?]`).

## 15.6 Skrip shell `[E]` (`scripts/`)
| Skrip | Fungsi |
|---|---|
| `setup.sh` | Instalasi end-to-end (Bab 3) |
| `verify.sh` | Verifikasi bertingkat `--quick/--phase/--full` (Bab 3) |
| `backup.sh` | Backup database + storage privat |
| `restore.sh <dump>` | Restore |
| `reset-db.sh` | Reset DB dev (hati-hati) |

## 15.7 Quality tools `[E]`
- **Pint** (`./vendor/bin/pint --test`) — gaya kode.
- **PHPStan/Larastan** level 5 (app) & level 8 (`app/Services`, `app/Enums`, `app/Domain` via `phpstan-services.neon`).
- **Pest** (`php artisan test`) — test.
- Guard tests: `tests/Unit/Guard/{NoForbiddenDependencyTest,NoSecretsTest,StrictTypesTest}.php`.

## 15.8 Composer scripts `[E]`
- `composer setup` → `bash scripts/setup.sh`.
- `composer verify` → `bash scripts/verify.sh --phase`.
- `composer test` → `config:clear` + `artisan test`.
- `composer dev` → `artisan serve`.

## 15.9 Catatan `[?]`
Baseline QA live belum dijalankan ulang saat manual disusun. Jalankan `bash scripts/verify.sh --phase` untuk menutup gap (lihat Bab 2 §2.7, Bab 21).
