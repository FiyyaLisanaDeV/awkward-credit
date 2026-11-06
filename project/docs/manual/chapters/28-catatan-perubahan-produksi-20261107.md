# Bab 28 — Catatan Perubahan Produksi (2026-11-07)

> **Ringkas:** bab ini mencatat perubahan produksi pada 2026-11-07: snapshot parameter, aturan bertahap, IDIR dua tingkat, penomoran atomik, DSCR dengan SLIK, dan workbench analisis. Waktu sebelum/sesudah, bukti verifikasi, dan commit aplikasi dicantumkan agar perubahan dapat diaudit.

## 28.1 Rentang waktu perubahan

| Item | Nilai |
|---|---|
| Sebelum | **2026-11-07 01:37 WITA** |
| Sesudah | **2026-11-07 01:57 WITA** |
| Checkpoint lanjutan | **2026-11-07 02:00–02:10 WITA** |
| Commit aplikasi | `3a7d60a` (fondasi) · `fb652ad` (workbench Sheet 3–9) |
| Bukti | `docs/evidence/produksi-20261107/verify-full.txt` |

## 28.2 Ringkasan sebelum → sesudah

| Aspek | Sebelum | Sesudah |
|---|---|---|
| Test | 195 test / 1993 assertions | **206 test / 2156 assertions** |
| Migrasi | 67 | **70** |
| Route | 104 | **105** |
| Snapshot parameter | Belum ada | `parameter_snapshot` JSONB + `forPermohonan()` |
| Rule engine | Hanya class + test; alur submit/approve belum memakai stage | `validateStage()` submit/analisis/approve |
| IDIR TPP | Soft 35% saja | Soft >35%, **hard stop >80%** (`BR-11H`) |
| Penomoran | `count()+lockForUpdate()` | SQL upsert atomik + backfill `GREATEST` |
| DSCR | Hanya angsuran baru | Angsuran baru + total fasilitas SLIK aktif |
| Workbench analisis | Belum ada | `/permohonan/{id}/analisis` — neraca, laba rugi, rasio, modal kerja, kapasitas, sensitivitas 6 skenario, scoring 5C |
| Golden G-TPP-03 | 80,00% ditandai hard stop | 80,00% lolos; 80,01% hard stop |
| Golden verified | 0 / 29 | 0 / 29 (fixture diperbaiki; tanda tangan manusia tetap pending) |
| Ledger | 20 baris | 20 baris, rantai utuh |

## 28.3 Perubahan per gate

**Gate 0 — Konsolidasi dokumen**
- `RENCANA-PRODUKSI-FINAL.md` menjadi satu-satunya rencana eksekusi.
- `RENCANA_IMPLEMENTASI.md` → `RUANG-LINGKUP-PRODUK.md`.
- Rencana lama diarsipkan ke `docs/arsip/rencana/` dan `_arsip/rencana/`.
- `DocsCheck`/`StateCheck` menunjuk 7 dokumen wajib yang baru.

**Gate 1 — Grandfathering**
- `permohonan_kredit.parameter_snapshot` JSONB; snapshot immutable.
- `PolicyParameters::toArray()/fromArray()`.
- `PolicyParameterResolver::forPermohonan()`; tidak fallback ke `active()`.
- 15 rule memakai parameter snapshot permohonan.

**Gate 2 — Aturan bertahap & IDIR**
- Submit: BR-04/08/14/15.
- Analisis: BR-01/02/03/10/11/11H/12/13, hasil dicatat ke `audit_log`.
- Approve: BR-01/02/03/05/11H/13; BR-06 tetap di `ApprovalService`.
- `BR11IdirHardStop` + parameter `idir_hard_stop_tpp` (default 0.80).
- `status_idir` disimpan: LAYAK / PERHATIAN / TIDAK LAYAK.

**Gate 3 — Penomoran**
- `NomorService` memakai `INSERT ... ON CONFLICT ... RETURNING`.
- Scope lowercase `{jenis}:{cabang}:{tahun}`; dokumen menambah kode.
- `BackfillNomorSequenceSeeder` memakai regex anchor dan `GREATEST`.
- Backfill dijalankan sebelum penomoran baru aktif.

**Gate 4 — SLIK & DSCR**
- Kolom `angsuran_slik_debitur`, `angsuran_slik_pasangan`, `total_angsuran_slik`, `jumlah_fasilitas_slik_aktif`.
- `is_angsuran_estimated` pada tabel SLIK.
- `SlikAggregationService`: `baki_debet > 0`, dedupe `no_fasilitas`, estimasi flat.
- `KapasitasService` dipakai controller dan workbench; DSCR memakai total kewajiban.

**Gate 5 — Golden**
- `G-TPP-03` diperbaiki: 80,00% `expected_hard_stop=false`, 80,01% `true`.
- `GoldenVectorSuiteTest` memuat 29 kasus dan batas IDIR.
- Status **belum verified**; menunggu tanda tangan/evidence manusia.

**Gate 6 — Workbench**
- Route `GET /permohonan/{permohonan}/analisis`.
- Livewire: ringkasan, neraca, laba rugi, rasio, modal kerja, kapasitas, sensitivitas, scoring.
- Banner neraca tidak seimbang; CSS native `public/css/app.css`.
- Isolasi cabang via `ScopesByCabang`.
- Checkpoint lanjutan (`fb652ad`): rasio otomatis, modal kerja cash turnover/WCD/gap, 6 skenario sensitivitas dari parameter policy, scoring 5C input + simpan.

**Gate 7 — QA**
- `verify.sh --full` OK; Pint, PHPStan app/services, 206 test, hardcode 0, docs 0, ledger 20.
- Backup/restore drill dan UAT 3 cabang belum dijalankan.

## 28.4 Bukti verifikasi

```bash
cd "APP BPR/loan-analytic-bpr"
bash scripts/verify.sh --full          # VERIFY: OK (--full)
php artisan test --compact             # 206 passed, 2156 assertions
php artisan ledger:verify              # OK — 20 baris, rantai utuh
php artisan hardcode:audit             # 0 pelanggaran
php artisan docs:check --strict        # 0 error, 0 peringatan
php artisan state:check                # 70 migrasi, 105 route, 61 file test
curl https://loan-analytic-bpr.test/health  # 200
```

## 28.5 Yang belum selesai

- **Golden `verified=29`**: fixture sudah dikoreksi, tetapi tanda tangan manusia/evidence belum ada.
- **UI penuh Sheet 3–9**: form utama sudah ada; validasi/field rinci masih dapat ditambah.
- **Backup/restore drill + UAT 3 cabang**: belum dijalankan pada sesi ini.
- **Token GitHub**: file `Github Fine Grained token.txt` masih ada di direktori spesifikasi; rotasi dan hapus sebelum publikasi.

## 28.6 Dampak operasional

- Permohonan baru mengunci parameter saat dibuat; perubahan setting tidak mengubah analisis berjalan.
- Approval TPP dengan IDIR >80% akan ditolak oleh hard gate; 35–80% menjadi perhatian.
- Nomor baru tidak lagi memakai hitungan baris; backfill wajib dijalankan pada deployment berikutnya.
- DSCR dapat turun karena kewajiban SLIK eksisting ikut dihitung; dampak ke approval perlu dipantau pada UAT.

## 28.7 Checkpoint lanjutan (02:00–02:10 WITA)

- `fb652ad`: Workbench Sheet 3–9 dilengkapi.
- Rasio: current/quick/cash, debt-to-asset/equity, equity-to-asset, NPM, ROA, ROE.
- Modal kerja: perputaran kas, cash turnover, WCD, rata-rata kebutuhan, gap.
- Sensitivitas: 6 skenario (baseline, omzet ringan/berat, biaya naik, dua kombinasi).
- Scoring 5C: input Condition + skor tertimbang/rating/override.
- `Scoring5CService` memakai `forPermohonan()` (grandfathering).
- Verifikasi ulang: **206 test / 2156 assertions**, `verify.sh --full` OK.
