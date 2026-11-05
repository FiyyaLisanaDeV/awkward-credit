# 05 — Bukti Ekstraksi & Keterbatasan

Tanggal: 2026-11-05 (Asia/Makassar). Pelaksana: sesi analisis (Glyph Cluster). Tujuan: inventaris struktural workbook Excel untuk perencanaan, **tanpa menyalin nilai sel identitas**.

> **Klarifikasi user (2026-11-05):** data yang terinput di dalam Excel adalah **data dummy**. Ekstraksi tetap tidak menyalin nilai identitas ke dokumen rencana; ini bukan pemindai PII formal.

## 1. Sumber file (enumerasi) `[E]`
Perintah: pencarian `*.xlsx/*.xls/*.xlsm` pada direktori kerja lokal.

| Workbook | Cakupan ringkas |
|---|---|
| X1 — kasus TPP konsumtif | 25 sheet, ±560 rumus |
| X2 — MASTER Modal Kerja | 15 sheet, ±3.160 rumus |
| X3 — MASTER Grace Period | 15 sheet, ±3.170 rumus |
| (vendor `phpoffice` — Translations.xlsx) | diabaikan: bukan referensi bisnis |

> Nama berkas, ukuran, dan lokasi workbook **tidak didokumentasikan** karena data kritikal (dikecualikan via `.gitignore`).

Workbook bisnis yang dianalisis = **3 file** (X1, X2, X3).

## 2. Metode ekstraksi `[E]`
- `python3` tersedia; modul `openpyxl/xlrd/pandas` **tidak terpasang**. Ekstraksi dilakukan dengan membaca kontainer `.xlsx` sebagai ZIP dan mem-parse XML (`xl/workbook.xml`, `xl/_rels/workbook.xml.rels`, `xl/sharedStrings.xml`, `xl/worksheets/sheet*.xml`).
- Data yang diambil: nama sheet, jumlah sheet, defined names/print area, jumlah formula/validasi/merge per sheet, sampel formula (teks rumus), dropdown `dataValidation/formula1`, dan **label/teks header** (kolom A–H) yang lolos filter.
- **Filter nilai identitas** pada label yang dicetak: melewati nilai yang tampak seperti NIK/KK 16 digit atau nomor HP (+62/08), dan tidak mencetak nilai numerik/nominal mentah; fokus pada struktur/rumus. (Data Excel = dummy sesuai klarifikasi user; langkah ini tetap menjaga agar dokumen rencana tidak menyalin nilai identitas.)

## 3. Hasil kunci (ringkas) `[E]`
- X1: 25 sheet (`xl/worksheets/sheet1..25.xml`), 143 entri ZIP, 13 defined names (print area/titles); total ±560 formula. Memuat foto embedded (ukuran 14 MB) dan TERBILANG add-in eksternal/`#REF!` pada beberapa referensi.
- X2: 15 sheet (0–14), 63 entri, 21 defined names; ±3.160 formula (dominan jadwal 360 periode). Dropdown: JK/status kawin/kepemilikan/pendidikan/pemilik jaminan, `POJK_Jenis_Pengikatan` (named range), sistem `Flat,Anuitas`, kolektibilitas 1–5, status verifikasi/rekomendasi.
- X3: 15 sheet (0–14), 59 entri, 21 defined names; ±3.170 formula. Relasi workbook memakai `Target="/xl/worksheets/..."` (ditangani terpisah). Memuat elemen Grace Period: sistem validasi `Flat,Anuitas,Grace Period`, sumber & perkiraan pelunasan pokok, rasio kecukupan balloon (≥1,1/≥1,0/<1,0), jadwal pokok `MIN($C$8,saldo)`/pelunasan akhir tenor.

## 4. Keterbatasan
- Tanpa evaluasi nilai sel hasil rumus (tanpa engine spreadsheet/openpyxl); angka kalkulasi (mis. contoh IDIR/angsuran/rate eksak) **tidak dihitung ulang** dari nilai workbook mentah di sesi ini — dikutip hanya bila berasal dari dokumen rencana/koreksi yang sudah ada, dan ditandai `[K]` untuk verifikasi.
- X1 besar (foto/data nyata): ekstraksi struktural mungkin tidak menangkap semua objek/gambar/VBA (jika ada). Tidak ada klaim “lengkap 100% semua sel”.
- Mapping sel (alamat baris/kolom) pada sebagian label X3 tidak diekspose penuh karena bentuk XML/inline string; formula kunci grace/balloon/jadwal tetap terambil.
- Ini **bukan** bukti pemindai PII hijau formal. Sebelum fixture/golden/commit: wajib jalankan pemindai PII dan gunakan data **dummy/anonim**.

## 5. Kepatuhan PII (tindakan yang dilakukan)
- Tidak menyalin nilai sel identitas (NIK/KK/nama/alamat/HP/rekening), foto, atau isi dokumen bernama orang ke folder `commandcode bpr/` atau ke output rencana.
- Referensi ke X1 diberi peringatan eksplisit: **bersifat kritikal → jangan masuk repo/prompt/fixture** (nama/ukuran/lokasi berkas tidak didokumentasikan).

## 6. Reproduksi (aman, struktural)
Jalankan parser ZIP/XML sejenis di atas pada 3 file X1–X3 untuk mendapatkan daftar sheet, hitungan formula/validasi, defined names, dan sampel formula/label. Untuk nilai terhitung/golden: gunakan environment dengan `openpyxl`/LibreOffice recalc headless pada salinan workbook yang **sudah dianonimkan** `[K, Rencana F0]`.