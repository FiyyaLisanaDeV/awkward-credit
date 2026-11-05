# 03 — Formulasi, Teknik, dan Best Practice (BPR) untuk Penyempurnaan App

Status: **DRAFT riset terstruktur** (2026-11-05). Rujukan: X1–X3 `[E]` + rencana `commandcode bpr/` + peta regulasi `01`. Label: `[E]` bukti Excel/repo, `[A]` best practice/usulan teknis, `[K]` wajib verifikasi (pasal POJK/SEOJK, kebijakan internal BPR, atau angka kasus), `[H]` keputusan bertanda tangan.

> **Penting:** Angka/pasal regulasi di dokumen ini bersifat **indikatif untuk verifikasi**. Sebelum dikunci ke engine/seed/keputusan, Compliance/Legal BPR harus mengonfirmasi naskah resmi yang berlaku (OJK/peraturan.go.id). Jika Excel bertentangan dengan regulasi/kebijakan `[H]`, **regulasi/kebijakan menang**.

## 1) Prinsip desain formulasi (lintas engine)
| Prinsip | Implementasi `[A]` | Terkait `[E]` |
|---|---|---|
| Deterministik & snapshot | Input + `policy_version` + `engine_version` + output disimpan immutable; hitung ulang = hasil sama | `application_snapshots` (rencana), koreksi T14 (tanpa `TODAY()`) |
| Presisi uang | BIGINT rupiah (`Money`) + `brick/math`; **tanpa float**; satu aturan pembulatan (D-05) | X1/X2/X3 nominal Rp; selisih pembulatan jadwal |
| Transparansi & auditability | Simpan komponen (pokok, bunga, biaya, margin, DSR/DSCR, basis LTV) dan alasan keputusan | DecisionEngine, ledger/audit |
| Satu sumber kebenaran | Jadwal/dokumen/ledger dari model/snapshot yang sama; no angka hardcode/terbilang add-in | [!] T8,T9,T12 |
| Policy-as-data | Ambang, band skor, haircut, tarif, denda/penalti = data berversi + `sumber` + provenance | D-02,D-05–D-08,D-11 |
| Regulator-first untuk kepatuhan | Kualitas aset/haircut/kolektibilitas/consent/transparansi mengikuti POJK yang berlaku `[K]` | D-03,D-07,D-09,D-10 |

## 2) Jadwal bunga & suku bunga efektif (Schedule/Fee/Disclosure)
### 2.1 Metode perhitungan (implementasi)
- **Flat** `[E X1,X2,X3]`: `bunga_per_bulan = P * r / 12`; `pokok_per_bulan = P / n` (penyesuaian pembulatan periode akhir per D-05 `[K]`); `angsuran = pokok + bunga` (konstan). Cocok untuk tampilan sederhana; **beban efektif lebih tinggi** daripada nominal flat pada outstanding menurun — wajib diungkap transparan `[A,K]`.
- **Anuitas** `[E X2,X3]`: `A = P*i / (1-(1+i)^-n)`, `i=r/12`; `bunga_t = saldo_{t-1}*i`; `pokok_t = A-bunga_t`; saldo akhir 0 (koreksi pembulatan akhir). Total bunga < flat pada P,r,n sama `[A]`.
- **Grace (balloon/bullet pokok)** `[E X3]`: masa grace (sesuai tenor grace) `bunga_t = saldo*i`, `pokok_t = 0` (kecuali definisi varian `[K]`); periode akhir melunasi **sisa pokok (balloon)**. X3: kewajiban akhir relevan = pokok + komponen akhir; jadwal menandai “pelunasan pokok akhir tenor”. **Risiko utama**: kemampuan pelunasan bullet, bukan beban bunga bulanan `[A,E]`.
- **Bunga efektif/IRR (EIR-like)** `[A, koreksi wajib]`: hitung rate diskonto yang menyamakan arus kas bersih diterima debitur dengan kewajiban pembayaran (XIRR/IRR numerik). **Jangan** gunakan faktor pendekatan `1,81x/0,87x` `[! koreksi]`. Tampilkan: suku bunga nominal, metode (flat/anuitas/grace), **estimasi suku bunga efektif/total biaya** sesuai ketentuan transparansi `[K]`.

### 2.2 Best practice implementasi
- Satu engine menghasilkan `schedule_lines` (periode, due, opening, principal, interest, fee_component bila relevan, installment, closing, flags). Dokumen/memo cair/ledger memakai output ini `[!T8]`.
- Parameterisasi: day-count/convention, tanggal jatuh tempo, perlakuan libur (shift/none) `[K kebijakan]`, pembulatan (per rupiah/per baris/akhir). Simpan `rounding_adjustment` eksplisit.
- Guardrail: `sum(principal)=P` (±adjustment tercatat), `closing_n=0` (non-balloon), grace butuh `balloon_due>0` & sumber pelunasan. Uji golden + property tests.

### 2.3 Transparansi ke konsumen (rujukan verifikasi `[K]`)
- Pastikan pengungkapan suku bunga/biaya/total biaya, simulasi angsuran, dan konsekuensi keterlambatan/pelunasan dipercepat sesuai ketentuan perlindungan konsumen/transparansi yang berlaku untuk BPR. Tampilkan rincian: pokok, bunga, biaya (provisi/admin/asuransi/notaris/appraisal/lain), dana diterima, penalti/denda (jika ada), dan jadwal. `[K: POJK perlindungan konsumen/transparansi BPR]`

## 3) Kapasitas bayar: IDIR/DSR (TPP/konsumsi) & DSCR/RPC (produktif)
### 3.1 Definisi yang harus dikunci di policy `[K]`
| Istilah | Definisi kerja (usulan) `[A]` | Sumber komponen `[E/A]` | Risiko Excel `[!]` |
|---|---|---|---|
| **IDIR** (angsuran baru) | `angsuran_baru / penghasilan_bersih` | penghasilan (gaji/TPP/pasangan/lain) − pengeluaran − kewajiban lain (policy) | angka contoh dikoreksi (85,93%/2.948.080) `[K]`; komponen income/expense harus eksplisit |
| **DSR/DBR total** | `(angsuran_baru + total_angsuran_eksisting) / penghasilan_bersih` | **SLIK** (fasilitas aktif/berjalan) + kewajiban internal | T2: angsuran eksisting kosong di Excel; wajib otomatis dari SLIK |
| **RPC** | `surplus_bulanan * margin_kapasitas` | LR/surplus (produktif) | T7: komponen biaya eksplisit |
| **DSCR** | `RPC / debt_service_periodik` (atau definisi bank `[K]`) | RPC vs angsuran skenario; grace: bunga periodik + mitigasi balloon | definisi “debt service” grace harus konsisten (bunga saja vs total jatuh tempo) `[K]` |

### 3.2 Best practice
- **Normalisasi penghasilan** `[A,K]`: bedakan tetap/variabel; variabel/bonus pakai haircut/rata-rata periode; pendapatan usaha pakai bukti (rekening koran/laporan). Simpan basis perhitungan.
- **Kewajiban eksisting dari SLIK** `[A, perbaikan T2/T3/T4]`: hitung angsuran eksisting per fasilitas (atau gunakan field angsuran bila tepercaya, dengan validasi); tangani joint/guarantee sesuai policy `[K]`; validasi baki≤plafon (flag), kolek 1–5, duplikat, tanggal pull; kualitas data memengaruhi confidence.
- **Hard-stop vs soft-score** `[A, perbaikan T1]`: pelanggaran ambang IDIR/DSR/SLIK fatal → hard-stop (alasan tercatat); FSS/5C/stress = soft-score/mitigasi. Tampilkan konflik, jangan sembunyikan.
- **Simulator**: hitung plafon/tenor maksimum agar IDIR/DSR/DSCR memenuhi ambang (flat/anuitas/grace; grace gunakan batas bunga periodik + uji balloon).

## 4) Grace period & balloon (risiko khusus) `[E X3]`
| Item | Formulasi/aturan `[E/A]` | Kontrol `[A]` |
|---|---|---|
| Bunga grace | `saldo * r/12` (bunga berjalan) | tampilkan beban periodik vs total kewajiban akhir |
| Balloon akhir | sisa pokok (+ komponen akhir) jatuh tempo sekaligus | **wajib** sumber & perkiraan pelunasan (termin/piutang/panen/kontrak) |
| Rasio kecukupan | `IF(kewajiban_akhir=0,0, perkiraan_penerimaan/kewajiban_akhir)`; X3: <1 tidak layak, 1–<1.1 perhatian, ≥1.1 buffer `[E]` | parameterkan ambang; verifikasi bukti sumber dana; stress (delay/shortfall) |
| Keputusan | balloon < 1 → hard-stop atau mitigasi ketat `[K policy]` | covenant/monitoring milestone pencairan sumber pelunasan |

## 5) Modal kerja & stress testing `[E X2/X3]`
- **Modal kerja** `[A,E]`: (1) *Cash conversion/turnover*: kebutuhan ≈ biaya operasional tunai tahunan / perputaran kas (hari kerja ÷ periode perputaran) — definisi komponen harus eksplisit; (2) *Working Capital Days*: (pembelian/hari × hari persediaan) atau siklus piutang/persediaan-hutang. `Gap = max(0, kebutuhan − MK_tersedia)`; `MK_tersedia = aktiva lancar − hutang lancar` (policy). Bandingkan plafon vs gap/kewajaran (mis. ≤120% kebutuhan) `[K policy]`.
- **Stress test** `[E X3 pola 6 skenario]`: omzet turun ringan/berat, biaya naik, kombinasi → hitung dampak ke surplus/RPC/DSCR/balloon. Best practice `[A]`: simpan asumsi; tambahkan skenario suku bunga/penundaan termin untuk grace bila relevan; gunakan untuk mitigasi/covenant, bukan mengganti data dasar.
- **Perbaikan Excel**: T6 (klasifikasi aset enum, ekuitas riil), T7 (komponen biaya eksplisit; golden kasus terisi).

## 6) Agunan, haircut/PPKA, dan LTV (kualitas aset) — area verifikasi regulasi tinggi `[K]`
### 6.1 Pemisahan nilai (anti double-haircut) `[A, perbaikan T13]`
| Nilai | Makna | Catatan |
|---|---|---|
| Nilai pasar/taksasi | Hasil penilaian/appraisal (metode & penilai sesuai ketentuan) `[K]` | X1: likuidasi contoh 80% pasar `[E]` — jadikan parameter/policy, bukan konstanta global |
| Nilai likuidasi | Estimasi realisasi cepat (haircut likuidasi) | kebijakan internal + bukti appraisal |
| Nilai diperhitungkan/PPKA | Nilai agunan yang **dapat diperhitungkan** untuk perhitungan PPAP/kualitas aset sesuai POJK kualitas aset BPR/BPRS `[K]` | kategori & % **harus** dari ketentuan `[H]`, berversi, bersumber pasal |
| Basis LTV | `exposure (plafon/outstanding) / nilai basis` — **basis tunggal** yang disahkan (pasar/likuidasi/diperhitungkan) `[K D-07]` | jangan: nilai sudah haircut PPKA lalu dipotong lagi implisit oleh LTV tanpa definisi |

### 6.2 Master kategori agunan `[E X2/X3, K verifikasi %/pasal]`
- X2/X3 memuat tabel kategori (emas; tanah/bangunan bersertifikat dengan/tanpa HT/fidusia; resi gudang menurut tenor penilaian; tanah adat; tempat usaha + kuasa; kendaraan/mesin fidusia/hipotek; penjaminan BUMN/BUMD memenuhi kriteria; independen ≤1 tahun; agunan tunai; lain) dengan % contoh. **Tindakan:** petakan ulang ke **naskah POJK kualitas aset BPR/BPRS yang berlaku** `[K]`; simpan `category, collateral_type, encumbrance, valuation_age, pct_recognized, legal_basis, effective_date, status`.
- Validasi: pengikatan (HT/fidusia/hipotek/kuasa jual), kecukupan asuransi (jika relevan), keabsahan dokumen, usia penilaian, marketability — checklist verifikasi (X2/X3 S14).

## 7) Scoring 5C (produktif) & redesign FSS TPP (konsumsi) `[E/K]`
### 7.1 5C (best practice)
- **Character**: rekam jejak bayar/SLIK, integritas, perilaku; gunakan data SLIK terstruktur (kolek terburuk, tunggakan, fasilitas bermasalah) + verifikasi. Override wajib alasan & otorisasi `[A, perbaikan T4]`.
- **Capacity**: IDIR/DSR/DSCR, stabilitas penghasilan/cashflow, stress. `[E]`
- **Capital**: ekuitas/aset, leverage (D/E, D/A), setoran modal; **data riil neraca** (T6).
- **Collateral**: coverage berbasis nilai yang disahkan (D-07), pengikatan, likuiditas; LTV.
- **Condition**: tujuan kredit, sektor/usaha, siklus, kondisi ekonomi/industri, ketergantungan (TPP: stabilitas instansi/PKS/bendahara) `[A,K]`.

### 7.2 Implementasi skor
- Model berversi (faktor, bobot, opsi, band 1–5, rating, ambang). **Band pemetaan** (DSCR/E-A/LTV/SLIK→skor) = **policy/data `[K/H]`**, bukan hardcode; Excel contoh = DRAFT.
- FSS TPP X1 bertanda KPR/`#REF!` `[E]` → **redesign** faktor TPP (potong TPP/bendahara/PKS, blokir tabungan, stabilitas penempatan, SLIK, rasio angsuran) dan sahkan Komite `[K D-02]`.
- Hasil skor = rekomendasi; keputusan final = pejabat berwenang + hard-stop terpenuhi `[A]`.

## 8) SLIK/IDeb: consent, kualitas data, penggunaan `[K]`
- **Consent/persetujuan debitur** aktif sebelum akses/request/import/pemanfaatan data SLIK, sesuai ketentuan SLIK yang berlaku; simpan bukti (tanggal, tujuan, cakupan, kanal) dan audit akses `[A,E X1 surat persetujuan]`. `[K: POJK SLIK]`
- **Kualitas & validasi**: tanggal data/pelaporan, kecocokan identitas (NIK/nama), baki≤plafon (flag), duplikat fasilitas, kolektibilitas range, kewajaran angsuran/tenor. Tangani sengketa/koreksi melalui proses resmi (bukan edit sepihak di sistem) `[A,K]`.
- **Penggunaan**: DSR total, deteksi fasilitas bermasalah (kol khusus/macett/DPK sesuai definisi `[K]`), input Character; simpan `slik_pull_id/date/hash` di snapshot.

## 9) PDP, kerahasiaan, dan kontrol operasional `[K]`
- Data Excel saat ini **dummy** `[E user]`; aplikasi produksi memproses PII (KTP/KK/HP/alamat/SLIK). Terapkan: dasar pemrosesan/consent, minimasi data, enkripsi at-rest/in-transit, masker, RBAC least privilege, audit akses/ekspor, retensi & penghapusan, pembatasan akses pihak ketiga/OCR/LLM/cloud `[A,K: UU PDP + kebijakan internal D-09/D-10]`.
- Fixture/test = dummy berlabel; **no PII nyata** di repo/prompt/log.

## 10) Tata kelola: approval, maker-checker, dokumen `[A,K]`
- Authority limits (plafon/tenor/produk/cabang/jabatan) sebagai data `[K D-12]`; AO tidak self-approve; pengecualian tercatat.
- Dokumen legal/akad: draf dari template, nilai dari snapshot; review Legal/Notaris `[K]`. Pencairan maker-checker → ledger idempoten & seimbang (D-01/D-05/D-06).

## 11) Checklist verifikasi regulasi (untuk Compliance/Legal) `[K]`
1. Konfirmasi **POJK kualitas aset BPR/BPRS** yang berlaku (nomor/tahun/pasal): kolektibilitas, agunan diperhitungkan/PPKA, penilaian agunan, kategori & % (petakan ke tabel X2/X3).
2. Konfirmasi **POJK manajemen risiko BPR**: kewajiban analisis kredit/5C, kebijakan & prosedur, limit/kewenangan.
3. Konfirmasi **POJK SLIK**: bentuk persetujuan debitur, tujuan penggunaan, hak koreksi, kewajiban pengguna/pelapor.
4. Konfirmasi **ketentuan transparansi/perlindungan konsumen BPR**: pengungkapan suku bunga (nominal/efektif/total biaya), biaya, simulasi, denda/penalti/pelunasan dipercepat.
5. Konfirmasi **akuntansi/COA/pengakuan pendapatan** BPR (D-01/D-05).
6. Tetapkan `[H]` untuk D-01..D-12; isi `sumber` (pasal/keputusan) pada master data.
