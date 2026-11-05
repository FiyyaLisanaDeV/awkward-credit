# Evidence Pack — UAT & Go-Live

Kumpulkan output asli (bukan ringkasan) ke folder `docs/evidence/uat-<tanggal>/`. Jangan menyertakan PII/token.

## 1. Perintah wajib
| # | Perintah | Harapan | File bukti |
|---|---|---|---|
| 1 | `bash scripts/verify.sh --full` | exit 0 | `verify-full.txt` |
| 2 | `php artisan test --compact` | semua lulus | `pest.txt` |
| 3 | `php artisan ledger:verify` | OK | `ledger.txt` |
| 4 | `php artisan hardcode:audit` | 0 pelanggaran | `hardcode.txt` |
| 5 | `php artisan docs:check --strict` | 0 temuan | `docs.txt` |
| 6 | `php artisan state:check` | OK | `state.txt` |
| 7 | `php artisan route:list --json` (hitung) | jumlah route tercatat | `routes.json` |
| 8 | `php artisan migrate:status` | semua migrasi ran | `migrate.txt` |

## 2. Bukti fungsional (UAT)
- Tangkapan layar/alur: debitur → permohonan → approval → pencairan → pembayaran.
- Timeline approval (2 level) dan notifikasi.
- Dokumen DOC-01..17 + hash stabil.
- Negatif: SoD 403, BR-07 422, pembayaran ganda 409.
- Auditor read-only.

## 3. Snapshot teknis (baseline repo, 2026-11-06)
| Item | Nilai |
|---|---|
| Migrasi | 61 |
| Route | 77 |
| File test | 44 (termasuk auditor) |
| Test | 140 passed (1789 assertions) |
| `verify.sh --full` | exit 0 |
| `ledger:verify` | OK |
| `hardcode:audit` | 0 |
| Golden | 29 recomputed / 0 verified `[K]` |

> Snapshot ini adalah bukti teknis, **bukan** pengganti tanda tangan UAT.

## 4. Tanda tangan
| Peran | Nama | Tanggal | Tanda tangan |
|---|---|---|---|
| Kepala Cabang KDI | | | |
| Kepala Cabang 2 | | | |
| Kepala Cabang 3 | | | |
| Kepatuhan | | | |
| IT/Admin | | | |
