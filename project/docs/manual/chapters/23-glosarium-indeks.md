# Bab 23 — Glosarium & Indeks Cepat

## 23.1 Glosarium
| Istilah | Definisi kerja | Label |
|---|---|---|
| **TPP** | Kredit pegawai/pensiun (konsumtif, potong TPP) | `[E]` |
| **Modal Kerja (MK)** | Kredit produktif untuk kebutuhan modal kerja | `[E]` |
| **Grace Period** | Sistem dengan bunga berjalan & pokok/balloon di akhir tenor | `[E]` |
| **Balloon** | Kewajiban pokok besar di akhir tenor | `[E]` |
| **Flat** | Bunga dihitung dari plafon tetap | `[E]` |
| **Anuitas** | Angsuran total tetap; bunga dari saldo | `[E]` |
| **RPC** | Kapasitas bayar = surplus × margin | `[E]` |
| **DSCR** | RPC ÷ angsuran periodik | `[E]` |
| **IDIR** | Angsuran baru ÷ penghasilan bersih | `[E]`, definisi final `[K]` (D-11) |
| **DSR/DBR total** | (angsuran baru + kewajiban eksisting) ÷ penghasilan | `[K]` (belum impl) |
| **LTV** | Plafon ÷ nilai jaminan | `[E]`; basis tunggal & PPKA `[K]` (D-07) |
| **EIR/IRR** | Suku bunga efektif dari arus kas (numerik via bisection) | `[E]`; definisi cashflow `[K]` (D-05) |
| **Kolektibilitas** | Kualitas aset 1–5 | `[E]` |
| **Red flag** | Peringatan/critical hasil evaluasi | `[E]` |
| **5C** | Character, Capacity, Capital, Collateral, Condition | `[E]` |
| **Ledger** | Jejak peristiwa append-only ber-hash chain | `[E]` |
| **Hash chain** | Rantai hash `prev_hash`→`hash` untuk integritas | `[E]` |
| **Audit log** | Jejak perubahan field entitas (immutable) | `[E]` |
| **Snapshot simulasi** | Input+output+versi tersimpan untuk replay | `[E]` |
| **BMPK** | Batas Maksimum Pemberian Kredit | `[Rencana]` (D-13) |
| **RAC** | Rencana Aksi/portofolio & konsentrasi | `[Rencana]` (D-14) |
| **PPKA** | Agunan yang dapat diperhitungkan (haircut) | `[K]` (D-07) |
| **CKPN/PPAP** | Cadangan kerugian/penyisihan | `[Rencana]` (D-16) |
| **PD/LGD** | Probability of Default / Loss Given Default | `[Rencana]` (D-16) |
| **EWS** | Early Warning System | `[Rencana]` (D-17) |
| **APU-PPT** | Anti Pencucian Uang & Pencegahan Pendanaan Terorisme | `[Rencana]` (D-19) |
| **COA** | Chart of Accounts (jurnal) | `[K]` (D-01) |
| **DRP** | Disaster Recovery Plan (RTO/RPO) | `[K]` (D-20) |
| **SoD** | Segregation of Duties | `[K]` (D-12/D-19) |

## 23.2 Indeks bab
| Bab | Judul |
|---|---|
| 1 | Ringkasan & ruang lingkup |
| 2 | Arsitektur & stack teknis |
| 3 | Instalasi, konfigurasi, operasi dasar |
| 4 | Autentikasi, otorisasi, RBAC, keamanan akses |
| 5 | Debitur, pasangan, usaha, aset, pengkinian |
| 6 | Permohonan kredit, produk, workflow, SLIK |
| 7 | Analisis kredit (neraca→rekomendasi) |
| 8 | Perhitungan, jadwal, EIR, simulasi |
| 9 | Business rules BR-01..BR-15 & validasi |
| 10 | Approval, checklist, dokumen, TTD, notifikasi |
| 11 | Kredit, biaya, asuransi, agunan, jadwal, pembayaran, memo |
| 12 | Ledger, audit log, integritas, replay |
| 13 | Referensi API v1 |
| 14 | UI web & Livewire |
| 15 | CLI/artisan, scheduler, quality tools |
| 16 | Data master, parameter kebijakan, seeder, zero-hardcode |
| 17 | Import, ekstraksi, OCR, data quality |
| 18 | Keamanan, PII/PDP, consent, retensi |
| 19 | Struktur data (model/migrasi/enum) |
| 20 | Known issues, gap, C-002, rencana L1–L9 |
| 21 | Operasional, backup/restore, rilis |
| 22 | Mekanisme update berkala manual book |
| 23 | Glosarium & indeks |

## 23.3 Indeks "cari cepat" (peran)
| Saya ingin… | Lihat |
|---|---|
| Menjalankan aplikasi | Bab 3 |
| Menambah user/role/permission | Bab 4, 16 |
| Membuat debitur/permohonan | Bab 5, 6, 14 |
| Memahami rumus angsuran/jadwal/EIR | Bab 8 |
| Memahami skor 5C & red flag | Bab 7, 9 |
| Menambah/mengubah aturan bisnis | Bab 9 |
| Menelusuri approval | Bab 10 |
| Membaca/menulis API | Bab 13 |
| Memahami ledger/replay | Bab 12, 15 |
| Menambah parameter kebijakan | Bab 16 |
| Impor SLIK/KTP | Bab 17 |
| Kepatuhan PII/PDP | Bab 18 |
| Menemukan tabel/enum | Bab 19 |
| Tahu apa yang belum ada | Bab 20 |
| Backup/restore/rilis | Bab 21 |
| Memperbarui manual | Bab 22 |

## 23.4 Indeks label & sumber
- `[E]` bukti kode/route/migrasi/config/test/evidence.
- `[A]` usulan.
- `[K]` perlu verifikasi (angka/pasal/kebijakan/golden).
- `[H]` keputusan bertanda tangan (belum ada).
- `[Rencana]` belum terimplementasi.

## 23.5 Rujukan silang dokumen kunci
- Repo app: `README.md`, `docs/{RUANG-LINGKUP-PRODUK,RENCANA-PRODUKSI-FINAL,TEKNIS,INVENTARIS_DATA,PROGRESS,DECISIONS,VERIFIKASI}.md`.
- Workspace planning: `commandcode bpr/research/*`, `commandcode bpr/project/docs/{decisions.md,questions.md,claims.md,spec/*}`, `commandcode bpr/project/tests/golden/*`.
