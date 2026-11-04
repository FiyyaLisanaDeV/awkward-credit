# RENCANA IMPLEMENTASI — Loan Analytic BPR

| Meta | Nilai |
|---|---|
| Versi dokumen | 2.4 |
| Tanggal | 2026-11-05 (Asia/Makassar) |
| Status | Disetujui untuk dijadikan acuan |
| Klien | PT BPR Bahteramas Kendari + cabang Bombana & Kolaka |
| Regulasi acuan | POJK Nomor 1 Tahun 2024 tentang Kualitas Aset BPR |
| Sumber v1.0 | `deepseek_markdown_20261104_f6a1db.md` (2.939 baris, 16 fase) — **SUPERSEDED**, tetap disimpan sebagai rujukan |
| Baseline bukti | [`evidence/00-baseline-2026-11-05.txt`](evidence/00-baseline-2026-11-05.txt) |

> Dokumen ini menjawab **apa & mengapa**. Rincian **bagaimana** ada di [TEKNIS.md](TEKNIS.md); data & kolom di [INVENTARIS_DATA.md](INVENTARIS_DATA.md); aturan bukti di [VERIFIKASI.md](VERIFIKASI.md); status kerja di [PROGRESS.md](PROGRESS.md); keputusan di [DECISIONS.md](DECISIONS.md); prompt eksekusi di [DSH_PROMPTS_SIMPLE.md](DSH_PROMPTS_SIMPLE.md).

---

## 1. Ringkasan eksekutif

Sistem analisis kredit untuk 3 produk (TPP, Modal Kerja, Grace Period) di 3 cabang, mengikuti POJK 1/2024. Dokumen v1.0 sudah lengkap secara domain tetapi mahal disiapkan: Docker, Redis, MinIO, pg_partman, Browsershot+Puppeteer, Breeze+Tailwind/Vite, dan 16 fase tanpa mekanisme bukti. Rencana v2.4 mempertahankan **seluruh cakupan domain** (60 tabel bisnis, 17 dokumen, 23 event audit, 15 business rule) dan mengganti seluruh lapisan yang menambah kerumitan.

Hasil yang dijanjikan:

1. Satu mesin Mac + PostgreSQL native + PHP Herd. **Tanpa** Docker, Redis, MinIO, Node/Puppeteer, npm, atau partisi tabel.
2. Setup dari nol ke halaman login dalam **< 5 menit** dengan satu perintah.
3. Setiap simulasi kredit meninggalkan jejak lengkap yang tidak bisa diubah (ledger + hash chain), dapat diputar ulang identik.
4. Input data maksimal tanpa form yang melelahkan; impor SLIK/iDeb, KTP, dan 6 sumber lain untuk memotong ketik ulang.
5. Kebenaran kerja dijamin mekanisme: tidak ada klaim tanpa bukti, tidak ada nilai bisnis yang di-hardcode, tidak ada sesi yang mengandalkan ingatan.

## 2. Fakta lingkungan terverifikasi

Semua baris di bawah berasal dari perintah nyata pada 2026-11-05; output lengkap di [`evidence/00-baseline-2026-11-05.txt`](evidence/00-baseline-2026-11-05.txt).

| Fakta | Nilai | Perintah |
|---|---|---|
| OS / arsitektur | macOS 14.4.1 / arm64 | `sw_vers`, `uname -m` |
| Disk bebas | 64 GB | `df -h .` |
| PHP aktif | 8.3.7 (Herd, scan ini Herd 83) | `php -v`, `php --ini` |
| Ekstensi wajib | pdo_pgsql, bcmath, mbstring, intl, gd, imagick, zip, dom, xml, fileinfo, exif | `php -m` |
| Coverage driver | xdebug/pcov/phpdbg **tidak ada** | `extension_loaded()` |
| PHP Homebrew | **rusak** (icu4c 74 hilang) → jangan dipakai | `/opt/homebrew/bin/php -v` |
| Composer | 2.10.2 | `composer -V` |
| Herd / installer | Herd 1.30.0, `laravel` 5.32.0 | `herd --version` |
| Node/npm | 21.7.3 / 10.5.0 (**tidak dipakai**) | `node -v`, `npm -v` |
| PostgreSQL | 16.15, **running**, role `fiyyalisanna` (superuser) | `pg_isready`, `select version()` |
| DB yang ada | `scm_job_recap`, `scm_job_recap_test` | `psql -lqt` |
| Ekstensi PG | pgcrypto, pg_trgm, uuid-ossp, citext, btree_gin tersedia; **pg_partman tidak ada** | `pg_available_extensions` |
| Tidak terpasang | docker, redis-cli/server, tesseract, pdftotext, qpdf, minio | `which` |
| Pola proyek eksisting | `~/Herd/scm-job-recap`: Laravel 13.17, Postgres lokal, `SESSION/CACHE/QUEUE=database`, Livewire 4.4, maatwebsite/excel 4 | `composer.json`, `.env` |
| Workspace | 3 workbook sumber, belum ada repo | `ls -la` |

## 3. Prinsip desain

1. **Satu mesin, satu DB, satu proses.** Tidak ada layanan yang harus dinyalakan sebelum `php artisan serve`.
2. **Domain tidak dikompromikan.** Formula, BR-01..BR-15, snapshot audit, versioned policy, multi-cabang, UUID, soft delete tetap.
3. **Data adalah sumber kebenaran.** Threshold, tarif, kategori, checklist, bobot, template — semua di DB; kode hanya mekanisme.
4. **Setiap angka punya asal.** Nilai referensi mencantumkan sumber (sheet+baris, pasal POJK, atau persetujuan manusia).
5. **Audit tidak bisa dihapus.** Ledger append-only + hash chain + trigger; audit_log terpisah untuk perubahan entitas.
6. **Bukti sebelum klaim.** Fase selesai hanya jika `verify.sh` hijau, bukti tersimpan, PROGRESS terisi.
7. **Satu fase satu sesi.** Titik henti jelas; tidak ada pekerjaan yang bergantung pada ingatan sesi sebelumnya.

## 4. Keputusan infrastruktur

| Area | v1.0 | v2.4 | Alasan |
|---|---|---|---|
| Kontainer | Docker/Sail | **tidak ada**, Herd native | tak perlu instalasi |
| Cache | Redis | `CACHE_STORE=database` | tabel cache bawaan |
| Session | Redis | `SESSION_DRIVER=database` | satu driver |
| Queue | Redis | `QUEUE_CONNECTION=sync` | tanpa worker; `database` terdokumentasi |
| Storage dokumen | MinIO/S3 | disk privat lokal, PII terenkripsi | tanpa server |
| PDF kompleks | Browsershot + Puppeteer | **DomPDF** | tanpa Node/Chrome |
| Partisi tabel | pg_partman | **tanpa partisi**, index | pg_partman tidak ada; PK komposit tidak cocok Eloquent; volume kecil |
| Ekstensi PG | pgcrypto/pg_trgm/uuid-ossp | **tanpa ekstensi** | UUID di PHP, pencarian ILIKE |
| Frontend | Breeze + Tailwind + Vite | Livewire 4 + satu `public/css/app.css` | tanpa build step |
| API | Sanctum | **tetap** | tanpa infra tambahan |
| RBAC | Spatie | **tetap** | composer-only |
| Paket tak terpakai | activitylog, query-builder, laravel-data, Scramble | dibuang | duplikat/ tidak dipakai |
| Static analysis | PHPStan L8 semua | L5 `app/` + L8 `app/Services`+`app/Enums` | realistis untuk Eloquent |
| Coverage | `--min=80` | golden vector + enumerasi edge case | tidak ada driver coverage |

Paket terverifikasi untuk PHP 8.3.7 + Laravel 13:

```
runtime : laravel/framework ^13.0 · barryvdh/laravel-dompdf ^3.1 · livewire/livewire ^4.4
          spatie/laravel-permission ^6 · laravel/sanctum ^4
          maatwebsite/excel ^4.0.6 · smalot/pdfparser ^2.12
opsional: thiagoalessio/tesseract_ocr ^2.13  (hanya bila tesseract dipasang)
dev     : pestphp/pest ^4.7 · pestphp/pest-plugin-laravel ^4.1
          larastan/larastan ^3.14 · laravel/pint ^1.32 · nikic/php-parser (audit hardcode)
```

## 5. Koreksi terhadap dokumen v1.0

| # | Klaim v1.0 | Hasil hitung | Bukti |
|---|---|---|---|
| 1 | Laravel 11 "non-negotiable" | **Laravel 13** (11 EOL 12-03-2026) | laravel.com/docs/13.x/releases |
| 2 | Pest 3 | **Pest 4.7.8** (Pest 5 butuh PHP 8.4) | packagist |
| 3 | 14 kategori agunan POJK | **13** baris | Sheet `2.Data Kredit & Asumsi` baris 34–46 |
| 4 | 29 item checklist | **28** (10 pemohon + 7 jaminan + 8 verifikasi + 3 kesimpulan) | Sheet `14.Checklist Dok & Verifikasi` |
| 5 | 50 tabel | **51** menurut penjumlahan grup v1.0; **60** setelah tabel baru v2.4 | enumerasi §7 |
| 6 | Fase 06 = 11 tabel | **13 migrasi** (5 kredit + 6 workflow + 2 audit) | daftar file v1.0 |
| 7 | `REVOKE UPDATE, DELETE` pada audit_log | role aplikasi superuser → tidak berefek | `pg_roles` |
| 8 | `pest --coverage --min=80` | tidak ada xdebug/pcov | `extension_loaded()` |
| 9 | Band skor 5C | **tidak didefinisikan**, workbook hanya contoh (5/1/5/5/3 → 3,6 "Layak") | Sheet `8.Penilaian 5C` |
| 10 | Draf menghitung "22 event" / "±58 tabel" | **23 event**, **60 tabel** (setelah dienumerasi) | [INVENTARIS_DATA.md](INVENTARIS_DATA.md) §7, §9 |

Angka yang belum bisa dibuktikan sampai kode ada (jumlah tabel final di DB) **wajib diverifikasi ulang** di Fase 06B dengan `\dt`, bukan dikutip dari dokumen ini.

## 6. Arsitektur target

```
Blade + Livewire ── HTTP ──> Route ──> FormRequest
                                        │
                                        v
                                  Controller (tipis)
                                        │
        ┌───────────────┬───────────────┼───────────────┬────────────────┐
        v               v               v               v                v
  Calculation      Rules/Scoring   Workflow        Document          Import
  (8 service)      (15 BR + 5C)    (approval)      (17 generator)    (8 adapter)
        │               │               │               │                │
        └───────────────┴───────┬───────┴───────────────┴────────────────┘
                                v
                    LedgerService + SimulationService
                                │
                    ┌───────────┴───────────┐
                    v                       v
             ledger (append-only)     audit_log (perubahan entitas)
                                │
                                v
                      PostgreSQL 16 native
```

Aturan layer (detail di TEKNIS §2): tidak ada logika bisnis di controller/Blade; tidak ada query di view; tidak ada `env()` di luar `config/`; service tidak menyimpan state; semua parameter lewat DTO.

## 7. Model data

60 tabel bisnis + 9 tabel sistem. Aturan: UUID v4 (`HasUuids`), `cabang_id` pada tabel bisnis, `timestamps` + soft delete sesuai kebutuhan, `numeric(15,2)` untuk uang, `jsonb` untuk payload/parameter, tanpa partisi, tanpa ekstensi.

| Grup | Jumlah | Tabel |
|---|---|---|
| A. Master/Reference | 13 | cabang, produk, ref_kategori_agunan_pojk, ref_kolektibilitas, ref_status_pelunasan_internal, ref_rate_asuransi_jiwa, ref_rate_asuransi_kebakaran, ref_rate_kendaraan, ref_konversi_suku_bunga, ref_checklist_dokumen, ref_parameter_kebijakan, ref_approval_matrix, ref_template_dokumen |
| B. Auth | 6 | users + permissions, roles, model_has_permissions, model_has_roles, role_has_permissions (Spatie) |
| C. Debitur | 6 | debitur, debitur_pasangan, debitur_usaha, debitur_siklus_usaha, debitur_aset, debitur_pengkinian |
| D. Permohonan & SLIK | 5 | permohonan_kredit, permohonan_slik_debitur, permohonan_slik_pasangan, permohonan_riwayat_internal, slik_permintaan |
| E. Analisis | 10 | analisis_neraca, analisis_laba_rugi, analisis_rasio, analisis_modal_kerja, analisis_kapasitas_bayar, analisis_sensitivitas, analisis_scoring_5c, analisis_red_flags, analisis_rekomendasi, analisis_7aspek |
| F. Kredit | 5 | kredit, jadwal_angsuran, biaya_kredit, asuransi_kredit, kredit_agunan |
| G. Dokumen/Workflow | 6 | dokumen, approval_log, signature_log, checklist_status, memo_pencairan, pemblokiran_tabungan |
| H. Audit | 2 | audit_log, notifikasi |
| I. Simulasi/Ledger/Import | 7 | ledger, simulasi_kredit, import_profil, import_batch, import_baris, ekstraksi_dokumen, pembayaran_angsuran |

Tabel sistem: `migrations`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, `personal_access_tokens`.

## 8. Ledger & simulasi kredit

- **`simulasi_kredit`**: satu baris per run — input `jsonb` lengkap, output `jsonb` lengkap, kolom ringkas yang dapat di-query, `parameter_kebijakan_id` + versi, `engine_version`, `input_hash`, `durasi_ms`, pelaku, waktu, `ledger_id`. Append-only.
- **`ledger`**: jurnal append-only seluruh peristiwa (23 event, lihat INVENTARIS §9) dengan `occurred_at`, `correlation_id`, `idempotency_key`, `payload`, `prev_hash`, `hash`. Trigger menolak UPDATE/DELETE; `ledger:verify` merekonstruksi rantai; `simulasi:replay` membuktikan hasil dapat direproduksi.
- **Siapa & kapan** terjawab oleh kolom `actor_id`/`occurred_at`, bukan oleh narasi; halaman Ledger menyediakan filter dan ekspor untuk Auditor.

## 9. Input data maksimal

Kolom diperluas: +14 debitur, +8 pasangan, +12 usaha, +12 aset, +12 per fasilitas SLIK, +10 neraca, +8 laba-rugi, +6 kapasitas, +6 scoring (justifikasi), +4 red flag, +6 rekomendasi, +10 kredit, 10 pembayaran. Fitur: NIK auto-decode, masker Rupiah + terbilang, hitung langsung (LTV/DSCR/plafon max), paste-grid dari Excel, autosave, deteksi duplikat, **skor kelengkapan %** dengan progressive disclosure. Daftar kolom per tabel ada di [INVENTARIS_DATA.md](INVENTARIS_DATA.md).

## 10. Import & efisiensi operasi

Delapan adapter dengan alur seragam (unggah → deteksi → staging → validasi → pratinjau → commit → ledger → laporan): SLIK Excel/CSV, SLIK PDF teks (PHP murni), SLIK tempel-teks, KTP/KK/NPWP (NIK parser selalu; OCR opsional), debitur+aset XLSX, dokumen massal ZIP, workbook analisis lama, pembayaran angsuran CSV. Anti-duplikat via `file_hash`; PII terenkripsi; profil mapping disimpan di DB, bukan kode.

## 11. Zero hardcode

Tidak ada nilai bisnis di kode. Threshold/tarif/bobot/band → `ref_parameter_kebijakan.params` via DTO; label → `lang/id.php`; format/path/batas → `config/*`; angka golden → fixture hasil ekstraksi workbook; CHECK constraint dibangkitkan dari enum. Penegak: `hardcode:audit` (AST), `ParameterMutationTest`, `SeedProvenanceTest`, `EnumCheckConstraintTest`, `NoSecretsTest`. Detail: TEKNIS §6.5, VERIFIKASI §7.

## 12. Reality check & anti-halusinasi

Format klaim wajib `[E]/[H]/[A]/[?]`; hierarki kebenaran (runtime → kode/test → dokumen → rencana → ingatan sesi); loop PRE/POST/GATE dengan file bukti; 18 tripwire proyek; cold-start drill 4 titik; verifier pass. Detail: [VERIFIKASI.md](VERIFIKASI.md).

## 13. Kerja multi-sesi

Bootstrap (E → B → C sepotong → prompt), handoff wajib, papan status, ADR append-only, tag `phase-XX-done`, deteksi drift via `state:check`. Detail: PROGRESS, TEKNIS §0, DSH_PROMPTS §0.

## 14. Peta fase (22 unit)

| Fase | Tujuan | Deliverable | Verifikasi kunci |
|---|---|---|---|
| 00 | Recon | `PHASE_00_RECON.md`, baseline bukti | semua tripwire dijalankan, health matrix |
| 01 | Scaffold Laravel 13 | proyek + struktur domain + scripts + config | `php artisan about`, `state:check` versi awal, commit |
| 02 | Auth + Health | login/logout/ganti sandi, role, `/health` | feature test login, `curl /health` 200 |
| 03 | Migrasi master (13) | 13 migrasi + CHECK dari enum + kolom `sumber` | `migrate --pretend`, `EnumCheckConstraintTest` |
| 04 | Migrasi auth+debitur (11) | 6 tabel debitur + users diperluas + 5 tabel Spatie | `\dt`, `SchemaInventoryTest` subset, drill #1 |
| 05 | Migrasi permohonan+analisis+SLIK (15) | 15 migrasi | `\dt`, indeks `pg_indexes` |
| 06 | Migrasi kredit+workflow+audit (13) | 13 migrasi + trigger audit | uji UPDATE audit_log gagal |
| 06B | Migrasi ledger+simulasi+import+pembayaran (7) | 7 migrasi + trigger ledger | `\dt` = 60 tabel bisnis, `ledger:verify` awal |
| 07 | Seeders (14) | data referensi + admin | semua angka bersumber; `SeedProvenanceTest` |
| 08 | Models (55) + 34 enum | model, relasi, cast, DTO param | `SchemaInventoryTest` penuh, drill #2 |
| 08B | Ledger Service | `LedgerService`, `ChainHasher`, `ledger:verify` | chain utuh, manipulasi terdeteksi |
| 09 | Calculation engine + SimulationService | 8 service + simulasi/replay + golden test | `golden:extract`, replay identik |
| 10 | Rules + Scoring | 15 BR, 5C, red flag, rekomendasi | 15 test BR, mutation test |
| 11 | API | 15 controller, 15 request, 10 resource, 3 policy | `RouteContractTest`, 401 tanpa token |
| 12 | Blade UI forms | layout, komponen, wizard, simulator, riwayat, diff | render test, drill #3 |
| 12B | Import & efisiensi | 8 adapter + staging + UI import | fixture anonim, file ganda ditolak, PII masked |
| 13 | Dashboard + workflow | KPI, inbox, timeline, notifikasi, pembayaran | feature test alur approval |
| 14 | Dokumen (17) | generator + template + terbilang | PDF valid, hash, BR-07 |
| 15 | Testing | suite lengkap, arch, performa, drill #4 | `verify.sh --full`, `hardcode:audit` nol |
| 16 | Hardening | role DB terbatas, backup+restore, performa | uji restore, baseline ms |
| 17 | Dokumentasi pengguna & SOP | manual AO/admin/komite, SOP, materi pelatihan | review pemilik proses |
| 18 | UAT, go-live, serah terima | skrip UAT, checklist, evidence pack, handover | tanda tangan UAT 3 cabang |

## 15. Setup & operasi

```bash
# sekali
composer setup          # install, .env, key, createdb, migrate --seed, storage:link
herd link loan-analytic-bpr   # atau: php artisan serve
scripts/verify.sh --quick
```
`.env` inti: `DB_CONNECTION=pgsql`, `DB_DATABASE=loan_analytic_bpr`, `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=sync`, `APP_TIMEZONE=Asia/Makassar`, `APP_LOCALE=id`, `MAIL_MAILER=log`, `LEDGER_CHAIN=true`, `OCR_DRIVER=auto`, `PII_STORAGE=encrypted`. Tanpa blok Redis. Backup harian `pg_dump` + tar storage (launchd, dipasang manual); restore diuji di Fase 16.

## 16. Testing & gate

Golden vector formula (dari workbook, toleransi Rp0,01), 15 test BR, scoring boundary, 17 dokumen, arch test, konsistensi DB, ledger chain, replay identik, SchemaInventory/RouteContract/EventCatalog, `NoSecrets`, `NoForbiddenDependency`. Tanpa gate coverage (tidak ada driver); kualitas dijaga enumerasi edge case + golden. Nol pelanggaran `hardcode:audit` untuk rilis.

## 17. Definition of Done

Fase dianggap selesai hanya jika: (1) deliverable ada, (2) `verify.sh --phase` exit 0, (3) bukti tersimpan di `docs/evidence/`, (4) baris PROGRESS + commit + tag, (5) handoff ditulis, (6) tidak ada pelanggaran hardcode/klaim tanpa tag. Aturan lengkap: VERIFIKASI §10.

## 18. Risiko & mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| DomPDF tidak sanggup layout kompleks | dokumen tidak sesuai | template ramping + page-break, uji DOC-04/DOC-09, fallback cetak HTML |
| Band skor 5C belum diputuskan | skor salah | parameter `DRAFT` + halaman persetujuan; `[H]` sebelum produksi |
| Data referensi kurang (13 kategori, 28 checklist, tarif asuransi) | seeder salah | sumber workbook + POJK, konfirmasi Legal, `SeedProvenanceTest` |
| Variasi PDF iDeb | import gagal | 3 jalur + wizard mapping + pratinjau wajib |
| OCR KTP lemah pada foto kecil | data salah | praproses imagick, confidence, verifikasi manusia; NIK parser tetap jalan |
| Import massal merusak data | data kotor | staging, commit transaksional, rollback pratinjau, batch pembalik, ledger |
| Ledger chain jadi bottleneck | lambat | advisory lock per insert, uji 1.000 event |
| PII bocor | risiko hukum | enkripsi at-rest, masking, RBAC, event `AKSES_PII`, retensi |
| Scope membengkak | terlambat | sub-fase 06B/08B/12B/17/18 dapat ditunda tanpa membatalkan inti |

## 19. Rilis & operasi

Pilot 1 cabang (Kendari) → UAT 3 cabang → go-live. Produksi di VPS/PC kantor dengan PHP 8.3 + PostgreSQL 16 native (tanpa Docker), role DB **bukan** superuser. Backup harian + uji restore bulanan. Retensi: ledger & audit permanen; dokumen PII mengikuti kebijakan BPR; arsip tahunan.

## 20. Estimasi

| Kelompok | Jam agent |
|---|---|
| Fase 00–08 (fondasi, migrasi, model) | 18–22 |
| Fase 08B–11 (ledger, kalkulasi, rules, API) | 16–19 |
| Fase 12–14 (UI, import, workflow, dokumen) | 18–23 |
| Fase 15–18 (tes, hardening, dokumen, UAT) | 10–13 |
| **Total** | **62–77 jam** |

Realistis **11–15 hari kerja** dengan review manusia; bisa ±9 hari bila Fase 17–18 dan import pembayaran ditunda.

## 21. Traceability dokumen kredit

Pemetaan indikatif (dikunci di Fase 14 dengan pemilik proses), sumber: sheet workbook `ANALISIS EDDIY (ut kredit TPP).xlsx`.

| Kode | Dokumen | Sheet sumber |
|---|---|---|
| DOC-01 | Analisis Kredit | analisis kredit, ANALISA&USULAN |
| DOC-02 | Perhitungan Kredit & Jadwal | hitungan, FLAT-ANN-EFF |
| DOC-03 | Memo Pencairan | MEMO CAIR |
| DOC-04 | Perjanjian Kredit (PK) | PK |
| DOC-05 | Surat Kuasa | Surat Kuasa, kuasa |
| DOC-06 | Surat Pernyataan | Surat Pernyataan |
| DOC-07 | Pemblokiran Tabungan | PEMBLOKIRAN TABUNGAN |
| DOC-08 | Persetujuan SLIK | Surat Persetujuan Slik |
| DOC-09 | SPK/SPAPP | SPK |
| DOC-10 | Pernyataan Asuransi | AS. JAMINAN |
| DOC-11 | Penyerahan Jaminan | REGISTRASI |
| DOC-12 | Lembar Konfirmasi | LEMBAR KONFIRMASI |
| DOC-13 | Form Pengkinian | FORM PENGKINIAN |
| DOC-14 | Checklist Dokumen & Verifikasi | (sheet 14 workbook MASTER) |
| DOC-15 | Asuransi Jiwa | ASS JIWA, ASS AL AMIN, SUPER ASS |
| DOC-16 | Asuransi Kebakaran | BUMIDA |
| DOC-17 | Appraisal | apraisal |

Sheet `FOTO`, `REK LISTRIK`, `Scoring-konsumtif` menjadi lampiran/bukti pendukung, bukan dokumen cetak.

## 22. Lampiran

**Istilah yang dilarang muncul sebagai perintah kerja** (kecuali dalam kalimat penjelasan): `docker`, `sail`, `redis-cli`, `redis-server`, `minio`, `puppeteer`, `npm run`, `pg_partman`, `xdebug`, `pcov`. Penggantinya ada di TEKNIS §15–16.

**Checklist serah terima proyek:** 60 tabel terverifikasi `\dt`; 17 dokumen ter-generate; 15 BR lulus test; ledger `verify` hijau; `hardcode:audit` nol; `verify.sh --full` hijau; UAT 3 cabang ditandatangani; backup+restore diuji; dokumentasi A–G diperbarui.
