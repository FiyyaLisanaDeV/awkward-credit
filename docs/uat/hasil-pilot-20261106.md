# Hasil UAT Pilot — 1 Cabang (KDI)

**Tanggal:** 2026-11-06 · **Lingkungan:** Mac lokal (`php artisan serve` 127.0.0.1:8000), `APP_ENV=local`
**Data:** dummy, tanpa PII nyata · **Cabang:** KDI · **Status:** **PILOT TEKNIS — LULUS DENGAN CATATAN**
**Bukan:** produksi, bukan pengganti tanda tangan UAT manusia, bukan pengesahan template/SOP.

> Herd tidak aktif saat eksekusi (`loan-analytic-bpr.test` tidak resolve), sehingga UAT dijalankan
> lewat server Laravel lokal. Database: `loan_analytic_bpr` (dev/pilot), bukan DB produksi.

## 1. Akun pilot

| Username | Role | Email | Password |
|---|---|---|---|
| `ao.pilot` | AO | `ao.pilot@bpr-pilot.local` | env `PILOT_INITIAL_PASSWORD` |
| `ao2.pilot` | AO | `ao2.pilot@bpr-pilot.local` | env `PILOT_INITIAL_PASSWORD` |
| `mgr.pilot` | MGR_PEMASARAN | `mgr.pilot@bpr-pilot.local` | env `PILOT_INITIAL_PASSWORD` |
| `koordinator.pilot` | KOORDINATOR | `koordinator.pilot@bpr-pilot.local` | env `PILOT_INITIAL_PASSWORD` |
| `dirut.pilot` | DIRUT | `dirut.pilot@bpr-pilot.local` | env `PILOT_INITIAL_PASSWORD` |
| `komite.pilot` | KOMITE | `komite.pilot@bpr-pilot.local` | env `PILOT_INITIAL_PASSWORD` |
| `admin.pilot` | ADMIN | `admin.pilot@bpr-pilot.local` | env `PILOT_INITIAL_PASSWORD` |
| `legal.pilot` | LEGAL | `legal.pilot@bpr-pilot.local` | env `PILOT_INITIAL_PASSWORD` |
| `auditor.pilot` | AUDITOR | `auditor.pilot@bpr-pilot.local` | env `PILOT_INITIAL_PASSWORD` |

Dibuat oleh `Database\Seeders\Local\PilotUatSeeder` (menolak berjalan bila `APP_ENV=production`).
Password **tidak** disimpan di repo; isi `PILOT_INITIAL_PASSWORD` di `.env` lokal sebelum seeding.

## 2. Data pilot

| Data | Nilai |
|---|---|
| Permohonan `KDI/PILOT/0001` | 5.000.000 · 18% · 12 bulan · Flat · **SUBMITTED → RECOMMENDED → APPROVED → ACTIVE** |
| Permohonan `KDI/PILOT/0002` | 5.000.000 · 18% · 12 bulan · Flat · **ACTIVE** dengan 1 pembayaran periode 1 yang telah direversal |
| Kredit | 2 rekening aktif |
| Jadwal angsuran | 12 baris per kredit |
| Ledger | 10 baris, rantai utuh |

## 3. Hasil per skenario

| # | Skenario | Hasil | Bukti |
|---|---|---|---|
| 1 | Login 9 role + dashboard | **LULUS** | `http-smoke.txt` |
| 2 | AO membuka `/pengaturan` | **LULUS** — `403` | `http-smoke.txt` |
| 3 | Auditor membuka pengaturan | **LULUS** — `200`, editor “Simpan draft” tidak tampil | `http-smoke.txt` |
| 4 | Komite/Legal membuka pengaturan | **LULUS** — `200` | `http-smoke.txt` |
| 5 | Pengaturan: draft → submit → approve → apply (`RATE_JIWA`) | **LULUS** — revisi `APPLIED`, 2 baris rate pilot, 6 audit | `settings-workflow.txt` |
| 6 | SoD: pembuat menyetujui pengajuannya sendiri | **LULUS** — `403` | `functional.txt` |
| 7 | Approval level 1 (AO lain) | **LULUS** — `RECOMMENDED`, log level 1 | `functional.txt` |
| 8 | Approval level 2 (MGR) | **LULUS** — `APPROVED`, total 2 log | `functional.txt` |
| 9 | BR-07: generate dokumen saat template `DRAFT` | **LULUS** — `422` | `functional.txt` |
| 10 | Pencairan kredit | **LULUS (backend)** — `KreditService`, 60/12 jadwal, memo, ledger | `functional.txt` |
| 11 | Pencairan lewat UI web | **TIDAK TERSEDIA** — belum ada route/form | route list |
| 12 | Catat pembayaran | **LULUS (data pilot)** — form tersedia di detail kredit | `http-smoke.txt` |
| 13 | Reversal pembayaran | **LULUS** — pembayaran batal, jadwal `BELUM_BAYAR`, ledger | `functional.txt` |
| 14 | Auditor read-only | **LULUS** — tanpa aksi edit/approve di halaman pengaturan | `http-smoke.txt` |
| 15 | `ledger:verify` | **LULUS** — 10 baris, rantai utuh | `ledger.txt` |
| 16 | `verify.sh --full` | **LULUS** — 162 test, PHPStan L5+L8, hardcode 0, docs strict 0 | `verify-full.txt` |

## 4. Temuan & perbaikan selama UAT

| Temuan | Dampak | Tindakan | Status |
|---|---|---|---|
| `DashboardService` menyimpan Eloquent Collection ke cache | dashboard `500` setelah cache terisi (`__PHP_Incomplete_Class`) | cache hanya agregat; `recent` di-query ulang; regression test | **diperbaiki** |
| `lang/id.php` bukan format grup Laravel | seluruh UI menampilkan key (`dashboard.title`) | dipecah ke `lang/id/<group>.php` | **diperbaiki** |
| Seeder user tidak re-assign role bila user sudah ada | `admin`/`auditor` tanpa role | seeder idempoten + test role | **diperbaiki** |
| Cache dashboard lama setelah deploy | dashboard error sampai cache dibersihkan | `cache:clear` masuk prosedur deploy/pilot | **didokumentasikan** |

## 5. Gap yang belum lulus / belum diuji

1. **Pencairan belum punya UI web** — hanya service/API internal. UAT pilot tidak dapat menyelesaikan pencairan dari browser.
2. **Dokumen, ledger, dan import belum punya UI web** — hanya service/CLI/API; menu tidak tampil.
3. **17 template dokumen masih `DRAFT`** — generate dokumen positif belum bisa diuji; BR-07 (negatif) lulus.
4. **UAT 3 cabang belum dijalankan** — pilot hanya KDI; cabang BOM/KOL belum.
5. **Tanda tangan UAT/SOP/template belum ada** — butuh manusia `[K]`.
6. **Golden verified 0/29; policy ACTIVE vs DRAFT divergen** — keputusan bisnis `[K]` (D-07/D-11).
7. **Browser automation tidak tersedia** — bukti UI berupa HTTP status + marker, bukan tangkapan layar.
8. **`Test User` sisa scaffold** ada di DB dev; tidak dibuat oleh seeder dan tidak akan ada di DB baru.

## 6. Putusan

**Pilot teknis KDI: LULUS DENGAN CATATAN.** Alur inti (login/RBAC, dashboard, pengaturan, approval berjenjang, SoD, pencairan backend, pembayaran/reversal, ledger) terbukti berjalan pada data dummy.

**Belum layak disebut produksi** sampai:
- UI pencairan/dokumen/ledger tersedia (atau prosedur operator disepakati),
- 17 template disahkan `[K]`,
- UAT 3 cabang + tanda tangan,
- hardening Fase 16 dan keputusan kebijakan selesai.

## 7. Indeks bukti

Folder: `docs/evidence/uat-20261106/`

| File | Isi |
|---|---|
| `verify-full.txt` | `scripts/verify.sh --full` exit 0 |
| `pest.txt` | 162 test lulus |
| `http-smoke.txt` | login/RBAC/halaman per role |
| `settings-workflow.txt` | draft→approve→apply |
| `functional.txt` | SoD, approval 2 level, BR-07, pencairan backend, reversal |
| `ledger.txt` | `ledger:verify` |
| `hardcode.txt` | `hardcode:audit` 0 |
| `docs.txt` | `docs:check --strict` 0 |
| `state.txt` | `state:check` |
| `routes.json` | daftar route |
| `migrate.txt` | status migrasi |
