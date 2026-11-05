# 04 — Keputusan Terbuka, Risiko, Temuan Excel, dan Gap

Label: `[E]` bukti (ekstraksi Excel/repo/rencana), `[A]` rekomendasi/usulan, `[K]` harus dikonfirmasi/diputuskan, `[H]` keputusan bertanda tangan (belum ada). Dokumen ini **bukan** keputusan; isinya input untuk `docs/decisions.md`/rapat pemutus.

## 1. Keputusan terbuka (harus ditutup sebelum fase terkait)
| ID | Keputusan | Konteks/bukti `[E]` | Dampak jika tidak diputus | Usulan `[A]` | Pemutus `[K]` | Target fase |
|---|---|---|---|---|---|---|
| D-01 | Ledger **resmi** vs **bayangan** core banking; mapping COA & sumber kebenaran saldo | X1 REGISTRASI memuat CIF/No Loan/No Fasilitas → core banking ada; rencana kerja §0/§6 | desain F2/F8, rekonsiliasi, posting | R1: ledger bayangan + rekonsiliasi harian + `external_refs`; resmi hanya jika core banking diganti | Direksi, IT, Akuntansi | sebelum F2/F8 |
| D-02 | **Model scoring TPP baru** (ganti FSS template KPR) + band 5C (DSCR/E-A/LTV/SLIK→skor 1–5) | X1 FSS berlabel KPR, `#REF!`; X2/X3 sheet 8 band DRAFT/tidak eksplisit final | engine scoring & golden TPP/5C tidak bisa dikunci | scoring sebagai data berversi; workshop Komite: faktor/bobot/ambang TPP (jaminan TPP+blokir+PKS) & band 5C | Komite Kredit, Risk | sebelum F3/F4 (TPP & 5C) |
| D-03 | **Format & akses SLIK iDeb** (PDF/TXT/Excel/JSON/API) + korpus sampel **dummy/anonim** | X1 tabel SLIK manual; X2/X3 8 baris; inkonsistensi data (T3) | parser & akurasi F5 | unggah manual dulu (multi-format) + wizard mapping; API bila tersedia; kumpulkan 10–20 sampel dummy/anonim | Kepala Kredit, IT, Kepatuhan | sebelum F0/F5 |
| D-04 | UI stack: **Filament** vs **Livewire/Blade** polos (pin versi) | repo existing pakai Livewire 4, tanpa Filament `[E]`; rencana awal menyebut Filament | arsitektur UI, kecepatan delivery | ikuti keputusan repo bila melanjutkan codebase (`Livewire/Blade`); Filament hanya jika greenfield & disetujui | Tim teknis + owner | sebelum F1/F6 |
| D-05 | **Pembulatan uang** (Rp penuh/satuan), selisih ke angsuran terakhir; **pengakuan pendapatan** (bunga/provisi/admin: akrual vs saat bayar/cair) | Excel jadwal punya sisa pembulatan; rencana §6 usul akrual/cair campuran | golden jadwal, jurnal, laporan | BIGINT rupiah; 1 kebijakan pembulatan tertulis; pengakuan sesuai kebijakan akuntansi BPR (engine configurable) | Akuntansi, Direksi | sebelum F2/F4 |
| D-06 | **Alokasi pembayaran** (denda/bunga/pokok), **denda** (%), **penalti pelunasan** (10% vs 15%), **perlakuan saldo blokir lama** | X1 SPK vs hitungan penalti inkonsisten `[!]`; T10 saldo blokir lama tak jelas | ledger posting, payoff, memo cair | satu kebijakan tertulis + parameter berversi; blokir lama eksplisit (debit/kredit) | Direksi, Kepatuhan, Akuntansi | sebelum F2/F4/F8 |
| D-07 | **Basis LTV** vs haircut POJK (nilai pasar/likuidasi/pengurang PPKA) + persentase haircut final | X2/X3 tabel POJK; risiko double haircut `[!T13]` | CollateralEngine, 5C Collateral, kepatuhan | pisahkan 4 nilai; konfirmasi tabel % & pasal ke Compliance; LTV basis tunggal yang disahkan | Kepatuhan, Komite Kredit | sebelum F3/F4 |
| D-08 | **Penanggung & tarif asuransi** default per produk/umur/masa (Al-Amin/Jasindo/Bumida/kendaraan) | X1 memuat 3 skema tarif berbeda `[!]T11` | Fee/InsuranceEngine, dokumen, memo cair | master tarif berversi per penanggung/produk; snapshot pilihan per kasus; pemilik data tarif ditunjuk | Operasional, Keuangan | sebelum F3/F4/F7 |
| D-09 | **Hosting & retensi** data (PII/SLIK/ledger/dokumen), backup, DR | PII nyata di X1 `[!]`; ledger/audit sensitif | keamanan, UU PDP, operasi | VPS lokal/hosting terkontrol; retensi per jenis data; backup terenkripsi + uji restore; akses berjejak | IT, Kepatuhan/DPO, Direksi | sebelum F1/F9 |
| D-10 | **OCR cloud / LLM** untuk KTP/SLIK diizinkan? | X1 KTP/SLIK/foto; kebutuhan efisiensi | privasi & arsitektur parser | default **tidak** (lokal: NIK parser, `pdftotext`, Tesseract opsional); cloud/LLM hanya jika DPO izinkan + redaksi + consent + audit | Kepatuhan/DPO | sebelum F5 |
| D-11 | Ambang **IDIR/DSR** (TPP) & hard-stop | X1 IDIR>80% → ditolak `[E]`; koreksi angka kasus `[K]` | TppCapacityEngine, keputusan | tetapkan ambang & basis (IDIR saja vs DSR total) sebagai policy berversi | Komite/Direksi | sebelum F4 |
| D-12 | **Matriks kewenangan** approval (jabatan, plafon, tenor, produk, cabang) | X1 memuat blok Manager/Dirut/Direktur `[E]` | ApprovalService, maker-checker | authority limits sebagai data; maker-checker; AO tak self-approve | Direksi, HR/Kepatuhan | sebelum F3/F6 |

## 2. Temuan Excel — tidak boleh disalin mentah
| ID | Tingkat `[A]` | Temuan `[E]` | Dampak | Tindakan wajib di aplikasi |
|---|---|---|---|---|
| T1 | Tinggi | X1: IDIR 83% → DITOLAK, FSS 3,81 → DISETUJUI (dua mesin konflik) | keputusan salah/tak konsisten | DecisionEngine: hard-stop vs soft-score, alasan, flag konflik |
| T2 | Tinggi | “Angsuran Bank/Koperasi” kosong padahal SLIK memuat fasilitas aktif (termasuk kol 2) | IDIR/DSR terlalu rendah | angsuran eksisting otomatis dari SLIK; DSR total |
| T3 | Tinggi | Anomali SLIK: baki identik antar baris/baki > plafon (indikasi salah salin) | risiko understated | validasi parser (baki≤plafon, duplikat, tanggal pull) + flag |
| T4 | Tinggi | Faktor pengalaman kredit skor 5 walau SLIK ada kol 2; aturan −5 jika DPK | skor character menyesatkan | skor dari SLIK terstruktur; override wajib alasan+persetujuan |
| T5 | Tinggi | FSS TPP = template KPR (rumah ke-2/KC BTN/marketabilitas); `#REF!` | model tak relevan TPP | redesign model TPP berversi (D-02) |
| T6 | Tinggi | MASTER neraca: `SUMIF *Tanah*` & `*Kebun*` ganda hitung “Tanah/Kebun”; ekuitas plug | aktiva/Capital skor salah | klasifikasi aset enum; ekuitas riil; flag tak balance |
| T7 | Tinggi | Referensi baris basi (biaya operasional/stress menunjuk baris salah, mis. Hiburan) | MK/stress keliru | komponen eksplisit; golden kasus terisi lengkap |
| T8 | Sedang | PK bisa pakai rumus flat walau sistem Anuitas/Grace | dokumen tidak akurat | satu sumber jadwal/terms; dokumen sistem-aware |
| T9 | Sedang | Angka/terbilang hardcode di dokumen; tanggal bercampur; penalti 10% vs 15%; denda 0%; pasal “modal usaha” pada konsumtif | sengketa/kepatuhan | semua nilai dari model; template tanpa hardcode; uji otomatis |
| T10 | Sedang | Dana diterima hitungan ≠ memo cair; perlakuan saldo blokir lama tak jelas | selisih kas/ledger | perlakuan blokir lama eksplisit + jurnal seimbang |
| T11 | Sedang | 3 tarif asuransi jiwa berbeda dalam 1 berkas | premi tak konsisten | master tarif per penanggung/produk (D-08), snapshot |
| T12 | Sedang | 6 link eksternal rusak, TERBILANG add-in `#NAME?`, blok tarif `#VALUE!/#N/A` | template rapuh | engine terbilang & tarif mandiri; no add-in |
| T13 | Sedang | Haircut POJK dipakai sebagai nilai agunan lalu dibatasi LTV lagi (double haircut) | plafon/LTV bias | pisah nilai pasar/likuidasi/PPKA/basis LTV (D-07) |
| T14 | Rendah | `DATEDIF/TODAY` ubah umur; Condition input manual tanpa dasar; foto 14 MB di workbook | hasil tak reproducible/berat | snapshot usia pada `analysis_date`; sub-kriteria Condition; file di storage |
| T15 | Peringatan (konteks produksi) | X1 berisi field identitas (NIK/rekening/HP/alamat) — **data input Excel adalah dummy** (klarifikasi user 2026-11-05) `[E user]` | saat produksi/fixture: tetap jaga kepatuhan PDP & kualitas data | perlakukan fixture/golden sebagai **dummy berlabel sumber**; aplikasi produksi wajib consent, enkripsi+mask, retensi, audit akses, dan **no PII nyata** di repo/prompt/log (D-09/D-10) |

## 3. Koreksi wajib angka/rumus (dipegang rencana ini)
| Item | Nilai/aturan Excel (indikasi) `[E]` | Koreksi wajib `[K: verifikasi sumber]` | Status |
|---|---|---|---|
| IDIR kasus contoh TPP | ±83% (sheet memutus DITOLAK) | **85,93%** | butuh verifikasi kasus sumber & golden |
| Penghasilan bersih kasus contoh | ±Rp3.050.000-an | **Rp2.948.080** | butuh verifikasi kasus sumber & golden |
| Konversi bunga | pendekatan flat→efektif 1,81x / anuitas 0,87x (X1 FLAT-ANN-EFF) | **TIDAK dipakai**; hitung **RATE/IRR eksak** (contoh 28,88%/60 bln) | konfirmasi case; engine numerik |
| DRC #4 | haircut/penuaan disebut tak terdeskripsikan di 1 file | X2/X3 **memuat tabel POJK + % diperhitungkan** `[E]`; tetap masukkan ke spec v2 & verifikasi %/pasal | [K D-07] |

## 4. Risiko & mitigasi
| Risiko | Dampak | Mitigasi | Pemilik `[K]` |
|---|---|---|---|
| Format SLIK/KTP beragam, OCR buruk | data salah → keputusan salah | korpus sampel anonim, parser deterministik, confidence+review wajib, NIK decode cross-check, fallback manual | Dev + Kredit |
| Parity engine vs Excel (dan bug Excel) | trust turun/angka salah | golden dari kasus terverifikasi, perbaiki T1–T14, paralel run, laporan selisih | Dev + QA + User |
| Ledger salah/diubah | selisih uang | invarian balance, trigger immutable, hash chain, `verify`, pembalik saja, maker-checker, rekon core banking | Dev + Akuntansi |
| PII/Kepatuhan PDP (konteks produksi) | sanksi/reputasi | data Excel saat ini **dummy** `[E user]`; aplikasi produksi tetap wajib consent, enkripsi+mask, retensi (D-09), audit akses, no PII nyata di repo/log/model (D-10), fixture dummy berlabel | Kepatuhan/DPO + Dev |
| Aturan bisnis disalahpahami (agent/model) | keputusan kredit keliru | spec pack, golden dulu, PR kecil, review silang, pertanyaan di questions | Tim + reviewer |
| Band/ambang belum diputus (5C, IDIR/DSR, LTV, penalti) | output “DRAFT”/inkonsisten | policy berversi bertanda DRAFT→ACTIVE; hard-stop hanya untuk yang [H]; blokir produksi sampai diputus | Komite/Direksi |
| Versi framework/paket drift | bug/teknik utang | kunci versi, `composer qa`, smoke test, ikuti ADR repo | Dev |
| Template hukum tidak sah | sengketa | draf ditinjau Legal/Notaris; versi & approval template | Legal |
| Queue DB lambat (batch TPP) | keterlambatan | chunking/index, `--max-time`, ukur F8; scale-up worker bila perlu (tanpa Redis awal) | Dev/Ops |

## 5. Gap yang memblokir (status saat rencana disusun)
| Gap | Bukti `[E]` | Memblokir | Tindakan |
|---|---|---|---|
| Golden case terverifikasi < target (dan nilai contoh konflik koreksi) | `tests/Fixtures/golden/tpp.json` hanya 1 kasus; kontrak minta ≥25 di `tests/golden/` (atau setara) | F4 parity, scoring/IDIR | bentuk ≥25 kasus **dummy terverifikasi** (termasuk koreksi IDIR/penghasilan/rate, sumber sheet/sel jelas) `[K]` |
| Keputusan D-01..D-12 belum bertanda tangan | repo pakai ADR-001..026; tidak ada D-01..D-12 format keputusan F1 | F2–F8 (scope tertentu), produksi | rapat putusan + catat di decisions/ADR bertanda tangan |
| Spec pack `docs/spec/` & traceability sumber | `docs/spec/` belum ada (scan F1) | traceability & konsistensi implementasi | generate spec pack dari workbook (label dummy/sumber) `[K]` |
| Band skor 5C & model FSS TPP final | X2/X3 DRAFT/contoh; X1 template KPR | scoring produksi | D-02 + data berversi |
| Tarif asuransi/haircut % final + COA mapping | multi-tarif X1; % butuh verifikasi | F3/F4/F7/F8 | D-07,D-08,D-01 |
| Sampel SLIK/KTP/KK/daftar TPP anonim | belum di repo | F5 akurasi | kumpulkan korpus di luar git publik (D-03,D-10) |
| QA gap repo existing | Larastan 1 error; `tests/Feature` 11 failed (scan) | baseline rilis | triase & perbaiki terpisah (non-domain) sebelum gate fase terkait |

## 6. Pertanyaan klarifikasi (untuk `docs/questions.md`/rapat)
1. Konfirmasi sumber **kasus golden TPP** (IDIR 85,93%, penghasilan bersih 2.948.080, rate eksak 28,88%/60 bln): workbook/sheet/baris mana yang menjadi acuan `[K]`?
2. D-01: ledger bayangan atau resmi? COA inti BPR (kode/nama) yang wajib dipakai `[K]`?
3. D-02: susunan faktor/bobot/ambang **FSS TPP baru** (pengganti KPR) dan **band 5C** final `[K]`?
4. D-03: format SLIK iDeb yang paling sering diterima (PDF hasil unduh/Excel/CSV) dan bolehkan contoh anonim 10–20 file `[K]`?
5. D-05/D-06: pembulatan, pengakuan pendapatan, alokasi bayar, denda, penalti (10/15%), saldo blokir lama `[K]`?
6. D-07: tabel haircut POJK (% & pasal) versi mana yang disahkan; basis LTV yang dipakai `[K]`?
7. D-08: penanggung asuransi default TPP/produktif dan file tarif resmi `[K]`?
8. D-09/D-10: retensi PII/SLIK, hosting, dan boleh/tidak OCR/LLM cloud `[K]`?
9. D-11/D-12: ambang IDIR/DSR dan matriks kewenangan approval (plafon/jabatan/cabang) `[K]`?
10. Apakah implementasi dilanjutkan pada repo `APP BPR/loan-analytic-bpr` (fase 0–11 `[E]`) dengan **gap assessment**, atau jalur baru di folder terpisah `[K]`?