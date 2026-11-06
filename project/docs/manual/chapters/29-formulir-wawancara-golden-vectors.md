# Bab 29 — Formulir Wawancara Golden Vectors (Grill-Me)

> **Ringkas:** formulir ini dipakai untuk mewawancarai ahli kredit/Produk/Keuangan sebelum 29 golden vector dinyatakan `verified`. Setiap jawaban wajib menyebut sumber (workbook/sheet/sel), hitung ulang independen, boundary/negatif, dan tanda tangan. Formulir ini **bukan** bukti verifikasi sampai diisi dan ditandatangani.

## 29.1 Tujuan

- Mengubah 29 kasus `RECOMPUTED_DRAFT_NOT_VERIFIED` menjadi kontrak yang disahkan manusia.
- Memisahkan **fakta sumber** dari **asumsi teknis**.
- Merekam alasan keputusan bila ahli menolak/mengoreksi nilai.
- Mencegah `verified=true` diisi tanpa bukti.

## 29.2 Peran & aturan bukti

| Peran | Tugas |
|---|---|
| Ahli kredit/Produk | Menjawab sumber, formula, nilai, boundary, dan keputusan bisnis |
| Reviewer independen | Menguji jawaban, menghitung ulang, mencatat konflik |
| Notulen | Mengisi formulir, menyimpan evidence, memperbarui JSON |

Aturan:
1. Data nasabah/PII **tidak boleh** masuk formulir; gunakan dummy/anonim.
2. Setiap angka wajib punya sumber: workbook, sheet, sel, atau hitungan independen (Python/Decimal/BCMath).
3. Dilarang memakai faktor `flat × 1,81` atau `× 0,87`; EIR harus numerik (IRR/bisection).
4. Jawaban “kira-kira”, “biasanya”, atau “standar industri” tanpa sumber dianggap `[K]`.
5. Jika ahli tidak tahu, tulis `UNKNOWN` dan hentikan kasus; jangan menebak.
6. `verified=true` hanya sah bila ada `verified_by`, `verified_at`, `evidence`, dan tanda tangan.

## 29.3 Mekanisme grill-me (3 putaran)

**Putaran 1 — Fakta**
- Apa sumber persisnya?
- Input apa saja, satuan, dan asumsi?
- Formula/urutan langkah?
- Nilai expected dan pembulatan?

**Putaran 2 — Uji silang**
- Hitung ulang independen; apakah sama?
- Uji boundary (tepat di ambang, di atas, di bawah).
- Uji kasus negatif: apakah harus ditolak?
- Apa yang terjadi bila input berubah 1 rupiah/1 persen?

**Putaran 3 — Keputusan**
- Terima nilai, koreksi, atau tandai `UNKNOWN`?
- Siapa yang menyetujui dan kapan?
- Bukti apa yang disimpan?
- Apakah kasus lain terdampak?

## 29.4 Formulir identitas wawancara

| Field | Isi |
|---|---|
| Tanggal/waktu | |
| Ahli (nama/peran) | |
| Reviewer independen | |
| Notulen | |
| Lingkup | 29 golden vector |
| Versi policy/engine | |
| Catatan konflik | |

## 29.5 Formulir per kasus

Salin blok ini untuk setiap kasus:

```text
ID kasus:
Grup/metode:
Fokus:

P1. Sumber workbook/sheet/sel:
P2. Input & satuan:
P3. Formula/urutan:
P4. Pembulatan:
P5. Nilai expected:
P6. Hitung ulang independen:
P7. Boundary (tepat/di atas/di bawah):
P8. Kasus negatif:
P9. Konflik dengan kasus lain:
P10. Keputusan (terima/koreksi/UNKNOWN):
Nama & tanggal:
Evidence:
Status: [K] / [H]
```

## 29.6 Matriks 29 kasus & pertanyaan kunci

| ID | Fokus | Pertanyaan kunci (wajib dijawab) |
|---|---|---|
| G-TPP-01 | Koreksi C-002: IDIR/net/RATE eksak | 1) Sel mana untuk plafon 80 jt, rate 18%, tenor 60, net 2.948.080? 2) Angsuran flat 2.533.333,33 dihitung dari pokok+bunga apa? 3) EIR 28,88% adalah RATE×12 atau effective annual? Tunjukkan arus kas. |
| G-TPP-02 | DSR total otomatis dari SLIK | 1) Fasilitas debitur/pasangan mana yang masuk? 2) Bagaimana dedupe dan fasilitas baki 0? 3) Apakah angsuran SLIK masuk denominator IDIR/DSR? |
| G-TPP-03 | Boundary IDIR hard-stop | 1) Hard stop `>80%` atau `>=80%`? Tunjukkan formula. 2) Status tepat 80,00% dan 80,01%? 3) Apakah warning 35% memakai basis yang sama? |
| G-TPP-04 | Top-up, payoff, penalti | 1) Komponen payoff (pokok, bunga berjalan, penalti 10%, biaya)? 2) Penalti dihitung dari pokok lama atau sisa? 3) Bagaimana jurnal dan dana diterima? |
| G-TPP-05 | Dana diterima, blokir 2x, blokir lama | 1) Blokir 2x dihitung dari angsuran baru? 2) Blokir lama dikurangi/ditambah? 3) Dana diterima = plafon - biaya - blokir - payoff? |
| G-SCH-01 | Tenor 1, rounding akhir | 1) Pokok dan bunga tenor 1? 2) Closing balance 0? 3) Apakah ada biaya yang memengaruhi jadwal? |
| G-SCH-02 | Plafon kecil, adjustment Rp1 | 1) Pembagian tidak bulat dialokasikan ke periode mana? 2) Adjustment tercatat? 3) Toleransi Rp1? |
| G-SCH-03 | Anuitas closing balance 0 | 1) PMT memakai rate bulanan? 2) Closing balance tepat 0? 3) Pembulatan bunga per periode? |
| G-SCH-04 | Flat vs EIR numerik, no 1,81x | 1) Metode IRR/bisection? 2) Mengapa bukan 1,81x? 3) EIR annualized×12 atau effective compounding? |
| G-SCH-05 | Grace: bunga berjalan, bullet | 1) Pokok 0 sampai periode berapa? 2) Bunga dihitung dari plafon? 3) Bullet akhir = plafon + bunga terakhir? |
| G-SCH-06 | Balloon ratio ≥1,10 | 1) Kewajiban = plafon + bunga akhir? 2) Sumber pelunasan dari mana? 3) Status pada 1,10x? |
| G-SCH-07 | Balloon ratio 1,00 | 1) Status tepat 1,00x? 2) Apakah masuk PERHATIAN? 3) Mitigasi yang wajib? |
| G-SCH-08 | Balloon ratio <1,00 | 1) Status 0,99x? 2) Hard stop atau mitigasi? 3) Sumber pelunasan wajib? |
| G-CAP-01 | RPC/DSCR/plafon max Flat | 1) RPC = surplus × margin berapa? 2) DSCR denominator hanya angsuran baru atau +SLIK? 3) Formula plafon max flat? |
| G-CAP-02 | RPC/DSCR/plafon max Anuitas | 1) PMT anuitas? 2) DSCR pada plafon max? 3) Formula plafon max anuitas? |
| G-CAP-03 | DSCR bunga periodik + balloon | 1) DSCR memakai bunga periodik atau total kewajiban? 2) Balloon ratio dihitung terpisah? 3) Status kombinasi? |
| G-MK-01 | Cash turnover, komponen biaya | 1) Formula cash turnover? 2) Hari kerja 360? 3) Komponen biaya eksplisit? |
| G-MK-02 | WCD/gap, klasifikasi aset | 1) Formula WCD? 2) Rata-rata = (CT+WCD)/2? 3) Gap = max(0, rata-rata - MK)? |
| G-COL-01 | PPKA/LTV 4 nilai, anti double-haircut | 1) Empat nilai: pasar, likuidasi, PPKA, basis LTV? 2) Mana yang dipakai LTV? 3) Bagaimana anti double-haircut? |
| G-COL-02 | Boundary LTV max | 1) Kategori POJK dan persen? 2) LTV dihitung dari exposure/basis apa? 3) Status tepat di batas? |
| G-SCR-01 | 5C rating boundary | 1) Bobot 25/30/15/20/10? 2) Band DSCR/equity/LTV/SLIK? 3) Rating boundary? |
| G-SCR-02 | SLIK override | 1) Kapan character dipaksa 1? 2) Alasan dan otorisasi? 3) Audit event apa? |
| G-STR-01 | Stress omzet ringan | 1) Delta omzet? 2) RPC/DSCR baru? 3) Status dan mitigasi? |
| G-STR-02 | Stress terburuk | 1) Kombinasi delta? 2) DSCR baru? 3) Mitigasi/hard stop? |
| G-DEC-01 | Konflik IDIR vs soft-score | 1) Mana yang menang? 2) Rekomendasi plafon = MIN(pengajuan, max, LTV)? 3) Alasan/audit? |
| G-RAC-01 | BMPK exposure | 1) Exposure dan limit? 2) Related party? 3) Bypass ditolak? |
| G-RAC-02 | Over-limit sektor + override | 1) Limit sektor? 2) Override Direksi dan justifikasi? 3) Audit? |
| G-CKP-01 | Arah PPKA↔CKPN | 1) Arah transisi? 2) CKPN < PPKA ditolak? 3) Jurnal/transition? |
| G-WO-01 | Hapus buku parsial | 1) Partial ditolak? 2) Full write-off syarat eligibility? 3) Audit? |

## 29.7 Contoh pengisian: G-TPP-03

| Pertanyaan | Jawaban ahli | Bukti |
|---|---|---|
| Hard stop | `IDIR > 80%` (bukan `>=`) | Formula Excel `IF(D34>80%,...)` |
| 80,00% | Lolos hard stop; hanya warning 35% | Hitung ulang |
| 80,01% | Hard stop | Hitung ulang |
| 35% | Basis yang sama (angsuran/net) | Definisi IDIR |
| Keputusan | Terima | Nama/tanggal |

## 29.8 Cara menutup kasus

1. Isi formulir per kasus.
2. Reviewer menghitung ulang dan menandatangani.
3. Perbarui JSON:
   - `verification.verified = true`
   - `verification.verified_by`
   - `verification.verified_at`
   - `verification.evidence`
4. Jalankan `python3 project/scripts/verify-golden.py` dan target `verified=29`.
5. Perbarui `golden-vectors-protocol.md` dan Bab 28.

## 29.9 Larangan

- Mengisi `verified=true` tanpa nama/tanggal/evidence.
- Menyalin PII atau isi sel nasabah ke formulir.
- Memakai faktor 1,81x/0,87x.
- Mengubah expected hanya agar test hijau.
- Menandai “sesuai standar” tanpa pasal/sumber.

## 29.10 Output yang diharapkan

- 29 formulir terisi.
- 29 `verification` lengkap.
- Ringkasan `verified=29`, `fail=0`.
- Daftar konflik/koreksi yang disetujui ahli.
