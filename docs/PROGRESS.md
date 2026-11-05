# PROGRESS — Loan Analytic BPR

> Dokumen **hidup**. Diperbarui di akhir setiap sesi/fase. Jangan menghapus baris; tandai statusnya.
> Aturan bukti & gate: [VERIFIKASI.md](VERIFIKASI.md). Referensi teknis: [TEKNIS.md](TEKNIS.md).

## §1. Header snapshot

| Item | Nilai | Diperbarui |
|---|---|---|
| Versi dokumen | 2.6 | 2026-11-06 |
| `updated_at` | 2026-11-06T00:00+08:00 | 2026-11-06 |
| Fase aktif | **12 selesai**; berikutnya **12B** (Import & Efisiensi) | 2026-11-06 |
| Repo | `loan-analytic-bpr` (git, branch main) | 2026-11-06 |
| Branch / last commit | main / `phase-12-done` (`5b38403`, Fase 12) | 2026-11-06 |
| PHP | 8.3.7 (Herd) | 2026-11-06 |
| Laravel | 13.40.0 | 2026-11-06 |
| PostgreSQL | 16.15, running, role `fiyyalisanna` | 2026-11-06 |
| Jumlah migrasi DB aplikasi | 61 (53 + 7 ledger/simulasi/import + Sanctum) | 2026-11-06 |
| Jumlah route | 73 (42 API v1) | 2026-11-06 |
| Jumlah test | 31 file (**104 test**, 1616 assertions) | 2026-11-06 |
| `composer.lock` hash | `76cd829130f7` | 2026-11-06 |
| `ledger:verify` | tersedia (Fase 08B); chain **OK** (diverifikasi sesi ini) | 2026-11-06 |
| Bukti fase terakhir | `docs/evidence/phase-12-post-20261106-022557.txt` (verify --phase exit 0) + `docs/evidence/drill-03-20261106-022734.txt` | 2026-11-06 |
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
| 06 | Migrasi Kredit + Workflow + Audit (13) | DONE | 02:31 | 02:37 | `docs/evidence/phase-06-post-20261105-0227.txt` | `5e22739` / `phase-06-done` | trigger BR-09 aktif |
| 06B | Migrasi Ledger + Simulasi + Import + Pembayaran (7) | DONE | 02:37 | 02:41 | `docs/evidence/phase-06b-post-20261105-0228.txt` | `237ce96` / `phase-06b-done` | 68 tabel terverifikasi |
| 07 | Seeders (14) | DONE | 02:41 | 02:48 | `docs/evidence/phase-07-post-20261105-0231.txt` | `b65b05c` / `phase-07-done` | 12 aktif + 2 kondisional |
| 08 | Models (55) + Enum (31) | DONE | 02:48 | 02:55 | `docs/evidence/phase-08-post-20261105-0233.txt` + `docs/evidence/drill-02-20261105-0233.txt` | `7f95fb8` / `phase-08-done` | 55 model, relasi, DTO; drill #2 PASS |
| 08B | Ledger Service | DONE | 02:55 | 02:59 | `docs/evidence/phase-08b-post-20261105-0235.txt` | `f4428af` / `phase-08b-done` | chain + idempotency |
| 09 | Calculation + SimulationService | DONE | 02:59 | 03:06 | `docs/evidence/phase-09-post-20261105-0240.txt` | `1de50be` / `phase-09-done` | golden TPP + replay |
| 10 | Rules + Scoring | DONE | 03:06 | 03:14 | `docs/evidence/phase-10-post-20261105-0245.txt` | `7e49459` / `phase-10-done` | 15 BR + 5C + red flag |
| 11 | API | DONE | 03:14 | 03:22 | `docs/evidence/phase-11-post-20261105-024945.txt` | `7e11e8e` / `phase-11-done` | 42 endpoint, scoping cabang |
| 12 | Blade UI Forms | DONE | 2026-11-06 | 2026-11-06 | `docs/evidence/phase-12-post-20261106-022557.txt` + `docs/evidence/drill-03-20261106-022734.txt` | `feat(phase-12)` / `phase-12-done` | verify --phase exit 0; hardcode:audit 0; pint+phpstan OK; 104 test |
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
SESI: 2026-11-06 (sesi 15) | FASE: 12 (IN-PROGRESS) | STATUS: partial
BERUBAH: commit 011d856 — UI Web (Debitur/Permohonan/Simulasi controller), Livewire
  (DebiturWizard, PermohonanWizard, Simulator), komponen blade, Support/Validation/*Rules,
  Services/Debitur+Permohonan+SimulationRunner; fix QA: middleware Spatie (bootstrap/app.php),
  namespace route API (routes/api.php), NomorService (FOR UPDATE vs agregat PG), default NOT NULL
  DebiturWizard, validasi Simulator; golden: tests/Fixtures/golden/c002-recomputed.json +
  tests/Feature/Calculation/GoldenRecomputedParityTest.php
BUKTI: vendor/bin/pest --compact = 104 passed (1616 assertions); phpstan = no errors;
  php artisan ledger:verify = chain OK (dijalankan 2026-11-06)
KEPUTUSAN: fixture C-002 status RECOMPUTED_DRAFT_NOT_VERIFIED (bukan verified); angka IDIR 85,93% /
  net 2.948.080 / RATE x12 28,88% (efektif 33,02%) dikunci sebagai paritas engine, menunggu
  RAT-02 (sumber sheet/sel) + tanda tangan
BLOCKER: Q1–Q7 (§3) + D-01..D-20 & RAT-01/RAT-03 (lihat docs/decisions) + pasal V1/V3/V4 PENDING
NEXT: 1) hasilkan evidence phase-12 + drill #3; 2) selaraskan policy ACTIVE seeder
  (idir_threshold_tpp 0.35 / margin 0.5 / ltv 0.7) vs rekomendasi DRAFT (0.80/0.80/0.80) via D-11/D-07;
  3) lanjut Fase 12B/13
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
| 2026-11-05 | 7 | 06 | 13 migrasi kredit/workflow/audit; trigger audit_log immutable; test UPDATE/DELETE ditolak; Spatie migration dipindah ke 000010 | `bash scripts/verify.sh --phase` | OK, exit 0 (34 test) | `5e22739` / `phase-06-done` |
| 2026-11-05 | 8 | 06B | 7 migrasi (ledger, simulasi_kredit, import_profil/batch/baris, ekstraksi_dokumen, pembayaran_angsuran) + trigger ledger; 68 tabel total | `bash scripts/verify.sh --phase` | OK, exit 0 (37 test) | `237ce96` / `phase-06b-done` |
| 2026-11-05 | 9 | 07 | 14 seeder referensi (cabang 3, produk 9, kategori 13, kolektibilitas 5, status 5, checklist 28, parameter 34 kunci, matrix 1, template 17, role 8, admin 1, profil import 8); rate/konversi kondisional (Q3) | `bash scripts/verify.sh --phase` | OK, exit 0 (41 test) | `b65b05c` / `phase-07-done` |
| 2026-11-05 | 10 | 08 | 55 model (generator dari skema) + relasi belongsTo/hasMany + PolicyParameters DTO + resolver; test SchemaInventory & PolicyParameters; drill #2 PASS | `bash scripts/verify.sh --phase` | OK, exit 0 (46 test) | `7f95fb8` / `phase-08-done` |
| 2026-11-05 | 11 | 08B | LedgerService + ChainHasher (canonical JSON, UTC, advisory lock), ledger:verify; 5 test chain | `bash scripts/verify.sh --phase` | OK, exit 0 (51 test) | `f4428af` / `phase-08b-done` |
| 2026-11-05 | 12 | 09 | 8 calculator + SimulationEngine/Service + simulasi:replay + golden:extract (TPP: angsuran 2.533.333,33; IDIR 0,8311); 4 test file | `bash scripts/verify.sh --phase` | OK, exit 0 (66 test) | `1de50be` / `phase-09-done` |
| 2026-11-05 | 13 | 10 | 15 business rule (BR-01..15), engine, Scoring5C (override BR-01), RedFlagEvaluator 7 kategori, RekomendasiService; 10 test | `bash scripts/verify.sh --phase` | OK, exit 0 (79 test) | `7e49459` / `phase-10-done` |
| 2026-11-05 | 14 | 11 | API Sanctum v1: login/logout/me, debitur+aset, permohonan+submit, slik, analisis (neraca, laba-rugi, kapasitas, scoring, red flag, rekomendasi), simulasi+replay, approval inbox/approve/reject/return, kredit/jadwal, ledger/verify, dashboard; scoping cabang; 17 test API | `bash scripts/verify.sh --phase` | OK, exit 0 (87 test) | `7e11e8e` / `phase-11-done` |
| 2026-11-06 | 15 | 12 | UI Web + Livewire (DebiturWizard, PermohonanWizard, Simulator), komponen blade, validasi, SimulationRunner; fix QA (middleware Spatie, namespace route API, NomorService PG, default NOT NULL, validasi Simulator); golden C-002 recomputed + GoldenRecomputedParityTest | `vendor/bin/pest --compact`, `vendor/bin/phpstan analyse`, `php artisan ledger:verify` | OK, exit 0; **104 test** lulus, phpstan no errors, chain OK; belum ada evidence phase-12/drill #3 | `011d856` |
| 2026-11-06 | 16 | 12 | Tutup gate Fase 12: pindahkan literal bisnis UI ke `Konstanta` (step wizard, indeks paste SLIK) + enum `SistemSukuBunga`/`ProdukTipe` (hardcode:audit 0); rapikan pint & phpstan L8 (PermohonanService, SimulationRunner.diff, imports); evidence phase-12 + drill #3 | `bash scripts/verify.sh --phase`, `php artisan hardcode:audit`, `vendor/bin/pint --test` | OK, exit 0 (104 test, hardcode 0) | `feat(phase-12)` / `phase-12-done` |

## §6. Catatan drift

| Tanggal | Drift | Tindakan | Status |
|---|---|---|---|
| 2026-11-05 | `Password::defaults` dipanggil di `register()` → `auth.password` belum terikat | dipindah ke `boot()` + memakai `Validation\Rules\Password` (bukan facade) | selesai |
| 2026-11-05 | Model `User` baru mengembalikan `is_active = null` (default DB belum termuat) | tambah `$attributes` default di model | selesai |
| 2026-11-05 | Skrip pembaruan PROGRESS menimpa baris fase 03 (placeholder `—` tertangkap) | baris dipulihkan manual; skrip berikutnya memakai penanda eksplisit | selesai |
| 2026-11-05 | Migrasi Spatie bertimestamp 2026_11_05 berjalan setelah `approval_log` → FK ke `roles` gagal di DB segar | dipindah ke `2026_01_01_000010`; baris `migrations` DB dev disesuaikan | selesai |
| 2026-11-05 | Parameter v1.0 berisi 34 kunci (dokumen menyebut 33); enum nyata 31 (dokumen 24) | dokumen & `docs:check` disesuaikan dengan hasil hitung | selesai |
| 2026-11-05 | Relasi `analisis_red_flags` memakai `AnalisisRedFlag`, kelas nyata `AnalisisRedFlags` | relasi dikoreksi | selesai |
| 2026-11-05 | Waktu ledger disimpan tanpa normalisasi zona → rawan beda hash | hash memakai UTC `Y-m-dTH:i:sZ` + skala kanonik dari config | selesai |
| 2026-11-05 | Rumus PMT memakai tanda Excel (negatif) → angsuran anuitas negatif | PMT dikembalikan positif; bisection suku bunga efektif dibalik arahnya | selesai |
| 2026-11-05 | Relasi Eloquent tanpa generics → PHPStan buta tipe properti | seluruh relasi diberi `@return BelongsTo<X,$this>`/HasOne/HasMany | selesai |
| 2026-11-06 | Snapshot header usang: tercatat 87 test/63 route, padahal live 104 test/73 route | header diperbarui ke angka hasil hitung 2026-11-06 | selesai |
| 2026-11-06 | Fase 12 tertulis TODO padahal kode UI (Web controller/Livewire/blade) sudah ada di working tree | status diubah ke IN-PROGRESS; evidence phase-12/drill #3 masih perlu dibuat | terbuka |
| 2026-11-06 | Policy ACTIVE seeder (IDIR 0,35; margin 0,5; LTV 0,7) berbeda dari rekomendasi DRAFT (0,80/0,80/0,80) | jangan ubah diam-diam; putuskan via D-11/D-07 | terbuka |

## §7. Aturan pengisian

1. Satu sesi = satu entri log; cantumkan perintah verifikasi yang benar-benar dijalankan.
2. Setiap baris `DONE` wajib punya: file bukti, commit/tag, dan handoff.
3. Bila sesi berhenti di tengah fase: isi `IN-PROGRESS` + daftar langkah tersisa yang spesifik (file + perintah).
4. Bila verifikasi gagal 2×: `BLOCKED` + penyebab konkret; dilarang mengubah test untuk meloloskan.
5. Jangan mengedit baris historis; koreksi lewat baris baru.
