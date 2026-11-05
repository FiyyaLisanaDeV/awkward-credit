# 01 — Peta Regulasi & Sumber (untuk verifikasi formulasi BPR)

Status: **DRAFT riset awal** (2026-11-05). Angka/pasal yang belum diverifikasi dari naskah final ditandai `[K: perlu verifikasi pasal/naskah OJK]`. **Jangan dikunci sebagai `[H]` tanpa verifikasi Compliance/Legal BPR terhadap naskah resmi yang berlaku.**

## A. Regulasi inti yang relevan ke formulasi app
| Topik app | Regulasi/rujukan utama (indikatif) `[K]` | Relevansi ke engine/dokumen |
|---|---|---|
| Kualitas aset BPR/BPRS, kolektibilitas, PPAP, agunan yang dapat diperhitungkan (haircut/PPKA), penilaian agunan | POJK tentang Kualitas Aset BPR/BPRS (workbook menyebut **POJK 1/2024**; verifikasi nomor/tahun/pasal & status perubahan) | D-07/T13: pisah nilai pasar/likuidasi/PPKA/basis LTV; kategori agunan & % diperhitungkan; kolektibilitas SLIK/internal |
| Analisis & pemberian kredit (prinsip kehati-hatian), 5C, kebijakan kredit, dokumentasi, kewenangan | POJK Penerapan Manajemen Risiko bagi BPR (verifikasi nomor terbaru yang berlaku) + ketentuan pemberian kredit BPR | D-02/D-11/D-12: 5C/FSS TPP redesign, hard-stop vs soft-score, approval/maker-checker, snapshot keputusan |
| Transparansi suku bunga/biaya, total biaya kredit, informasi ke debitur, perlindungan konsumen | POJK/SEOJK terkait transparansi & publikasi informasi bank/BPR + POJK Perlindungan Konsumen dan Masyarakat sektor jasa keuangan (verifikasi yang berlaku untuk BPR) | D-05/D-06: bunga efektif/total biaya, rincian biaya, jadwal, dana diterima, denda/penalti, dokumen |
| SLIK (Sistem Layanan Informasi Keuangan, dulunya SID) — izin debitur, data, koreksi | POJK SLIK (verifikasi nomor terbaru) + ketentuan pelapor/pengguna | D-03/T2–T4: consent sebelum akses/import, kualitas data (baki≤plafon/duplikat/tanggal), angsuran eksisting/DSR, skor character |
| Pelindungan data pribadi & kerahasiaan | UU PDP + POJK/ketentuan terkait kerahasiaan/data (berlaku lintas sektor; verifikasi turunan relevan) | D-09/D-10: enkripsi/mask/audit akses/retensi, consent, pembatasan OCR/LLM/cloud |
| Akuntansi BPR (pengakuan pendapatan, PPAP, penyajian) | POJK/PSAK relevan untuk BPR + pedoman akuntansi BPR (verifikasi) | D-01/D-05: jurnal, basis akrual/saat bayar, COA mapping, rekonsiliasi |
| APU-PPT | POJK APU-PPT sektor jasa keuangan/bank (verifikasi) | form pengkinian, verifikasi identitas/beneficial owner bila relevan |

## B. Pemetaan cepat: regulasi/praktik → area formulasi
| Area formulasi (Excel/app) | Rujukan yang harus dicek `[K]` | Implikasi implementasi `[A]` |
|---|---|---|
| Kolektibilitas 1–5 & flag SLIK (X2/X3 S2) | POJK kualitas aset BPR/BPRS: kriteria kolektibilitas | master enum + mapping; input SLIK = bukti; keputusan pakai snapshot |
| Haircut/% diperhitungkan agunan & kategori (X2/X3 S1,S2) | POJK kualitas aset: agunan dapat diperhitungkan/PPKA & penilaian | `collateral_categories` berversi, `sumber=pasal/POJK [H]`; **pisah pasar/likuidasi/PPKA/LTV** (T13) |
| 5C/Character dari SLIK (X2/X3 S8) | manrisk kredit/prinsip kehati-hatian | band skor = policy berversi [K/H]; override beralasan |
| IDIR/DSR/kapasitas bayar (X1, X2/X3 S6) | praktik internal; transparansi beban debitur | definisi komponen income/expense/angsuran eksisting eksplisit; DSR total dari SLIK (T2); ambang = policy [K] |
| DSCR, RPC, plafon max, grace/balloon (X2/X3 S6,S13) | praktik kelayakan produktif; manrisk | rumus deterministik + stress; grace butuh sumber pelunasan (X3) & mitigasi risiko bullet |
| Suku bunga efektif/total biaya (X1 FLAT-ANN-EFF, X2/X3 S13) | transparansi ke konsumen | tampilkan efektif (IRR eksak) & rincian biaya; **tanpa faktor 1,81x**; pembulatan konsisten (D-05) |
| Jadwal Flat/Anuitas/Grace (S13) | transparansi + akuntansi | saldo akhir 0, selisih pembulatan ke periode akhir bila diputuskan; satu sumber jadwal |
| Consent SLIK & PDP (X1 surat) | POJK SLIK + UU PDP | consent aktif sebelum fetch/import/akses; audit akses; retensi |
| Approval/maker-checker (X1/X2/X3) | manrisk/tata kelola | authority limits data; AO no self-approve; jejak keputusan |

## C. Sumber yang harus diambil/dirujuk (primer)
1. Teks **POJK kualitas aset BPR/BPRS** yang berlaku (cek apakah POJK 1/2024 sesuai konteks workbook) — pasal kolektibilitas, agunan diperhitungkan/PPKA, penilaian agunan, restrukturisasi (bila dipakai).
2. Teks **POJK penerapan manajemen risiko BPR** (terbaru berlaku) — analisis kredit/5C, limit, dokumentasi.
3. Teks **POJK SLIK** (terbaru) — persetujuan debitur, hak koreksi, kewajiban pengguna.
4. Teks **POJK/ketentuan transparansi & perlindungan konsumen** yang berlaku untuk BPR — informasi suku bunga/biaya/total biaya.
5. **UU PDP** (UU 27/2022) — dasar pemrosesan, consent, keamanan, hak subjek data.

## D. Catatan integritas
- Dokumen ini **bukan nasihat hukum**. Pasal/nomor regulasi final harus diisi dari naskah resmi OJK/peraturan.go.id oleh Compliance/Legal.
- Jika ditemukan ketidaksesuaian antara Excel dan regulasi, **regulasi/kebijakan [H] menang**; Excel menjadi referensi historis/UX.
