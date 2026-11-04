# PROGRESS — Loan Analytic BPR

> Dokumen **hidup**. Diperbarui di akhir setiap sesi/fase. Jangan menghapus baris; tandai statusnya.
> Aturan bukti & gate: [VERIFIKASI.md](VERIFIKASI.md). Referensi teknis: [TEKNIS.md](TEKNIS.md).

## §1. Header snapshot

| Item | Nilai | Diperbarui |
|---|---|---|
| Versi dokumen | 2.4 | 2026-11-05 |
| `updated_at` | 2026-11-05T02:26+08:00 | 2026-11-05 |
| Fase aktif | **05 selesai**; berikutnya 06 (Kredit + Workflow + Audit) | 2026-11-05 |
| Repo | `loan-analytic-bpr` (git, branch main) | 2026-11-05 |
| Branch / last commit | main / `0d85655` (Fase 05) | 2026-11-05 |
| PHP | 8.3.7 (Herd) | 2026-11-05 |
| Laravel | 13.40.0 | 2026-11-05 |
| PostgreSQL | 16.15, running, role `fiyyalisanna` | 2026-11-05 |
| Jumlah migrasi DB aplikasi | 40 (25 + 15 permohonan/analisis) | 2026-11-05 |
| Jumlah route | 20 | 2026-11-05 |
| Jumlah test | 13 file (30 test) | 2026-11-05 |
| `composer.lock` hash | `c76f6d030170` | 2026-11-05 |
| `ledger:verify` | belum ada (Fase 08B) | 2026-11-05 |
| Bukti fase terakhir | `docs/evidence/phase-05-post-20261105-0225.txt` | 2026-11-05 |
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
| 02 | Auth + Health | DONE | 02:18 | 02:22 | `docs/evidence/phase-02-post-20261105-0220.txt` | `7be6549` / `phase-02-done` | auth tulis sendiri; UserAdmin ditunda ke Fase 13 |
| 03 | Migrasi Master (13) | DONE | 02:22 | 02:28 | `docs/evidence/phase-03-post-20261105-0222.txt` | `6bd13d2` / `phase-03-done` | CHECK dari enum; 9 enum dibuat |
| 04 | Migrasi Auth + Debitur (11) | DONE | 02:22 | 02:25 | `docs/evidence/phase-04-post-20261105-0224.txt` + `docs/evidence/drill-01-20261105-0223.txt` | `e02b78d` / `phase-04-done` | drill #1 PASS |
| 05 | Migrasi Permohonan + Analisis + SLIK (15) | DONE | 02:25 | 02:31 | `docs/evidence/phase-05-post-20261105-0225.txt` | `0d85655` / `phase-05-done` | BR-04 CHECK di DB |
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
SESI: 2026-11-05 (sesi 6) | FASE: 05 | STATUS: done
BERUBAH: 15 migrasi (permohonan, slik_permintaan, 2 SLIK, riwayat, 10 analisis) + 2 test schema
BUKTI: docs/evidence/phase-05-post-20261105-0225.txt (verify.sh --phase, exit 0; 30 test; total 48 tabel)
KEPUTUSAN: BR-04 & BR-08 ditegakkan di level DB (CHECK + FK NOT NULL)
BLOCKER: Q1–Q7 (lihat §3)
NEXT: 1) Fase 06: 13 migrasi kredit/workflow/audit + trigger; 2) migrate; 3) uji trigger immutable
```

## §5. Log sesi (append-only)

| Tanggal | Sesi | Fase | Ringkasan | Perintah verifikasi | Hasil | Commit |
|---|---|---|---|---|---|---|
| 2026-11-05 | perencanaan | — | Menyusun paket dokumen A–G: rencana v2.4, teknis 22 bab, inventaris 60 tabel/23 event/8 adapter, 22 prompt, ADR, protokol verifikasi; baseline lingkungan direkam | `sw_vers`, `php -v`, `pg_isready`, `psql`, `composer -V`, `ls` (lihat evidence) | baseline lengkap, exit 0 | — (workspace belum repo) |
| 2026-11-05 | 2 | 00 | Recon read-only: health matrix, tripwire, target path, risiko | `php -m`, `pg_isready`, `command -v` (lihat evidence) | exit 0 | — |
| 2026-11-05 | 2 | 01 | Scaffold Laravel 13.40.0; paket runtime/dev; struktur domain; config tanpa redis/s3; scripts; 3 command penjaga; 3 test guard; docs disalin; DB dev+test dibuat | `bash scripts/verify.sh --phase` | OK, exit 0 | `3504a6f` / `phase-01-done` |
| 2026-11-05 | 3 | 02 | Auth (login/logout/ganti sandi, rate limit, middleware aktif & wajib ganti), `/health`, layout guest/app, lang id, phpunit → pgsql test, 4 test file auth/health | `bash scripts/verify.sh --phase` | OK, exit 0 (20 test) | `7be6549` / `phase-02-done` |
| 2026-11-05 | 4 | 03 | 13 migrasi master/referensi + 9 enum + `CheckConstraint` (CHECK dari enum), index parsial ACTIVE, kolom sumber/approval; test constraint enum | `bash scripts/verify.sh --phase` | OK, exit 0 (22 test) | `cbbdb00` / `phase-03-done` |
| 2026-11-05 | 5 | 04 | Users profile (cabang/username/jabatan/no_hp), 6 tabel debitur, Spatie publish; 2 test schema/constraint; drill #1 PASS | `bash scripts/verify.sh --phase` | OK, exit 0 (27 test) | `e02b78d` / `phase-04-done` |
| 2026-11-05 | 6 | 05 | 15 migrasi: permohonan (BR-04 CHECK), slik_permintaan, SLIK debitur/pasangan, riwayat internal, 10 analisis (parameter NOT NULL = BR-08) | `bash scripts/verify.sh --phase` | OK, exit 0 (30 test) | `0d85655` / `phase-05-done` |

## §6. Catatan drift

| Tanggal | Drift | Tindakan | Status |
|---|---|---|---|
| 2026-11-05 | `Password::defaults` dipanggil di `register()` → `auth.password` belum terikat | dipindah ke `boot()` + memakai `Validation\Rules\Password` (bukan facade) | selesai |
| 2026-11-05 | Model `User` baru mengembalikan `is_active = null` (default DB belum termuat) | tambah `$attributes` default di model | selesai |
| 2026-11-05 | Skrip pembaruan PROGRESS menimpa baris fase 03 (placeholder `—` tertangkap) | baris dipulihkan manual; skrip berikutnya memakai penanda eksplisit | selesai |

## §7. Aturan pengisian

1. Satu sesi = satu entri log; cantumkan perintah verifikasi yang benar-benar dijalankan.
2. Setiap baris `DONE` wajib punya: file bukti, commit/tag, dan handoff.
3. Bila sesi berhenti di tengah fase: isi `IN-PROGRESS` + daftar langkah tersisa yang spesifik (file + perintah).
4. Bila verifikasi gagal 2×: `BLOCKED` + penyebab konkret; dilarang mengubah test untuk meloloskan.
5. Jangan mengedit baris historis; koreksi lewat baris baru.
