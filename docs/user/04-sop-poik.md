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
