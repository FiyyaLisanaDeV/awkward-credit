# SOP POJK — Checklist, Approval, Retensi, Audit

> **Sifat:** SOP operasional internal. Rujukan regulasi yang berlaku wajib dikonfirmasi Compliance/Legal `[K]`; dokumen ini tidak mengutip pasal dari ingatan. Angka/ambang kebijakan mengikuti `ref_parameter_kebijakan` berversi.

## 1. Tujuan & ruang lingkup
Mengatur tata cara analisis, persetujuan, dokumentasi, penyimpanan, dan audit kredit agar konsisten, tertelusur, dan patuh pada ketentuan yang berlaku.

## 2. Peran & tanggung jawab `[E]`
| Peran | Tanggung jawab |
|---|---|
| AO/Analis | Input data, SLIK, analisis, simulasi, ajukan |
| MGR_PEMASARAN / KOORDINATOR / DIRUT / KOMITE | Menyetujui sesuai level & kewenangan |
| Admin/IT | User, parameter, backup, verifikasi teknis |
| Legal | Review/pengesahan template dokumen |
| Auditor | Audit read-only: ledger, audit log, laporan |
| Kepatuhan/DPO | Consent, retensi, insiden PII |

## 3. Alur standar
1. **Intake:** identitas debitur + dokumen + consent SLIK (bila relevan).
2. **Analisis:** neraca/LR → rasio → modal kerja → kapasitas (IDIR/DSR/DSCR/LTV/balloon) → sensitivitas → 5C → red flag → rekomendasi.
3. **Kelengkapan:** checklist wajib (BR-05); dokumen yang dihasilkan memakai template `APPROVED` (BR-07).
4. **Persetujuan:** berjenjang sesuai matriks; **tidak boleh melompati level**; pembuat tidak menyetujui sendiri (SoD).
5. **Keputusan:** setujui/tolak/kembalikan dengan alasan; semua tercatat di `ApprovalLog`, ledger, dan notifikasi.
6. **Pencairan:** setelah `APPROVED/SIGNED`, buat kredit + jadwal + memo; ledger `AKAD_DICATAT`/`PENCAIRAN_DICATAT`.
7. **Pembayaran:** catat per periode; ledger `ANGSURAN_DIBAYAR`; pembatalan via reversal `ANGSURAN_DIREVERSAL`.

## 4. Checklist dokumen `[E]`
- Master 28 baris (`ref_checklist_dokumen`), flag `wajib`/`is_active`.
- Status per permohonan: `ADA / TIDAK_ADA / NA / TIDAK_WAJIB`.
- BR-05: semua checklist wajib harus terisi sebelum submit/persetujuan.
- Red flag `CHECKLIST_KURANG` bila belum lengkap.

## 5. Persetujuan & pemisahan tugas `[E]`
- Kewenangan = role matriks **dan** permission `permohonan.approve_level_N`.
- SoD: `ao_id` tidak boleh sama dengan approver (kecuali ADMIN).
- Perubahan status hanya lewat `ApprovalService`; jangan edit DB langsung.
- Semua aksi wajib alasan untuk tolak/kembalikan.

## 6. Dokumentasi & tanda tangan `[E]`
- 17 template dokumen; hanya template `APPROVED` yang boleh dipakai.
- Dokumen dihasilkan dari data permohonan (bukan input manual angka).
- Tanda tangan: `signature_log` (pihak, nama, jabatan, hash, waktu) + ledger.
- Simpan file di storage privat; akses terbatas.

## 7. Retensi & perlindungan data `[K D-09/D-10]`
- Data PII/SLIK/dokumen/ledger: retensi per jenis data sesuai kebijakan yang disahkan.
- Backup terenkripsi + uji restore berkala; simpan `APP_KEY` terpisah.
- Akses PII: consent, least privilege, audit akses; tanpa OCR/LLM cloud kecuali disetujui DPO.
- Jangan menyalin PII ke repo/prompt/log; fixture uji = dummy/anonim.

## 8. Audit & integritas `[E]`
- `ledger` append-only + hash chain; `ledger:verify` harus OK.
- `audit_log` immutable (trigger menolak UPDATE/DELETE).
- Auditor read-only (`audit.view`, `ledger.view`, `report.view`, `permohonan.view`).
- Temuan/insiden dicatat; koreksi memakai jurnal balik/reversal, bukan hapus.

## 9. Bukti & pengesahan
- Setiap langkah menyimpan bukti: `ApprovalLog`, ledger event, notifikasi, dokumen/hash.
- SOP ini perlu **review & tanda tangan pemilik proses** (Kepatuhan/Operasional/Komite) sebelum go-live `[K]`.

## 10. Rujukan regulasi & kewajiban kunci
Riset lengkap: `commandcode bpr/docs/research/sop-ojk.md`. Pasal POJK 1/2024 & 9/2024 di bawah **dibaca dari salinan resmi PDF**; regulasi lain dipetakan pada level judul/kewajiban, pasal menyusul `[K]`.

**POJK 9/2024 — Tata Kelola BPR/BPRS `[V]`**
- Pasal 2–3: prinsip tata kelola di seluruh jenjang; wajib **prosedur internal** dan evaluasi/pengkinian.
- Pasal 22: fungsi **audit intern, manajemen risiko, kepatuhan** (satuan kerja/Pejabat Eksekutif).
- Pasal 70: kebijakan **benturan kepentingan**; Pasal 72: fungsi & prosedur kepatuhan.
- Pasal 79–83: **audit intern efektif** (rencana tahunan, independensi, pelaporan).
- Pasal 87–90: **manajemen risiko efektif**, integrasi tata kelola/MR/kepatuhan, **APU-PPT**, **strategi anti fraud**.
- Pasal 92: **BMPK**; Pasal 93 & 97: transparansi kondisi/produk/data konsumen dan **tata kelola TI**.
- Pasal 102–103: **laporan transparansi & pelaksanaan tata kelola**.

**POJK 1/2024 — Kualitas Aset BPR `[V]`**
- Pasal 2–12: kelola aset prudent; kualitas kredit berdasarkan **prospek, kinerja, kemampuan bayar**; kolektibilitas **1–5**; penyimpangan → **macet**.
- Pasal 17: agunan tunai yang diblokir → **lancar**.
- Pasal 19–21: **PPKA umum ≥0,5%**, PPKA khusus **3%/10%/50%/100%**; **haircut agunan** (emas 85%, HT/fidusia 80%, dst.) dan penuaan agunan; penilaian agunan berkala.
- Pasal 26–27: **CKPN** sesuai standar akuntansi; bila CKPN < PPKA, selisih **mengurangi modal**.
- Pasal 29–34: **restrukturisasi** (kriteria, larangan menghindari downgrade, kualitas pasca-restrukturisasi).
- Pasal 42–43: **hapus buku hanya macet + cadangan 100%**, **dilarang parsial**, wajib upaya recovery & dokumentasi.
- Pasal 45–46: wajib **kebijakan & prosedur perkreditan tertulis** (disetujui Dewan Komisaris/Direksi) dan pengawasan efektif.

**Regulasi terkait `[V*]`**
- **POJK 23/2022** (+ SEOJK 11/SEOJK.03/2023) — BMPK BPR/BPRS.
- **POJK 12/2024** — strategi anti fraud LJK (pencegahan, deteksi, investigasi, pelaporan, sanksi, pemantauan).
- **POJK 8/2023** — APU-PPT/PPPSPM (koreksi: bukan hanya PBI/SE BI lama).
- **POJK 22/2023** — perlindungan konsumen: transparansi produk/biaya, perlakuan adil, perlindungan data, pengaduan/sengketa.
- **POJK 18/2017 + POJK 11/2024 + SEOJK 11/SEOJK.01/2024** — SLIK: persetujuan, penggunaan, koreksi.
- **POJK 34/2025** — TI/DRP (pusat data & DRP; Pasal 32 perlu verifikasi salinan resmi `[K]`).
- **UU 27/2022** PDP; **UU 10/2020 + PP 71/2019** TTE/e-meterai; **SEOJK 21/SEOJK.03/2024** PA BPR dan **PSAK 71** (EIR).

**Implikasi operasional:** SOP ini wajib disahkan; parameter (BMPK, PPKA, ambang) dan engine pendukung (PPKA/CKPN, BMPK, anti-fraud, APU-PPT) masih `[Rencana/K]`.
