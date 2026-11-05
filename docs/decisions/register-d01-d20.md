# Decisions (D-01..D-20) — DRAFT Register

Status: **INIT**. Semua entri **DRAFT/UNKNOWN** sampai ditandatangani (`[H]`). **Jangan** jadikan ACTIVE.

| ID | Topik | Status | Pemutus | Sumber target `[K]` | Catatan |
|---|---|---|---|---|---|
| D-01 | Ledger resmi vs bayangan + COA mapping & sumber saldo | DRAFT | Direksi, IT, Akuntansi | kebijakan + core banking | prasyarat F2/F8 |
| D-02 | Model scoring TPP baru (ganti FSS KPR/`#REF!`) + band 5C | DRAFT | Komite Kredit, Risk | workshop + data | F3/F4 |
| D-03 | Format/akses SLIK iDeb + consent + korpus dummy/anonim | DRAFT | Kepala Kredit, IT, Kepatuhan | POJK SLIK `[K]` | F0/F5 |
| D-04 | UI stack (Filament vs Livewire/Blade, pin versi) | DRAFT | Tim teknis + owner | repo/ADR | F1/F6 |
| D-05 | Pembulatan, pengakuan pendapatan, EIR/disclosure | DRAFT | Akuntansi, Direksi | kebijakan + transparansi `[K]` | F2/F4 |
| D-06 | Alokasi bayar, denda, penalti, saldo blokir lama | DRAFT | Direksi, Kepatuhan, Akuntansi | kebijakan `[K]` | F2/F4/F8 |
| D-07 | Kategori agunan/PPKA/haircut, nilai 4-lapis, basis LTV | DRAFT | Kepatuhan, Komite Kredit | POJK kualitas aset BPR/BPRS `[K]` | F3/F4, prioritas |
| D-08 | Penanggung/tarif asuransi per produk/umur/masa | DRAFT | Operasional, Keuangan | tarif resmi | F3/F4/F7 |
| D-09 | Hosting/retensi/backup/DRP (residensi Indonesia `[K]`) | DRAFT | IT, Kepatuhan/DPO, Direksi | POJK TI/34/2025 `[K]` + kebijakan | F1/F9 |
| D-10 | OCR/LLM/cloud untuk PII-SLIK (default: tidak) | DRAFT | Kepatuhan/DPO | UU PDP `[K]` | F5 |
| D-11 | Ambang IDIR/DSR (definisi komponen, hard-stop) | DRAFT | Komite/Direksi | kebijakan `[K]` | F4 |
| D-12 | Authority limits/approval maker-checker/SoD | DRAFT | Direksi, HR/Kepatuhan | kebijakan `[K]` | F3/F6 |
| D-13 | BMPK (pihak terkait/satu peminjam/kelompok) & modal rujukan | DRAFT | Direksi, Kepatuhan | POJK/kententuan BMPK `[K]` | L1 |
| D-14 | RAC portofolio (sektor, 10 debitur, instansi TPP, agregat, histori) | DRAFT | Direksi, Komite, Risk | POJK/SEOJK + kebijakan `[K]` | L3 |
| D-15 | Rating (ICRR/ICRS-like) & migrasi, re-underwriting, pricing linkage | DRAFT | Komite, Risk | kebijakan `[K]` | L2 |
| D-16 | CKPN/PPKA/PD-LGD, arah PPKA↔CKPN/KPMM, write-off non-parsial, restrukturisasi | DRAFT | Akuntansi, Risk, Kepatuhan | POJK kualitas aset/akuntansi `[K]` | L4 |
| D-17 | EWS indikator tier/ambang/frekuensi/SLA tindakan/eskalasi | DRAFT | Risk, Operasional, IT | kebijakan `[K]` | L5 |
| D-18 | Stress makro (variabel/skenario/sumber/metode) & dampak CKPN/CAR/likuiditas | DRAFT | Risk, Direksi | kebijakan + data `[K]` | L6 |
| D-19 | APU-PPT (risk rating/CDD-EDD, monitoring, LTKM, retensi, SoD) | DRAFT | Kepatuhan, PPATK liaison, IT | POJK APU-PPT/PPATK `[K]` | L7 |
| D-20 | Tata kelola TI/AI (POJK 34/2025 `[K]`, residensi, audit TI, DRP RTO/RPO, third-party, model explainable) | DRAFT | IT, Kepatuhan, Direksi | POJK TI `[K]` | L8/L9 |

## Aturan
- Perubahan status ke `[H]` wajib: tanggal, pemutus, bukti/tautan, ringkasan, dampak, versi policy.
- Nomor/pasal/angka hanya diisi dari naskah resmi/keputusan — bukan ingatan model.
