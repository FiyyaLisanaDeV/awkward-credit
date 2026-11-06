# Flow Proses Kredit BPR — End to End

Sistem yang dibangun adalah **sistem manajemen kredit lengkap**: dari calon nasabah mendaftar
sampai dana kredit masuk ke rekening, atau permohonan ditolak. Dokumen ini memetakan alurnya
dalam tiga lapis diagram + skenario contoh C-002 (TPP flat).

> Status angka: semua angka contoh berstatus **klaim/recomputed, belum verified** kecuali
> dinyatakan lain. Diagram menggambarkan desain, bukan klaim hasil audit.

---

## 1. Pipeline 7 fase (overview)

Satu baris proses; setiap tepi membawa artefak kunci yang dihasilkan fase sebelumnya.

```mermaid
flowchart LR
  classDef phase fill:#e8f1f3,stroke:#0f4c5c,color:#17202a
  classDef ok fill:#dcfce7,stroke:#16a34a,color:#14532d
  classDef hard fill:#fde8e8,stroke:#dc2626,color:#7f1d1d

  P1:::phase -->|"CIF-KDI-000001"| P2:::phase
  P2:::phase -->|"KDI/2026/0001"| P3:::phase
  P3:::phase -->|"banner + audit log"| P4:::phase
  P4:::phase -->|"rekomendasi plafon"| P5:::phase
  P5:::phase -->|"SPK/KDI/2026/0001"| P6:::phase
  P6:::phase -->|"dana masuk rekening"| P7:::phase

  P1["<b>Fase 1 · CIF</b><br/>AO · KYC · cek NIK<br/>lintas cabang"]
  P2["<b>Fase 2 · Permohonan</b><br/>AO · produk/plafon<br/>snapshot policy"]
  P3["<b>Fase 3 · Analisis</b><br/>AO · neraca, WCD,<br/>RPC/DSCR, IDIR, LTV, 5C"]
  P4["<b>Fase 4 · Rekomendasi</b><br/>MIN(pengajuan,<br/>kapasitas, LTV)"]
  P5["<b>Fase 5 · Persetujuan</b><br/>MGR/Komite · SoD<br/>hard gate"]
  P6["<b>Fase 6 · Pencairan</b><br/>SPK · memo cair<br/>blokir/payoff"]
  P7["<b>Fase 7 · Pasca</b><br/>buku besar · monitoring<br/>CKPN/write-off"]

  P3 -. "hard stop / tolak" .-> TOLAK:::hard
  P5 -. "gagal approve" .-> TOLAK
  P6 -. "ditolak" .-> TOLAK
  P6 --> CAIR:::ok
  TOLAK(["<b>Permohonan ditolak</b><br/>surat + alasan · semua jejak diaudit"])
  CAIR(["<b>Dana masuk rekening nasabah</b>"])
```

## Ringkasan tiap fase

| Fase | Job utama | Artefak kunci |
|---|---|---|
| 1 · CIF | Verifikasi identitas, cegah nasabah ganda | `CIF-KDI-000001`, hash dokumen |
| 2 · Permohonan | Kunci produk, plafon, dan parameter kebijakan | `KDI/2026/0001`, snapshot policy |
| 3 · Analisis | Hitung kapasitas, agunan, scoring, sensitivitas | Banner warning, audit log, status IDIR/DSCR/LTV |
| 4 · Rekomendasi | Batasi plafon agar tidak melebihi kapasitas/agunan | Rekomendasi MIN(...) |
| 5 · Persetujuan | Persetujuan berjenjang dengan pemisahan tugas | Keputusan approve + hard gate |
| 6 · Pencairan | Booking kredit dan kirim dana | `SPK/KDI/2026/0001`, `KDI/2026/K/0001`, memo cair |
| 7 · Pasca | Catat posisi, pantau, kelola risiko | Buku besar, buku agunan, CKPN |

---

## 2. Gerbang keputusan detail (Analisis + Persetujuan)

Diagram ini memperbesar bagian yang menentukan: aturan mana yang lolos, warning, atau menolak.

```mermaid
flowchart TD
  classDef warn fill:#fef3c7,stroke:#f59e0b,color:#92400e
  classDef hard fill:#fde8e8,stroke:#dc2626,color:#7f1d1d
  classDef ok fill:#dcfce7,stroke:#16a34a,color:#14532d
  classDef netral fill:#f8fafc,stroke:#cbd5e1,color:#334155

  A0["Hasil analisis masuk gerbang"]:::netral --> IDIR{"IDIR vs batas<br/>(BR-11 / BR-11H)"}
  IDIR -- "≤ 35%" --> LTV{"LTV > batas maksimum?<br/>(G-COL-02)"}
  IDIR -- "35% – 80%" --> WARN["Banner PERHATIAN<br/>batas internal, bukan OJK<br/>(G-TPP-03-A)"]:::warn --> LTV
  IDIR -- "> 80%" --> HARDIDIR["HARD STOP<br/>(G-TPP-03-B)"]:::hard --> TOLAK(["<b>Ditolak</b> — audit trail"])

  LTV -- "Tidak" --> S5C["Scoring 5C<br/>bobot total 1,00<br/>override SLIK teraudit<br/>(G-SCR-01/02)"]
  LTV -- "Ya" --> MITIGASI["Mitigasi agunan<br/>atau tolak"]:::warn --> TOLAK

  S5C --> STRESS["6 skenario sensitivitas<br/>delta dari policy<br/>(G-STR-01/02)"]
  STRESS --> REK["Rekomendasi plafon<br/>MIN(pengajuan, kapasitas, LTV)<br/>(G-DEC-02)"]:::netral
  REK --> SOD{"SoD: pembuat ≠ penyetuju<br/>dan cabang sama?"}
  SOD -- "Tidak" --> FORBID["403"]:::hard --> TOLAK
  SOD -- "Ya" --> LADDER["MGR Pemasaran → Komite<br/>sesuai limit"]:::netral
  LADDER --> GATE{"Hard gate approve:<br/>BR-01/02/03/05/11H/13"}
  GATE -- "Gagal" --> TOLAKAPPROVE["Tolak approve<br/>+ alasan"]:::hard --> TOLAK
  GATE -- "Lulus" --> APPROVE["Persetujuan final"]:::ok --> CAIR(["<b>Dana masuk rekening</b>"])

  TOLAK:::hard
  CAIR:::ok
```

Kode warna: **hijau** = lanjut/cair, **kuning** = peringatan/mitigasi, **merah** = hard stop/tolak.

---

## 3. Sequence — skenario C-002 (TPP flat)

Skenario konkret: angka dihitung langkah demi langkah, lalu sistem menolak di hard gate.

```mermaid
sequenceDiagram
  participant N as Calon Nasabah
  participant AO as AO (KDI)
  participant S as Sistem (Laravel + PostgreSQL)
  participant M as MGR / Komite

  N->>AO: Mengajukan kredit TPP
  AO->>S: Permohonan: plafon Rp80.000.000, bunga 18%/tahun, tenor 60 bulan
  S->>S: (1) Snapshot policy + nomor KDI/2026/0001
  S->>S: (2) Pokok = 80.000.000 ÷ 60 = Rp1.333.333,33
  S->>S: (3) Bunga = 80.000.000 × 18% ÷ 12 = Rp1.200.000
  S->>S: (4) Angsuran flat = Rp2.533.333,33
  S->>S: (5) IDIR = 2.533.333,33 ÷ 2.948.080 = 85,93%
  S-->>AO: Banner: IDIR > 80% (hard stop)
  AO->>M: Ajukan keputusan
  M->>S: Approve
  S-->>M: Hard gate MENOLAK: IDIR > 80%
  M-->>N: Surat penolakan + alasan (audit trail)
  Note over S: Angka C-002 = klaim/recomputed, belum verified.
  Note over S: Alternatif: jika IDIR ≤ 80% → scoring/agunan → komite → SPK → dana cair.
```

---

## 4. Tabel keputusan per gate

| Gate | Kondisi | Keluaran | Status |
|---|---|---|---|
| Pendaftaran CIF | NIK duplikat lintas cabang | Tolak pendaftaran + alasan | Tolak |
| Permohonan | Dokumen/consent/agunan belum lengkap | Kembali ke nasabah | Tunda |
| Neraca | Selisih > toleransi | Soft warning (banner + audit), tidak blokir submit | Warning |
| IDIR | ≤ 35% | Lanjut | Lolos |
| IDIR | 35% – 80% | Banner PERHATIAN (batas internal) | Warning |
| IDIR | > 80% | Hard stop — tolak | Tolak |
| LTV | > batas maksimum | Tolak / mitigasi agunan | Tolak |
| Scoring 5C | Bobot ≠ 1,00 | Validasi konfigurasi gagal | Tolak |
| Override SLIK | Tanpa alasan/otorisasi/audit | Ditolak | Tolak |
| Persetujuan | Pembuat = penyetuju, atau lintas cabang | 403 | Tolak |
| Approve | Hard gate BR-01/02/03/05/11H/13 gagal | Tolak approve | Tolak |
| Pencairan | SPK/booking belum lengkap | Tunda pencairan | Tunda |
| Write-off | Parsial | Ditolak | Tolak |
| Write-off | Full tapi tidak eligible | Ditolak | Tolak |

---

## 5. Legenda singkatan

| Istilah | Arti |
|---|---|
| CIF | Catatan identitas nasabah (satu nomor per nasabah, lintas cabang) |
| KYC | Kenali nasabah: verifikasi identitas dan dokumen |
| SLIK | Informasi riwayat kredit dari OJK; dipakai untuk DSR/IDIR |
| IDIR | Installment to Disposable Income Ratio: angsuran baru ÷ penghasilan bersih |
| DSCR | Debt Service Coverage Ratio: RPC ÷ total kewajiban bulanan |
| RPC | Repayment Capacity: surplus × margin kapasitas |
| LTV | Loan to Value: kredit ÷ nilai agunan basis tunggal |
| PPKA | Penyisihan Penilaian Kualitas Aset (basis nilai agunan) |
| CKPN | Cadangan Kerugian Penurunan Nilai |
| BMPK | Batas Maksimum Pemberian Kredit (pihak terkait/borrower group) |
| SoD | Segregation of Duties: pembuat tidak boleh menyetujui miliknya sendiri |
| Hard stop | Aturan yang menolak transaksi |
| Soft warning | Peringatan yang tidak memblokir, tetapi tercatat |
| Snapshot | Salinan parameter kebijakan saat permohonan dibuat |
| Grandfathering | Permohonan lama tetap memakai snapshot lama |
| Ledger | Catatan peristiwa append-only dengan hash chain |

---

## 6. Pemetaan ke aturan & kasus golden

| Fase | Aturan terkait | Kasus golden terkait |
|---|---|---|
| Analisis kapasitas | BR-01/02/03/10/11/11H/12/13 | G-TPP-01, G-CAP-01..05, G-MK-01..03 |
| Jadwal/EIR | — | G-SCH-01..08 |
| Agunan/scoring | — | G-COL-01/02, G-SCR-01/02 |
| Keputusan/stres | BR-05 | G-DEC-01/02, G-STR-01/02 |
| Negatif/operasional | — | G-RAC-01/02, G-CKP-01, G-WO-01 |
| Submit/approve | BR-04/05/08/14/15 (submit); BR-01/02/03/05/11H/13 (approve) | — |
| Penomoran/backfill | NomorService atomik | B4-01..05 |
| Pencairan TPP | — | G-TPP-04/05 |
