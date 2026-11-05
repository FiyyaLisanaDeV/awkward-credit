# Riset SOP Berbasis Regulasi OJK & Lembaga Terkait

**Status:** riset terdokumentasi (2026-11-06). Pasal bertanda **`[V]`** dibaca dari **salinan resmi PDF**; **`[V*]`** = judul/halaman resmi ditemukan tetapi pasal belum dikutip; **`[K]`** = perlu verifikasi/pengesahan Compliance. Dokumen ini **bukan** nasihat hukum.

## 1. Metode
1. Menelusuri halaman resmi OJK/BPK/JDIH dan mengunduh salinan PDF.
2. Mengutip **pasal** yang relevan untuk SOP kredit BPR (bukan ringkasan artikel).
3. Memetakan kewajiban regulasi → langkah SOP → kontrol di aplikasi (`loan-analytic-bpr`).
4. Menandai celah yang belum dapat diverifikasi sebagai `[K]`.

Sumber utama yang **pasalnya sudah dibaca**: POJK 9/2024 (tata kelola) dan POJK 1/2024 (kualitas aset). Regulasi lain dipetakan pada level judul/kewajiban; pasal spesifik menyusul `[K]`.

## 2. Matriks regulasi → SOP

| Regulasi | Status | Inti kewajiban | SOP/Implementasi |
|---|---|---|---|
| **POJK 9/2024** Tata Kelola BPR/BPRS | `[V]` pasal | Prinsip tata kelola; prosedur internal; fungsi MR/kepatuhan/audit; benturan kepentingan; anti-fraud; BMPK; integritas laporan & TI | SOP governance, RBAC/SoD, audit, notifikasi, pelaporan |
| **POJK 1/2024** Kualitas Aset BPR | `[V]` pasal | Kualitas aset, PPKA/CKPN, restrukturisasi, hapus buku, **kebijakan & prosedur perkreditan** | SOP analisis, approval, PPKA, restrukturisasi, write-off |
| **POJK 23/2022** BMPK BPR/BPRS (+ SEOJK 11/SEOJK.03/2023) | `[V*]` | Batas maksimum pemberian kredit/penyaluran dana | SOP limit & exposure; kontrol BMPK |
| **POJK 12/2024** Strategi Anti Fraud LJK | `[V*]` | Pencegahan, deteksi, investigasi, pelaporan, sanksi, pemantauan | SOP anti-fraud, whistleblowing, investigasi |
| **POJK 8/2023** APU-PPT & PPPSPM di SJK | `[V*]` | CDD/EDD, pemantauan, pelaporan, retensi, SoD | SOP APU-PPT; integrasi `POJK 9/2024 Pasal 89` |
| **POJK 22/2023** Pelindungan Konsumen & Masyarakat | `[V*]` | Edukasi, transparansi produk/biaya, perlakuan adil, perlindungan aset/privasi/data, pengaduan & sengketa | SOP transparansi, pengaduan, consent |
| **POJK 18/2017 + POJK 11/2024 + SEOJK 11/SEOJK.01/2024** SLIK | `[V*]` | Pelaporan/permintaan informasi debitur, persetujuan, koreksi | SOP SLIK review-first, consent, audit |
| **POJK 34/2025** Penyelenggaraan TI BPR/BPRS | `[V*]` (Pasal 32 `[K]`) | Tata kelola/risiko TI, DRP, pusat data & DRP di lokasi risiko berbeda, audit TI | SOP TI/DRP, backup, residensi |
| **UU 27/2022** PDP | `[V*]` | Consent, hak subjek, retensi, keamanan, transfer | SOP PII, retensi, audit akses |
| **UU 10/2020 + PP 71/2019** TTE/e-meterai | `[V*]` | Tanda tangan elektronik tersertifikasi, e-meterai | SOP dokumen & TTD |
| **SEOJK 21/SEOJK.03/2024** PA BPR; **PSAK 71** | `[V*]` | COA, pengakuan pendapatan, CKPN, EIR | SOP akuntansi, jurnal, EIR |

## 3. Kewajiban SOP yang terbaca langsung dari pasal

### 3.1 Tata kelola, manajemen risiko & kepatuhan — POJK 9/2024 `[V]`
- **Pasal 2 ayat (1)–(3):** BPR wajib menerapkan tata kelola pada seluruh jenjang; prinsip keterbukaan, akuntabilitas, tanggung jawab, independensi, kewajaran; aspek meliputi **manajemen risiko & strategi anti fraud**, **BMPK**, **integritas pelaporan & TI**, rencana bisnis.
- **Pasal 3 ayat (1)–(2):** wajib memiliki **prosedur internal** tata kelola dan **mengevaluasi/mengkinikan** secara berkala.
- **Pasal 22:** fungsi **audit intern, manajemen risiko, kepatuhan** — satuan kerja (modal inti ≥ Rp50 M) atau Pejabat Eksekutif (modal inti < Rp50 M); MR & kepatuhan dapat digabung.
- **Pasal 70:** wajib **kebijakan benturan kepentingan** (identifikasi, pengungkapan, pengurangan).
- **Pasal 72:** fungsi kepatuhan; wajib menyusun/mengkinikan **pedoman kerja, sistem, dan prosedur kepatuhan**.
- **Pasal 79–83:** fungsi **audit intern efektif**, rencana audit tahunan, independensi, pelaporan temuan.
- **Pasal 87–88:** **manajemen risiko efektif**; tata kelola, MR, dan kepatuhan **terintegrasi** dengan kebijakan/prosedur.
- **Pasal 89:** program **APU-PPT/PPPSPM**.
- **Pasal 90:** **strategi anti fraud** (pencegahan, deteksi, investigasi, pelaporan, sanksi, pemantauan/evaluasi/tindak lanjut).
- **Pasal 92:** prinsip kehati-hatian dalam penyediaan dana sesuai **POJK BMPK**.
- **Pasal 93:** transparansi kondisi keuangan/non-keuangan, **informasi produk & penggunaan data konsumen**; laporan lengkap, akurat, kini, utuh.
- **Pasal 97:** penggunaan TI memperhatikan **tata kelola & manajemen risiko**.
- **Pasal 102–103:** **laporan transparansi tata kelola** dan **laporan pelaksanaan tata kelola** (self-assessment, kepatuhan, audit intern), disampaikan berkala.

**Implikasi SOP:** prosedur internal berversi; pemisahan tugas (maker-checker/SoD); fungsi audit/kepatuhan; kebijakan benturan kepentingan; pelaporan tata kelola; pengawasan TI.

### 3.2 Kebijakan & prosedur perkreditan — POJK 1/2024 `[V]`
- **Pasal 45 ayat (1):** BPR wajib **memiliki dan menerapkan kebijakan perkreditan dan prosedur perkreditan tertulis**, mengacu **Lampiran III**, dan **mengevaluasi secara berkala**.
- **Pasal 45 ayat (2):** kebijakan wajib memuat minimal: prinsip kehati-hatian; organisasi & manajemen perkreditan; **kebijakan persetujuan kredit**; dokumentasi & administrasi; pengawasan kredit; **penanganan kredit bermasalah**; evaluasi berkala.
- **Pasal 45 ayat (3)–(4):** kebijakan **disetujui Dewan Komisaris**; prosedur **disetujui Direksi**.
- **Pasal 46:** Dewan Komisaris mengawasi efektivitas kebijakan; laporan memuat penerapan kehati-hatian, PPKA, BMPK, kredit pihak terkait/grup/debitur besar, dan penanganan NPL (restrukturisasi/AYDA/hapus buku).

**Implikasi SOP:** dokumen kebijakan & prosedur wajib ada, berversi, disetujui; matriks approval & limit; dokumentasi kredit; pengawasan; playbook NPL.

### 3.3 Kualitas aset, PPKA, CKPN, agunan — POJK 1/2024 `[V]`
- **Pasal 2–3:** kelola aset prudent; **Direksi** menilai/memantau; OJK dapat menetapkan kualitas yang berlaku.
- **Pasal 4–5:** kualitas sama untuk satu debitur/proyek; ikuti **kualitas terendah**; evaluasi triwulanan.
- **Pasal 7–11:** kualitas kredit berdasarkan **prospek usaha, kinerja debitur, kemampuan membayar**; ≤ Rp5 M dapat berbasis ketepatan bayar; kolektibilitas **1–5** (lancar, DPK, kurang lancar, diragukan, macet).
- **Pasal 12:** penyimpangan pemberian kredit → **macet**.
- **Pasal 17:** agunan tunai (tabungan/deposito/logam mulia/SBN) yang diblokir → **lancar**.
- **Pasal 19:** **PPKA umum ≥0,5%** aset lancar; **PPKA khusus**: DPK **3%**, kurang lancar **10%**, diragukan **50%**, macet **100%** setelah dikurangi agunan.
- **Pasal 20:** **haircut agunan**: emas 85%; tanah/bangunan HT/fidusia 80%; resi gudang ≤12 bln 70%; tanah/bangunan tanpa HT 60%; NJOP/tanah adat/tempat usaha/kendaraan/BUMN 50%; resi gudang 13–18 bln 50%; 19–24 bln 30%; lainnya 20%; **penuaan agunan macet** (50% setelah 2–4 tahun; 0 setelah 4 tahun; kendaraan 50% setelah 1–2 tahun; 0 setelah 2 tahun).
- **Pasal 21:** **penilaian agunan berkala**; agunan tidak diperhitungkan bila tidak dinilai/tidak ditemukan/tidak dapat dieksekusi/tanpa persetujuan pemilik.
- **Pasal 26–27:** **CKPN** sesuai standar akuntansi; bila **CKPN < PPKA**, selisih menjadi **pengurang modal** (KPMM).

**Implikasi SOP:** 4 nilai agunan (pasar/likuidasi/PPKA/basis LTV); larangan double haircut; penuaan agunan; PPKA/CKPN; klasifikasi kualitas.

### 3.4 Restrukturisasi & hapus buku — POJK 1/2024 `[V]`
- **Pasal 29:** restrukturisasi hanya untuk debitur kesulitan bayar **dan** berprospek usaha baik; melalui penjadwalan/persyaratan/penataan; wajib dituangkan dalam perjanjian yang merujuk perjanjian sebelumnya.
- **Pasal 30:** **dilarang** restrukturisasi untuk menghindari penurunan kualitas/PPKA/penghentian akrual; wajib objektif, independen, bebas benturan, wajar.
- **Pasal 31:** kualitas setelah restrukturisasi **paling tinggi kurang lancar** (bila sebelumnya diragukan/macet) atau **tidak berubah**; dapat menjadi **lancar** setelah **3 periode pembayaran** berturut-turut tanpa tunggakan; jika gagal → kembali ke kualitas semula.
- **Pasal 32–34:** perlakuan akuntansi sesuai standar; OJK dapat mengoreksi bila tujuan/berulang/dokumen tidak memadai.
- **Pasal 42–43:** **hapus buku hanya untuk macet dengan cadangan 100%**; **dilarang hapus buku sebagian**; wajib upaya penagihan/pemulihan dan **dokumentasi**.

**Implikasi SOP:** playbook restrukturisasi (kriteria, larangan, kualitas), larangan write-off parsial, bukti upaya recovery.

## 4. Regulasi terkait (level judul/kewajiban, pasal menyusul `[K]`)

### 4.1 BMPK — POJK 23/2022 + SEOJK 11/SEOJK.03/2023 `[V*]`
Wajib mematuhi batas maksimum pemberian kredit/penyaluran dana; BMPK tidak dapat di-bypass. **SOP:** hitung exposure per debitur/grup/pihak terkait; blokir otomatis; audit override. Angka limit final `[K]`.

### 4.2 Anti-fraud — POJK 12/2024 `[V*]`
Strategi anti-fraud wajib mencakup pencegahan, deteksi, investigasi, pelaporan, sanksi, pemantauan/evaluasi/tindak lanjut. **SOP:** kanal whistleblowing, investigasi, laporan kejadian fraud, sanksi, evaluasi berkala.

### 4.3 APU-PPT/PPPSPM — POJK 8/2023 `[V*]`
Wajib program APU-PPT: kebijakan & prosedur, identifikasi/verifikasi nasabah (CDD/EDD), pemantauan transaksi, pelaporan (LTKM), retensi, SDM & pelatihan, pengendalian intern. **Koreksi:** kerangka saat ini **POJK 8/2023**, bukan hanya PBI/SE BI lama. **SOP:** CDD/EDD, beneficial owner, pemantauan, LTKM, retensi, SoD.

### 4.4 Perlindungan konsumen — POJK 22/2023 `[V*]`
Prinsip: edukasi memadai; **keterbukaan/transparansi informasi produk & layanan**; perlakuan adil & perilaku bisnis bertanggung jawab; **perlindungan aset, privasi, dan data konsumen**; **penanganan pengaduan & sengketa** efektif. **SOP:** lembar informasi produk/biaya, simulasi, consent, kanal pengaduan, SLA, log.

### 4.5 SLIK — POJK 18/2017 + POJK 11/2024 + SEOJK 11/2024 `[V*]`
Pelaporan & permintaan informasi debitur melalui SLIK; wajib **persetujuan debitur** dan penggunaan sesuai ketentuan; koreksi melalui proses resmi. **SOP:** consent sebelum akses, review-first, validasi (baki≤plafon, duplikat, kolektibilitas), audit akses, larangan edit sepihak.

### 4.6 TI/DRP — POJK 34/2025 `[V*]`
Tata kelola & manajemen risiko TI; pusat data dan **Pusat Pemulihan Bencana (DRP)**; ketahanan siber; audit TI. **Pasal 32** (teks terindeks `[K]`): sistem elektronik pada pusat data di lokasi dengan **karakteristik risiko berbeda** dari DRP. **SOP:** klasifikasi data, backup terenkripsi, uji restore, DRP, audit TI tahunan.

### 4.7 PDP, TTE, akuntansi
- **UU 27/2022:** consent, hak subjek, retensi, keamanan, transfer lintas batas. **SOP:** consent, minimasi, enkripsi/mask, retensi, audit akses.
- **UU 10/2020 + PP 71/2019:** TTE tersertifikasi (PSrE), e-meterai untuk dokumen > Rp5 juta. **SOP:** TTD elektronik & verifikasi.
- **SEOJK 21/SEOJK.03/2024 (PA BPR) + PSAK 71:** COA, pengakuan pendapatan, CKPN, **EIR** (arus kas termasuk biaya transaksi/komisi). **SOP:** kebijakan akuntansi, jurnal, EIR numerik (bukan 1,81x).

## 5. Peta SOP aplikasi saat ini
| Kewajiban | Di aplikasi | Status |
|---|---|---|
| Prosedur internal & kebijakan berversi | `docs/user/04-sop-poik.md`, `docs/user/*` | Dokumen ada; pengesahan `[K]` |
| Persetujuan berjenjang & SoD | `ApprovalService`, matriks approval, permission ganda | `[E]` |
| Audit intern/read-only | role `AUDITOR`, `audit_log`, `ledger` | `[E]` (auditor seeder) |
| Kebijakan/prosedur perkreditan | SOP + Bab 7/10/24 | Dokumen; pasal terverifikasi |
| PPKA/CKPN/agunan | data model & spec; engine PPKA belum ada | `[Rencana]` |
| Restrukturisasi & write-off | golden G-WO/G-CKP, spec L4 | `[Rencana]` |
| BMPK | spec RAC/BMPK; engine belum ada | `[Rencana]` |
| Anti-fraud/APU-PPT | SOP; modul belum ada | `[Rencana]` |
| SLIK consent/review-first | wizard SLIK + validasi | `[E]` sebagian |
| TI/DRP/PDP | config, backup, PII policy | `[Rencana/K]` |
| EIR/akuntansi | `SukuBungaEfektifCalculator`, PSAK 71 note | `[E]` numerik; kebijakan `[K]` |

## 6. Celah & tindakan
1. **Pengesahan SOP** oleh pemilik proses (Kepatuhan/Operasional/Komite) — `[K]`.
2. **Kutipan pasal** POJK lain (22/2023, 23/2022, 12/2024, 8/2023, 11/2024, 34/2025, SEOJK) perlu dilengkapi dari salinan resmi — `[K]`.
3. **Engine PPKA/CKPN, BMPK, anti-fraud, APU-PPT** belum diimplementasikan — `[Rencana]`.
4. **Parameter/ambang** (BMPK, PPKA, D-11/D-12) perlu keputusan `[H]`.
5. **Bukti pelatihan & UAT** masih menunggu eksekusi manusia.

## 7. Sumber
- POJK 9/2024 Tata Kelola BPR/BPRS — [Portal OJK](https://www.ojk.go.id/id/regulasi/Pages/POJK-9-2024-Penerapan-Tata-Kelola-bagi-BPR-dan-BPRS.aspx) · [PDF resmi](https://peraturan.bpk.go.id/Download/361557/POJK%209%20Tahun%202024%20Penerapan%20Tata%20Kelola%20bagi%20BPR%20dan%20BPRS.pdf)
- POJK 1/2024 Kualitas Aset BPR — [Portal OJK](https://www.ojk.go.id/id/regulasi/Pages/Kualitas-Aset-Bank-Perekonomian-Rakyat.aspx) · [PDF resmi](https://peraturan.bpk.go.id/Download/361511/POJK%201%20Tahun%202024%20Kualitas%20Aset%20Bank%20Perekonomian%20Rakyat.pdf)
- POJK 23/2022 BMPK BPR/BPRS — [Portal OJK](https://www.ojk.go.id/id/regulasi/Pages/Batas-Maksimum-Pemberian-Kredit-Bank-Perkreditan-Rakyat-dan-Batas-Maksimum-Penyaluran-Dana-Bank-Pembiayaan-Rakyat-Syariah.aspx)
- POJK 12/2024 Anti Fraud — [Portal OJK](https://www.ojk.go.id/id/regulasi/Pages/Penerapan-Strategi-Anti-Fraud-Bagi-Lembaga-Jasa-Keuangan.aspx)
- POJK 8/2023 APU-PPT/PPPSPM — [Portal OJK](https://ojk.go.id/apu-ppt/id/peraturan/pojk/Pages/POJK-8-2023-.aspx)
- POJK 22/2023 Pelindungan Konsumen — [Portal OJK](https://ojk.go.id/id/regulasi/Pages/Pelindungan-Konsumen-dan-Masyarakat-di-Sektor-Jasa-Keuangan.aspx)
- POJK 18/2017 SLIK — [Portal OJK](https://ojk.go.id/id/kanal/perbankan/regulasi/peraturan-ojk/Pages/POJK-tentang-Pelaporan-dan-Permintaan-Informasi-Debitur-melalui-Sistem-Layanan-Informasi-Keuangan.aspx) · POJK 11/2024 [BPK](https://peraturan.bpk.go.id/Details/301733/peraturan-ojk-no-11-tahun-2024) · SEOJK 11/SEOJK.01/2024 [PDF](https://ojk.go.id/id/regulasi/Documents/Pages/Pelaporan-dan-Permintaan-Informasi-Debitur-Melalui-Sistem-Layanan-Informasi-Keuangan-11-SEOJK01-2024/Abstrak%20SEOJK%2011-SEOJK.01-2024%20Pelaporan%20dan%20Permintaan%20Informasi%20Debitur%20Melalui%20Sistem%20Layanan%20Informasi%20Keuangan.pdf)
- POJK 34/2025 TI BPR/BPRS — [Portal OJK](https://www.ojk.go.id/id/regulasi/Pages/POJK-34-Tahun-2025-Penyelenggaraan-Teknologi-Informasi-oleh-Bank-Perekonomian-Rakyat-dan-Bank-Perekonomian-Rakyat-Syariah.aspx)
- UU 27/2022 PDP; UU 10/2020 + PP 71/2019 TTE; SEOJK 21/SEOJK.03/2024 PA BPR; PSAK 71.
