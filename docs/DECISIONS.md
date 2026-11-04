# DECISIONS — Loan Analytic BPR (ADR)

> **Append-only.** Entri lama tidak diedit; keputusan baru yang menggantikan ditulis dengan `Status: menggantikan ADR-xxx`. Setiap entri menjawab: konteks, keputusan, alternatif yang ditolak, konsekuensi, pemilik.
> Format tanggal: `YYYY-MM-DD` · Zona: Asia/Makassar.

## Template

```
## ADR-xxx — <judul>
- Status: diterima | menggantikan ADR-xxx | ditolak
- Tanggal: YYYY-MM-DD
- Pemilik: <nama/peran>
- Konteks: <fakta yang memaksa keputusan, sertakan bukti perintah>
- Keputusan: <satu kalimat, tegas>
- Alternatif ditolak: <dan alasannya>
- Konsekuensi: <positif / negatif / tindakan lanjutan>
```

---

## ADR-001 — Tanpa Docker/Sail
- Status: diterima · 2026-11-05 · Pemilik: IT
- Konteks: `which docker` → tidak ada; mesin sudah punya PHP 8.3.7 (Herd) dan PostgreSQL 16 native.
- Keputusan: lingkungan berjalan native (Herd + Postgres brew), tidak ada kontainer.
- Alternatif ditolak: Docker/Sail (menambah daemon, volume, port, waktu setup).
- Konsekuensi: setup < 5 menit; tanggung jawab versi PHP/DB pindah ke mesin; didokumentasikan di runbook.

## ADR-002 — PostgreSQL 16 native
- Status: diterima · 2026-11-05
- Konteks: `pg_isready` OK, role `fiyyalisanna`, proyek eksisting `scm-job-recap` memakai pola sama; presisi `numeric` penting untuk uang.
- Keputusan: PostgreSQL lokal (dev) dan PostgreSQL native di server (prod), tanpa ekstensi.
- Alternatif ditolak: SQLite (presisi uang & SQL terbatas); Docker Postgres (ADR-001).
- Konsekuensi: dua database (`loan_analytic_bpr`, `_test`); backup via `pg_dump`.

## ADR-003 — Laravel 13 (bukan 11)
- Status: diterima · 2026-11-05
- Konteks: Laravel 11 security fix berakhir 2026-03-12; PHP 8.3.7 memenuhi syarat Laravel 13 (min 8.3); proyek eksisting memakai 13.17.
- Keputusan: Laravel 13.x.
- Alternatif ditolak: Laravel 11 (EOL), Laravel 12 (lebih pendek masa dukungan).
- Konsekuensi: `bootstrap/app.php` baru, tanpa `Http/Kernel.php`, `install:api` untuk Sanctum; prompt v1.0 tidak bisa dipakai apa adanya.

## ADR-004 — Tanpa Redis
- Status: diterima · 2026-11-05
- Konteks: `redis-cli`/`redis-server` tidak ada (ekstensi PHP redis ada tetapi tanpa server).
- Keputusan: `CACHE_STORE=database`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=sync`; tidak ada worker.
- Alternatif ditolak: memasang Redis (layanan tambahan); file cache (boros I/O, tidak cocok multi-user).
- Konsekuensi: rate limit & cache memakai DB; job berat (bila nanti ada) pindah ke `database` queue tanpa ubah kode.

## ADR-005 — Tanpa partisi tabel/pg_partman
- Status: diterima · 2026-11-05
- Konteks: `pg_partman` tidak tersedia; PK komposit pada tabel terpartisi tidak didukung Eloquent; volume 3 cabang kecil.
- Keputusan: tabel biasa + index; `jadwal_angsuran` PK `bigserial`, `ledger` memakai `seq bigserial`.
- Alternatif ditolak: memasang pg_partman + partisi manual (kompleks, tanpa manfaat pada volume ini).
- Konsekuensi: perlu arsip tahunan bila data tumbuh; jalur upgrade partisi terdokumentasi (migrasi terpisah, model tidak berubah).

## ADR-006 — Tanpa MinIO; storage lokal + PII terenkripsi
- Status: diterima · 2026-11-05
- Konteks: `minio` tidak ada; dokumen & KTP bersifat sensitif.
- Keputusan: disk `local` privat; file PII dienkripsi AES-256-GCM (`PiiStorage`), NIK dimasking.
- Alternatif ditolak: S3/MinIO (server tambahan); plaintext lokal (risiko UU PDP).
- Konsekuensi: backup harus menyertakan storage + `APP_KEY`; rotasi kunci perlu prosedur.

## ADR-007 — DomPDF saja
- Status: diterima · 2026-11-05
- Konteks: `tesseract`/`pdftotext`/Chrome tidak ada; Browsershot membutuhkan Node + Chromium.
- Keputusan: `barryvdh/laravel-dompdf` untuk 17 dokumen.
- Alternatif ditolak: Browsershot (Node/Chromium, berat); layanan PDF eksternal (data keluar).
- Konsekuensi: template harus ramping; uji visual DOC-04/DOC-09; fallback cetak HTML.

## ADR-008 — Tanpa Vite/npm; Livewire + satu CSS
- Status: diterima · 2026-11-05
- Konteks: Breeze/Tailwind mensyaratkan `npm run build`; Livewire 4 menyajikan aset sendiri (diverifikasi dokumentasi).
- Keputusan: Livewire 4 (composer-only) + `public/css/app.css` tulis tangan; hapus `package.json`/`vite.config.js`.
- Alternatif ditolak: Vite+Tailwind (build step), CDN (butuh internet, kurang aman).
- Konsekuensi: disiplin CSS manual; komponen Livewire untuk wizard/paste-grid/polling.

## ADR-009 — Auth ditulis sendiri (tanpa Breeze)
- Status: diterima · 2026-11-05
- Keputusan: `LoginController`, `LogoutController`, `PasswordController`, `UserAdminController`, middleware aktif/`must_change_password`.
- Alternatif ditolak: Breeze (menarik Vite/Tailwind); Fortify (berlebih).
- Konsekuensi: ±6 file tambahan; reset sandi lewat admin (mail = log); rate limit login.

## ADR-010 — Sanctum untuk API
- Status: diterima · 2026-11-05
- Keputusan: `/api/v1` dengan token Sanctum; UI tetap Blade.
- Alternatif ditolak: tanpa API (kehilangan integrasi); Passport (OAuth berlebih).
- Konsekuensi: `personal_access_tokens`; perlu uji 401/403.

## ADR-011 — Spatie Permission dipertahankan
- Status: diterima · 2026-11-05
- Keputusan: 8 role + permission string; 5 tabel paket.
- Alternatif ditolak: kolom `role` enum (mengubah ERD & fleksibilitas).
- Konsekuensi: 6 tabel auth; seeding role/permission; cache permission memakai DB.

## ADR-012 — Ledger hash chain + trigger immutable
- Status: diterima · 2026-11-05
- Konteks: BR-09 meminta audit tak dapat diubah; role aplikasi superuser membuat `REVOKE` tidak efektif.
- Keputusan: trigger `BEFORE UPDATE/DELETE` menolak perubahan; rantai `prev_hash`/`hash`; `ledger:verify` harian.
- Alternatif ditolak: `REVOKE` (tidak efektif), arsip pasif (tidak mendeteksi perubahan).
- Konsekuensi: insert diserialisasi advisory lock (volume kecil); `TRUNCATE` tetap diizinkan untuk reset/test.

## ADR-013 — `simulasi_kredit` append-only per run
- Status: diterima · 2026-11-05
- Keputusan: setiap simulasi disimpan lengkap (input/output/parameter/engine/hash/durasi/pelaku) dan tidak diubah; analisis merujuk simulasi.
- Alternatif ditolak: hanya menyimpan hasil akhir (kehilangan reproduksibilitas).
- Konsekuensi: data tumbuh (dapat diarsipkan); `simulasi:replay` menjadi bukti determinisme.

## ADR-014 — Import staging + profil di DB
- Status: diterima · 2026-11-05
- Keputusan: semua import lewat `import_batch`/`import_baris` (pratinjau → commit), mapping kolom di `import_profil`, file hash unik.
- Alternatif ditolak: import langsung per baris (tidak bisa dibatalkan/diaudit).
- Konsekuensi: UI 2 langkah; rollback hanya pada pratinjau; koreksi setelah commit lewat batch pembalik.

## ADR-015 — PII terenkripsi + masking + `AKSES_PII`
- Status: diterima · 2026-11-05
- Keputusan: file KTP/iDeb/KK/NPWP disimpan terenkripsi; NIK dimasking; setiap unduhan dicatat di ledger.
- Alternatif ditolak: plaintext + kontrol akses saja.
- Konsekuensi: prosedur kunci & restore; larangan data nyata di repo/fixture.

## ADR-016 — `baki_debet ≤ plafon` menjadi peringatan
- Status: diterima · 2026-11-05
- Konteks: data SLIK nyata dapat menunjukkan baki > plafon (kapitalisasi bunga/tunggakan).
- Keputusan: CHECK hanya `baki >= 0`; selisih dicatat sebagai peringatan data-quality + alasan override.
- Alternatif ditolak: CHECK keras (menolak data valid).
- Konsekuensi: validasi pindah ke aturan aplikasi; laporan anomali per import.

## ADR-017 — Perluasan field input (progressive disclosure)
- Status: diterima · 2026-11-05
- Keputusan: tambah kolom sesuai INVENTARIS (14 debitur, 8 pasangan, 12 usaha, 12 aset, 12 SLIK, 10 neraca, 8 laba-rugi, 6 kapasitas, 6 scoring, 4 red flag, 6 rekomendasi, 10 kredit); wajib minimum per entitas + skor kelengkapan.
- Alternatif ditolak: semua field wajib (form melelahkan); field minimum saja (data miskin).
- Konsekuensi: form bertahap, indikator kelengkapan, import untuk mempercepat.

## ADR-018 — PHPStan dua level + tanpa gate coverage
- Status: diterima · 2026-11-05
- Konteks: xdebug/pcov/phpdbg tidak ada; level 8 untuk 55 model Eloquent menambah beban docblock besar.
- Keputusan: level 5 untuk `app/`, level 8 untuk `app/Services`+`app/Enums`; gate coverage diganti golden + enumerasi edge case + mutation test.
- Alternatif ditolak: memasang pcov (langkah setup tambahan); melepas static analysis.
- Konsekuensi: kualitas dijaga test perilaku; bila pcov dipasang, gate coverage bisa diaktifkan bertahap.

## ADR-019 — Pemecahan fase rilis menjadi 22 unit
- Status: diterima · 2026-11-05
- Konteks: draf menyebut "22 fase" tetapi daftar hanya 20; rilis butuh dokumentasi pengguna dan UAT terpisah.
- Keputusan: 22 unit: 00–15 seperti semula + 06B, 08B, 12B + 16 Hardening, 17 Dokumentasi & Pelatihan, 18 UAT/Go-Live. Fase 16 lama dipecah.
- Alternatif ditolak: memaksakan 20 unit (dokumentasi & UAT tidak punya fase).
- Konsekuensi: papan PROGRESS dan prompt memakai 22 baris; angka 22 pada draf kini terpenuhi secara nyata.

## ADR-020 — Zero hardcode: parameter DTO + audit AST
- Status: diterima · 2026-11-05
- Keputusan: semua nilai bisnis di `ref_parameter_kebijakan`/`ref_*`/`config`/`lang`; `PolicyParameters` gagal keras bila kunci hilang; `hardcode:audit` (AST) + `ParameterMutationTest` + `SeedProvenanceTest`.
- Alternatif ditolak: konvensi tanpa alat (terbukti bocor); konstanta di kelas (tetap duplikasi nilai).
- Konsekuensi: nilai baru wajib lewat parameter/seed dengan `sumber`; audit berjalan di `verify.sh --full`.

## ADR-021 — Golden value diekstrak dari workbook
- Status: diterima · 2026-11-05
- Keputusan: `php artisan golden:extract` membaca 3 workbook → `tests/fixtures/golden/*.json`; test membandingkan service vs fixture (toleransi Rp0,01).
- Alternatif ditolak: angka harapan diketik di test (rawan salah & tidak bersumber).
- Konsekuensi: workbook menjadi bagian dari fixture (versi berubah → fixture regenerasi + catat).

## ADR-022 — Sumber SLIK tiga jalur
- Status: diterima · 2026-11-05
- Keputusan: Excel/CSV, PDF teks (PHP murni), dan tempel-teks; wizard mapping + pratinjau.
- Alternatif ditolak: hanya Excel (bergantung format ekspor yang belum tentu ada); hanya PDF (rapuh).
- Konsekuensi: tiga adapter + fixture; parser terus disesuaikan bila format portal berubah.

## ADR-023 — OCR KTP opsional
- Status: diterima · 2026-11-05
- Konteks: tesseract tidak terpasang; user memilih adapter opsional.
- Keputusan: `OCR_DRIVER=auto|none|tesseract`; NIK parser selalu jalan; OCR hanya mengisi saran yang wajib diverifikasi manusia.
- Alternatif ditolak: mewajibkan tesseract (menambah langkah setup); tanpa OCR (kehilangan efisiensi).
- Konsekuensi: `ekstraksi_dokumen` menyimpan raw text/confidence/engine; data tidak menjadi data debitur sebelum diverifikasi.

## ADR-024 — Menerima koreksi angka draf
- Status: diterima · 2026-11-05
- Konteks: enumerasi menunjukkan 23 event (bukan 22), 34 enum (bukan 23), 60 tabel bisnis (bukan 50/58), 13 kategori agunan (bukan 14), 28 checklist (bukan 29), fase 06 = 13 migrasi (bukan 11), 22 unit fase (bukan 20).
- Keputusan: dokumen A–G memakai angka hasil enumerasi; angka draf hanya dikutip di tabel koreksi.
- Alternatif ditolak: mempertahankan angka draf agar "konsisten" (melanggar anti-halusinasi).
- Konsekuensi: setiap angka di dokumen harus punya cara verifikasi; jumlah tabel final tetap dihitung ulang via `\dt` di Fase 06B.

## ADR-025 — Cakupan seeder referensi (14) & data yang menunggu sumber
- Status: diterima · 2026-11-05 · Pemilik: IT + Legal
- Konteks: draf menyebut 17 seeder termasuk `ref_provinsi` dan tarif asuransi. Tabel `ref_provinsi` belum ada; tarif asuransi belum tersedia (Q3). Menyemai angka karangan melanggar aturan provenance.
- Keputusan: 14 seeder referensi (12 aktif + 2 kondisional). `RateAsuransiSeeder` dan `KonversiSukuBungaSeeder` hanya membaca `database/seeders/data/*.json`; tanpa berkas dilewati dengan peringatan. `ref_provinsi` ditunda ke Fase 12B (NIK parser).
- Alternatif ditolak: menyemai tarif 0/karangan; membuat tabel `ref_provinsi` tanpa data resmi.
- Konsekuensi: angka seeder di dokumen dikoreksi 17 → 14; Q3 tetap terbuka; fixture tarif menyusul.

## ADR-026 — SimulationService & replay di Fase 09
- Status: diterima · 2026-11-05 · Pemilik: IT
- Konteks: `SimulationService` membutuhkan 8 calculator yang baru dibangun di Fase 09. Membangunnya di 08B akan memaksa stub/kalkulasi ganda.
- Keputusan: Fase 08B hanya LedgerService + ChainHasher + `ledger:verify`. SimulationService, `simulasi:replay`, dan integrasi event `SIMULASI_DIJALANKAN` dikerjakan di Fase 09 bersama calculator.
- Alternatif ditolak: membuat calculator di 08B (melanggar urutan fase); membuat SimulationService tanpa kalkulasi (tidak dapat direplay).
- Konsekuensi: judul fase 08B di dokumen disesuaikan; tidak ada perubahan skema.

---

## Aturan penambahan ADR

1. Satu keputusan teknis/bisnis yang mengubah arsitektur, skema, atau aturan wajib punya ADR.
2. Nomor berurutan; jangan mengedit entri lama.
3. Keputusan yang menggantikan menyebut `menggantikan ADR-xxx` dan alasannya.
4. Keputusan yang butuh persetujuan manusia wajib mencantumkan nama + tanggal `[H]`.
