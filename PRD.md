# PRD — Aplikasi Analisis Kredit, Dokumen & Ledger BPR

**Produk:** Loan Analytic BPR (PT BPR Bahteramas Kendari)  
**Versi dokumen:** 0.1 (DRAFT, living) · **Tanggal:** 2026-11-05 (WITA)  
**Label bukti:** `[E]` terverifikasi kode/route/migrasi/test/evidence repo app · `[A]` usulan/desain · `[K]` perlu verifikasi/keputusan · `[H]` keputusan bertanda tangan · `[Rencana]` belum terimplementasi. Dokumen ini **bukan** `[H]` dan bukan nasihat hukum.

> Data kritikal (workbook Excel sumber rumus) dikecualikan dari repo; nama/ukuran/lokasi berkas tidak didokumentasikan.

## 1. Ringkasan produk
Aplikasi web untuk **mendigitalkan siklus kredit BPR** — dari pengajuan debitur, analisis kelayakan (kapasitas, rasio, scoring 5C/FSS), kalkulasi & jadwal angsuran, approval bertingkat, pembuatan dokumen, hingga **pencairan, pembayaran, dan pencatatan ledger double-entry append-only** — dengan jejak audit dan integritas data yang dapat diverifikasi.

Tujuan inti: menggantikan proses manual (spreadsheet/dokumen lepas) dengan alur **terstruktur, dapat diaudit, dan konsisten rumusnya**, sambil menjaga koreksi wajib atas temuan pada referensi spreadsheet (lihat §5).

## 2. Latar belakang & masalah
- Proses analisis, dokumen, dan pencatatan tersebar di spreadsheet/dokumen terpisah → rawan **inkonsistensi rumus, salah salin, dan jejak keputusan yang lemah** `[A]`.
- Ada temuan pada referensi spreadsheet yang **tidak boleh disalin mentah**: konflik mesin keputusan (IDIR hard-stop vs skor), SLIK tidak terhubung ke DSR, bug neraca/ringkasan, angka/terbilang hardcode di dokumen, risiko double-haircut LTV `[E: analisis referensi, §5]`.
- Kebutuhan kontrol: maker-checker, RBAC, audit akses PII, ledger append-only ber-hash-chain, dan rekonsiliasi `[E]/[A]`.

## 3. Tujuan & metrik keberhasilan
| Tujuan | Metrik/indikator (target `[K]` kecuali `[E]`) |
|---|---|
| Paritas & kebenaran kalkulasi | Golden test lulus (toleransi jadwal ≤ Rp1/period) sebelum engine/fix dikunci `[A]`; kasus acuan IDIR 85,93%/penghasilan Rp2.948.080/rate eksak `[K]` tervalidasi |
| Integritas keuangan | `debit = credit` per jurnal (guard + DB trigger), ledger immutable + hash-chain, `ledger:verify` hijau `[E/A]` |
| Jejak keputusan | 100% transisi status/override/akses PII tercatat di audit event dengan aktor & waktu `[A]` |
| Kontrol akses | RBAC + maker-checker + authority limits; AO tidak self-approve `[A, D-12]` |
| Kualitas data import | Review-first (confidence + provenance); dedupe; validasi SLIK/NIK `[A]` |
| Operasional | Paralel run ≥ 30 kasus dengan selisih terjelaskan sebelum go-live `[A]` |

## 4. Pengguna & persona
| Peran | Kebutuhan utama | Catatan akses `[A]` |
|---|---|---|
| Account Officer (AO) | Input permohonan/profil, unggah dokumen, ajukan analisis | tidak bisa self-approve |
| Analis Kredit | Analisis kapasitas/rasio/MK/DSCR/stress/5C, simulasi plafon-tenor, narasi | tulis analisis & rekomendasi |
| Komite/Pejabat berwenang | Review paket, putusan setuju/tolak/syarat sesuai authority limit | maker-checker |
| Operasional/Kasir | Pencairan (posting ledger), pembayaran, TPP batch, blokir tabungan | aksi finansial terkontrol |
| Kepatuhan/Legal/Audit | Cek dokumen, consent, akses log, verifikasi integritas ledger | baca/audit; PII termasker |
| Admin/IT | Master/policy, user/RBAC, backup/restore, monitoring | least privilege |

> Matriks RBAC & authority limits final = `[K, D-12]`.

## 5. Ruang lingkup

### 5.1 In-scope (berdasarkan fase, `[A]/[Rencana]`)
- **Fondasi `[E/Rencana lanjut]`:** auth, tenant/cabang, RBAC+policy, audit event, file privat+sha256, queue/scheduler, Money/terbilang/tanggal.
- **Master & policy berversi `[A]`:** products, `policy_sets` (snapshot per analisis), fee/insurance/haircut, scoring model & band (data berversi), checklist & template registry, COA mapping.
- **Domain kredit:**
  - Debitur + identitas (NIK terenkripsi + blind index) + pasangan/usaha/pekerjaan `[A]`.
  - Permohonan (plafon, rate, tenor, sistem **Flat/Anuitas/Grace**) + SLIK terstruktur + consent `[E ref/A]`.
  - **Engine murni, deterministik, versioned** `[A]`: `ScheduleEngine` (Flat/Anuitas/Grace + balloon), `FeeEngine` & dana diterima, `PayoffEngine` (top-up), kapasitas TPP (IDIR+DSR total dari SLIK), kapasitas produktif (RPC/DSCR/plafon max), modal kerja, neraca/LR/rasio, stress 6 skenario, agunan/haircut/LTV (pisah nilai pasar/likuidasi/PPKA/basis LTV), scoring 5C & FSS TPP (redesign, data berversi), `BalloonEngine`, `DecisionEngine` (hard-stop vs soft-score), `Terbilang/Money`.
  - **Snapshot analisis** immutable (input + output + versi policy/engine + hash) `[A]`.
- **Workflow & dokumen `[A]`:** status bertahap (draft→analisis→verifikasi→rekomendasi→approval→akad→cair→aktif→lunas/…); approval maker-checker; generate dokumen dari snapshot (tanpa hardcode, versi/hash/QR); checklist & verifikasi lapangan.
- **Pencairan & servicing `[A, D-01]`:** memo cair → ledger, aktivasi + jadwal, pembayaran (alokasi policy) / TPP batch, blokir/rilis tabungan, denda, pelunasan dipercepat, rekonsiliasi.
- **Ledger & audit `[E/A]`:** double-entry append-only, idempoten, hash-chain, trigger immutable, `ledger:verify`, jurnal pembalik (koreksi), rekonsiliasi `external_refs`.
- **Import review-first `[A, D-03/D-10]`:** SLIK, KTP/KK (NIK decode; OCR opsional lokal), workbook legacy, TPP batch, statement, ZIP dokumen, payment CSV — dengan confidence + provenance; consent SLIK aktif.
- **UI workbench `[A]`:** dashboard KPI/red flag, wizard, workbench analisis (tab), simulator plafon/tenor, review import, layar dokumen/approval, kartu pinjaman.
- **Rilis bertahap:** R1 TPP end-to-end (X1) → R2 produktif MK/Grace (X2/X3) → R3 servicing lanjut `[A]`.

### 5.2 Out of scope (saat ini / kecuali `[K]` disetujui)
- Mengganti core banking resmi (R1 = ledger bayangan + rekonsiliasi, `[K D-01]`).
- Integrasi Dukcapil / e-sign / e-materai / API SLIK langsung (bertahap, `[K D-03/D-10]`).
- OCR/LLM cloud atas data PII/SLIK (default **tidak**; lokal/opsional, `[K D-10]`).
- Redis/MinIO (kecuali diputuskan); Filament (kecuali `[K D-04]`).
- Penyalinan data/PII workbook nyata ke repo/fixture/log/prompt (dilarang).

### 5.3 Non-goals
- Menjadi sistem akuntansi umum BPR (hanya ledger kredit terkontrol & mapping COA).
- Meng-otomasi putusan kredit final (putusan tetap manusia berwenang).

## 6. Kebutuhan fungsional (ringkas per modul)
| ID | Modul | Kebutuhan inti | Bukti/Ref |
|---|---|---|---|
| FR-01 | Auth & akses | Login, tenant/cabang scope, RBAC, maker-checker, authority limits `[K D-12]` | `[E/A]` |
| FR-02 | Debitur & consent | CRUD debitur/pasangan/usaha, NIK terenkripsi+mask, consent SLIK/PDP | `[E ref/A]` |
| FR-03 | Permohonan & SLIK | Plafon/rate/tenor/sistem, import/entry SLIK terstruktur, gate consent | `[E ref/A]` |
| FR-04 | Analisis & engine | IDIR/DSR, neraca/LR/rasio/MK/DSCR/stress, 5C/FSS, agunan/LTV, balloon, snapshot | `[E ref/A, §4]` |
| FR-05 | Jadwal & kalkulasi | Flat/Anuitas/Grace, RATE/IRR eksak, fee & dana diterima, top-up/payoff | `[E ref/A]` |
| FR-06 | Keputusan & approval | Hard-stop vs soft-score, rekomendasi + narasi, komite berjenjang, alasan override | `[A, !T1]` |
| FR-07 | Dokumen & checklist | Template versioned dari snapshot (tanpa hardcode), numbering/hash/QR, checklist/verifikasi | `[A, !T8/T9]` |
| FR-08 | Pencairan & servicing | Memo cair→ledger, jadwal aktif, bayar/TPP/blokir/denda/pelunasan, kartu pinjaman | `[A, D-01/D-06]` |
| FR-09 | Ledger & audit | Double-entry append-only, idempoten, hash-chain, immutable, `verify`, reversal, audit event | `[E/A]` |
| FR-10 | Import & DQ | Adapter multi-sumber, staging + confidence + provenance + review, dedupe, validasi | `[A]` |
| FR-11 | Laporan & integritas | Dashboard/red flag, rekap, rekon `external_refs`, verifikasi ledger | `[A]` |

## 7. Koreksi wajib (tidak boleh disalin dari referensi)
| ID | Larangan | Implementasi wajib |
|---|---|---|
| T1 | Konflik IDIR vs FSS | DecisionEngine: hard-stop vs soft-score + flag konflik |
| T2,T4 | SLIK manual/tak konsisten skor | Angsuran eksisting & faktor karakter dari SLIK terstruktur; override beralasan |
| T3 | Anomali SLIK (baki>plafon/duplikat) | Validasi + peringatan (bukan silent) |
| T5 | FSS TPP = template KPR | Model TPP redesign berversi `[K D-02]` |
| T6,T7 | Neraca double-count/plug; ref baris basi | Klasifikasi enum, saldo riil/flag, komponen eksplisit; golden terisi |
| T8,T9,T12 | Dokumen angka/terbilang hardcode; add-in; tak ikut sistem | `DocumentData` + terbilang engine + 1 sumber jadwal; no hardcode/add-in |
| T10 | Dana diterima vs memo/blokir lama | Perlakuan blokir lama eksplisit + jurnal seimbang |
| T13 | Double haircut LTV vs PPKA | Pisah nilai pasar/likuidasi/PPKA/basis LTV `[K D-07]` |
| T14 | `TODAY()` ubah umur; Condition manual | Snapshot usia `analysis_date`; sub-kriteria Condition |
| — | Konstanta flat→efektif 1,81x | **RATE/IRR eksak**; 1,81x/0,87x hanya pembanding |

Koreksi angka acuan `[K]`: IDIR **85,93%** (bukan 83%), penghasilan bersih **Rp2.948.080**, rate eksak (contoh 28,88%/60 bln).

## 8. Kebutuhan non-fungsional
- **Akurasi & determinisme:** uang `BIGINT` rupiah + `brick/math` (tanpa float); engine murni/deterministik; snapshot reproducible `[E/A]`.
- **Integritas:** ledger append-only + immutable trigger + hash-chain + idempoten + `SELECT FOR UPDATE` per loan; koreksi via jurnal pembalik `[A]`.
- **Keamanan & privasi:** RBAC least privilege, PII terenkripsi + blind index + masker, audit akses/ekspor, consent SLIK/PDP, storage privat (signed URL, sha256), backup terenkripsi + uji restore; **no PII/secret di repo/log/fixture/prompt** `[A]`.
- **Kepatuhan:** angka/pasal/haircut/tarif dari sumber terverifikasi bertanda tangan (`[H]`); retensi/hosting sesuai `[K D-09]`.
- **Keterlacakan:** snapshot (input+policy+engine+output), provenance field import, versioning dokumen/policy/scoring/template.
- **Kualitas & rilis:** Pest + Larastan bertahap + Pint; `composer qa` hijau; golden sebelum engine; PR kecil; paralel run ≥30 kasus `[A]`.
- **Portabilitas stack `[A, selaras repo]`:** Laravel 13, PHP ≥ 8.3, PostgreSQL, queue database/sync; Livewire/Blade (Filament hanya `[K D-04]`); tanpa Redis/MinIO kecuali diputuskan.

## 9. Ketergantungan & keputusan terbuka (ringkas)
| ID | Keputusan | Target fase |
|---|---|---|
| D-01 | Ledger resmi vs bayangan + COA mapping | F2/F8 |
| D-02 | Model FSS TPP redesign + band 5C | F3/F4 |
| D-03 | Format/akses SLIK iDeb + korpus sampel | F0/F5 |
| D-04 | UI stack (Livewire/Blade vs Filament) | F1/F6 |
| D-05 | Pembulatan & pengakuan pendapatan | F2/F4 |
| D-06 | Alokasi bayar, denda, penalti, saldo blokir lama | F2/F4/F8 |
| D-07 | Basis LTV & haircut POJK final | F3/F4 |
| D-08 | Penanggung & tarif asuransi | F3/F4/F7 |
| D-09 | Hosting & retensi (PII/SLIK/ledger) | F1/F9 |
| D-10 | OCR/LLM cloud diizinkan? (default tidak) | F5 |
| D-11 | Ambang IDIR/DSR hard-stop | F4 |
| D-12 | Matriks kewenangan approval | F3/F6 |

Detail: `04-keputusan-risiko-gap.md` dan `project/docs/decisions.md`.

## 10. Risiko utama
Format SLIK/KTP beragam (parser + review wajib), paritas engine vs referensi (golden + paralel run, perbaiki T1–T14), kesalahan ledger (invarian + immutable + verify + rekon), kebocoran PII (enkripsi/mask/audit/retensi, no PII di repo), band/ambang belum `[H]` (policy berversi DRAFT→ACTIVE), dan template hukum (review Legal/Notaris). Lihat `04-keputusan-risiko-gap.md`.

## 11. Milestone (usulan)
| Rilis/Fase | Cakupan | Bukti selesai (gerbang) |
|---|---|---|
| F0–F1 | Spec pack, golden ≥25 `[K]`, fondasi + audit | QA hijau, isolasi tenant |
| F2–F3 | Ledger append-only + master/policy berversi | fuzz/verify hijau, no update/delete |
| F4 | Semua engine + snapshot + koreksi T1–T14 | **100% golden** lulus (toleransi disepakati) |
| F5–F7 | Import, workflow/UI, dokumen | kasus end-to-end; dokumen tanpa hardcode |
| F8 | Pencairan & servicing + rekon | dana diterima = saldo; ledger=kartu=rekon sampel |
| F9 | Hardening & go-live | paralel run ≥30 kasus, restore lulus, SOP |

## 12. Rujukan
- [README](README.md) · [01 inventaris](01-inventaris-excel.md) · [02 pemetaan](02-pemetaan-excel-ke-aplikasi.md) · [03 rencana implementasi](03-rencana-implementasi-detail.md) · [04 keputusan/risiko/gap](04-keputusan-risiko-gap.md) · [05 bukti ekstraksi](05-bukti-ekstraksi.md)
- [ERD](ERD.md) · [Spec pack](project/docs/spec/README.md) · [Decisions](project/docs/decisions.md) · [Manual book](project/docs/manual/README.md)
