# Rencana Produksi Final — Loan Analytic BPR

> **Status:** rencana kanonik disetujui 2026-11-07. Menggantikan seluruh rencana eksekusi lama yang tersebar.
> **Prinsip:** satu rencana eksekusi, satu sumber status, satu sumber arsitektur, satu sumber data. Rencana lama diarsipkan, bukan dihapus permanen.

## Status eksekusi (2026-11-07)

| Gate | Status | Bukti |
|---|---|---|
| 0 — Konsolidasi dokumen | DONE | `docs/arsip/rencana/`, `_arsip/rencana/` |
| 1 — Snapshot & grandfathering | DONE | `tests/Feature/Policy/GrandfatheringTest.php` |
| 2 — Stage-aware + IDIR hard stop | DONE | `tests/Feature/Workflow`, `BR11IdirHardStop` |
| 3 — Penomoran atomik + backfill | DONE | `tests/Feature/Workflow/AtomicNomorServiceTest.php` |
| 4 — SLIK & DSCR | DONE | `tests/Feature/Analisis/SlikAggregationTest.php` |
| 5 — Golden 29 | PARTIAL | `G-TPP-03` diperbaiki; `verified=29` menunggu tanda tangan manusia |
| 6 — Workbench analisis | DONE | Ringkasan, neraca, laba rugi, rasio, modal kerja, kapasitas, sensitivitas 6 skenario, scoring 5C |
| 7 — QA & rilis | PARTIAL | `verify.sh --full` OK; backup/restore drill & UAT belum |

Bukti: `docs/evidence/produksi-20261107/`.

## 0. Putusan

1. **Laravel 13 + PostgreSQL pusat** adalah satu-satunya System of Record. Tidak ada SQLite/ledger lokal. Tauri ditunda.
2. **UI:** Livewire 4.4.x + `public/css/app.css`. Tanpa Node/npm/Vite/Tailwind.
3. **Aturan bertahap:** Submit hanya aturan yang datanya tersedia; analisis mengevaluasi + menyimpan status; approve menjalankan hard gate; BR-06 ditangani `ApprovalService`.
4. **Grandfathering:** setiap permohonan menyimpan `parameter_snapshot` JSONB; resolver tidak boleh fallback ke `active()`.
5. **Penomoran:** `nomor_sequence` atomik dengan scope `{jenis}:{cabang}:{tahun}` (dokumen + kode), diekstrak regex ber-anchor, backfill `GREATEST`.
6. **Golden:** target `verified=29`, bukan `recomputed=29`; test 29 kasus harus benar-benar memanggil engine.
7. **Dokumen:** hanya satu dokumen rencana eksekusi; sisanya menjadi arsip historis.

## 1. Dokumen kanonik setelah konsolidasi

| Peran | File | Aturan |
|---|---|---|
| Rencana eksekusi tunggal | `APP BPR/loan-analytic-bpr/docs/RENCANA-PRODUKSI-FINAL.md` | Satu-satunya dokumen “apa yang dibangun, urutan, gate” |
| Status hidup | `APP BPR/loan-analytic-bpr/docs/PROGRESS.md` | Satu-satunya sumber status/commit/bukti |
| Arsitektur | `docs/TEKNIS.md` | Layer, stack, konvensi |
| Skema/data | `docs/INVENTARIS_DATA.md` | Tabel/kolom/enum |
| Quality gate | `docs/VERIFIKASI.md` | Definisi selesai & bukti |
| Keputusan | `docs/DECISIONS.md` | `[H]`/`[K]` append-only |
| Ruang lingkup produk | `docs/RUANG-LINGKUP-PRODUK.md` (rename dari `RENCANA_IMPLEMENTASI.md`) | “Apa & mengapa”, bukan rencana eksekusi |
| Status rilis | `docs/STATUS-GOLIVE-20261106.md` | Snapshot historis, bukan rencana |
| Fitur register/CIF | `docs/FITUR-BUKU-BESAR-CIF.md` | Catatan fitur selesai |
| UAT & manual | `docs/uat/`, `docs/user/` | Bukti & panduan |
| Arsip | `docs/arsip/rencana/` | Rencana lama, read-only |

Setelah konsolidasi, `README.md` dan `TEKNIS.md` harus menunjuk ke `RENCANA-PRODUKSI-FINAL.md` sebagai satu-satunya rencana eksekusi.

## 2. Pembersihan rencana yang membingungkan

**Kebijakan: arsipkan, jangan hapus permanen.** Rencana lama menyimpan jejak keputusan dan bukti; menghapusnya menghilangkan audit trail.

### 2.1 Root repo

Pindahkan ke `/Users/fiyyalisanna/Desktop/BPR/_arsip/rencana/`:

- `Rencana Bangun Ulang v2 Aplikasi Analisis Kredit, Dokumen & Ledger BPR (Laravel).md`
- `Rencana Kerja Aplikasi Analisis Kredit, Dokumen & Ledger BPR (Laravel).md`
- `deepseek_markdown_20261104_f6a1db.md`

Buat `_arsip/rencana/README.md` yang menyatakan: dokumen-dokumen ini historis, superseded, dan tidak boleh dijadikan acuan implementasi.

### 2.2 Repo aplikasi (`APP BPR/loan-analytic-bpr`)

- Rename `docs/RENCANA_IMPLEMENTASI.md` → `docs/RUANG-LINGKUP-PRODUK.md`; tambahkan banner “Dokumen ruang lingkup, bukan rencana eksekusi. Rencana eksekusi: RENCANA-PRODUKSI-FINAL.md.”
- Pindahkan ke `docs/arsip/rencana/`:
  - `docs/DSH_PROMPTS_SIMPLE.md`
  - `docs/PHASE_00_RECON.md`
- Jangan pindahkan: `PROGRESS.md`, `TEKNIS.md`, `INVENTARIS_DATA.md`, `VERIFIKASI.md`, `DECISIONS.md`, `STATUS-GOLIVE-20261106.md`, `FITUR-BUKU-BESAR-CIF.md`, `README.md`, `runbook-produksi-vps.md`, `questions.md`, `evidence/`, `uat/`, `user/`, `examples/`.

### 2.3 Repo spesifikasi (`commandcode bpr`)

Pindahkan ke `docs/arsip/rencana/`:

- `03-rencana-implementasi-detail.md`
- `research/05-rencana-verifikasi-dan-penyempurnaan.md`
- `docs/research-plan-blockers.md`
- `docs/spec/pr-plan-c002-money.md`

Perlakuan khusus:

- `docs/spec/golden-vectors-plan.md` → rename menjadi `docs/spec/golden-vectors-protocol.md`; bukan rencana proyek, melainkan protokol golden. Pertahankan.
- `docs/spec/` vs `project/docs/spec/`: bandingkan hash. Jika identik, pertahankan `project/docs/spec/` dan arsipkan salinan `docs/spec/`. Jika berbeda, gabungkan dulu, baru arsipkan.
- `project/docs/spec/pr-plan-c002-money.md` → arsipkan; isinya historis.

### 2.4 Duplikat di luar repo aplikasi

`APP BPR/docs/RENCANA_IMPLEMENTASI.md` dan `APP BPR/docs/DSH_PROMPTS_SIMPLE.md` adalah salinan. Sebelum memindahkan:

```bash
shasum -a 256 "APP BPR/docs/RENCANA_IMPLEMENTASI.md" "APP BPR/loan-analytic-bpr/docs/RENCANA_IMPLEMENTASI.md"
shasum -a 256 "APP BPR/docs/DSH_PROMPTS_SIMPLE.md" "APP BPR/loan-analytic-bpr/docs/DSH_PROMPTS_SIMPLE.md"
```

Jika identik, arsipkan seluruh `APP BPR/docs/` ke `APP BPR/_arsip/docs/`. Jika tidak, merge perbedaan lalu arsipkan.

### 2.5 Plan pribadi (`~/.commandcode/plans`) — opsional, di luar repo

Pindahkan ke `~/.commandcode/plans/archive/`:

- `rencana-migrasi-rust-desktop.md`
- `kritik-rencana-migrasi-rust-desktop.md`
- `etl-platform-pivot.md`
- `saas-data-model.md`
- `buku-besar-kredit-agunan.md`
- `job-thread-tracking.md`
- `phase-13-workflow-dashboard-pembayaran.md`
- `settings-configuration.md`
- `rencana-go-live-pilot-mac.md`

Pertahankan hanya `plans-index.json` dan `rencana-produksi-final-bpr.md`.

### 2.6 Verifikasi pasca-pembersihan

```bash
cd "/Users/fiyyalisanna/Desktop/BPR"
rg -n "RENCANA_IMPLEMENTASI|Rencana Bangun Ulang|Rencana Kerja|DSH_PROMPTS_SIMPLE|03-rencana-implementasi-detail|research-plan-blockers|pr-plan-c002" --glob '!**/arsip/**' --glob '!**/vendor/**'
```

Tidak boleh ada tautan aktif ke file yang diarsipkan. Perbarui `README.md`, `TEKNIS.md`, dan `PROGRESS.md` bila perlu.

## 3. Invariant arsitektur

1. **SoR tunggal:** PostgreSQL pusat; tidak ada state transaksi di perangkat.
2. **Domain murni:** `AngsuranCalculator`, `DscrCalculator`, `IdirCalculator`, `Scoring5C`, `ModalKerjaCalculator`, `SukuBungaEfektifCalculator` tanpa Eloquent/DB.
3. **Service orkestrasi:** SLIK aggregation, nomor, policy, approval, ledger.
4. **Immutable:** ledger/audit/buku besar/buku agunan append-only + trigger.
5. **Policy berversi:** `parameter_snapshot` per permohonan; semua rule memakai snapshot.
6. **Nomor atomik:** SQL upsert; tidak ada `count()`/`lockForUpdate()` untuk nomor.
7. **UI native:** Livewire 4.4.x + CSS existing.

## 4. Gate eksekusi

### Gate 0 — Konsolidasi dokumen

- Buat `docs/RENCANA-PRODUKSI-FINAL.md` dari plan ini.
- Rename `RENCANA_IMPLEMENTASI.md` → `RUANG-LINGKUP-PRODUK.md`; perbarui tautan.
- Arsipkan file §2.
- Tambah `docs/arsip/rencana/README.md`.
- **Exit:** hanya satu rencana eksekusi; `rg` bersih; `docs:check --strict` tetap 0 error.

### Gate 1 — Snapshot parameter & grandfathering

**File:**
- `app/Domain/Policy/PolicyParameters.php` — `toArray()`, `fromArray()` strict.
- `database/migrations/2026_11_07_000001_add_parameter_snapshot_to_permohonan_kredit_table.php`
- `app/Models/PermohonanKredit.php` — `$fillable`, `casts`, guard snapshot immutable.
- `app/Services/Permohonan/PermohonanService.php` — isi snapshot dari parameter yang dipilih.
- `app/Services/Policy/PolicyParameterResolver.php` — `forPermohonan()`; **tidak ada fallback `active()`**.
- `app/Services/Rules/AbstractRule.php` + 15 rule — `params(PermohonanKredit $p)`.
- `app/Http/Controllers/Api/V1/AnalisisController.php` — pakai `forPermohonan()`.

**Aturan:**
- `fromArray` wajib punya `id`, `version`, `params`; jika tidak, throw `MissingPolicyParameter`.
- Snapshot tidak boleh berubah setelah create.
- Permohonan lama tanpa snapshot memakai FK historis; jika FK tidak ada, throw.

**Test:** `tests/Feature/Policy/GrandfatheringTest.php` — ubah policy aktif, permohonan lama tidak berubah; permohonan baru memakai policy baru.

**Exit:** tidak ada pemanggilan `active()` untuk permohonan tersimpan.

### Gate 2 — Stage-aware engine + IDIR hard stop

**File:**
- `app/Services/Rules/BusinessRuleEngine.php` — `validateStage()` dengan hasil `{kode, level, result}`; registry class.
- `app/Exceptions/BusinessRuleViolation.php` — panggil `new BusinessRuleViolation($kode, $message)`.
- `app/Services/Permohonan/PermohonanService.php` — stage submit.
- `app/Services/Approval/ApprovalService.php` — stage approve.
- `app/Enums/PolicyParameterKey.php` — `IdirHardStopTpp`.
- `app/Services/Setting/SettingSectionRegistry.php`, `PolicyParameterValidator.php`, `ParameterKebijakanSeeder.php`.
- `app/Services/Rules/Rules/BR11IdirHardStop.php`.

**Matriks aksi:**

| Stage | Rule | Aksi |
|---|---|---|
| Submit | BR-04, BR-08, BR-14, BR-15 | HARD → tolak; SOFT → catat |
| Analisis | BR-01, BR-02, BR-03, BR-10, BR-11, BR-11H, BR-12, BR-13 | evaluasi + simpan status; **jangan abort** |
| Approve | BR-01, BR-02, BR-03, BR-05, BR-11H, BR-13 | HARD → tolak |
| Dokumen | BR-07 | tolak generate |

**Catatan:** BR-05 tidak di submit; aksinya “ditolak maju ke Komite”. BR-06 tidak dimasukkan ke engine approve; ditangani `ApprovalService`.

**BR11IdirHardStop:** hanya TPP; `idir > 0.80` fail; `idir === null` saat approve → fail (bukan pass); analisis → catat status.

**Exit:** submit tanpa analisis tetap lolos; approve IDIR 85% gagal; analisis 85% tersimpan dengan status TIDAK LAYAK.

### Gate 3 — Penomoran atomik & backfill

**File:**
- `app/Services/Workflow/NomorService.php` — `generate()`/method atomik; scope `strtolower(...)`.
- Callers: `PermohonanService`, `KreditService`, `DocumentService`.
- `database/seeders/BackfillNomorSequenceSeeder.php` — regex anchor, `no_rekening_kredit`, `GREATEST`, semua tahun.

**Aturan:**
- Scope service dan backfill identik (lowercase).
- Format: permohonan, kredit, dokumen.
- Backfill idempoten; tidak menurunkan `last_number`.
- Jalankan backfill setelah migrasi, sebelum penomoran baru aktif.

**Test:** `AtomicNomorServiceTest` — tiga format, konkurensi dua proses, backfill idempoten.

### Gate 4 — SLIK aggregation & kapasitas

**File:**
- `database/migrations/2026_11_07_000002_add_slik_fields...`
- `app/Models/PermohonanSlikDebitur.php`, `PermohonanSlikPasangan.php`, `AnalisisKapasitasBayar.php` — `$fillable`/`casts`.
- `app/Services/Slik/SlikAggregationService.php`.
- `AnalisisController::kapasitasCompute`.

**Aturan:**
- Filter `baki_debet > 0`.
- Dedupe `no_fasilitas`; fallback `nama_lembaga + plafon + baki + tanggal_data` (jangan menggabungkan semua null).
- Estimasi flat jika `angsuran_bulanan` null/0; set `is_angsuran_estimated`.
- Simpan `angsuran_slik_debitur`, `angsuran_slik_pasangan`, `total_angsuran_slik`, `jumlah_fasilitas_slik_aktif`, `status_idir`.
- `DscrCalculator` tetap murni; total kewajiban dihitung di service/controller.

**Exit:** DSCR memakai angsuran baru + SLIK; status IDIR tersimpan; test dedupe/null.

### Gate 5 — Golden vectors

**File:**
- `project/tests/golden/cases/*.json` — isi input/expected; `verification` metadata.
- `tests/Feature/Golden/GoldenVectorSuiteTest.php` — dispatcher per `group`.
- `project/scripts/verify-golden.py` — strict mode `verified=29`.

**Wajib:**
- G-TPP-03: 80,00 LOLOS; 80,01 DITOLAK; ubah `expected_hard_stop` di artefak, bukan hanya prosa.
- Rekonsiliasi angka: G-CAP-01/02, G-SCR-01, G-COL-01, G-SCH-04, G-SCH-08, G-STR-01.
- Isi `verified_by`, `verified_at`, `evidence`; target `verified=29`.
- Test mencakup seluruh 29, termasuk COL/RAC/CKP/WO/DEC.
- Dispatcher: kalkulator vs scoring vs policy/negative test.

**Exit:** `verify-golden.py` summary `verified=29`, `fail=0`; test 29 lulus.

### Gate 6 — UI Workbench

**File:**
- `app/Traits/ScopesByCabangLivewire.php` (atau gunakan `ScopesByCabang`).
- `app/Livewire/Analisis/Workbench.php`.
- `resources/views/livewire/analisis/workbench.blade.php` + partials.
- `routes/web.php`.

**Aturan:**
- Middleware: `auth`, `active`, `password.changed`, permission analisis.
- `mount()` memanggil `ensureSameCabang($permohonan, auth()->user())`.
- CSS existing (`alert-warning`, `card`, `badge-danger`); tanpa Tailwind.
- Route didaftarkan setelah komponen ada.
- Banner neraca: teks + ikon, bukan hanya warna.

**Exit:** route 200; role/branch 403 sesuai; UI test; submit tetap aktif saat neraca tidak seimbang.

### Gate 7 — QA & rilis

- `vendor/bin/pint --test`
- `vendor/bin/phpstan analyse --memory-limit=512M`
- `php artisan test --compact` (target ≥205)
- `php artisan ledger:verify`
- `php artisan hardcode:audit`
- `php artisan docs:check --strict`
- `bash scripts/verify.sh --full`
- Backup + restore drill
- UAT 3 cabang + berita acara
- Tanda tangan pemilik proses

**Exit:** semua gate hijau; known issues terdokumentasi; rollback plan siap.

## 5. Rollback

1. Sebelum migrasi: backup DB + `storage/app/private`.
2. Migrasi baru harus reversible; uji `migrate:rollback` di DB salinan.
3. Jika Gate 3/4 gagal: matikan pemakaian sequence baru, kembalikan `NomorService` lama, restore DB.
4. Jika UI gagal: route dapat dinonaktifkan tanpa memengaruhi alur API.
5. Jangan menandai rilis final jika backup/restore belum diuji.

## 6. Keputusan terbuka `[K]`

1. Sumber resmi ambang 35% dan 80% (internal vs payroll); jangan label OJK tanpa bukti.
2. Siapa penandatangan `verified=29` (RAT-02).
3. Definisi dedupe fasilitas tanpa `no_fasilitas`.
4. Kebijakan untuk `angsuran_bulanan` null dan `suku_bunga` null.
5. Apakah BR-05 masuk submit atau hanya Komite (rekomendasi: Komite).
6. Apakah `parameter_snapshot` boleh diubah saat perbaikan data (rekomendasi: tidak).
7. Apakah `ADMIN` boleh lintas cabang (rekomendasi: hanya untuk audit/emergency dengan jejak).

## 7. Urutan eksekusi

1. Gate 0 (dokumen) — hilangkan ambiguitas lebih dulu.
2. Gate 1–2 (fondasi parameter + engine).
3. Gate 3–4 (nomor + SLIK/DSCR).
4. Gate 5 (golden) — sebelum UI.
5. Gate 6 (UI).
6. Gate 7 (QA/rilis).

Setiap gate harus menghasilkan bukti di `docs/evidence/` dan entri `PROGRESS.md`. Tidak ada gate yang boleh ditandai selesai hanya karena kode ditulis.
