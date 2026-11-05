# CommandCode BPR — Rencana & Dokumentasi *Loan Analytic BPR*

Repositori **perencanaan, spesifikasi, dan dokumentasi** untuk aplikasi **Analisis Kredit, Dokumen & Ledger BPR** (PT BPR Bahteramas Kendari). Isinya menurunkan proses kredit BPR dari **workbook Excel referensi** menjadi modul aplikasi yang terstruktur, dapat diaudit, dan konsisten rumusnya.

> ⚠️ Repo ini **dokumen perencanaan/analisis** (`[A]/[K]/[Rencana]`). **Bukan** keputusan bertanda tangan (`[H]`), **bukan** spesifikasi produksi final, dan **tidak** memuat data nasabah (PII).

| Properti | Nilai |
|---|---|
| Tahap | Perencanaan/analisis — **DRAFT**, belum rilis |
| Versi dokumen | 0.1 · 2026-11-05 (WITA) |
| Aplikasi target | Laravel 13 · PHP ≥ 8.3 · PostgreSQL · Livewire/Blade |
| Wiki (manual + PRD/ERD) | **34 halaman** — [buka Wiki](https://github.com/FiyyaLisanaDeV/awkward-credit/wiki) |
| Lisensi | [`LICENSE`](LICENSE) |

**Baca cepat:** [PRD](PRD.md) *(apa & mengapa)* → [ERD](ERD.md) *(data)* → [03 Rencana](03-rencana-implementasi-detail.md) *(bagaimana)* → [04 Keputusan](04-keputusan-risiko-gap.md) *(apa yang harus diputuskan)*.

## Daftar isi
1. [Konteks & tujuan](#1-konteks--tujuan)
2. [Status proyek](#2-status-proyek)
3. [Sumber referensi (3 workbook)](#3-sumber-referensi-3-workbook)
4. [Peta dokumen](#4-peta-dokumen)
5. [Arsitektur & stack target](#5-arsitektur--stack-target)
6. [Kontrak & guardrail](#6-kontrak--guardrail)
7. [Koreksi wajib (jangan disalin mentah)](#7-koreksi-wajib-jangan-disalin-mentah)
8. [Golden test, spec pack & evidence](#8-golden-test-spec-pack--evidence)
9. [Manual book & wiki](#9-manual-book--wiki)
10. [Struktur repositori](#10-struktur-repositori)
11. [Label bukti](#11-label-bukti)
12. [Cara mulai](#12-cara-mulai)

## 1. Konteks & tujuan
Proses kredit BPR (analisis, kalkulasi, dokumen, pencatatan) berjalan manual di spreadsheet/dokumen lepas → rawan inkonsistensi rumus, salah salin, dan jejak keputusan yang lemah `[A]`.

**Tujuan repo:** memetakan seluruh struktur, rumus, alur, dan artefak bisnis dari 3 workbook Excel referensi ke **modul aplikasi, model data, engine perhitungan, workflow, dokumen, dan ledger** — disertai koreksi wajib atas temuan pada referensi. Semua hasil analisis ditargetkan **snapshot** (input + versi policy/engine + output) agar dapat diaudit dan di-*replay*.

**Cakupan rilis bertahap (usulan):** R1 TPP end-to-end → R2 produktif (Modal Kerja & Grace Period) → R3 servicing lanjut `[A]`.

## 2. Status proyek
| Area | Status |
|---|---|
| Dokumen produk (PRD, ERD) | ✅ tersedia (`DRAFT`) |
| Playbook perencanaan (01–05) | ✅ tersedia |
| Manual book (23 bab) | ✅ tersedia + terpublikasi ke Wiki |
| Spec pack (`project/docs/spec/`) | 🟡 14 dokumen · `DRAFT`, angka `[K]` |
| Golden vectors | 🟡 **29 kasus dummy** (`[K]` placeholder) |
| Keputusan D-01..D-20 | 🔴 `DRAFT/UNKNOWN` — belum ditandatangani `[H]` |
| Implementasi (repo app `loan-analytic-bpr`) | 🟡 fase 0–11 `[E]`; perlu **gap assessment** |

## 3. Sumber referensi (3 workbook)
Nama berkas, ukuran, dan lokasi workbook **tidak didokumentasikan** di repo ini karena tergolong **data kritikal** — dikecualikan lewat `.gitignore` (`*.xlsx/*.xls/*.xlsm`) dan tidak pernah di-commit. Ringkasan peran (label internal X1/X2/X3):

| # | Peran produk | Sistem bunga | Cakupan ringkas |
|---|---|---|---|
| **X1** | Kasus TPP konsumtif (end-to-end) | Flat | analisis, hitungan/biaya/jadwal, FSS scoring, SLIK consent, memo cair, akad/surat, asuransi, appraisal, checklist/registrasi |
| **X2** | Template MASTER Modal Kerja | Flat · Anuitas | profil → asumsi/POJK → neraca/LR → rasio → modal kerja → kapasitas/DSCR → sensitivitas → 5C → rekomendasi → jaminan → perjanjian → jadwal → checklist |
| **X3** | Template MASTER Grace Period (superset) | Flat · Anuitas · Grace | struktur X2 + field sumber/perkiraan pelunasan pokok (balloon) & blok kecukupan pelunasan |

> Fakta struktur bersumber dari enumerasi file + ekstraksi struktural (ZIP/XML) `[E]`. Nilai sel identitas **tidak dicatat**. Data input workbook bersifat **dummy** `[E user]`.

## 4. Peta dokumen
| Dokumen | Isi |
|---|---|
| [`PRD.md`](PRD.md) | Product Requirements: persona, ruang lingkup & non-goal, FR-01..FR-11, koreksi wajib, NFR, keputusan terbuka, milestone |
| [`ERD.md`](ERD.md) | Entity Relationship: skema data (61 migrasi · 55 model · 35 enum), ERD Mermaid, relasi, invariant, gap skema |
| [`01-inventaris-excel.md`](01-inventaris-excel.md) | Inventaris struktur 3 workbook: sheet, rumus kunci, input/dropdown, output, dependency, delta X2↔X3 |
| [`02-pemetaan-excel-ke-aplikasi.md`](02-pemetaan-excel-ke-aplikasi.md) | Traceability sheet→modul/domain, field→tabel/DTO, dokumen→template, import→adapter |
| [`03-rencana-implementasi-detail.md`](03-rencana-implementasi-detail.md) | Arsitektur, engine & formula, data model target, workflow, ledger/audit, UI/API, testing/golden, backlog & DoD |
| [`04-keputusan-risiko-gap.md`](04-keputusan-risiko-gap.md) | Keputusan terbuka (D-01..D-12), risiko, temuan Excel yang **tidak boleh disalin**, gap yang memblokir |
| [`05-bukti-ekstraksi.md`](05-bukti-ekstraksi.md) | Metode ekstraksi, keterbatasan, jejak bukti; konfirmasi tanpa PII |
| [`project/`](project/README.md) | Workspace eksekusi: spec pack, decisions D-01..D-20, claims, questions, evidence, handoff, golden vectors, manual book |
| [`research/`](research/00-scope.md) | Riset & rekonsiliasi kebijakan/POJK + [verifikasi primer](research/verifikasi-primer/README.md) (PPKA, SLIK, transparansi) |

## 5. Arsitektur & stack target
**Monolit modular berbasis domain** — logika bisnis murni di *engine* (tanpa Eloquent/HTTP), orkestrasi menangani DB/queue. `[A]`

| Layer | Komponen |
|---|---|
| UI | Livewire/Blade (Filament hanya bila `D-04` mengizinkan): wizard, workbench analisis, simulator plafon/tenor, dashboard KPI/red flag |
| API | REST/Sanctum (internal, scope tenant/cabang) |
| Engine (murni, deterministik) | `ScheduleEngine` (Flat/Anuitas/Grace), `FeeEngine`, `PayoffEngine`, `TppCapacityEngine` (IDIR/DSR), `CapacityEngine`/DSCR/`MaxLoan`, `WorkingCapitalEngine`, `RatioEngine`, `StressTestEngine`, `CollateralEngine` (LTV/PPKA), `ScoringEngine` (FSS TPP) & `Scoring5CEngine`, `BalloonEngine`, `DecisionEngine`, `InsuranceEngine`, `Money`/`Terbilang` |
| Orkestrasi | `AnalysisSnapshotService`, `ApprovalService`, `DocumentService`, `ImportPipeline`, `JournalPoster`/`LedgerService`, `AuditRecorder`, `ConsentService`, `FileService` |
| Data | PostgreSQL (uang/rasio numerik, `jsonb` payload & snapshot), storage privat terenkripsi, migrasi reversible |
| Integrasi (bertahap) | OCR lokal opsional, `pdftotext`, PhpSpreadsheet (impor), DomPDF/PHPWord, core banking via file/rekonsiliasi `[K]` |

## 6. Kontrak & guardrail
Mengikat seluruh rencana & implementasi:
- **Uang = `BIGINT` rupiah** (`Money` + `brick/math`) — **tanpa float**; satu kebijakan pembulatan tertulis `[K D-05]`.
- **Rumus/angka hukum/pasal tidak dari ingatan model** — hanya dari workbook/POJK/keputusan bertanda tangan, dilabeli `[V]/[E]/[K]/[?]`.
- **Policy sebagai data berversi** (`policy_sets`/`ref_*`) + snapshot per analisis; tanpa konstanta bisnis tersembunyi.
- **Ledger append-only** (double-entry, idempoten, hash-chain, trigger immutable); koreksi hanya via jurnal pembalik; maker-checker.
- **Golden test + tes negatif ditulis sebelum kode**; parity ke workbook dengan toleransi disepakati `[K]`.
- **Import review-first** (staging, skor keyakinan, provenance, tidak auto-commit); consent SLIK aktif sebelum akses.
- **Dokumen dari model** (nominal/tanggal/terbilang dari snapshot, versi+hash+QR) — tanpa angka hardcode/add-in.
- **Tanpa PII/secret di repo, fixture, prompt, atau log**; data kritikal dikecualikan via `.gitignore`.

## 7. Koreksi wajib (jangan disalin mentah)
Referensi Excel memuat temuan yang **tidak boleh di-*port* apa adanya** `[!]`:

| ID | Temuan | Implementasi wajib |
|---|---|---|
| T1 | IDIR (hard-stop) vs FSS (soft) saling konflik | `DecisionEngine`: pisahkan hard-stop vs soft-score + flag konflik |
| T2, T4 | SLIK tak mengisi IDIR/DSR; skor kredit tak konsisten SLIK | Angsuran eksisting & faktor karakter otomatis dari SLIK; override wajib alasan |
| T3 | Anomali SLIK (baki > plafon/duplikat) | Validasi parser + peringatan (bukan silent) |
| T5 | FSS TPP = template KPR | Redesign model TPP berversi `[K D-02]` |
| T6, T7 | Neraca double-count/plug; referensi baris basi | Klasifikasi enum, saldo riil/flag, komponen eksplisit |
| T8, T9, T12 | Dokumen angka/terbilang hardcode; add-in; tak ikut sistem | `DocumentData` + engine terbilang + satu sumber jadwal; no hardcode/add-in |
| T10 | Dana diterima ≠ memo cair; blokir lama tak jelas | Perlakuan blokir lama eksplisit + jurnal seimbang |
| T13 | Double haircut LTV vs PPKA | Pisah nilai pasar/likuidasi/PPKA/basis LTV `[K D-07]` |
| T14 | `TODAY()` mengubah umur; Condition manual | Snapshot usia pada `analysis_date`; sub-kriteria Condition |
| — | Konstanta flat→efektif **1,81x** | **RATE/IRR eksak**; 1,81x/0,87x hanya pembanding |

Rincian & angka acuan `[K]`: [04-keputusan-risiko-gap.md §2](04-keputusan-risiko-gap.md).

## 8. Golden test, spec pack & evidence
- **Golden vectors** — [`project/tests/golden/`](project/tests/golden/README.md): **29 kasus dummy** (`manifest.json` + `cases/G-*.json`), berlabel sumber (workbook/sheet/sel), status `[K]` sampai diverifikasi, sebagian `negative_test`. Rencana kerja: isi nilai terverifikasi → tulis **golden + tes negatif sebelum engine** → evidence card `[K]`.
- **Spec pack** — [`project/docs/spec/`](project/docs/spec/README.md): 14 dokumen `DRAFT` (schedule/fee/EIR, kapasitas, collateral/PPKA/LTV, scoring/decision, RAC/BMPK, CKPN/EWS/stress, APU-PPT/TI, ledger/audit, glosarium, ADR↔D, gap assessment).
- **Keputusan & pertanyaan** — [`decisions.md`](project/docs/decisions.md) (D-01..D-20), [`questions.md`](project/docs/questions.md), [`claims.md`](project/docs/claims.md).
- **Evidence & handoff** — [`project/docs/evidence/`](project/docs/evidence/README.md), [`project/docs/handoff/`](project/docs/handoff/README.md).

## 9. Manual book & wiki
- **Manual book** — [daftar isi](project/docs/manual/README.md), **23 bab** fungsional (auth/RBAC, debitur, permohonan/SLIK, analisis, perhitungan/EIR, business rules, approval/dokumen, kredit/servicing, ledger/audit, API, UI, CLI, master/policy, import, PII/PDP, struktur data, gap, operasional, mekanisme update, glosarium).
- **Mekanisme update berkala** (saat fitur menjadi **final**) — [Bab 22](project/docs/manual/chapters/22-mekanisme-update-manual-book.md), dengan kartu kendali [`MANIFEST.md`](project/docs/manual/MANIFEST.md), [`CHANGELOG.md`](project/docs/manual/CHANGELOG.md), dan [`feature-matrix`](project/docs/manual/templates/feature-matrix.md).
- **Wiki** — [buka Wiki](https://github.com/FiyyaLisanaDeV/awkward-credit/wiki) · **34 halaman**: `Home`, `_Sidebar`, `_Footer`, **PRD**, **ERD**, **Bab 1–23**, dan lampiran (feature matrix, checklist PR, template, changelog, manifest).

Bangun & publikasikan ulang wiki:
```bash
python3 project/scripts/build-wiki.py          # → folder wiki/ (34 halaman)
GH_TOKEN=<token> bash scripts/publish-wiki.sh  # dorong ke GitHub Wiki
```

## 10. Struktur repositori
```
.
├── PRD.md, ERD.md                 # dokumen produk
├── 01..05-*.md                    # playbook perencanaan (inventaris → pemetaan → rencana → keputusan → bukti)
├── README.md, LICENSE, .gitignore
├── project/                       # workspace eksekusi
│   ├── AGENTS.md, README.md
│   ├── docs/
│   │   ├── spec/                  # spec pack (14 dokumen, DRAFT)
│   │   ├── decisions.md           # D-01..D-20 (DRAFT)
│   │   ├── claims.md, questions.md
│   │   ├── evidence/, handoff/
│   │   └── manual/                # manual book: chapters/ (23), templates/, MANIFEST, CHANGELOG
│   ├── tests/golden/              # 29 vektor golden (dummy, berlabel)
│   └── scripts/                   # build-wiki.py, manual-snapshot.sh
├── research/                      # riset POJK/BPR + verifikasi-primer/ (PPKA, SLIK, transparansi)
├── scripts/publish-wiki.sh
└── wiki/                          # hasil build (gitignored)
```

## 11. Label bukti
| Label | Arti |
|---|---|
| `[E]` | Terverifikasi dari kode/route/migrasi/config/test/evidence |
| `[A]` | Usulan/desain |
| `[K]` | Perlu verifikasi (angka/pasal/kebijakan/golden) |
| `[H]` | Keputusan bertanda tangan |
| `[Rencana]` | Belum terimplementasi |
| `[!]` | Temuan Excel yang tidak boleh disalin mentah |

**Precedence sumber kebenaran** (bila bertentangan): `[H]` decisions → spec → rencana/riset → prompt. Catat konflik di `questions.md`.

## 12. Cara mulai
1. Baca **[PRD](PRD.md)** (ruang lingkup, kebutuhan, koreksi, milestone) dan **[ERD](ERD.md)** (skema data & gap).
2. Pahami **[01](01-inventaris-excel.md)** (isi workbook per sheet) → **[02](02-pemetaan-excel-ke-aplikasi.md)** (traceability ke aplikasi).
3. Jadikan **[03](03-rencana-implementasi-detail.md)** sebagai rencana eksekusi; **selesaikan [04](04-keputusan-risiko-gap.md)** (D-01..D-12) **sebelum** mengunci golden test/engine.
4. Gunakan **[manual book](project/docs/manual/README.md)** (dan Wiki) sebagai rujukan fungsi; perbarui saat fitur menjadi **final**.
5. Sebelum menyentuh angka: cek **golden vectors** & **spec pack** — angka/pasal baru sah bila bersumber `[E]/[H]`.

---

*Disusun 2026-11-05 (WITA). Dokumentasi dijaga sinkron dengan perubahan (lihat [Bab 22](project/docs/manual/chapters/22-mekanisme-update-manual-book.md)).*
