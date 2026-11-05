# 06 — Sintesis Deep Research: Formulasi, Teknik & Best Practice (POJK/BPR)

Tanggal: 2026-11-05 (WITA) | Status: **DRAFT kerja** untuk penyempurnaan app.  
Label: `[E]` bukti Excel/plan repo, `[A]` best practice/usulan teknis, `[K]` **wajib verifikasi** ke naskah POJK/SEOJK/OJK/kebijakan internal BPR yang berlaku, `[H]` keputusan bertanda tangan.

> **Batas integritas:** Sintesis ini **tidak** mengutip pasal/ayat/angka POJK secara final kecuali ditandai `[H]` setelah verifikasi. Workbook menyebut “POJK 2024” dan tabel haircut `[E X2/X3]`, tetapi **nomor/pasal/% harus diverifikasi** ke naskah resmi sebelum masuk seed/engine. Excel = referensi UX/struktur, bukan sumber hukum.

## 1) Kerangka regulasi/praktik yang harus memandu formulasi
| Area | Kenapa relevan ke app | Arah kebijakan formulasi `[A/K]` |
|---|---|---|
| **Kualitas aset BPR/BPRS** (kolektibilitas, PPAP, agunan diperhitungkan/PPKA, penilaian agunan) | D-07/T13, SLIK/kolek, LTV/collateral | Peta kategori agunan + % diperhitungkan dari POJK berlaku `[K→H]`; pisahkan **nilai pasar, likuidasi, PPKA, basis LTV**; snapshot kolektibilitas & tanggal penilaian |
| **Manajemen risiko BPR** (kredit: analisis, persetujuan, dokumentasi, pemantauan) | D-02,D-11,D-12, 5C/FSS, approval | Analisis 5C/kapasitas terdokumentasi; hard-stop vs mitigasi; maker-checker & authority limits; jejak override |
| **SLIK/IDeb** (persetujuan debitur, akurasi, penggunaan, koreksi) | D-03,T2–T4 | Consent aktif + tujuan/cakupan/masa; import terverifikasi; DSR/Character dari data terstruktur; sengketa via proses resmi |
| **Transparansi/perlindungan konsumen** (suku bunga/biaya/total biaya, jadwal, ketentuan) | D-05,D-06, T8/T9 | Ungkap nominal + **efektif/IRR (tanpa 1,81x)** + rincian biaya + dana diterima + jadwal; konsisten dokumen/model |
| **PDP & kerahasiaan** | D-09,D-10, T15 (Excel dummy) | Minimasi, consent, enkripsi/mask, RBAC, audit akses, retensi; no PII nyata di repo/test/log |
| **Akuntansi/COA** (pengakuan, ledger) | D-01,D-05 | Basis akrual/saat bayar per kebijakan `[K]`; jurnal seimbang/idempoten/immutable |

**Sumber primer untuk verifikasi** `[K]`: portal OJK (POJK/SEOJK/FAQ resmi) dan naskah di peraturan.go.id; konfirmasi versi **terbaru yang berlaku** untuk BPR (bukan BPRS/bank umum jika berbeda).

## 2) Temuan & penyempurnaan formulasi per area

### 2.1 Bunga, jadwal, dan suku bunga efektif (koreksi inti)
- `[E/A]` **Flat**: bunga tetap dari pokok awal (`P*r/12`), pokok `P/n` (adjust periode akhir). Mudah dipahami, tapi **beban bunga efektif > nominal flat** karena outstanding menurun.
- `[E/A]` **Anuitas**: PMT konstan (`P*i/(1-(1+i)^-n)`), komposisi bunga/pokok berubah, saldo 0.
- `[E/A]` **Grace (X3)**: bunga berjalan selama grace, **pokok bullet di akhir tenor** (per pola X3) — risiko likuiditas pelunasan tinggi; wajib sumber/bukti pelunasan.
- `[A, koreksi wajib]` **Suku bunga efektif/IRR**: hitung numerik dari arus kas bersih aktual (plafon diterima setelah biaya yang relevan `[K definisi disclosure]` vs seluruh kewajiban). **Larangan**: faktor pendekatan `1,81x/0,87x`. Selaraskan dengan DRC/F1: contoh referensi 28,88%/60 bln `[K verifikasi kasus]`.
- `[A]` **Best practice teknis**: satu `ScheduleEngine` murni → `schedule_lines` + `cashflows`; rounding rule tunggal (D-05) dengan `rounding_adjustment` tercatat; property test (`sum principal = P±adj`, closing 0 non-balloon, deterministik). Dokumen/ledger dari snapshot yang sama (fix T8/T9/T12).

### 2.2 Kapasitas bayar (IDIR/DSR/DSCR) — definisi eksplisit
- `[A/K]` **Komponen penghasilan**: tetap vs variabel (haircut/rata-rata), TPP/benefit (stabilitas), usaha (bukti); pasangan sesuai policy. Simpan `income_basis`.
- `[A]` **IDIR** = `angsuran_baru / penghasilan_bersih_terdefinisi` (policy). Gunakan kasus terkoreksi sebagai golden setelah verifikasi `[K: 85,93%, Rp2.948.080]`.
- `[A, perbaikan T2/T3/T4]` **DSR total** = `(angsuran_baru + kewajiban_eksisting) / penghasilan_bersih`. Kewajiban eksisting **dari SLIK terstruktur** (parse/normalisasi + confidence + review), bukan input manual; validasi duplikat/baki>plafon/kolek/tanggal; perlakuan penjamin/joint `[K]`.
- `[A]` **DSCR/RPC (produktif)**: `RPC = surplus * margin`; `DSCR = RPC / debt_service`. **Grace**: debt service periodik = bunga berjalan; risiko bullet diuji terpisah lewat balloon ratio — definisi harus konsisten dan diungkap `[K]`.
- `[A]` **Hard-stop vs soft-score** (fix T1): threshold (IDIR/DSR/SLIK fatal/balloon < 1/LTV `[K]`) memblokir atau memaksa mitigasi; scoring memberi rekomendasi. Selalu tampilkan alasan/konflik.

### 2.3 Agunan, PPKA/haircut, dan LTV (anti double-haircut, D-07/T13)
- `[A]` **Pemisahan nilai wajib**: (1) nilai pasar/taksasi, (2) nilai likuidasi (policy/appraisal), (3) **nilai diperhitungkan/PPKA** sesuai POJK kualitas aset BPR/BPRS `[K→H]`, (4) **basis LTV** tunggal yang disahkan. Hindari: nilai sudah dipotong PPKA lalu dipotong lagi oleh LTV tanpa definisi.
- `[E/K]` X2/X3 memuat tabel kategori+% contoh (emas, SHM+HT/fidusia, resi gudang/tenor, tanpa HT, tanah adat, kendaraan/mesin, penjaminan tertentu, independen, tunai, dll.). **Jangan dipakai sebagai angka final** sebelum verifikasi pasal/kategori/% pada POJK berlaku. Simpan master berversi: `category, encumbrance, appraisal_age, pct_recognized, legal_basis, effective_from`.
- `[A]` **Appraisal controls**: metode/penilai/frekuensi revaluasi `[K]`, usia penilaian, bukti foto/dokumen, pengikatan (HT/fidusia/hipotek/kuasa), asuransi jaminan bila relevan.

### 2.4 5C, FSS TPP, dan keputusan beralasan
- `[A]` **5C**: Character (SLIK+verifikasi), Capacity (IDIR/DSR/DSCR+stress), Capital (ekuitas/leverage riil, fix T6), Collateral (coverage/LTV), Condition (tujuan/usaha/siklus/ketergantungan instansi-TPP). Bobot/band/rating = policy data `[K/H]` (X2/X3 contoh/DRAFT).
- `[A, D-02]` **FSS TPP redesign**: X1 FSS = template KPR/`#REF!` `[E]` → model TPP baru berversi (potong TPP/bendahara/PKS, blokir, stabilitas, SLIK, rasio). Disahkan Komite `[H]`.
- `[A]` Override manual selalu: alasan, user, waktu, otorisasi, audit event.

### 2.5 Balloon/grace (X3) & stress
- `[E/A]` `kecukupan = penerimaan_terverifikasi / kewajiban_akhir` (guard div0). Threshold contoh 1.0/1.1 `[E X3]` → parameterkan `[K]`. Wajib bukti sumber (termin/kontrak/piutang/panen), milestone monitoring, dan stress keterlambatan/shortfall.
- `[A]` Grace bukan “lebih aman” karena cicilan bunga kecil; risiko terkonsentrasi di akhir — wajib disclosure internal & narasi keputusan.

### 2.6 SLIK & consent (operasional)
- `[A/K]` Consent sebelum akses/import/pemakaian: subjek, tujuan, cakupan data, kanal, waktu, masa berlaku; audit setiap akses/ekspor; hak koreksi data melalui mekanisme resmi.
- `[A]` Pipeline: ingest → parse → normalize → validate → confidence/provenance → **human review** → commit. Dedup `file_hash`/fasilitas; simpan `slik_date` di snapshot.

### 2.7 Transparansi, dokumen, dan PDP
- `[A/K]` Dokumen/simulasi memuat: struktur produk (flat/anuitas/grace), nominal & estimasi efektif/total biaya sesuai definisi disclosure, rincian biaya, dana diterima, jadwal, denda/penalti/pelunasan dipercepat, risiko grace. Nilai dari snapshot; terbilang engine (fix T9/T12).
- `[A/K]` PDP: data input Excel = dummy `[E user]`; produksi = enkripsi+mask+RBAC+audit+retensi; larangan PII di repo/prompt/log/fixture.

## 3) Peta dampak ke keputusan/gap (`commandcode bpr/04`)
| ID | Implikasi riset `[A/K]` |
|---|---|
| D-01,D-05,D-06 | COA/basis pengakuan/pembulatan/alokasi/denda/penalti/blokir lama harus eksplisit; ledger konsisten dengan disclosure & memo cair |
| D-02 | 5C band & FSS TPP = model berversi bersumber kebijakan `[H]`; SLIK feed Character/DSR |
| D-03 | Adapter SLIK multi-format + consent + confidence/review + golden korpus dummy/anonim |
| D-07 | **Prioritas verifikasi**: POJK kualitas aset → master PPKA & basis LTV; pecah 4 nilai (T13) |
| D-08 | Tarif asuransi master berversi + snapshot per kasus |
| D-09,D-10 | PDP/retensi/hosting; default no cloud OCR/LLM untuk PII tanpa izin DPO |
| D-11,D-12 | Definisi & ambang IDIR/DSR; authority/maker-checker |

## 4) Prioritas aksi (yang paling bernilai)
1. **Verifikasi Compliance/Legal** (V1–V4 di `05-rencana-verifikasi-dan-penyempurnaan.md`): kualitas aset/PPKA, manrisk, SLIK, transparansi konsumen. Hasilnya jadi `[H]`.
2. **Bekukan definisi formula** (IDIR/DSR/DSCR/RPC/EIR/pembulatan/dana diterima/balloon) + **golden tests dulu** (dummy, bersumber sheet/sel, terkoreksi).
3. **Perbaiki area risiko Excel**: T1 konflik keputusan, T2/T3/T4 SLIK, T6/T7 neraca/MK, T8 dokumen sistem-aware, T13 LTV/PPKA.
4. **Policy-as-data**: semua ambang/band/haircut/tarif berversi dengan `legal_basis`/`source`.

## 5) Daftar sumber primer untuk dicek (OJK/resmi) `[K]`
Cari naskah **terbaru yang berlaku** (judul/nomor dapat berubah; verifikasi di situs OJK):
- POJK **Kualitas Aset BPR/BPRS** (workbook merujuk konteks 2024; verifikasi nomor/tahun/pasal final) — kolektibilitas, PPAP, agunan diperhitungkan/PPKA, penilaian agunan.
- POJK **Penerapan Manajemen Risiko bagi BPR** (dan perubahan jika ada) — proses pemberian kredit/analisis.
- POJK **SLIK / Sistem Layanan Informasi Keuangan** (dan perubahan) — persetujuan debitur, pengguna/pelapor.
- POJK/ketentuan **perlindungan konsumen & transparansi informasi** produk/jasa keuangan yang berlaku untuk BPR — suku bunga/biaya/total biaya.
- **UU 27/2022 PDP** dan ketentuan pelaksana relevan.

> Setelah Compliance/Legal mengisi nomor/pasal/tautan naskah resmi, ubah rujukan di dokumen ini menjadi `[H]` dan turunkan ke ADR/`docs/decisions.md` + seed master.
