# ref.md — Referensi Regulasi & Status Verifikasi (rekonsiliasi)

**Versi:** 2026-11-06 (direkonsiliasi terhadap bukti repo `[E]`, bukan verifikasi naskah resmi)
**Status:** Dokumen hidup — diperbarui setiap ada verifikasi baru
**Pemilik:** Compliance/Legal (substansi regulasi) + Tim Teknis (format)
**Rujukan induk:** `docs/research-plan-blockers.md`, `docs/questions.md`, `docs/decisions/register-d01-d20.md`, `docs/execution-status-blockers.md`, `04-keputusan-risiko-gap.md`

> **Aturan status (kritis):**
> - `[V]` di dokumen ini **belum tentu evidence tersimpan di repo**. Kolom **Bukti tersimpan** wajib ada; jika kosong, item tidak boleh masuk seed/produksi/`legal_basis`.
> - `[H]` hanya valid bila ada tanggal, pemutus, bukti/tautan, ringkasan, dampak, versi policy. Sampai saat itu: DRAFT/UNKNOWN.
> - `[K]` = perlu keputusan; `[?]` = belum diketahui → agen menulis `UNKNOWN` dan berhenti.

---

## 1. Tujuan dan Ruang Lingkup

Dokumen ini adalah **satu-satunya sumber rujukan** untuk:
1. Klaim regulasi yang dipakai di spesifikasi, prompt, dan kode — beserta label verifikasi dan bukti tersimpan.
2. Koreksi terhadap klaim sebelumnya yang salah atau belum terverifikasi.
3. Item terbuka yang memerlukan tindakan Compliance/Legal, pemilik domain, atau penandatanganan keputusan.
4. Jejak audit atas perubahan status verifikasi.

Dokumen ini **bukan** pengganti naskah resmi OJK/peraturan.go.id. Setiap kutipan pasal/ayat diambil dari salinan resmi (OJK/JDIH) dengan nomor, tahun, pasal, ayat, tanggal berlaku, tautan, dan tanggal unduh.

---

## 2. Konvensi Label

| Label | Arti | Bukti diterima | Boleh masuk seed/produksi? |
|---|---|---|---|
| `[V]` | Terverifikasi **dengan bukti tersimpan** | File/tautan + metadata (nomor/tahun/pasal/ayat/berlaku/tautan/tanggal unduh) atau hasil uji yang dijalankan | Ya (setelah bukti terisi) |
| `[V*]` | Klaim `[V]` dari dokumen, **bukti belum tersimpan di repo** | Salinan resmi/uji (belum ada) | Tidak |
| `[E]` | Dari Excel/rencana lama | Alamat sel + nilai, dibandingkan orang kedua | Setelah cocok dengan golden dan disahkan pemilik produk |
| `[K]` | Keputusan kebijakan | Nomor keputusan D-xx + penandatangan | Setelah ditandatangani |
| `[?]` | Belum diketahui | Tidak ada | Tidak |
| `[H]` | Keputusan manusia | Tanda tangan pemutus (Direksi/Komite/Kepatuhan) | Setelah ditandatangani |

---

## 3. Matriks Verifikasi B1–B9 (direkonsiliasi)

### B1/B2 — IDIR 85,93% / Net 2.948.080 / EIR 28,88%@60

| Item | Status | Bukti tersimpan di repo | Yang masih diperlukan |
|---|---|---|---|
| Definisi IDIR | `[V*]` (klaim, belum evidence tersimpan) | Tidak ada naskah resmi tersimpan | Kutipan pasal/definisi resmi + pengesahan D-11 |
| Angka IDIR 85,93% | `[?]` | `docs/golden/cases/G-TPP-01.json`: `claimed_C002.idir = 0.8593`, `verified=null`, `verification.verified=false` `[E]` | Rujukan sel (`analisis kredit!D33`,`E28`) **tanpa isi PII**, rekalkulasi independen (Decimal/BCMath) yang disahkan |
| Net income Rp2.948.080 | `[?]` | `claimed_C002.penghasilan_bersih_rp = 2948080`, `verified=null` `[E]` | Definisi komponen net income oleh Kepala Kredit + Akuntansi |
| Definisi EIR (PSAK 71) | `[V*]` (klaim) | Belum ada salinan/metadata PSAK 71 tersimpan | Salinan + kutipan definisi + pengesahan D-05 |
| Angka EIR 28,88%@60 | `[?]` | `claimed_C002.eir_irr_pa = 0.2888@60`, `verified=null` `[E]` | Rekalkulasi independen; definisi arus kas EIR (biaya transaksi/komisi) oleh Akuntansi |

Catatan koreksi: rencana lama menulis IDIR ~83% / net ~3,05 jt; angka pegangan (dummy) = **85,93%** / **2.948.080** / RATE-IRR eksak contoh **28,88%@60** — semua tetap `[K]/[?]` sampai terverifikasi `[E]` (`04-keputusan-risiko-gap.md` §3, §5; `docs/questions.md` RAT-02/C-002).

### B3/B4/B11 — Keputusan D-01..D-20, RAT, Gate F1–F3

| Item | Status (repo `[E]`) | Catatan |
|---|---|---|
| D-01..D-20 | **DRAFT/UNKNOWN** (bukan `[H]`) | `docs/decisions/register-d01-d20.md`: "Semua entri DRAFT/UNKNOWN sampai ditandatangani (`[H]`)." |
| RAT-01 (repo/ADR↔D) | **UNKNOWN `[K]`** | `docs/questions.md` RAT-01 belum resolved |
| RAT-03/D-05–D-06 | **UNKNOWN `[K]`** | `docs/questions.md` RAT-03 belum resolved |
| Gate F1–F3 | **Belum terbukti LULUS `[K]`** | `docs/spec/gap-assessment-existing-repo.md` mencatat prasyarat F1 belum terpenuhi `[E/K]`; tidak ada Kartu Bukti tersimpan |

### B5/B6 — Klaim Regulasi V1–V7

| Item | Status | Catatan |
|---|---|---|
| V1, V3, V4 (prioritas) | **UNKNOWN `[K]`** (bukan "boleh dipakai") | `docs/questions.md`: `UNKNOWN (V1/V3/V4)`; belum ada evidence naskah resmi |
| V2, V5–V7 | **UNKNOWN `[?]`** | Butuh naskah resmi + metadata + pengesahan |

**Aturan:** kutipan pasal/ayat dari PDF resmi OJK/JDIH, bukan ringkasan artikel/ingatan model.

### B7–B9 — Spec, Korpus, Tarif/Haircut/COA

| Item | Status | Catatan |
|---|---|---|
| Spec `legal_basis`/`traceability` | DRAFT `[K]` | `docs/spec/*` kerangka; butuh pengesahan Compliance |
| Korpus SLIK/KTP/KK dummy | `[E]` (rencana, di luar repo) | Tidak ada isu PII; belum tersedia |
| Tarif asuransi (Al-Amin, Jasindo, Bumida) | `[E]` → `[K]` | Dari Excel dummy; perlu pengesahan Operasional |
| Haircut % + pasal per kategori | Kerangka `[V*]` → persentase `[K]` | Persentase perlu kutipan pasal PDF + pengesahan Compliance |
| COA resmi BPR | Kerangka `[V*]` → COA spesifik `[K]` | Kerangka PA BPR perlu bukti tersimpan; COA spesifik dari Akuntansi |

---

## 4. Referensi Regulasi (dengan Koreksi — **belum terverifikasi oleh bukti repo**)

> Catatan: label `[V]` di bawah dipertahankan dari dokumen sumber sebagai **klaim**, tetapi tanpa evidence tersimpan di repo item tetap `[V*]`. Compliance/Legal wajib mengisi metadata sebelum naik `[V]`.

### 4.1 POJK 1/2024 — Kualitas Aset BPR
- **Status klaim:** `[V]` (naskah resmi disebut tersedia di domain OJK) → repo: **`[V*]`**
- Substansi: kualitas aset, PPKA, CKPN (mulai 2025), restrukturisasi, hapus buku, agunan sebagai pengurang PPKA.
- Perlu dikutip persis: Pasal 17 (agunan tunai), 19–21 (agunan pengurang PPKA), 30 (restrukturisasi), 42 (hapus buku), 45 (kebijakan perkreditan) — **semua `[K]` sampai metadata tersimpan**.
- Catatan: bukan aturan DSCR/LTV/IDIR; bukan aturan COA.

### 4.2 POJK 9/2024 — Penerapan Tata Kelola BPR/BPRS
- **Status klaim:** `[V]` → repo: **`[V*]`**; koreksi naratif patut diverifikasi ke naskah resmi.
- **KOREKSI PENTING:** POJK ini tentang **Tata Kelola**, bukan POJK Manajemen Risiko tersendiri. Kewajiban manajemen risiko (kredit, operasional, kepatuhan) adalah **bagian dari kewajiban tata kelola**. Klaim "POJK 9/2024 tentang Manajemen Risiko" tidak akurat dan harus dikoreksi.
- Tindakan: petakan lokasi narasi lama yang perlu dikoreksi (belum ditemukan eksplisit di artefak inti repo `[E]`).

### 4.3 POJK 34/2025 — Penyelenggaraan TI oleh BPR/BPRS
- **Status klaim:** `[V]` (diundangkan 17 Desember 2025; PADK pelaksana 43/PADK.03/2025) → repo: **`[V*]`**
- Relevansi: F9 (bukan F1–F3). Perlu kutipan pasal lokasi pusat data/DRP, audit TI tahunan.

### 4.4 POJK 40/2024 — BPR
- **Status klaim:** `[V]` → repo: **`[V*]`**
- Substansi: modal inti minimum, konsolidasi BPR, kelembagaan. Relevansi R2/R3 (multi-tenant/konsolidasi), bukan inti R1.

### 4.5 APU-PPT untuk BPR
- **Status klaim:** kerangka `[V]`, pedoman teknis `[K]` → repo: **`[V*]`/`[K]`**
- **KOREKSI PENTING:** kerangka yang berlaku saat ini adalah **POJK Nomor 8 Tahun 2023** tentang Penerapan Program APU-PPT dan PPPSPM di Sektor Jasa Keuangan (menggantikan acuan lama PBI 12/20/PBI/2010 + SE BI 13/14/DKBU/2011 yang sebelumnya dicatat). Konfirmasi pasal teknis ke salinan resmi `[K]`.
- Selaras risiko repo: `docs/questions.md` V7 masih "APU-PPT/PPATK … `[K klaim independen]`".

### 4.6 SEOJK 21/SEOJK.03/2024 — Pedoman Akuntansi BPR (PA BPR)
- **Status klaim:** `[V]` → repo: **`[V*]`**
- Sumber COA kerangka + kebijakan pengakuan pendapatan (D-05). COA spesifik tetap `[K]` dari Akuntansi.

### 4.7 PSAK 71 — Instrumen Keuangan
- **Status klaim:** `[V]` → repo: **`[V*]`**
- Definisi EIR mencakup biaya transaksi/komisi; EIR 28,88%@60 dihitung dengan definisi ini, bukan sekadar `RATE()` pokok+bunga. Angka tetap `[?]` sampai rekalkulasi disahkan.

### 4.8 UU 27/2022 — Perlindungan Data Pribadi
- **Status klaim:** `[V]` (berlaku Oktober 2024) → repo: **`[V*]`**
- Relevansi: consent, enkripsi PII, log akses, retensi 5 tahun (APU-PPT).

### 4.9 UU 10/2020 + PP 71/2019 — Tanda Tangan Elektronik & e-Meterai
- **Status klaim:** `[V]` → repo: **`[V*]`**
- Relevansi: F7, PSrE, e-meterai.

### 4.10 UU 4/2023 — P2SK
- **Status klaim:** `[V]` → repo: **`[V*]`**
- Latar hukum umum untuk POJK terkait.

---

## 5. Item Terbuka yang Memerlukan Tindakan

### 5.1 Compliance/Legal
| # | Item | Tindakan | Tenggat |
|---|---|---|---|
| L1 | Kutipan pasal/ayat POJK 1/2024 haircut agunan | PDF resmi + metadata | Sebelum F3 |
| L2 | Konfirmasi POJK/SEOJK APU-PPT lebih baru dari PBI 12/20/PBI/2010 | JDIH OJK | Sebelum F6 |
| L3 | Konfirmasi POJK 9/2024 = Tata Kelola (bukan Manajemen Risiko) | naskah resmi | Segera |
| L4 | Kutipan pasal POJK 34/2025 (pusat data/audit TI) | PDF resmi | Sebelum F9 |
| L5 | Verifikasi V2, V5–V7 | JDIH | Sebelum F3 |
| L6 | Pengesahan % haircut per kategori | Tanda tangan Compliance | Sebelum F3 |

### 5.2 Pemilik Domain
| # | Item | Pemilik | Tindakan |
|---|---|---|---|
| D1 | Definisi komponen net income (IDIR TPP) | Kepala Kredit + Akuntansi | Sahkan D-xx |
| D2 | Definisi arus kas EIR (biaya transaksi/komisi) | Akuntansi | Sahkan D-05 |
| D3 | Tarif asuransi (Al-Amin/Jasindo/Bumida) | Operasional | Sahkan tarif |
| D4 | COA resmi BPR | Akuntansi | Ambil dari PA BPR |
| D5 | Ambang IDIR/DSR | Direksi + Komite | Sahkan D-11 |
| D6 | Matriks kewenangan | Direksi | Sahkan D-12 |

### 5.3 Rekalkulasi Independen
| # | Item | Metode | Pengesah |
|---|---|---|---|
| R1 | IDIR 85,93% | Decimal/BCMath terpisah | Pemeriksa berbeda |
| R2 | EIR 28,88%@60 | PSAK 71 | Pemeriksa berbeda + Akuntansi |
| R3 | Flat 80jt 18% 60bln = 2.533.333 | Decimal | Pemeriksa berbeda |
| R4 | Anuitas 80jt 18% 60bln = 2.031.474 | PMT eksak | Pemeriksa berbeda |
| R5 | Grace 80jt 18% 60bln = 1.200.000/bln + balloon 81.200.000 | Decimal | Pemeriksa berbeda |

---

## 6. Catatan Koreksi terhadap Dokumen Sebelumnya

| # | Klaim lama | Koreksi | Dampak |
|---|---|---|---|
| 1 | IDIR 83%, net Rp3,05 jt | IDIR 85,93%, net Rp2.948.080 (klaim `[K]`) | Vektor golden F1 diperbarui, tetap unverified |
| 2 | "POJK 9/2024 tentang Manajemen Risiko" | POJK 9/2024 tentang Tata Kelola; manajemen risiko bagian darinya | Narasi dikoreksi, perlu verifikasi naskah |
| 3 | "POJK APU-PPT untuk BPR" | Kerangka utama: PBI 12/20/PBI/2010 + SE BI 13/14/DKBU/2011 | Narasi dikoreksi |
| 4 | "COA dari POJK 1/2024" | COA dari PA BPR (SEOJK 21/SEOJK.03/2024) | Spesifikasi COA dikoreksi |
| 5 | "EIR = RATE() pokok+bunga" | EIR per PSAK 71 mencakup biaya transaksi & komisi | Definisi D-05 diperluas |
| 6 | "Faktor flat→efektif 1,81×" | Tidak konstan; RATE eksak 28,88%@60 | Sudah dikoreksi F1 |
| 7 | "T15 = PII nyata" | Data Excel = dummy | Sudah dikoreksi prompt F1 |

---

## 7. Bukti tersimpan (status saat rekonsiliasi)
- **Riset web resmi (2026-11-06):** `docs/claims.md` (V1/V3/V4 + koreksi V2/V7 dengan URL resmi OJK/BPK) dan `docs/case-study-3-blockers.md` (analisis C-002 + rekomendasi D-01..D-20 + rujukan regulasi). Status pasal tetap **PENDING** sampai Compliance/Legal unduh PDF + catat metadata.
- `docs/golden/manifest.json` + `docs/golden/cases/`: 29 kasus, **29 unverified**, `pii:false`, `dummy_data:true` `[E]`.
- `docs/decisions/register-d01-d20.md`: D-01..D-20 **DRAFT/UNKNOWN** `[E]`.
- `docs/questions.md`: V1–V7, RAT-01..03, C-002 **UNKNOWN** `[E]`.
- `docs/spec/*`: kerangka DRAFT `[E]`.
- `04-keputusan-risiko-gap.md` §3: koreksi angka `[K]` `[E]`.
- QA repo `APP BPR/loan-analytic-bpr`: `pest --compact` 99 passed; `phpstan` no errors (deterministik) `[E]`.

**Status pemanfaatan:** dokumen ini belum boleh menjadi dasar seed/produksi/`legal_basis` untuk item berlabel `[V*]`, `[K]`, atau `[?]`.
