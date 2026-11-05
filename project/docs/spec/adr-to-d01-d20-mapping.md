# Mapping ADR-001..026 (repo existing `[E]`) ↔ D-01..D-20 (rencana `project` `[K]`)

Status: DRAFT | Sumber ADR: `APP BPR/loan-analytic-bpr/docs/DECISIONS.md` pada workspace lokal (2026-11-05).  
Tujuan: ratifikasi tanpa overwrite — identifikasi selaras, tumpang tindih, konflik, dan keputusan yang belum ada. **Bukan** `[H]`.

## 1. Ringkasan
- ADR = keputusan teknis/arsitektur & beberapa guardrail data (26 entri). D = keputusan produk/kebijakan/kepatuhan kredit+risk (20 entri, DRAFT).
- Banyak D butuh **keputusan bisnis/Compliance bertanda tangan** yang ADR sengaja tidak putuskan (parameter/pasal/ambang). ADR menetapkan mekanisme (policy-as-data, provenance, no hardcode) — ini **selaras**.
- Konflik/gap utama: **C-002 golden/angka** vs evidence TPP existing; **float/Money** implementasi kalkulasi vs guardrail target; modul **L1–L9 maksimal** belum ada.

## 2. Matriks
| D | Topik D (`project/docs/decisions.md`) | ADR terkait `[E]` | Status | Tindakan ratifikasi `[A/K]` |
|---|---|---|---|---|
| D-01 | Ledger resmi vs bayangan + COA mapping & sumber saldo | ADR-012 (hash chain+trigger immutable, ledger:verify, advisory lock), ADR-013 (simulasi append-only) | **Parsial** | ADR-012 selaras integritas; D-01 masih butuh keputusan **ledger bayangan vs resmi + COA BPR + rekonsiliasi core banking + jurnal finansial seimbang** `[K/H]`. Mapping ke `JournalPoster`/COA target. |
| D-02 | Model scoring TPP baru (ganti FSS KPR/`#REF!`) + band 5C | ADR-020 (zero hardcode, PolicyParameters fail-hard, provenance), ADR-017 (perluasan field/progressive) | **Parsial** | Mekanisme band/policy ada (`Scoring5CService` band dari policy `[E]`); **model TPP baru, faktor/bobot/ambang** belum `[H D-02]`. Ratifikasi: skor 5C band default = DRAFT; tambah model TPP berversi. |
| D-03 | Format/akses SLIK iDeb + consent + korpus dummy/anonim | ADR-014 (import staging+profil), ADR-015 (PII encrypted/masking/AKSES_PII), ADR-016 (baki<=plafon → warning, bukan CHECK keras), ADR-022 (SLIK 3 jalur: xlsx/csv, PDF teks, tempel-teks + wizard/pratinjau) | **Parsial/selaras mekanisme** | Consent gate operasional, parser/validasi T2–T4, confidence/provenance, korpus dummy 10–20, definisi flag fatal → kebijakan `[K V3]`. ADR-016 selaras dengan quality flag. |
| D-04 | UI stack (Filament vs Livewire/Blade, pin versi) | ADR-008 (tanpa Vite/npm; Livewire 4 + CSS manual) | **Selaras (implisit)** | Repo existing Livewire 4 `[E]`. Ratifikasi formal: pilih **Livewire/Blade (existing)** atau Filament (hanya jika greenfield+`[H]`); catat pin versi. |
| D-05 | Pembulatan, pengakuan pendapatan, EIR/disclosure | ADR-013 (reproducibility/snapshot), ADR-021 (golden dari workbook) | **Parsial** | Rounding mekanis ada (`Rounding`, config scale) `[E]`; **kebijakan pembulatan bisnis, basis pengakuan, definisi EIR/total biaya disclosure (arus kas bersih, biaya termasuk)** = `[K V4/H]`. Konflik golden C-002 harus diselesaikan. |
| D-06 | Alokasi bayar, denda, penalti, saldo blokir lama | ADR-012 (ledger immutable/correction path), ADR-014 (rollback koreksi) | **Gap kebijakan** | Mekanisme audit/ledger ada; **alokasi, denda %, penalti (10% vs 15% `[E konflik]`), perlakuan blokir lama, reversal/jurnal** belum `[H]`. |
| D-07 | Kategori agunan/PPKA/haircut, nilai 4-lapis, basis LTV | ADR-020 (parameter/DTO, no hardcode), ADR-024 (angka harus terverifikasi) | **Gap besar** | LTV/rule dasar ada `[E]`; **PPKA/kategori/pengikatan/usia/%/basis LTV tunggal 4 nilai (anti double-haircut)** butuh POJK kualitas aset `[K V1→H]`. Tabel X2/X3 contoh ≠ final. |
| D-08 | Penanggung/tarif asuransi per produk/umur/masa | ADR-020 (provenance), ADR-025 (seeder bersumber; rate asuransi kondisional/tanpa karangan) | **Selaras proses, gap data** | Proses benar (no seed karangan) `[E ADR-025]`; tarif resmi & model versioned belum `[K/H]`. |
| D-09 | Hosting/retensi/backup/DRP (residensi Indonesia `[K]`) | ADR-001/002/006 (native, Postgres, storage lokal+PII terenkripsi, backup+APP_KEY) | **Parsial teknis, gap tata kelola** | Backup/PII storage ada arah; **residensi data/DRP Indonesia, RTO/RPO, retensi formal, drill** `[K D-20/V7]` belum dibuktikan. |
| D-10 | OCR/LLM/cloud untuk PII-SLIK (default: tidak) | ADR-023 (OCR KTP opsional, `OCR_DRIVER auto/none/tesseract`, wajib verifikasi manusia), ADR-015 (PII) | **Selaras default konservatif** | OCR opsional & verifikasi manusia `[E]`. Ratifikasi: default **no cloud/LLM PII** tanpa izin DPO `[K]`; SLIK/OCR pipeline tetap review-first. |
| D-11 | Ambang IDIR/DSR (definisi komponen, hard-stop) | ADR-020 (PolicyParameters/enum key `idir_threshold_tpp`, fail-hard bila hilang) | **Parsial** | `BR11IdirTpp` + policy key ada `[E]`; **definisi komponen net income/DSR total, SLIK auto, hard-stop vs soft per produk, ambang final** `[K]`. C-002 konflik. |
| D-12 | Authority limits/approval maker-checker/SoD | ADR-011 (Spatie 8 role+permission), ADR-009 (auth sendiri) | **Parsial** | Role/permission/approval matrix + scoping cabang ada `[E]`; **authority limits plafon/tenor/produk/cabang/jabatan final, AO no self-approve bukti eksplisit, SoD APU-PPT** `[K/H]`. |
| D-13 | BMPK (pihak terkait/satu peminjam/kelompok) & modal rujukan | *(tidak ada)* | **Gap** | Butuh `BmpkGuard`/exposure aggregator/related party-group, tes negatif non-bypass, definisi/batas/modal `[K V2/V7→H]`. |
| D-14 | RAC portofolio (sektor, 10 debitur, instansi TPP, agregat, histori) | *(tidak ada)* | **Gap** | Butuh RAC check pre-committee, metrik portofolio, histori sektor/NPL-by-sector, override Direksi teraudit `[K]`. |
| D-15 | Rating (ICRR/ICRS-like) & migrasi, re-underwriting, pricing linkage | ADR-020/013 (model/policy berversi, snapshot) — mekanisme saja | **Gap** | 5C band ada, bukan rating migrasi PD-style. Butuh rating berversi+golden+history+trigger+pricing `[K]`. |
| D-16 | CKPN/PPKA/PD-LGD, arah PPKA↔CKPN/KPMM, write-off non-parsial, restrukturisasi | ADR-012 (immutability) — bukan domain CKPN | **Gap besar** | Butuh domain kualitas aset/CKPN/PD-LGD/write-off eligibility/restructure guards `[K V1/V5]`. |
| D-17 | EWS indikator tier/ambang/frekuensi/SLA tindakan/eskalasi | *(tidak ada)* | **Gap besar** | Butuh EWS engine/scheduler/findings/actions/trends `[K]`. |
| D-18 | Stress makro (variabel/skenario/sumber/metode) & dampak CKPN/CAR/likuiditas | *(parsial)* analisis sensitivitas omzet/biaya `[E implisit]` | **Gap** | Stress macro 5 variabel/idiosinkratik/CAR/likuiditas belum `[K]`. |
| D-19 | APU-PPT operasional (risk rating/CDD-EDD, monitoring, LTKM, retensi, SoD) | ADR-015 (PII), ADR-023 (OCR verify) — parsial kontrol data | **Gap besar** | Workflow APU-PPT/PPATK/rules engine/retensi/SoD belum `[K V7]`. |
| D-20 | Tata kelola TI/AI (POJK 34/2025 `[K]`, residensi, audit TI, DRP RTO/RPO, third-party, model explainable) | ADR-001/006/010/023 | **Gap besar** | Perlu bukti operasional TI, residensi/DRP, third-party access, AI/model explainability gate `[K V7]`. |

## 3. ADR yang perlu ditambah/diubah (usulan, belum keputusan)
| Usulan | Alasan | Terkait |
|---|---|---|
| ADR-027 — Money canon (BIGINT rupiah) & strategi migrasi `float` | Kalkulasi existing `float` `[E]` bentrok guardrail; `brick/math` terpasang belum dipakai | D-05/D-06, C-002, semua engine |
| ADR-028 — Golden source-of-truth & C-002 resolution | Konflik `tpp.json` (0.831124/3,048,080/rate 0.18) vs C-002 `[K]`; definisi EIR disclosure | D-05, V4, golden plan |
| ADR-029 — SLIK DSR/Character integration & consent gate operasional | Implementasi T2–T4, D-03, V3 | D-03, D-11, L5 |
| ADR-030 — RAC/BMPK/Rating/CKPN/EWS/APU-PPT/TI domain boundaries | Cakupan L1–L9 melebihi ADR 001–026 | D-13..D-20 |
| ADR-031 — Ratifikasi D-01..D-20 format & status ACTIVE/DRAFT | Dua register (ADR vs D) harus dipetakan; ACTIVE hanya `[H]` | semua D |

## 4. Tindakan ratifikasi (urutan aman)
1. **Jangan** mengubah ADR lama; buat ADR baru “menggantikan/menambah” bila memutuskan.
2. Bentuk tabel ratifikasi: `ADR ↔ D ↔ status (selaras/parsial/gap/konflik) ↔ owner ↔ [H] evidence`.
3. Selesaikan V1/V3/V4/V5/V7 + C-002 sumber → isi D-01..D-20 `[K]`, lalu tanda tangan `[H]` bertahap.
4. Untuk konflik: kunci golden sumber terverifikasi dulu, baru ubah test/kode (PR test-first, evidence).
