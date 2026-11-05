# Case Study — 3 Bloker (best judgment, NON-ACTIVE)

Status: **DRAFT / NON-ACTIVE** (2026-11-06). Hasil riset web + best judgment. Bukan pengganti naskah resmi/pemutus `[H]`. Tidak menyalin isi sel/rumus/PII workbook.

## Blok 1 — RAT-02 / C-002 (IDIR 85,93% · net 2.948.080 · EIR 28,88%@60)

### Kasus dummy (best judgment, integer rupiah)
- Plafon `P = 80.000.000`, bunga nominal `r = 18%/tahun`, tenor `n = 60`, Flat.
- Bunga per bulan = `round(80.000.000 × 0,18/12) = 1.200.000`.
- Pokok dasar = `floor(80.000.000/60) = 1.333.333`; sisa = `20` → pokok terakhir `1.333.353`.
- Angsuran = `1.333.333 + 1.200.000 = 2.533.333` (periode terakhir `2.533.353`).
- `angsuran_baru = 2.533.333` (periode pertama).
- `penghasilan_bersih = 2.948.080` (dummy; komponen lihat definisi di bawah).
- **IDIR = 2.533.333 / 2.948.080 = 85,93%** → cocok klaim C-002 `[K]` secara aritmetika.

### Definisi komponen (best judgment, wajib disahkan)
- **Net income (IDIR/DSR basis):** `penghasilan kotor (gaji/TPP/usaha/pasangan) − pengeluaran rumah tangga − kewajiban lain yang disahkan`. Default yang dipakai: angka net dummy `2.948.080` (sudah setelah pengurang). Komponen persis = `[K]` Kepala Kredit + Akuntansi (D1).
- **Angsuran baru:** angsuran periode pertama dari jadwal Flat (bukan rata-rata; untuk Anuitas/Grace ikut definisi metode).
- **EIR/IRR cashflow (D2):** arus kas bulanan `[-P, angsuran_1..angsuran_n]` dengan pokok+bunga; **belum** menambah biaya transaksi/komisi/provisi. Untuk disclosure PSAK 71, biaya transaksi/komisi harus dimasukkan `[K D-05]`.
- **Konvensi rate:** hasil IRR bulanan → `rate_annualized_x12 = monthly×12` dan `eir_effective_annual = (1+monthly)^12−1`.
  - Flat 80jt/18%/60: `monthly = 2,4064%`, `×12 = 28,88%`, `efektif = 33,02%`.
  - Klaim C-002 `28,88%` = **rate ×12 (nominal annualized)**, bukan effective annual.

### Bukti tersimpan
- `docs/golden/cases/G-TPP-01.json` → `recomputed.values`: `idir=0.8593`, `eir_irr_pa=0.2888`, `eir_effective_annual=0.3302`, `schedule_sum_principal_eq_plafon=true`, `schedule_closing_balance_rp=0`, `no_flat_effective_factor_181x=true`.
- `docs/policy-draft-best-judgment.md` §1–2.
- **Tertahan:** rujukan `workbook/sheet/sel` + pengesahan D1/D2 untuk naik `verified=true`.

## Blok 2 — RAT-01/B11 + D-01..D-20/RAT-03 (decision register, DRAFT)

Register resmi tetap `docs/decisions/register-d01-d20.md` (DRAFT/UNKNOWN). Berikut **rekomendasi best judgment** yang diimplementasikan sebagai policy DRAFT (bukan `[H]`):

| ID | Rekomendasi best-judgment (DRAFT/NON-ACTIVE) |
|---|---|
| D-01 | Ledger **bayangan** + rekonsiliasi harian + `external_refs`; resmi hanya bila core banking diganti. COA kerangka dari PA BPR; akun final = `[H]` Akuntansi |
| D-02 | 5C bobot 25/30/15/20/10; FSS TPP v2 faktor: potong TPP/PKS/bendahara, blokir, SLIK, rasio angsuran. Band = data berversi DRAFT |
| D-03 | SLIK unggah manual (PDF/Excel/CSV/JSON) + wizard mapping; API bila tersedia; consent aktif sebelum akses |
| D-04 | Ikuti repo existing: **Livewire/Blade** (tanpa Filament), pin versi |
| D-05 | BIGINT rupiah, `ROUND_HALF_UP`, `LAST_ADJUST`; pengakuan pendapatan = konfigurasi akrual/saat-cair (default DRAFT); EIR disclosure per PSAK 71 |
| D-06 | Alokasi bayar: denda→bunga→pokok (best judgment, `[K]`); penalti default **10%** (bukan 15%); blokir lama = debit/kredit eksplisit |
| D-07 | 4 nilai terpisah (pasar/likuidasi/PPKA/basis LTV); basis LTV tunggal default **PPKA**; % haircut per kategori = `[K]` Compliance |
| D-08 | Master tarif asuransi per penanggung/produk/umur/masa (DRAFT kosong sampai disahkan Operasional) |
| D-09 | Hosting terkontrol, residensi Indonesia; retensi per jenis data; backup terenkripsi + uji restore |
| D-10 | Default **tanpa OCR/LLM cloud**; parser lokal (`pdftotext`, NIK decode, Tesseract opsional) |
| D-11 | IDIR hard-stop >= **0,80**; DSR total >= **0,50** (DRAFT) |
| D-12 | Authority limits data-driven; maker-checker; AO tidak self-approve |
| D-13 | BMPK pihak terkait 10%, kelompok/satu peminjam 20% (DRAFT, wajib V2 resmi) |
| D-14 | RAC: sektor/debitur/instansi limit berversi DRAFT |
| D-15 | Rating ICRR/ICRS-like; migrasi; re-underwriting; pricing linkage DRAFT |
| D-16 | CKPN/PPKA arah; write-off non-parsial; restrukturisasi DRAFT |
| D-17 | EWS tier/ambang/frekuensi/SLA DRAFT |
| D-18 | Stress makro variabel/skenario DRAFT |
| D-19 | APU-PPT: CDD/EDD, LTKM, retensi, SoD DRAFT |
| D-20 | POJK 34/2025 TI: pusat data/DRP Indonesia, audit TI tahunan DRAFT |

> RAT-01 (jalur): rekomendasi best-judgment = lanjut **`APP BPR/loan-analytic-bpr`** dengan gap-assessment. RAT-03 = D-05/D-06 di atas (DRAFT).

## Blok 3 — V1/V3/V4 (regulasi, hasil riset web)

Status: **OFFICIAL_PAGE_FOUND**, pasal/ayat **PENDING** Compliance/Legal. Tidak mengutip pasal dari ingatan.

### V1 — Kualitas aset BPR/BPRS (D-07, D-16)
- BPR konvensional: **POJK Nomor 1 Tahun 2024** tentang Kualitas Aset Bank Perekonomian Rakyat (menggantikan POJK 33/POJK.03/2018).
  - Portal OJK: https://www.ojk.go.id/id/regulasi/Pages/Kualitas-Aset-Bank-Perekonomian-Rakyat.aspx
  - PDF salinan: https://www.ojk.go.id/id/regulasi/Documents/Pages/Kualitas-Aset-Bank-Perekonomian-Rakyat/POJK%201%20Tahun%202024%20Kualitas%20Aset%20Bank%20Perekonomian%20Rakyat.pdf
- BPRS: **POJK Nomor 24 Tahun 2024** tentang Kualitas Aset BPRS.
  - https://www.ojk.go.id/id/regulasi/Pages/POJK-24-Tahun-2024-Kualitas-Aset-Bank-Perekonomian-Rakyat-Syariah.aspx

### V3 — SLIK (D-03, T2–T4)
- Dasar: **POJK 18/POJK.03/2017** + **POJK 64/POJK.03/2020** + **POJK 11 Tahun 2024** (perubahan kedua).
  - https://ojk.go.id/id/kanal/perbankan/regulasi/peraturan-ojk/Pages/POJK-tentang-Pelaporan-dan-Permintaan-Informasi-Debitur-melalui-Sistem-Layanan-Informasi-Keuangan.aspx
  - https://peraturan.bpk.go.id/Details/301733/peraturan-ojk-no-11-tahun-2024
- Pedoman teknis: **SEOJK 11/SEOJK.01/2024**.
  - https://ojk.go.id/id/regulasi/Pages/Pelaporan-dan-Permintaan-Informasi-Debitur-Melalui-Sistem-Layanan-Informasi-Keuangan-11-SEOJK01-2024.aspx
- Consent: wajib aktif sebelum permintaan/penggunaan; rujukan pasal persis = **PENDING**.

### V4 — Perlindungan konsumen/transparansi (D-05, D-06)
- **POJK Nomor 22 Tahun 2023** tentang Pelindungan Konsumen dan Masyarakat di Sektor Jasa Keuangan.
  - https://ojk.go.id/id/regulasi/Pages/Pelindungan-Konsumen-dan-Masyarakat-di-Sektor-Jasa-Keuangan.aspx
  - https://peraturan.bpk.go.id/Details/302699/peraturan-ojk-no-22-tahun-2023
- Catatan koreksi: **POJK 13 Tahun 2024** (Transparansi SBDK) adalah untuk **Bank Umum Konvensional**, **bukan BPR** — tidak dipakai sebagai dasar BPR.
- Rincian pengungkapan suku bunga/efektif/biaya/denda = pasal = **PENDING**.

### V2/V7 konteks (tambahan hasil riset)
- **POJK Nomor 9 Tahun 2024** tentang Penerapan Tata Kelola BPR dan BPRS (mulai 1 Juli 2024).
  - https://www.ojk.go.id/id/regulasi/Pages/POJK-9-2024-Penerapan-Tata-Kelola-bagi-BPR-dan-BPRS.aspx
- **POJK Nomor 34 Tahun 2025** tentang Penyelenggaraan TI oleh BPR dan BPRS (menggantikan POJK 75/POJK.03/2016).
  - https://www.ojk.go.id/id/regulasi/Pages/POJK-34-Tahun-2025-Penyelenggaraan-Teknologi-Informasi-oleh-Bank-Perekonomian-Rakyat-dan-Bank-Perekonomian-Rakyat-Syariah.aspx

## Tindak lanjut
1. Compliance/Legal unduh PDF resmi (V1/V3/V4) dan catat **pasal/ayat + tanggal unduh** → update `docs/claims.md` + `docs/ref.md`.
2. Pemilik domain sahkan D1/D2 (net income & EIR cashflow) + RAT-02 (sheet/sel) → naikkan G-TPP-01 ke `verified`.
3. Pemutus sahkan D-xx di atas (ubah DRAFT→`[H]`) → seed policy bisa ACTIVE.
