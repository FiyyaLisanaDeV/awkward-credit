# PROGRESS — Loan Analytic BPR

> Dokumen **hidup**. Diperbarui di akhir setiap sesi/fase. Jangan menghapus baris; tandai statusnya.
> Aturan bukti & gate: [VERIFIKASI.md](VERIFIKASI.md). Referensi teknis: [TEKNIS.md](TEKNIS.md).

## §1. Header snapshot

| Item | Nilai | Diperbarui |
|---|---|---|
| Versi dokumen | 2.4 | 2026-11-05 |
| `updated_at` | 2026-11-05T02:18+08:00 | 2026-11-05 |
| Fase aktif | **01 selesai**; berikutnya 02 (Auth + Health) | 2026-11-05 |
| Repo | `loan-analytic-bpr` (git, branch main) | 2026-11-05 |
| Branch / last commit | main / `3504a6f` (scaffold) + commit dokumen ini | 2026-11-05 |
| PHP | 8.3.7 (Herd) | 2026-11-05 |
| Laravel | 13.40.0 | 2026-11-05 |
| PostgreSQL | 16.15, running, role `fiyyalisanna` | 2026-11-05 |
| Jumlah migrasi DB aplikasi | 3 (tabel sistem) | 2026-11-05 |
| Jumlah route | 13 | 2026-11-05 |
| Jumlah test | 5 file (7 test) | 2026-11-05 |
| `composer.lock` hash | `c76f6d030170` | 2026-11-05 |
| `ledger:verify` | belum ada (Fase 08B) | 2026-11-05 |
| Bukti baseline | [evidence/00-baseline-2026-11-05.txt](evidence/00-baseline-2026-11-05.txt) | 2026-11-05 |

**Cara memperbarui header:** jalankan perintah, tempel hasilnya, jangan mengetik dari ingatan.

```bash
php artisan migrate:status | tail -1
php artisan route:list --json | php -r 'echo count(json_decode(stream_get_contents(STDIN), true)), PHP_EOL;'
php artisan test --compact 2>&1 | tail -1
shasum -a 256 composer.lock | cut -d' ' -f1
php artisan ledger:verify --quiet && echo "chain: OK"
```

## §2. Papan fase (22 unit)

| Fase | Nama | Status | Mulai | Selesai | Bukti verifikasi | Commit/tag | Catatan |
|---|---|---|---|---|---|---|---|
| 00 | Reconnaissance | DONE | 02:12 | 02:16 | `PHASE_00_RECON.md`, `evidence/phase-00-pre-20261105-0212.txt` | — | read-only |
| 01 | Scaffold Laravel 13 | DONE | 02:13 | 02:17 | `evidence/phase-01-post-20261105-0217.txt` | `3504a6f` / `phase-01-done` | 82 file; tanpa npm |
| 02 | Auth + Health | TODO | — | — | — | — | tanpa Breeze |
| 03 | Migrasi Master (13) | TODO | — | — | — | — | kolom `sumber` |
| 04 | Migrasi Auth + Debitur (11) | TODO | — | — | — | — | drill #1 |
| 05 | Migrasi Permohonan + Analisis + SLIK (15) | TODO | — | — | — | — | — |
| 06 | Migrasi Kredit + Workflow + Audit (13) | TODO | — | — | — | — | trigger audit |
| 06B | Migrasi Ledger + Simulasi + Import + Pembayaran (7) | TODO | — | — | — | — | hitung `\dt` |
| 07 | Seeders (17) | TODO | — | — | — | — | provenance wajib |
| 08 | Models (55) + Enum (24) | TODO | — | — | — | — | drill #2 |
| 08B | Ledger & Simulation Service | TODO | — | — | — | — | replay & verify |
| 09 | Calculation Engine | TODO | — | — | — | — | golden:extract |
| 10 | Rules + Scoring | TODO | — | — | — | — | band 5C `[H]` |
| 11 | API | TODO | — | — | — | — | RouteContract |
| 12 | Blade UI Forms | TODO | — | — | — | — | drill #3 |
| 12B | Import & Efisiensi | TODO | — | — | — | — | 8 adapter |
| 13 | Dashboard + Workflow | TODO | — | — | — | — | pembayaran |
| 14 | Documents (17) | TODO | — | — | — | — | — |
| 15 | Testing & Quality Gate | TODO | — | — | — | — | drill #4 |
| 16 | Hardening | TODO | — | — | — | — | role DB + restore |
| 17 | Dokumentasi Pengguna & SOP | TODO | — | — | — | — | — |
| 18 | UAT, Go-Live & Serah Terima | TODO | — | — | — | — | — |

Status yang dipakai: `TODO` · `IN-PROGRESS` · `BLOCKED` · `DONE`. `DONE` hanya sah bila memenuhi gate VERIFIKASI §10.

## §3. Blocker & pertanyaan terbuka

| # | Pertanyaan/keputusan | Dampak | Pemilik | Status | Batas waktu |
|---|---|---|---|---|---|
| Q1 | Tabel konversi band skor 5C (DSCR/equity/LTV/SLIK → skor 1–5) belum ada di workbook; hanya skor contoh | memblokir produksi Fase 10 | Mgr Pemasaran + Dirut | `[?]` DRAFT di parameter | sebelum Fase 10 |
| Q2 | Checklist 28 baris (workbook) vs 29 (dokumen v1.0) — mana yang final? | seeder & BR-05 | Legal/Kepatuhan | `[?]` | sebelum Fase 07 |
| Q3 | Tarif asuransi rekanan (Al-Amin, Jasindo, Bumida, kendaraan) belum ada dokumen | seeder rate | Admin/Legal | `[?]` | sebelum Fase 07 |
| Q4 | File contoh iDeb nyata (PDF/Excel) untuk mengunci parser | akurasi import | AO | `[?]` | sebelum Fase 12B |
| Q5 | Apakah tesseract dipasang? (aplikasi jalan tanpa itu) | kualitas OCR KTP | IT | `[?]` opsional | sebelum Fase 12B uji |
| Q6 | Role DB produksi & kredensial VPS | keamanan rilis | IT | `[?]` | sebelum Fase 16 |
| Q7 | Konfirmasi akhir bobot kelengkapan data (§11 INVENTARIS) | skor kelengkapan | Mgr Pemasaran | `[A]` usulan | sebelum Fase 07 |

## §4. Handoff terakhir

```
SESI: 2026-11-05 (sesi 2) | FASE: 00–01 | STATUS: done
BERUBAH: scaffold `loan-analytic-bpr` (82 file), docs/evidence, config/loan|import|ledger, scripts/*.sh,
         app/Console/Commands/{StateCheck,DocsCheck,HardcodeAudit}, tests/Unit/Guard/*
BUKTI: docs/evidence/phase-01-post-20261105-0217.txt (verify.sh --phase, exit 0)
KEPUTUSAN: ADR-024 tetap; AGENTS.md/CLAUDE.md bawaan Laravel dihapus (arahan Boost tidak dipakai)
BLOCKER: Q1–Q7 (lihat §3)
NEXT: 1) Fase 02 auth + /health; 2) phpunit.xml → pgsql `loan_analytic_bpr_test`; 3) commit+tag phase-02-done
```

## §5. Log sesi (append-only)

| Tanggal | Sesi | Fase | Ringkasan | Perintah verifikasi | Hasil | Commit |
|---|---|---|---|---|---|---|
| 2026-11-05 | perencanaan | — | Menyusun paket dokumen A–G: rencana v2.4, teknis 22 bab, inventaris 60 tabel/23 event/8 adapter, 22 prompt, ADR, protokol verifikasi; baseline lingkungan direkam | `sw_vers`, `php -v`, `pg_isready`, `psql`, `composer -V`, `ls` (lihat evidence) | baseline lengkap, exit 0 | — (workspace belum repo) |
| 2026-11-05 | 2 | 00 | Recon read-only: health matrix, tripwire, target path, risiko | `php -m`, `pg_isready`, `command -v` (lihat evidence) | exit 0 | — |
| 2026-11-05 | 2 | 01 | Scaffold Laravel 13.40.0; paket runtime/dev; struktur domain; config tanpa redis/s3; scripts; 3 command penjaga; 3 test guard; docs disalin; DB dev+test dibuat | `bash scripts/verify.sh --phase` | OK, exit 0 | `3504a6f` / `phase-01-done` |

## §6. Catatan drift

| Tanggal | Drift | Tindakan | Status |
|---|---|---|---|
| — | — | — | — |

## §7. Aturan pengisian

1. Satu sesi = satu entri log; cantumkan perintah verifikasi yang benar-benar dijalankan.
2. Setiap baris `DONE` wajib punya: file bukti, commit/tag, dan handoff.
3. Bila sesi berhenti di tengah fase: isi `IN-PROGRESS` + daftar langkah tersisa yang spesifik (file + perintah).
4. Bila verifikasi gagal 2×: `BLOCKED` + penyebab konkret; dilarang mengubah test untuk meloloskan.
5. Jangan mengedit baris historis; koreksi lewat baris baru.
