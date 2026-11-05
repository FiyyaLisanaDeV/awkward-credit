# QUESTIONS / UNKNOWN — F1 Fondasi Loan Analytic BPR

- Tanggal cek: 2026-11-05 (Asia/Makassar) [E: sesi scan]
- Pemicu: PROMPT TUNGGAL — F1 (2026-11-05, pasca review DRC)
- Status: **BERHENTI** — prasyarat F1 (A1–A12) tidak lengkap. Menunggu keputusan manusia. Tidak mengarang.

## Hasil cek prasyarat A1–A12 (bukti nyata)

| # | Prasyarat (kontrak F1) | Status | Bukti [E] |
|---|---|---|---|
| A1 | F0 LULUS oleh pemeriksa berbeda, `docs/evidence/F0.md` berstatus LULUS | **KURANG** | Tidak ada `docs/evidence/F0.md` / berkas F0 berstatus LULUS. Yang ada: `docs/evidence/00-baseline-*.txt`, `phase-00..11-post-*.txt`. `docs/PHASE_00_RECON.md` ada, tapi bukan bukti LULUS F0 sesuai kontrak. |
| A2 | D-01 (ledger resmi/bayangan) diputus, bertanda tangan | **KURANG** | `docs/DECISIONS.md` berisi ADR-001..ADR-026 (format/penomoran berbeda dari D-01..). Tidak ada entri D-01 bertanda tangan pemutus. Terkait: ADR-012 (ledger hash chain) ada, tapi bukan D-01 “resmi/bayangan” bertanda tangan sesuai kontrak. |
| A3 | D-02 (model scoring TPP) diputus | **KURANG** | Tidak ada D-02 bertanda tangan. Q1 di PROGRESS menyatakan band skor 5C (DSCR/equity/LTV/SLIK→1–5) belum final/DRAFT `[?]`. |
| A4 | D-03 (format SLIK) diputus (min. unggah manual) | **KURANG** | Tidak ada D-03 bertanda tangan. ADR-022/Q4 membahas SLIK (3 jalur/fixture contoh) tapi bukan keputusan D-03 bertanda tangan. |
| A5 | D-04 (Filament vs Livewire) diputus | **KURANG (format berbeda)** | Tidak ada D-04 bertanda tangan. Keputusan nyata: **Livewire polos** + CSS tulis tangan, **tanpa Filament** (ADR-008 `[E]`; `composer show filament/filament` = tidak terpasang, `livewire/livewire` v4.4.8 terpasang). Kontrak F1 butuh D-04 bertanda tangan eksplisit. |
| A6 | D-09 (hosting/retensi) diputus | **KURANG** | Tidak ada D-09 bertanda tangan. ADR-001/002/006 menyentuh infra/storage, tapi bukan keputusan hosting/retensi bertanda tangan. |
| A7 | D-11 (ambang IDIR/DSR) diputus atau DRAFT eksplisit | **KURANG (ambigu)** | Tidak ada D-11. Ada parameter terkait di seed/kode domain (fase 07/10 sudah jalan), namun kontrak minta D-11 bertanda tangan/DRAFT eksplisit di `docs/decisions.md`. Status aktual keputusan **tidak terbaca sebagai D-11**. |
| A8 | D-12 (matriks kewenangan) diputus | **KURANG** | Tidak ada D-12 bertanda tangan. |
| A9 | >=25 kasus golden dari Excel di `tests/golden/` | **KURANG** | Tidak ada folder `tests/golden/`. Yang ada: `tests/Fixtures/golden/tpp.json` (1 kasus) `[E]`. < 25. |
| A10 | Spec pack dari Excel di `docs/spec/`, PII bersih + pemindai PII hijau | **KURANG** | `docs/spec/` tidak ada `[E]`. Pemindai PII/hasil hijau tidak ditemukan. Workbook Excel ada di `APP BPR/` (diklaim dummy oleh prompt, belum diverifikasi pemindai). |
| A11 | Versi PostgreSQL diputus di F0 | **SEBAGIAN** | PostgreSQL 16.15 native terdeteksi & dipakai (ADR-002, baseline `[E]`: `psql --version` 16.15, `pg_isready` OK). Namun bukti “diputus di F0 + F0 LULUS” (A1) tidak ada. |
| A12 | Repo scaffold Laravel 13, `composer qa` hijau pada repo kosong | **TIDAK SESUAI KONTEKS (repo sudah jauh)** | Repo BUKAN kosong: Laravel 13.40.0, 61 migrasi, 286 file test (Pest), fase 00–11 DONE per PROGRESS `[E]`. QA saat ini: `pint --test` **PASS**; `phpstan` **FAIL (1 error, `Livewire/Simulator.php:73`)**; `pest` (full) **286 file PASS**, tapi `pest tests/Feature/` **11 failed, 82 passed** (regresi/isu lingkungan fitur). Tidak ada script `composer qa` yang diverifikasi di sini. |

## UNKNOWN (yang dibutuhkan agar F1 bisa mulai sesuai kontrak)

- UNKNOWN: A1 — bukti `docs/evidence/F0.md` berstatus **LULUS** oleh pemeriksa berbeda (atau konfirmasi bahwa F0/rekaman LULUS ada di lokasi lain dan boleh dirujuk).
- UNKNOWN: A2 — keputusan **D-01 (ledger resmi/bayangan)** yang bertanda tangan (pemutus + tanggal + sumber) di `docs/decisions.md` (atau konfirmasi pemetaan ADR-012 → D-01 disahkan).
- UNKNOWN: A3 — keputusan **D-02 (model scoring TPP)** bertanda tangan; termasuk tabel band skor 5C final (Q1).
- UNKNOWN: A4 — keputusan **D-03 (format SLIK, minimal unggah manual)** bertanda tangan.
- UNKNOWN: A5 — keputusan **D-04 (Filament vs Livewire)** bertanda tangan yang mengesahkan status aktual (Livewire polos, tanpa Filament).
- UNKNOWN: A6 — keputusan **D-09 (hosting/retensi)** bertanda tangan.
- UNKNOWN: A7 — keputusan **D-11 (ambang IDIR/DSR)** bertanda tangan **atau** DRAFT eksplisit yang disetujui sebagai pegangan F1.
- UNKNOWN: A8 — keputusan **D-12 (matriks kewenangan)** bertanda tangan.
- UNKNOWN: A9 — **>=25 kasus golden** dari Excel, format JSON, lokasi `tests/golden/` (atau konfirmasi `tests/Fixtures/golden/` diterima) — dengan nilai **terkoreksi** (IDIR 85,93%, penghasilan bersih Rp2.948.080, rate eksak 28,88%/60 bln; tanpa faktor 1,81x) dan sumber per kasus.
- UNKNOWN: A10 — **spec pack** hasil ekstraksi Excel di `docs/spec/`, bukti **pemindai PII hijau**, dan konfirmasi Excel = dummy (DRC #4: masukkan basis nilai per baris + aturan penuaan haircut ke spec v2).
- UNKNOWN: A11 — konfirmasi eksplisit versi PostgreSQL diputus di F0 (16.x) dalam format keputusan yang disyaratkan.
- UNKNOWN: A12 — definisi “repo scaffold kosong + `composer qa` hijau” pada konteks repo yang **sudah implementasi fase 0–11**: apakah F1 dijalankan sebagai (a) audit/penyelarasan fondasi di repo ini, (b) fondasi baru terpisah, atau (c) kontrak F1 ini tidak berlaku ke repo `loan-analytic-bpr` yang ada. Juga perlu keputusan atas regresi saat ini (phpstan 1 error; 11 test Feature gagal).

## Catatan konflik yang harus diselesaikan sebelum golden test (A9)

- Golden existing `tests/Fixtures/golden/tpp.json` [E]: `idir 0.831124…` (~83,11%), `penghasilan_bersih 3.048.080`, `rate 0.18` (flat). Ini **bertentangan** dengan koreksi wajib F1 (IDIR 85,93%, penghasilan bersih 2.948.080, rate eksak 28,88% pada 60 bln, tanpa faktor 1,81x). Per kontrak, angka hukum/kebijakan/hasil tidak boleh dari ingatan model — **butuh sumber workbook/kasus yang disepakati** dan regenerasi golden test (ditulis sebelum kode).

## Konteks repo aktual (ringkas) [E]

- App: `/APP BPR/loan-analytic-bpr` — Laravel 13.40.0, PHP 8.3.7, PostgreSQL 16.15, Livewire 4.4.8, Pest 4.7.8, Larastan 3.14.0, brick/math 1.0.0; Filament tidak terpasang.
- PROGRESS: fase 00–11 DONE (commit `7e11e8e`), next 12; 61 migrasi, 286 file test total.
- QA snapshot: Pint PASS; Larastan FAIL (1 error: `app/Livewire/Simulator.php:73`); Pest full PASS (286 files) tapi `tests/Feature` 11 failed / 82 passed.
- Repo belum commit/untracked di root `/BPR`; app repo punya working tree dirty (modifikasi controller/request/lang/css/routes + untracked).

**Tindakan saya:** berhenti sesuai kontrak. Tidak membuat/mengubah domain, seed, ledger, atau keputusan. Menunggu manusia melengkapi A1–A12 / mengklarifikasi UNKNOWN di atas.