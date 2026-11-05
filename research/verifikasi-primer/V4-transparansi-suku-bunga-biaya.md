# V4 — Verifikasi Primer: Transparansi Suku Bunga, Biaya, Total Biaya Kredit, & Perlindungan Konsumen (BPR)

Tanggal: 2026-11-05 (WITA) | Status: **DRAFT `[K]` — naskah pasal resmi belum dikunci**.  
Pemilik: Kepatuhan + Bisnis/Kredit + Legal + Akuntansi. **Bukan nasihat hukum.**

## 1. Tujuan
Mengunci kewajiban pengungkapan/informasi ke debitur atas suku bunga (nominal/efektif/total biaya sesuai definisi ketentuan), rincian biaya, simulasi/jadwal, denda/penalti/pelunasan dipercepat, dan risiko produk (termasuk grace/balloon) untuk **D-05, D-06, Schedule/Fee/EIR, dokumen/disclosure**.

## 2. Status verifikasi sumber
| Item | Status | Catatan |
|---|---|---|
| Ketentuan perlindungan konsumen & transparansi informasi produk/jasa keuangan yang **berlaku untuk BPR** | `[K]` | Identifikasi POJK/SEOJK yang tepat (lintas sektor jasa keuangan / spesifik bank/BPR) dan versi terbaru — **wajib verifikasi naskah resmi**. |
| Definisi “suku bunga efektif/total biaya kredit” untuk disclosure | `[K]` | Menentukan arus kas mana masuk perhitungan (plafon kotor vs dana diterima; biaya upfront/berkala) `[K]`. App harus configurable per definisi `[H]`. |
| Ketentuan denda/penalti/pelunasan dipercepat | `[K]` | X1 SPK/hitungan memuat penalti 10% vs 15% `[E, konflik]` — kebijakan + kepatuhan `[H]`. |

## 3. Checklist ekstraksi pasal `[K]`
| Poin | Yang harus dicari | Dampak app |
|---|---|---|
| Informasi sebelum perjanjian | suku bunga, metode (flat/anuitas), biaya, total kewajiban/biaya, simulasi, risiko | layar simulasi, ringkasan pra-akad, dokumen |
| Suku bunga efektif/total biaya | formula/definisi, komponen biaya, format tampilan | `EirCalculator` + disclosure config (D-05) |
| Jadwal angsuran | rincian pokok/bunga/angsuran/sisa pokok, tanggal | `ScheduleEngine`, dokumen, no hardcode |
| Biaya & denda/penalti | jenis, besaran/basis, kapan dikenakan, pelunasan dipercepat | `FeeEngine`, D-06, template |
| Perubahan & persetujuan | bila parameter berubah, notifikasi/persetujuan `[K]` | versioning, addendum workflow |
| Penanganan pengaduan | kanal & SLA `[K]` | operasional (di luar engine inti) |

## 4. Implikasi desain `[A]`
- **Dual disclosure**: tampilkan **nominal** (metode + % p.a.) dan **estimasi efektif/total biaya** sesuai definisi `[H]`; jelaskan komponen. **Larangan faktor 1,81x/0,87x**.
- **Rincian wajib di snapshot/dokumen**: plafon, tenor, metode, grace/balloon (jika ada), bunga, semua biaya (provisi/admin/asuransi/notaris/appraisal/lain), pajak/komponen `[K]`, blokir, payoff, **dana diterima**, jadwal, denda/penalti, konsekuensi keterlambatan/pelunasan `[K]`.
- **Grace/balloon risk disclosure**: X3 menanggung risiko pelunasan akhir besar — tampilkan `kewajiban_akhir`, sumber pelunasan, kecukupan `[K]`.
- **Konsistensi**: angka dokumen = snapshot = ledger posting terkait biaya; terbilang engine; no add-in (fix T8/T9/T12).
- **D-05/D-06 `[H]`**: rounding, pengakuan pendapatan, alokasi bayar, denda, penalti (selesaikan konflik 10% vs 15% `[E]`), blokir lama.

## 5. Cross-check ke workbook `[E]`
| Sumber | Bukti | Fix |
|---|---|---|
| X1 hitungan/memo cair/SPK | biaya, dana diterima, penalti 10/15% konflik, denda 0% | definisi tunggal `[H]`; dana diterima = jurnal |
| X1 FLAT-ANN-EFF | konversi pendekatan 1,81x/0,87x | **hapus**, ganti IRR/EIR numerik |
| X2/X3 Sheet 13 | jadwal 360 periode, RATE, biaya, dana bersih | satu sumber jadwal; disclosure lengkap |

## 6. Output penutup V4
1. Rujukan ketentuan transparansi/perlindungan konsumen berlaku untuk BPR `[H]` + definisi EIR/total biaya.
2. D-05, D-06 `[H]`; format disclosure/simulasi disahkan.
3. Golden EIR/dana diterima/jadwal/dokumen (dummy) + tes konsistensi snapshot↔dokumen↔ledger.

## 7. Pertanyaan terbuka
- `UNKNOWN (V4a)`: Ketentuan (nomor/pasal) transparansi/perlindungan konsumen yang berlaku untuk BPR?
- `UNKNOWN (V4b)`: Definisi resmi komponen “efektif/total biaya” yang harus ditampilkan?
- `UNKNOWN (V4c)`: Aturan denda keterlambatan & penalti pelunasan dipercepat yang sah dipakai `[H]`?
