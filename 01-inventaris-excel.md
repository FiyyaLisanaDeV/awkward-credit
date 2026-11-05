# 01 — Inventaris Referensi 3 Excel (tanpa menyalin nilai identitas)

Sumber: ekstraksi struktural ZIP/XML workbook (sheet, defined names/print area, formula, data validation/dropdown, label) pada 2026-11-05 `[E]`. **Nilai sel data tidak disalin** ke dokumen ini. **Klarifikasi user (2026-11-05):** data input Excel adalah **dummy**; inventaris tetap fokus pada struktur/rumus. `openpyxl/xlrd` tidak tersedia di mesin; parser ad-hoc XML dipakai.

## X1 — Workbook kasus TPP konsumtif (25 sheet, ±560 rumus) `[E]`
**Peran:** berkas kasus TPP konsumtif end-to-end (analisis + dokumen + asuransi + appraisal). Dipakai sebagai **referensi alur, field, dan daftar dokumen TPP**, bukan sebagai template bersih. `[A]` Data input **dummy** (klarifikasi user).

| Sheet | Rumus | Validasi | Merge | Fungsi/isi utama (dari label/formula) `[E]` | Modul target `[A]` |
|---|---:|---:|---:|---|---|
| `analisis kredit` | 21 | 3 | 62 | Analisis kredit konsumtif: identitas/TTL/usia (DATEDIF/TODAY), penghasilan & pengeluaran (SUM), **IDIR = angsuran/penghasilan bersih** (`D33/E28`), keputusan **IDIR>80% → DITOLAK/DITERIMA** (IF), link ke `hitungan!` (plafon/tenor/rate/angsuran), data SLIK ringkas; consent/tujuan; blokir 2× angsuran. | Analisis TPP, Capacity (IDIR), SLIK ringkas, Consent |
| `hitungan` | 70 | 0 | 9 | Perhitungan angsuran & biaya kredit (flat): plafon/tenor/rate dari analisis/surat pernyataan, provisi 1%, admin, notaris, **asuransi jiwa** (CHOOSE/tarif + komponen), appraisal 1,5% (`E9*0.015`), penalti/top-up pinjaman lama (indikasi 10% pokok via referensi), jadwal angsuran, dana diterima. | Fee/Rate engine, Jadwal Flat, Top-up/Payoff, Memo cair |
| `Sheet1` | 2 | 0 | 0 | Kalkulator/sisa kasus (penalti sederhana). **Tidak dimigrasi**; referensi saja. | (arsip referensi) |
| `MEMO CAIR` | 21 | 0 | 12 | Memo pencairan: identitas, plafon/rate/tenor/sistem, rincian potongan (provisi/admin/asuransi/dana diterima/biaya lain), TF/tujuan, alasan blokir, terbilang/TTD. Link `hitungan!`/`analisis kredit!`. | Dokumen pencairan, Ledger posting |
| `SPK` | 18 | 0 | 95 | Surat Perjanjian Kredit/akad TPP: para pihak, plafon/bunga/biaya/asuransi/blokir/tenor, jadwal penagihan SP1/2/3/segel/sita (teks), penalti/denda (teks+formula), kuasa/ketentuan. Memakai TERBILANG eksternal (`PROPER([1]!TERBILANG...)`) `[E]` → #NAME? risk. | Template dokumen akad |
| `Surat Kuasa` | 8 | 0 | 28 | Kuasa potong TPP/gaji (bendahara/dinas), nominal/angsuran dari hitungan/analisis. | Template dokumen |
| `Surat Pernyataan` | 5 | 0 | 22 | Pernyataan debitur (kebenaran data, kewajiban). | Template dokumen |
| `LEMBAR KONFIRMASI` | 0 | 0 | 19 | Konfirmasi AO ke pasangan/penjamin dan/atau bendahara dinas. | Template verifikasi |
| `ASS AL AMIN` | 3 | 0 | 9 | SPAPP/daftar peserta asuransi jiwa (Al-Amin): plafond, tenor, premi (link hitungan). | Asuransi + dokumen |
| `FORM PENGKINIAN` | 0 | 0 | 34 | Form pengkinian data nasabah (APU-PPT): CIF/data, tujuan/penghasilan/setoran/tarikan. | Dokumen compliance |
| `REGISTRASI` | 0 | 0 | 24 | Checklist/registrasi berkas kredit (±29 butir disebut rencana) `[? verifikasi hitung butir]`. | Checklist workflow |
| `PEMBLOKIRAN TABUNGAN` | 3 | 0 | 6 | Surat pemblokiran tabungan = **2× angsuran** (link hitungan), jangka waktu/alasan. | Dokumen + blocked savings |
| `SUPER ASS` | 21 | 0 | 14 | Daftar peserta asuransi (Super/Jasindo-like): umur/masa/tarif/manfaat, referensi blok tarif (rawan #VALUE! di blok tarif hitungan). | Asuransi |
| `Surat Persetujuan Slik` | 2 | 0 | 42 | Persetujuan pengecekan SLIK (UU PDP/tujuan), identitas link hitungan. | Consent SLIK |
| `REK LISTRIK` | 0 | 0 | 0 | Bukti token listrik (lampiran). **Tidak dimigrasi**. | Lampiran/arsip |
| `ASS JIWA` | 0 | 0 | 6 | Pernyataan/asuransi jiwa (ringkas). | Asuransi dokumen |
| `FLAT-ANN-EFF` | 291 | 0 | 0 | Konversi & tabel **Flat/Efektif/Anuitas** (tingkat suku pinjaman, angsuran pokok/bunga/total, jadwal 360 baris). Rumus RATE/PV/PMT-like pola & perhitungan eksak per baris `[E]`. | Master referensi (read-only) & golden pembanding (bukan konstanta 1,81x/0,87x) |
| `Scoring-konsumtif` | 2 | 0 | 20 | **FSS** 22 faktor/3 pilar (Kemampuan/Kemauan/Agunan), bobot, skor pilar & total, ambang White/Grey/Black, keputusan. Berisi `=hitungan!#REF!` `[E]`. Label/opsi terindikasi template KPR (rumah ke-2, marketabilitas properti) `[? konfirmasi]`. | Scoring TPP (harus dirancang ulang, D-02) |
| `AS. JAMINAN` | 37 | 0 | 48 | Surat pernyataan/asuransi jaminan. | Asuransi/dokumen |
| `ANALISA&USULAN` | 0 | 0 | 21 | Sisa kasus lain/usulan (produktif/modal kerja lama). **Tidak dimigrasi**. | Referensi |
| `FOTO` | 0 | 0 | 35 | Foto tempat tinggal/usaha/jaminan (menyumbang ukuran 14 MB). | Lampiran → storage terpisah + thumbnail |
| `BUMIDA` | 3 | 0 | 9 | Asuransi kebakaran (Bumida): data jaminan/premi. | Asuransi |
| `PK` | 12 | 0 | 50 | Perjanjian Kredit (varian/BPR lain konteks arsip). **Referensi dokumen**. | Template referensi |
| `kuasa` | 6 | 0 | 10 | Kuasa jual sukarela jaminan (varian). | Template dokumen |
| `apraisal` | 3 | 0 | 37 | Penilaian tanah & bangunan: nilai pasar wajar (rata-rata harga × luas), nilai likuidasi (80%). `[E]` | Appraisal/agunan |

**Catatan X1 `[E]`:** defined names/print area banyak (25 area), fungsi `TERBILANG` add-in eksternal, 6 link eksternal rusak, foto embedded, dropdown validasi terbatas (3 di analisis kredit). Data input **dummy** (klarifikasi user); ekstraksi ini tidak menyimpan nilai sel.

## X2 — Workbook MASTER Modal Kerja (15 sheet, ±3.160 rumus) `[E]`
**Peran:** template analisis kredit produktif **Modal Kerja** (sistem **Flat/Anuitas**) sesuai struktur POJK 2024 (label workbook). Logika inti ±260+ rumus non-jadwal; sisanya jadwal 360 periode.

| Sheet | Rumus | Validasi | Merge | Isi/rumus kunci `[E]` | Modul `[A]` |
|---|---:|---:|---:|---|---|
| `0.Cover & Dashboard` | 22 | 0 | 45 | KPI & navigasi; link identitas/permohonan dari sheet 1–2, rekomendasi/5C/DSCR/rpc dari 4,6,8,9. Red-flag/indikator ringkas. | Dashboard kasus |
| `1.Profil Debitur` | 14 | 9 | 22 | Data pribadi/pasangan/pekerjaan/usaha; **usia = DATEDIF(...,TODAY())**; tabel aset 10 baris: % diperhitungkan via **VLOOKUP ke Sheet 2 (POJK_Jenis_Pengikatan)**, nilai diperhitungkan = luas/jumlah × harga × %. Dropdown: JK, status kawin, kepemilikan tempat, pendidikan, pemilik jaminan, kategori pengikatan (named range), frekuensi/satuan. | Debitur, Usaha, Agunan/aset |
| `2.Data Kredit & Asumsi` | 15 | 4 | 46 | Permohonan (plafon/rate/tenor/sistem **Flat,Anuitas**), parameter kebijakan (margin kapasitas, DSCR min/ideal, LTV maks, hari kerja, sensitivitas omzet/biaya), nilai jaminan utama (link aset), **tabel referensi haircut POJK** (13 baris label: emas 85%, SHM+HT/fidusia 80%, resi gudang 70%/30%, SHM tanpa HT 60%, tanah adat/tempat usaha+kuasa/kendaraan-mesin fidusia/resi 12–18 & penjaminan BUMN/BUMD 50%, independen ≤1 th 20%, lain 0%, agunan tunai 100% — **persen final [H/POJK verifikasi]**), **SLIK pemohon & pasangan** (8 baris): angsuran ≈ `plafon/tenor + plafon*rate/12`, agregat baki/kol bermasalah, flag **“TIDAK LAYAK” bila macet/kol berat (threshold)**. | Permohonan, Policy, Master agunan POJK, SLIK, Red flag |
| `3.Neraca & Laba Rugi` | 30 | 0 | 10 | Neraca proforma (aktiva lancar/tetap, hutang, modal) & LR proforma. **[!] Temuan T6**: `SUMIF *Tanah*` dan `SUMIF *Kebun*` keduanya menangkap “Tanah/Kebun” → potensi hitung ganda aktiva tetap; ekuitas plug → neraca selalu balance. LR jadi basis surplus. | Analisis neraca/LR (engine harus perbaiki klasifikasi) |
| `4.Rasio Keuangan` | 13 | 0 | 8 | Likuiditas (current/quick/cash), solvabilitas (D/A, D/E, E/A), profitabilitas (NPM, ROA/ROE disetahunkan) + interpretasi teks. Handle hutang lancar 0. | Rasio |
| `5.Modal Kerja` | 14 | 0 | 9 | Siklus usaha (link sheet 1), **Cash Turnover** (biaya operasional tahunan / perputaran kas; hari kerja dari asumsi), **Working Capital Days** (pembelian/hari × hari persediaan), rata-rata kebutuhan, gap = max(0, kebutuhan−MK tersedia = aktiva lancar−hutang lancar), % kebutuhan vs omzet. | Modal kerja |
| `6.Kapasitas Bayar & DSCR` | 29 | 0 | 18 | **RPC = surplus bulanan × margin**; skenario diajukan vs direkomendasikan (plafon/tenor/rate → pokok, bunga, angsuran); **DSCR = RPC/angsuran**; status vs DSCR min/ideal; surplus setelah angsuran; **plafon maksimum** (rumus flat/anuitas dari RPC/rate/tenor); banding kebutuhan MK. **[!] T7**: ada referensi baris basi di ekosistem MASTER (cek silang sheet 5/7). | Kapasitas/DSCR/Plafon max |
| `7.Sensitivitas` | 37 | 0 | 10 | 6 skenario stress: dasar, omzet turun ringan/berat, biaya naik, kombinasi; delta dari parameter sheet 2; output pendapatan/pengeluaran/surplus/RPC/DSCR & status LAYAK/TIDAK vs DSCR syarat (pakai angsuran skenario rekomendasi). **[!] T7**: referensi baris non-pengeluaran berpotensi di beberapa versi. | Stress test |
| `8.Penilaian 5C` | 12 | 0 | 13 | **5C**: bobot Character 25%, Capacity 30% (DSCR rekomendasi), Capital 15% (E/A), Collateral 20%, Condition 10%; skor×bobot → total (1–5); **rating** (Sangat Layak ≥4, Layak ≥3, Layak Bersyarat ≥2, Tidak Layak); override skor 1 bila SLIK tidak layak (link sheet 2). **Catatan:** band pemetaan DSCR/E/A/LTV/SLIK→skor 1–5 adalah **DRAFT/harus diputuskan** `[? D-02/data]`. | Scoring 5C |
| `9.Rekomendasi & Keputusan` | 13 | 0 | 24 | Ringkasan identitas/hasil, **rekomendasi otomatis sistem** (kombinasi DSCR ideal/min & skor 5C), narasi, usulan plafon/tenor/rate/angsuran, jaminan, **keputusan komite** (tanda tangan). | Decision & approval |
| `10.Dokumentasi Jaminan` | 0 | 0 | 12 | Slot foto jaminan 1–4. | Lampiran agunan |
| `11.Surat Penyerahan Jaminan` | 8 | 0 | 25 | Draft surat + kuasa: debitur/KTP/alamat, daftar jaminan & kategori/pengikatan (TEXTJOIN dari sheet 1), lokasi. Teks hukum = draf `[!]`. | Template dokumen |
| `12.Perjanjian Kredit` | 11 | 0 | 37 | Draft perjanjian 9 pasal: plafon/rate/tenor/angsuran, jaminan, kewajiban/cidera janji/eksekusi/biaya/sengketa. **[!] T8 analog**: perlu satu sumber angsuran/sistem (jangan rumus flat hardcode saat sistem beda). | Template dokumen |
| `13.Perhitungan Kredit` | 774 | 2 | 18 | Ringkasan: plafon/rate/tenor, **bunga efektif = RATE(n,-angsuran,plafon)*12**, biaya (admin/provisi/survey/asuransi/perpanjangan kondisional), angsuran pokok/bunga/total (Flat/Anuitas), total bunga, dana bersih; **jadwal 360 periode** (saldo, pokok, bunga, total, sisa). Validasi dropdown sistem `Flat,Anuitas`. | Schedule & fee engine, dokumen |
| `14.Checklist Dok & Verifikasi` | 0 | 4 | 20 | Kelengkapan dokumen pemohon & jaminan (wajib/kondisional), **verifikasi lapangan** (kunjungan, tempat tinggal/usaha/fisik agunan, trade checking, estimasi omzet, kesesuaian), kesimpulan/rekomendasi petugas. Dropdown status verifikasi/rekomendasi. | Checklist & verifikasi |

## X3 — Workbook MASTER Grace Period (15 sheet, ±3.170 rumus) `[E]`
**Peran:** MASTER **Grace Period**; struktur 0–14 paralel X2, dengan ekstensi grace/balloon.

### Delta utama X2 → X3 (berdasarkan ekstraksi)
| Area | X2 (Modal Kerja) `[E]` | X3 (Grace Period) `[E]` | Implikasi `[A]` |
|---|---|---|---|
| Sistem suku bunga (Sheet 2 / 13) | Dropdown `Flat,Anuitas` | Dropdown memuat **`Grace Period`** (validasi “Flat,Anuitas,Grace Period”) | Enum/engine mendukung 3 sistem |
| Sumber pelunasan pokok (Sheet 2) | tidak ada blok khusus | **Sumber Pelunasan Pokok saat Jatuh Tempo** + **Perkiraan Penerimaan/Termin saat Jatuh Tempo (Rp)** | Wajib saat grace (BR pelunasan balloon) |
| Kapasitas/DSCR (Sheet 6) | angsuran & plafon max Flat/Anuitas | Cabang **Grace Period**: komponen pokok/bunga grace (bunga berjalan, pokok akhir), plafon max grace `RPC/(rate/12)` bila rate>0, **total kewajiban jatuh tempo = pokok+komponen akhir** saat grace; **kecukupan sumber pelunasan pokok (balloon)** | Engine grace + balloon check |
| Kecukupan balloon (Sheet 6) | — | **Rasio Kecukupan Pelunasan** = `IF(F48=0,0,C49/F48)`; status: C49 kosong → “BELUM DIISI…”, rasio ≥1,1 LAYAK buffer, ≥1,0 LAYAK DENGAN PERHATIAN, <1 TIDAK LAYAK | Hard/soft gate + alasan |
| Jadwal (Sheet 13) | pokok/bunga Flat/Anuitas, 360 baris | Cabang **Grace**: pokok = `IF(bulan terakhir, saldo, MIN($C$8, saldo))` (pokok NIHIL selama grace kecuali akhir, sesuai C8), bunga = `saldo*rate/12`, baris akhir “Pelunasan pokok akhir tenor / angsuran terakhir”; total baris formula 2.902 | Schedule engine grace + label akhir tenor |
| Dashboard/istilah (Sheet 0/13/7) | Modal Kerja | Label “SKEMA GRACE PERIOD…” dan penjelasan grace/balloon | Konten produk & help text |

## Perbandingan ringkas lintas workbook
| Aspek | X1 TPP `[E]` | X2 Modal Kerja `[E]` | X3 Grace Period `[E]` |
|---|---|---|---|
| Produk/sistem | Flat + TPP (potong gaji/bendahara) | Flat, Anuitas | Flat, Anuitas, Grace Period (balloon) |
| Keputusan utama | **IDIR** (hard stop 80% di sheet) + **FSS** (soft) | **DSCR** + **5C** + stress + plafon/MK | sama + **kecukupan pelunasan balloon** |
| Agunan/POJK haircut | appraisal + jaminan dokumen; konversi/list di FLAT-ANN-EFF | tabel POJK kategori+% diperhitungkan, LTV maks | sama |
| SLIK | tabel manual/link, consent | 8 baris pemohon+pasangan, angsuran dihitung, flag kol/macet | sama |
| Dokumen | banyak (≥20 sheet dokumen/administrasi) | profil→analisis→jaminan→PK→jadwal→checklist | sama + konteks grace |
| Risiko utama | PII nyata, TERBILANG eksternal/#REF, angka hardcode, konflik IDIR vs FSS | bug neraca SUMIF ganda/plug, ref baris basi, PK harus ikut sistem, DRAFT band 5C | sama + konsistensi grace/balloon & sumber pelunasan wajib |

## Cakupan yang diturunkan ke aplikasi (gabungan X1+X2+X3) `[A]`
- **Engine:** jadwal Flat/Anuitas/Grace, biaya & dana diterima, top-up/pelunasan lama, IDIR & DSR (dari SLIK), RPC/DSCR/plafon max (flat/anuitas/grace), modal kerja (2 metode), rasio, stress 6 skenario, LTV & nilai agunan (pasar/likuidasi/pengurang PPKA dipisah per keputusan), 5C + FSS TPP (redesign), balloon ratio, keputusan beralasan.
- **Data/master:** produk, policy berversi, haircut POJK, tarif asuransi, scoring model berversi, checklist, template dokumen, instansi/bendahara (TPP).
- **Workflow:** TPP end-to-end + produktif modal kerja/grace; approval berjenjang; maker-checker; snapshot analisis immutable.
- **Dokumen:** set TPP + set MASTER; angka/terbilang dari model (bukan hardcode); QR/hash/verifikasi.
- **Import:** SLIK (Excel/CSV/PDF teks/tempel), KTP/KK (NIK parser; OCR opsional), workbook lama, daftar TPP, pembayaran, aset/debitur XLSX.
- **Ledger/audit:** append-only, hash chain, idempoten, rekonsiliasi bayangan core banking (bila D-01).