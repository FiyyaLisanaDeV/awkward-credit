# CHANGELOG — Manual Book

Semua perubahan manual dicatat di sini (append-oriented). Format: tanggal, versi, ruang lingkup, bukti, penulis/reviewer.

## 0.1 — 2026-11-05 (DRAFT, init)
- **Cakupan:** pembuatan manual book lengkap berbasis bukti live repo `loan-analytic-bpr` (fase 00–11 `[E]`) + rencana L1–L9/research `[K]`; TOC 23 bab; aset mekanisme update berkala (MANIFEST, feature matrix, template, checklist, evidence note, `scripts/manual-snapshot.sh`).
- **Sumber bukti `[E]`:** `routes/web.php`, `routes/api.php` (73 route), `app/Http/Controllers/*`, `app/Livewire/*`, `app/Services/{Calculation,Rules,Scoring,Ledger,Simulation,Policy,Workflow,Debitur,Permohonan}`, `app/Enums/*` (35), `app/Models/*` (55), `config/{loan,ledger,import}*`, `database/migrations` (61), seeders, `tests/Feature/*`, fixture `tests/Fixtures/golden/tpp.json`, `docs/PROGRESS.md`, `docs/DECISIONS.md`, evidence phase 00–11.
- **Koreksi snapshot (dari `manual-snapshot.sh`):** enum = **35** (bukan 37); file test = **30** (working tree, fase 12) vs 26 di PROGRESS fase 11; commit HEAD = `1f4e3d2` (tag `phase-11-done`); working tree punya 26 berkas belum di-commit (fase 12 UI/Livewire).
- **Known issues dicatat:** konflik golden C-002 `[K]`; kalkulasi dominan `float` `[E]`; gap L1–L9; baseline QA live saat edit = `[?]`.
- **Aksi wajib berkala:** update manual pada setiap fitur/fungsi **final** (lihat `chapters/22-mekanisme-update-manual-book.md`).

## 0.1.1 — 2026-11-05 (publikasi repo + wiki)
- **Repo GitHub:** playbook + manual dipublikasikan ke `FiyyaLisanaDeV/awkward-credit` (branch `main`).
- **Wiki:** mekanisme publikasi manual → GitHub Wiki ditambahkan (`project/scripts/build-wiki.py`, `scripts/publish-wiki.sh`); 32 halaman (Home, _Sidebar, _Footer, Bab 1–23, + lampiran). Bab 22 §22.7b diperbarui.
- **Keamanan:** `.gitignore` mengecualikan `*token*`, `.env*`, `*.key/*.pem`, `.DS_Store`; file token ditemukan di 2 lokasi (`/` dan `project/docs/spec/`) — **tidak dipublikasikan**.
- **Master Excel (KRITIS):** 3 workbook (kasus TPP, MASTER Modal Kerja, MASTER Grace Period) di workspace lokal **tidak** masuk repo (terverifikasi 404 di remote). **Nama/ukuran/lokasi berkas tidak didokumentasikan** (hanya pola generik `*.xlsx/*.xls/*.xlsm` di `.gitignore`).

## 0.1.2 — 2026-11-05 (PRD + ERD → wiki)
- **Dokumen produk baru (root repo):** `PRD.md` — Product Requirements (ringkasan, persona, ruang lingkup & non-goal, FR-01..FR-11, koreksi wajib T1–T14, NFR, keputusan D-01..D-12, milestone F0–F9); `ERD.md` — Entity Relationship (konvensi skema, peta domain, ERD Mermaid inti + per domain, relasi/kardinalitas, kolom kunci & invariant, enum, entitas target `[Rencana]`, gap skema).
- **Bukti `[E]`:** 61 migrasi · 55 model · 35 enum pada repo `loan-analytic-bpr` (nama tabel aktual: `debitur`, `permohonan_kredit`, `analisis_*`, `kredit`, `jadwal_angsuran`, `ledger`, dll).
- **Wiki:** `project/scripts/build-wiki.py` diperluas → publikasi dokumen root sebagai halaman `PRD` & `ERD`; Home + `_Sidebar` menambah bagian **Dokumen Produk**. Wiki kini **34 halaman**.
- **Guardrail:** tautan internal diverifikasi ke berkas nyata; tanpa nama/ukuran/lokasi workbook Excel maupun path absolut.

## 0.1.3 — 2026-11-05 (README dipoles)
- **README.md** disusun ulang agar lebih informatif & terstruktur: tabel properti proyek, daftar isi bertaut, konteks & tujuan, tabel status per area, sumber referensi X1/X2/X3 (tetap tanpa identitas berkas), peta dokumen, arsitektur & stack, kontrak/guardrail, tabel koreksi wajib T1–T14, golden/spec/evidence, manual & wiki, struktur repositori, label bukti + precedence, dan panduan mulai.
- **Verifikasi:** 23 tautan internal dicek (semua valid); tanpa nama/ukuran/lokasi workbook Excel maupun path absolut.
- **Wiki:** 34 halaman (tidak berubah); README bukan halaman wiki.

## 0.1.4 — 2026-11-05 (mockup interaktif + golden readiness)
- **Mockup interaktif (verifikasi visual):** `mockup-loan-analytic-bpr.html` (standalone) — shell + navigasi (Dasbor, Debitur, Permohonan wizard+SLIK review-first, Workbench Analisis: neraca/LR, rasio/MK, kapasitas/DSCR + demo hard-stop vs soft-score T1, stress, 5C/FSS, rekomendasi, Simulasi Flat/Anuitas/Grace-bullet dengan jadwal + estimasi IRR numerik — tanpa 1,81x, Ledger & integritas, Golden Readiness). Data **dummy**, tanpa PII, tanpa identitas workbook.
- **Golden checker:** `project/scripts/verify-golden.py` (Decimal) → 29/29 OK, 0 error, 4 negative_test, 0 PII; filled 6/29 (partial/claim), **verified 0/29** (gate F4 tertutup). Evidence/handoff/golden README diperbarui.

## 0.1.5 — 2026-11-06 (Fase 12–15: UI, workflow, dashboard, pembayaran, dokumen, quality gate)
- **Fase 12–15 selesai** di `loan-analytic-bpr`: Blade UI + Livewire; `ApprovalService` (matriks band, permission ganda, SoD, BR-06) + UI approval & timeline; notifikasi bell; dashboard nyata (cache 300 dtk); pencairan→jadwal→pembayaran ber-ledger; 17 generator dokumen + template; quality gate (arch/guard/performance).
- **QA live:** `verify.sh --full` exit 0; **138 test** (1779 assertions, 43 file); pint + PHPStan L5/L8 OK; `hardcode:audit` 0; `docs:check --strict` 0; `ledger:verify` OK; 61 migrasi · 77 route.
- **Golden:** 29 kasus `recomputed` (Decimal) — **0 verified**. TPP-01: IDIR 85,93%, rate×12 28,88%, effective annual 33,02%; tanpa 1,81x.
- **Remote:** repo app dipush ke GitHub `FiyyaLisanaDeV/awkward-credit` **branch `app`** (+tags `phase-01-done`..`phase-15-done`); `main` remote tetap `commandcode bpr` (tidak ditimpa).
- **Manual:** Bab 10/11/14/20 disinkronkan dengan implementasi; Bab 20 mencatat sisa gap (Fase 16–18, 12B, template DRAFT, policy divergence).
