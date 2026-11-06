# Manual Book — Loan Analytic BPR (Aplikasi Analisis Kredit, Dokumen & Ledger)

**Versi manual:** 0.1 (DRAFT, berbasis bukti live)  
**Tanggal:** 2026-11-05 (WITA)  
**Status cakupan:** mencakup **fitur/fungsi yang terimplementasi** di repo aplikasi `APP BPR/loan-analytic-bpr` (fase 00–11, `[E]`) **plus** perencanaan/lapis risiko L1–L9 yang ditandai **`[Rencana/DRAFT, K]`**. Dokumen ini **bukan** keputusan `[H]`, bukan nasihat hukum, dan bukan pengganti verifikasi Compliance/Legal.

## Label bukti (wajib dipahami)
- `[E]` = terverifikasi dari kode/route/migrasi/config/test/evidence repo existing.
- `[A]` = usulan/desain operasional.
- `[K]` = klaim/perlu verifikasi (pasal, angka, ambang, kebijakan, sumber golden) — **bukan `[H]`**.
- `[Rencana]` = fitur/lapis belum terimplementasi (terutama L1–L9 risiko maksimal).
- Angka regulasi/kebijakan tidak dikunci sebagai final tanpa `[H]`. Money target: BIGINT rupiah + `brick/math`, tanpa float `[K kontrak]` (implementasi existing masih dominan `float` `[E]` — lihat Bab 18/20).

## Daftar Isi
1. [Ringkasan & ruang lingkup](chapters/01-ringkasan-ruang-lingkup.md)
2. [Arsitektur & stack teknis](chapters/02-arsitektur-stack.md)
3. [Instalasi, konfigurasi, environment, dan operasi dasar](chapters/03-instalasi-konfigurasi-operasi.md)
4. [Autentikasi, otorisasi, role/permission, dan keamanan akses](chapters/04-auth-rbac-keamanan.md)
5. [Debitur, pasangan, usaha, siklus, aset, dan pengkinian](chapters/05-debitur-dan-data-nasabah.md)
6. [Permohonan kredit, produk, workflow, dan SLIK](chapters/06-permohonan-produk-slik.md)
7. [Analisis kredit: neraca, laba rugi, rasio, modal kerja, kapasitas, sensitivitas, 5C, red flag, rekomendasi](chapters/07-analisis-kredit.md)
8. [Mesin perhitungan, jadwal, EIR, dan simulasi](chapters/08-perhitungan-jadwal-eir-simulasi.md)
9. [Business rules (BR-01..BR-15) dan validasi](chapters/09-business-rules-validasi.md)
10. [Approval, checklist, dokumen, tanda tangan, dan notifikasi](chapters/10-approval-dokumen-workflow.md)
11. [Kredit, biaya, asuransi, agunan, jadwal angsuran, pembayaran, dan memo pencairan](chapters/11-kredit-fasilitas-pembayaran.md)
12. [Ledger append-only, audit log, integritas hash chain, dan replay](chapters/12-ledger-audit-integritas.md)
13. [Referensi API v1 (lengkap per endpoint)](chapters/13-api-v1.md)
14. [UI Web & Livewire (wizard, simulator, dashboard, komponen)](chapters/14-ui-web-livewire.md)
15. [Perintah CLI/artisan, scheduler, dan quality tools](chapters/15-cli-artisan-tools.md)
16. [Data master, parameter kebijakan, seeder, dan zero-hardcode/provenance](chapters/16-master-policy-seeder.md)
17. [Import/ekstraksi, profil, OCR, dan data quality](chapters/17-import-ekstraksi-data-quality.md)
18. [Keamanan, PII/PDP, consent, retensi, dan kontrol operasional](chapters/18-keamanan-pii-pdp.md)
19. [Struktur data (model/migrasi/enum) dan peta domain](chapters/19-struktur-data-model-migrasi.md)
20. [Known issues, gap assessment, konflik C-002, dan rencana L1–L9](chapters/20-known-issues-gap-rencana.md)
21. [Prosedur operasional, backup/restore, dan rilis](chapters/21-operasional-backup-rilis.md)
22. [Mekanisme update berkala manual book saat fitur final](chapters/22-mekanisme-update-manual-book.md)
23. [Glosarium & indeks cepat](chapters/23-glosarium-indeks.md)
24. [Workflow, peran & wewenang](chapters/24-workflow-peran-wewenang.md)
25. [Analisis fungsi: komparasi master Excel vs web](chapters/25-analisis-fungsi-komparasi-excel-web.md)
26. [Workflow master Excel: breakdown teknis](chapters/26-workflow-master-excel-breakdown.md)
27. [Buku besar kredit, buku agunan & nomor CIF](chapters/27-buku-besar-kredit-agunan-cif.md)
28. [Catatan perubahan produksi (2026-11-07)](chapters/28-catatan-perubahan-produksi-20261107.md)
29. [Formulir wawancara golden vectors (grill-me)](chapters/29-formulir-wawancara-golden-vectors.md)
30. [Daftar pertanyaan verifikasi golden & fungsi aplikasi](chapters/30-daftar-pertanyaan-verifikasi-golden-fungsi.md)

## Aset pendukung
- [MANIFEST.md](MANIFEST.md) — kartu kendali versi manual & snapshot target
- [CHANGELOG manual](CHANGELOG.md)
- [Matriks fitur → bab/status](templates/feature-matrix.md)
- [Template entri bab (fitur final)](templates/fitur-final-entry.md)
- [Checklist PR update manual](templates/checklist-pr.md)
- [Contoh evidence note](templates/evidence-note.md)
- [`scripts/manual-snapshot.sh`](../../scripts/manual-snapshot.sh) — snapshot angka target (repo app) untuk mendeteksi drift

## Mekanisme pembaruan berkala (ringkas)
Setiap fitur/fungsi/perubahan yang **bersifat final** (`[H]` disetujui / merged ke release / di-tag) wajib: (1) update bab relevan + [CHANGELOG.md](CHANGELOG.md), (2) isi [feature-matrix](templates/feature-matrix.md), (3) lampirkan evidence (route/test/migrasi/ADR/keputusan), (4) jalankan checklist PR. Detail: [Bab 22](chapters/22-mekanisme-update-manual-book.md). **Unknown tetap `[K]`**; jangan memfinalkan angka/pasal tanpa sumber `[H]`.
