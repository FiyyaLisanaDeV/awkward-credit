# Loan Analytic BPR

Sistem analisis kredit PT BPR Bahteramas Kendari (cabang Bombana & Kolaka) untuk produk TPP, Modal Kerja, dan Grace Period. Laravel 13 + PostgreSQL native, **tanpa Docker, Redis, MinIO, npm, atau partisi tabel**.

Dokumentasi lengkap ada di [`docs/`](docs/README.md):

| Dokumen | Isi |
|---|---|
| [RENCANA-PRODUKSI-FINAL.md](docs/RENCANA-PRODUKSI-FINAL.md) | rencana eksekusi tunggal: gate, urutan, DoD |
| [RUANG-LINGKUP-PRODUK.md](docs/RUANG-LINGKUP-PRODUK.md) | ruang lingkup, strategi, keputusan, kriteria sukses |
| [TEKNIS.md](docs/TEKNIS.md) | referensi teknis (arsitektur, formula, BR, ledger, import, runbook) |
| [INVENTARIS_DATA.md](docs/INVENTARIS_DATA.md) | 60 tabel, enum, event ledger, mapping import |
| [PROGRESS.md](docs/PROGRESS.md) | status kerja, blocker, handoff |
| [DECISIONS.md](docs/DECISIONS.md) | ADR |
| [VERIFIKASI.md](docs/VERIFIKASI.md) | reality check, anti-halusinasi, zero hardcode |

## Kebutuhan

- PHP 8.3+ dengan ekstensi `pdo_pgsql`, `bcmath`, `mbstring`, `intl`, `gd`, `zip`
- Composer 2
- PostgreSQL 16 (native, berjalan di `127.0.0.1:5432`)
- Herd (opsional, untuk domain `.test`)

## Setup

```bash
composer setup          # install, .env, key, buat database, migrasi, seeder
php artisan serve       # http://127.0.0.1:8000
# atau: herd link loan-analytic-bpr
```

Tanpa Docker, tanpa Redis, tanpa `npm install`.

## Verifikasi

```bash
bash scripts/verify.sh --quick
bash scripts/verify.sh --phase
bash scripts/verify.sh --full
```

## Perintah penting

| Perintah | Fungsi |
|---|---|
| `php artisan state:check` | reality check environment & drift |
| `php artisan docs:check [--strict]` | konsistensi dokumen dengan kode |
| `php artisan hardcode:audit` | deteksi nilai bisnis yang di-hardcode |
| `php artisan ledger:verify` | verifikasi hash chain ledger (fase 08B) |
| `php artisan simulasi:replay {id}` | reproduksi hasil simulasi (fase 08B) |
| `bash scripts/backup.sh` | backup database + storage privat |
| `bash scripts/restore.sh <dump>` | restore |

## Alur kerja

Ikuti `docs/RENCANA-PRODUKSI-FINAL.md` sebagai satu-satunya rencana eksekusi. Setiap gate berakhir dengan bukti di `docs/evidence/`, pembaruan `docs/PROGRESS.md`, dan commit. Protokol sesi historis ada di `docs/arsip/rencana/DSH_PROMPTS_SIMPLE.md` (read-only).
