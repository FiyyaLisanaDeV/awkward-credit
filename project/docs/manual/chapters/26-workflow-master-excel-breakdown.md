# Bab 26 — Workflow Master Excel: Breakdown Teknis

> **Ringkas:** bab ini membedah **struktur, urutan kerja, ketergantungan antar-sheet, dan kontrak formula** pada workbook master Excel analisa kredit BPR. **Seluruh nilai input (identitas, NIK, nominal, tanggal) diabaikan** dan tidak dikutip; yang dibahas hanya fungsi, alur, dan mekanisme. Fokus: memahami workflow Excel sebagai acuan migrasi/paritas ke aplikasi web.

<details>
<summary><b>Cara membaca bab ini (klik)</b></summary>

- **Input** = sel yang diisi manusia; **Kalkulasi** = sel formula; **Output** = ringkasan/dokumen; **Kontrol** = gate/checklist.
- Tabel formula adalah **kontrak**, bukan angka. Nilai contoh tidak disalin.
- Untuk komparasi fungsi Excel vs web, lihat [Bab 25](Bab-25-Analisis-Fungsi-Komparasi-Excel-Web).
</details>

## 26.1 Inventaris file

| File | Jumlah sheet | Peran | Catatan |
|---|---:|---|---|
| `MASTER_Analisa_Kredit_MODAL_KERJA_BPR_V2_POJK2024_FIXED_F4_V12.xlsx` | 15 | Template utama **Modal Kerja** | Basis analisa lengkap; 1 debitur = 1 salinan file |
| `MASTER_Analisis_Kredit_Grace_Period_V2_POJK2024_FIXED_F4_V12.xlsx` | 15 | Varian **Grace Period** | Struktur sama; Sheet 6 & 13 berbeda (balloon) |
| `ANALISIS EDDIY (ut kredit TPP).xlsx` | ±20 | Contoh **TPP konsumtif** | Bukan template kosong; memuat studi kasus + tabel referensi |

## 26.2 Klasifikasi sheet (Modal Kerja / Grace)

| Sheet | Jenis | Fungsi |
|---|---|---|
| 0. Cover & Dashboard | **Output/ringkasan** | Key metrics, red flags, rekomendasi otomatis, peta navigasi |
| 1. Profil Debitur | **Input** | Data pribadi, pasangan, aset/agunan, siklus usaha |
| 2. Data Kredit & Asumsi | **Input + referensi** | Plafon/tenor/rate/sistem bunga, parameter kebijakan, tabel POJK, SLIK debitur/pasangan, riwayat internal |
| 3. Neraca & Laba Rugi | **Input → kalkulasi** | Neraca proforma + laba rugi proforma |
| 4. Rasio Keuangan | **Turunan** | Likuiditas, solvabilitas, profitabilitas |
| 5. Modal Kerja | **Turunan** | Cash Turnover + Working Capital Days + gap |
| 6. Kapasitas Bayar & DSCR | **Turunan** | RPC, DSCR, plafon maks, EIR; grace: kecukupan balloon |
| 7. Sensitivitas | **Turunan** | 6 skenario stress |
| 8. Penilaian 5C | **Turunan + input kualitatif** | Skor berbobot, rating, override SLIK |
| 9. Rekomendasi & Keputusan | **Keputusan + output** | Ringkasan, narasi, jaminan, tanda tangan komite |
| 10. Dokumentasi Jaminan | **Output** | Slot foto agunan |
| 11. Surat Penyerahan Jaminan | **Dokumen** | Draf surat/kuasa jaminan |
| 12. Perjanjian Kredit | **Dokumen** | Draf perjanjian |
| 13. Perhitungan Kredit | **Turunan** | Jadwal angsuran, biaya, EIR, jadwal s.d. lunas |
| 14. Checklist Dok & Verifikasi | **Kontrol** | Kelengkapan dokumen + verifikasi lapangan |

## 26.3 Workflow end-to-end (Modal Kerja / Grace)

**Tahap 1 — Profil & aset (Sheet 1).** Input identitas, pasangan, pekerjaan/usaha, siklus usaha, tabel aset. Tabel aset menghitung `Nilai Diperhitungkan = Luas/Jumlah × Harga Taksasi × % Kategori POJK`. Output: total taksasi + total nilai diperhitungkan → Sheet 2 (LTV) dan Sheet 9 (jaminan).

**Tahap 2 — Permohonan & asumsi (Sheet 2).** Input plafon/tenor/rate/sistem bunga (Flat/Anuitas/Grace), parameter kebijakan, tabel kategori agunan POJK, SLIK debitur/pasangan, riwayat kredit internal. Output: parameter aktif, total angsuran fasilitas lain (masuk Laba Rugi), nilai jaminan utama.

**Tahap 3 — Neraca & Laba Rugi (Sheet 3).** Input komponen aktiva/pasiva/pendapatan/pengeluaran. Kalkulasi: total aktiva, total pasiva, surplus 6 bulan, laba ditahan, ekuitas penyeimbang, `selisih cek`. Output: surplus/bulan → Sheet 4, 5, 6; ekuitas → Sheet 4 & 8.

**Tahap 4 — Rasio (Sheet 4).** Turunan langsung dari Sheet 3: current/quick/cash, D/A, D/E, E/A, NPM, ROA, ROE (annualized). Output: `equity_to_asset` → Capital 5C.

**Tahap 5 — Modal kerja (Sheet 5).** Input siklus (frekuensi belanja, modal per siklus, pendapatan kotor per siklus) + biaya operasional. Kalkulasi: perputaran kas/tahun, kebutuhan Cash Turnover, kebutuhan Working Capital Days, rata-rata 2 metode, gap = rata-rata − modal kerja tersedia, rasio kebutuhan terhadap omzet. Output: kebutuhan modal kerja → pembanding plafon (Sheet 6).

**Tahap 6 — Kapasitas & DSCR (Sheet 6).** RPC = surplus × margin; angsuran skenario diajukan vs direkomendasikan; DSCR = RPC / angsuran; surplus setelah angsuran; plafon maksimum. **Grace Period menambah**: total pembayaran jatuh tempo, sumber pelunasan, rasio kecukupan, status balloon. Output: DSCR → Capacity 5C; plafon maks → rekomendasi; EIR transparansi.

**Tahap 7 — Sensitivitas (Sheet 7).** 6 skenario: dasar, omzet −ringan, omzet −berat, biaya +, kombinasi ringan+biaya, kombinasi terburuk. Tiap skenario menghitung ulang pendapatan → surplus → RPC → DSCR → status. Output: jumlah skenario layak → Condition 5C & red flag.

**Tahap 8 — 5C (Sheet 8).** Skor 1–5 per aspek dengan bobot (Character 25%, Capacity 30%, Capital 15%, Collateral 20%, Condition 10%) → nilai tertimbang → total → rating (≥4 Sangat Layak; 3–3.99 Layak; 2–2.99 Bersyarat; <2 Tidak Layak). **Override**: ≥2 fasilitas macet atau ≥1 kolektibilitas ≥3 → skor 1.00 / Tidak Layak.

**Tahap 9 — Rekomendasi & keputusan (Sheet 9).** Menggabungkan plafon, angsuran, DSCR, skor, plafon maks, jaminan → rekomendasi otomatis + narasi. Keputusan komite = **input manual + tanda tangan**.

**Tahap 10 — Perhitungan kredit (Sheet 13).** Turunan Sheet 2: angsuran pokok/bunga/total per sistem bunga, total bunga, total pembayaran, biaya (provisi/admin/survey/asuransi/perpanjangan), dana bersih diterima, EIR, jadwal sampai lunas. **Grace Period**: pokok nihil selama grace, bunga konstan dari plafon penuh, pembayaran bulan terakhir = pokok + bunga (balloon).

**Tahap 11 — Dokumen (Sheet 10–12).** Foto agunan, surat penyerahan jaminan/kuasa menjual, perjanjian kredit. Data ditarik dari Sheet 1/2/13.

**Tahap 12 — Checklist & verifikasi (Sheet 14).** Checklist dokumen pemohon/agunan + verifikasi lapangan + rekomendasi petugas. Gate sebelum berkas ke Komite.

### 26.3.1 Alur Grace Period (beda inti)
- Sheet 6 menilai **dua risiko terpisah**: (a) DSCR bunga bulanan, (b) **kecukupan pelunasan pokok saat jatuh tempo** (sumber pelunasan vs total balloon).
- Sheet 13: `angsuran pokok selama grace = 0`, `bunga/bulan = plafon × rate/12`, `pembayaran bulan terakhir = pokok penuh + bunga`.
- EIR Grace = rate nominal (saldo pokok tidak turun sebelum jatuh tempo).

## 26.4 Workflow TPP (konsumtif)

| Urutan | Sheet | Fungsi |
|---|---|---|
| 1 | `analisis kredit` | Data pribadi, penghasilan (gaji/TPP), pengeluaran, kebutuhan (plafon/tenor/rate/angsuran), IDIR, keputusan awal, biaya, SLIK, catatan AO, tanda tangan |
| 2 | `hitungan` | Angsuran & biaya, jadwal teoritis, total bunga/pokok/dibayar, dana diterima, blokir, pelunasan pinjaman lama |
| 3 | `Sheet1` | Rekap saldo pokok+bunga, bunga berjalan, saldo rekening (kertas kerja) |
| 4 | `MEMO CAIR` | Memo pencairan (plafon, bunga, tenor, angsuran, biaya, dana diterima, rekening tujuan) |
| 5 | `SPK` | Surat Perjanjian Kredit (pasal 1–10) |
| 6 | `Surat Kuasa` | Kuasa pemotongan TPP |
| 7 | `Surat Pernyataan` | Pernyataan debitur (potong TPP, jaminan tambahan) |
| 8 | `LEMBAR KONFIRMASI` | Konfirmasi AO ke pasangan/penjamin/bendahara |
| 9 | `ASS AL AMIN`, `ASS JIWA`, `AS. JAMINAN`, `SUPER ASS` | Dokumen & tarif asuransi |
| 10 | `FORM PENGKINIAN` | Pengkinian data nasabah (CIF, alamat, pekerjaan, sumber dana) |
| 11 | `Surat Persetujuan Slik` | Consent SLIK (UU PDP) |
| 12 | `PEMBLOKIRAN TABUNGAN` | Persetujuan blokir tabungan |
| 13 | `REGISTRASI` | Registrasi + checklist kelengkapan berkas (29 item) + no. PK/memo/fasilitas |
| 14 | `Scoring-konsumtif` | FSS: Ability/Willingness/Collateral, bobot, white/grey/black case |
| 15 | `FLAT-ANN-EFF` | Tabel konversi rate Flat/Efektif/Anuitas + tabel margin |
| 16 | `ANALISA&USULAN` | Analisa usaha + usulan |
| 17 | `REK LISTRIK` | Bukti pendukung |

**Rantai TPP:** `analisis kredit` → `hitungan` → (`MEMO CAIR`, `SPK`, `Surat Kuasa`, `Surat Pernyataan`, asuransi, blokir) → `REGISTRASI` (checklist) → `Scoring` (keputusan). `FLAT-ANN-EFF` hanya referensi rate.

## 26.5 Rantai ketergantungan (dependency graph)

**Modal Kerja / Grace**
```
Sheet1 (aset, siklus) ─┬→ Sheet2 (nilai jaminan, parameter)
                       └→ Sheet5 (input siklus)

Sheet2 (SLIK, angsuran lain) ─→ Sheet3 (pengeluaran)
Sheet3 ─┬→ Sheet4 (rasio) ─→ Sheet8 (Capital)
        ├→ Sheet5 (biaya operasional)
        └→ Sheet6 (surplus → RPC)

Sheet6 ─┬→ Sheet7 (skenario)
        ├→ Sheet8 (Capacity, Collateral)
        └→ Sheet9 (angsuran, DSCR, plafon maks)

Sheet8 ─→ Sheet9 (rating, skor)
Sheet2 + Sheet13 (plafon/tenor/rate → jadwal/biaya/EIR)
Sheet1 ─→ Sheet11/12 (identitas & jaminan)
Sheet6/8/9 → Sheet0 (red flags, rekomendasi)
Sheet14 → Sheet0 (kelengkapan/verifikasi)
```

**TPP**
```
analisis kredit ─→ hitungan ─┬→ MEMO CAIR
                             ├→ SPK
                             ├→ Surat Kuasa / Pernyataan / Konfirmasi
                             └→ asuransi (ASS AL AMIN/JIWA/JAMINAN)
analisis kredit ─→ Scoring-konsumtif (FSS)
REGISTRASI ← checklist + no. PK/memo/fasilitas
FLAT-ANN-EFF = referensi rate (tidak mengikat alur)
```

## 26.6 Formula inti (kontrak, tanpa nilai)

| Kalkulasi | Rumus/kontrak |
|---|---|
| Neraca | Total aktiva = Σ komponen; total pasiva = hutang + modal; laba ditahan = surplus 6 bulan; ekuitas = **plug** (aktiva − hutang − laba ditahan); `selisih cek` |
| Rasio | current = AL/HL; quick = (AL−persediaan)/HL; cash = (kas+bank)/HL; D/A = hutang/aktiva; D/E = hutang/ekuitas; E/A = ekuitas/aktiva; NPM = laba/pendapatan; ROA/ROE annualized |
| Modal kerja | perputaran = hari kerja / periode; cash turnover = biaya tahunan / perputaran; WCD = (pembelian/30) × hari persediaan; rata-rata = (CT+WCD)/2; gap = max(0, rata-rata − MK tersedia) |
| RPC/DSCR | RPC = surplus × margin; DSCR = RPC / angsuran; plafon max flat = RPC / (1/tenor + rate/12); anuitas = RPC × faktor PV; grace = RPC / (rate/12) |
| Balloon (grace) | rasio kecukupan = sumber pelunasan / total jatuh tempo |
| Sensitivitas | recompute pendapatan/biaya per skenario → surplus → RPC → DSCR → status |
| 5C | total = Σ(skor × bobot); rating band; override SLIK |
| EIR | flat: `RATE(plafon, angsuran, tenor)`; anuitas: = nominal; grace: = nominal |
| TPP | angsuran flat = plafon/tenor + plafon×rate/12; IDIR = angsuran / penghasilan bersih; dana diterima = plafon − biaya − blokir − pelunasan lama; FSS = Σ(skor×bobot) dengan ambang white/grey/black |

## 26.7 Gate kontrol & keputusan

| Gate | Sumber | Aksi |
|---|---|---|
| DSCR ≥ minimum | Sheet 6 | Layak/Tidak Layak |
| LTV ≤ maksimum | Sheet 6/8 | Collateral score + red flag |
| Rating 5C | Sheet 8 | Sangat Layak/Layak/Bersyarat/Tidak Layak |
| Override SLIK | Sheet 8 | Paksa 1.00/Tidak Layak |
| Kecukupan balloon | Sheet 6 (grace) | Wajib verifikasi sumber pelunasan |
| IDIR/angsuran vs penghasilan | TPP `analisis kredit` | Disetujui/Ditolak |
| Checklist dokumen | Sheet 14 / `REGISTRASI` | Berkas belum lengkap → tahan |
| Tanda tangan | Sheet 9 / dokumen | Keputusan manual komite |
| Printing | Sheet 0 | Wajib (hijau) vs opsional (oranye) |

## 26.8 Kelemahan teknis Excel yang terlihat

1. **Ekuitas sebagai plug** di Neraca → ketidakseimbangan bisa tersembunyi.
2. **SUMIF wildcard** untuk aset (mis. `*Tanah*`, `*Kebun*`) berisiko **double counting**.
3. **Duplikasi data manual** identitas/agunan di banyak sheet (1, 9, 11, 12, 13) → rawan tidak sinkron.
4. **Error formula** ditemukan di template TPP (`#NAME?`, `#REF!`) → rapuh saat disalin.
5. **Tidak ada versi/audit/SoD**; tiap debitur = salinan file → risiko salah file/overwrite.
6. **Parameter tersebar di sel**, bukan single source of truth.
7. **Grace**: DSCR bulanan tinggi tidak menangkap risiko balloon (karena itu ditambah gate terpisah).
8. **TPP**: IDIR memakai penghasilan bersih; fasilitas SLIK tidak otomatis masuk pengeluaran; bobot FSS hardcoded.
9. **Blokir tabungan/pelunasan lama** manual, tidak terhubung core banking.

## 26.9 Rekonsiliasi ringkas ke web

| Excel | Web |
|---|---|
| Sheet 1–2 | Wizard Debitur + Permohonan + SLIK + pengaturan parameter `[E]` |
| Sheet 3–9 | API/service ada, **UI belum** (`analisis_*`) |
| Sheet 10, 14 | UI foto/verifikasi **belum** |
| Sheet 11–13 | `/dokumen` (17 generator), `/simulasi`, `/kredit/cairkan` `[E]` |
| TPP (memo/SPK/kuasa/asuransi/registrasi) | Sebagian generator dokumen; registrasi/checklist via `ref_checklist_dokumen` |
| Kontrol | Web menambah RBAC, approval, ledger, audit, versioning `[E]` |

Detail komparasi fungsi: [Bab 25 — Analisis Fungsi: Komparasi Excel–Web](Bab-25-Analisis-Fungsi-Komparasi-Excel-Web).
