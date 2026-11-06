# PHASE 00 — RECONNAISSANCE (read-only)

| Meta | Nilai |
|---|---|
| Tanggal | 2026-11-05 (Asia/Makassar) |
| Mode | read-only (tidak ada perubahan kode/DB) |
| Bukti | `docs/evidence/phase-00-pre-20261105-0212.txt` |
| Acuan | [RENCANA_IMPLEMENTASI.md](RENCANA_IMPLEMENTASI.md) §2, [VERIFIKASI.md](VERIFIKASI.md) §5 |

## 1. Health matrix

| Item | Hasil | Status |
|---|---|---|
| PHP | 8.3.7 (Herd `php83`) | ✅ (minimum Laravel 13 = 8.3) |
| pdo_pgsql / bcmath / mbstring / intl / gd / zip / dom / fileinfo / imagick | semua ada | ✅ |
| Composer | 2.10.2 | ✅ |
| PostgreSQL | 16.15, `pg_isready` accepting, role `fiyyalisanna` | ✅ |
| Database aplikasi | belum ada (`loan_analytic_bpr` / `_test`) | ⚠️ dibuat di Fase 02 |
| Herd / installer | Herd 1.30.0 / laravel 5.32.0 | ✅ |
| Git | 2.39.3; workspace belum repo | ⚠️ init di Fase 01 |
| Disk bebas | 64 GB | ✅ |
| Port 5432 / 8000 | 5432 terpakai (Postgres), 8000 bebas | ✅ |
| jq | tidak ada | ✅ diantisipasi (`php -r`) |
| Docker / redis / minio / tesseract / pdftotext | tidak ada (sesuai rencana) | ✅ |
| xdebug / pcov | tidak ada | ✅ gate coverage diganti golden test |

## 2. Target proyek

- Path: `/Users/fiyyalisanna/Downloads/APP BPR/loan-analytic-bpr` (tanpa spasi).
- Domain: `loan-analytic-bpr.test` (Herd) atau `php artisan serve`.
- Dokumen A–G disalin ke `<repo>/docs/` pada Fase 01 dan menjadi sumber kebenaran.
- Tiga workbook tetap di workspace sebagai referensi (tidak disalin ke repo).

## 3. Konflik & keputusan

| Temuan | Keputusan |
|---|---|
| `jq` tidak terpasang | semua hitung JSON memakai `php -r` (lihat PROGRESS header & prompt) |
| PHP Homebrew rusak (icu4c) | hanya memakai PHP Herd (`which php`) |
| `pg_partman` tidak ada, `xdebug/pcov` tidak ada | tanpa partisi, tanpa gate coverage (ADR-005, ADR-018) |
| `tesseract` tidak ada | OCR opsional (`OCR_DRIVER=auto`), NIK parser tetap (ADR-023) |
| Workspace bukan git repo | `git init` di dalam proyek, bukan di workspace |

## 4. Risiko awal

1. Unduhan Composer memerlukan jaringan; kegagalan → ulangi dengan `COMPOSER_CACHE_DIR` lokal.
2. `composer create-project` menulis banyak file; sandbox mengizinkan karena di dalam workspace.
3. Proyek harus berada di subfolder (bukan root workspace) agar tiga workbook tetap utuh.

## 5. Rekomendasi

**LANJUT ke Fase 01** — scaffold Laravel 13 di `loan-analytic-bpr`, tanpa Docker/Redis/npm.
