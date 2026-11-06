# TEKNIS — Loan Analytic BPR

| Meta | Nilai |
|---|---|
| Versi dokumen | 2.4 |
| Tanggal | 2026-11-05 |
| Status | Acuan implementasi (satu-satunya untuk keputusan teknis) |
| Pasangan | [RUANG-LINGKUP-PRODUK.md](RUANG-LINGKUP-PRODUK.md) · [RENCANA-PRODUKSI-FINAL.md](RENCANA-PRODUKSI-FINAL.md) · [INVENTARIS_DATA.md](INVENTARIS_DATA.md) · [VERIFIKASI.md](VERIFIKASI.md) |

---

## §0. Cara memakai dokumen ini (protokol multi-sesi)

**Urutan baca minimum per sesi (hemat konteks):**
1. [PROGRESS.md](PROGRESS.md) — status, blocker, next action.
2. Bab ini §3 (stack), §6 (konvensi), §6.5 (zero hardcode), lalu **satu** bab domain yang digarap.
3. [INVENTARIS_DATA.md](INVENTARIS_DATA.md) — hanya tabel yang disentuh.
4. Gate & urutan eksekusi di [RENCANA-PRODUKSI-FINAL.md](RENCANA-PRODUKSI-FINAL.md).
5. [VERIFIKASI.md](VERIFIKASI.md) bila perlu memutuskan "sudah selesai atau belum".

**Baseline wajib sebelum menyentuh kode:**

```bash
git status -sb && git log --oneline -5
php artisan migrate:status | tail -3
php artisan route:list --json | php -r 'echo count(json_decode(stream_get_contents(STDIN), true)), PHP_EOL;'
php artisan test --compact 2>&1 | tail -5
php artisan state:check
```

Cocokkan hasil dengan header PROGRESS. Berbeda → tulis sebagai `DRIFT` di PROGRESS dan selesaikan **sebelum** menambah fitur.

**Handoff wajib** (template di PROGRESS §4). Tanpa handoff, fase tidak boleh `DONE` — lihat VERIFIKASI §10.

**Aturan pembaruan dokumen:** PROGRESS tiap fase; TEKNIS saat arsitektur/konvensi berubah (naikkan versi + catat di §21); INVENTARIS saat kolom/tabel berubah; DECISIONS append-only; RENCANA hanya saat scope berubah.

---

## §1. Identitas & ruang lingkup

- Produk kredit: **TPP** (konsumtif), **Modal Kerja**, **Grace Period** (balloon).
- Entitas: PT BPR Bahteramas — cabang Kendari (KDI), Bombana (BOM), Kolaka (KOL).
- Aktor: AO, Mgr Pemasaran, Koordinator, Dirut, Komite, Admin, Legal, Auditor.
- Batas sistem: analisis & administrasi kredit internal. **Di luar cakupan:** integrasi core banking, API SLIK OJK, e-signature tersertifikasi, akuntansi umum.
- Semua data SLIK/iDeb diinput/diimpor manual oleh AO/Admin; tanda tangan dokumen dilakukan basah lalu scan diunggah (sistem menyimpan hash & metadata).

## §2. Arsitektur & aturan layer

`Route → FormRequest → Controller (tipis) → Service → Calculator/Rule/Repository → Model`

**Aturan keras:**
1. Controller hanya: otorisasi, panggil service, kembalikan response/redirect. Maks ±40 baris per method.
2. Tidak ada query Eloquent di Blade/Livewire view; tidak ada logika bisnis di view.
3. Service stateless; dependensi lewat constructor injection.
4. Semua akses parameter kebijakan lewat `PolicyParameters` (DTO), tidak lewat array mentah.
5. Semua penulisan peristiwa penting lewat `LedgerService` **dalam transaksi yang sama** dengan perubahan data.
6. Tidak ada `env()` di luar `config/` (dijaga arch test).
7. Tidak ada `dd()`, `dump()`, `var_dump()`, `print_r()` di kode produksi.
8. Semua uang `numeric(15,2)` di DB dan `Decimal`/`float` terbatas di PHP; pembulatan di §7.
9. Exception domain: `BusinessRuleViolation`, `MissingPolicyParameter`, `ImportValidationException`, `DocumentGenerationException`, `KreditStateException`.
10. Pesan pengguna berbahasa Indonesia via `lang/id.php`, bukan literal di controller.

## §3. Stack & versi terverifikasi

| Komponen | Versi | Catatan |
|---|---|---|
| PHP | 8.3.7 (Herd) | minimum Laravel 13; Homebrew PHP rusak, jangan dipakai |
| Laravel | 13.x | 11 EOL; tanpa `Http/Kernel.php`; API via `php artisan install:api` |
| PostgreSQL | 16.15 native | role `fiyyalisanna`; produksi pakai role terbatas |
| Livewire | 4.4.x | aset disajikan Laravel, **tanpa npm** |
| DomPDF | 3.1.x | tanpa Puppeteer/Chrome |
| maatwebsite/excel | 4.0.6 | Excel/CSV import |
| smalot/pdfparser | 2.12 | ekstraksi teks PDF murni PHP |
| Pest | 4.7.8 | Pest 5 butuh PHP 8.4 |
| Pint | 1.32.x | `--test` di gate |
| Larastan | 3.14.x | level 5 global + level 8 Services/Enums |
| Spatie Permission | ^6 | 8 role, tanpa Redis |
| Sanctum | ^4 | token API |

Tidak dipakai: Docker/Sail, Redis, MinIO, pg_partman, Browsershot/Puppeteer, npm/Vite/Tailwind build, Scramble, activitylog, query-builder, laravel-data.

## §4. Struktur repositori & peta modul

```
app/
├── Domain/{Debitur,Permohonan,Analisis,Kredit,Workflow,Dokumen,Simulasi}/
│   └── (DTO, enum domain, aturan murni tanpa I/O)
├── Services/
│   ├── Calculation/   Angsuran, Dscr, PlafonMax, Ltv, RasioKeuangan, ModalKerja, Idir, SukuBungaEfektif
│   ├── Rules/         BusinessRule + 15 implementasi + BusinessRuleEngine
│   ├── Scoring/       Scoring5C, RedFlagEvaluator, Rekomendasi
│   ├── Workflow/      ApprovalService, ChecklistService, StateMachine
│   ├── Document/      DocumentService + 17 generator
│   ├── Import/        8 adapter + ImportPipeline + profil
│   ├── Ledger/        LedgerService, SimulationService, ChainHasher
│   └── Policy/        PolicyParameters + resolver
├── Http/{Requests,Resources,Controllers/Api/V1,Controllers/Web,Middleware}/
├── Livewire/{Debitur,Permohonan,Analisis,Simulasi,Import,Notifikasi}/
├── Models/            ±55 model
├── Enums/             34 enum
├── Support/           Money, Percentage, Terbilang, Konstanta, NIK, PiiStorage
├── Policies/          Debitur, Permohonan, Kredit, Dokumen, Ledger
└── Providers/         AppServiceProvider, LedgerServiceProvider
config/  loan.php · import.php · ledger.php · permission.php
database/{migrations,seeders,factories}
docs/    A–G + evidence/
resources/views/  layouts, auth, dashboard, debitur, permohonan, analisis, simulasi, workflow, dokumen, admin, components
routes/   web.php, api.php, console.php
scripts/  setup.sh, verify.sh, reset-db.sh, backup.sh, restore.sh
tests/    Unit, Feature, Arch, Fixtures(golden, import, pdf)
```

**File acuan cepat:** formula → `app/Services/Calculation`; aturan → `app/Services/Rules`; event → `app/Enums/LedgerEvent.php`; kontrak API → §13; environment → §15; error umum → §18.

## §5. Model data

60 tabel bisnis (lihat RENCANA §7) + 9 tabel sistem. Rincian kolom per tabel di [INVENTARIS_DATA.md](INVENTARIS_DATA.md). Aturan skema:

- PK `uuid` (`HasUuids`) untuk entitas bisnis; `users` memakai bigint bawaan Laravel; `jadwal_angsuran` pakai `bigserial`.
- Semua tabel bisnis punya `cabang_id` (kecuali `ref_*` global) dan `created_at/updated_at`.
- `softDeletes` untuk entitas yang bisa dibatalkan (debitur, permohonan, kredit, dokumen); **tidak** untuk ledger/audit/pembayaran.
- Uang `numeric(15,2)`; rasio/persen `numeric(10,4)`; tanggal `date`; waktu peristiwa `timestamptz`.
- CHECK constraint dibangkitkan dari enum (`EnumCheckConstraint`), bukan daftar string terpisah.
- Unique parsial untuk soft delete: `UNIQUE(...) WHERE deleted_at IS NULL`.
- Tanpa partisi. Index utama: `(cabang_id, status, tanggal_permohonan DESC)`, `(permohonan_id)`, `(kredit_id, tanggal_jatuh_tempo)`, `(entity_type, entity_id, occurred_at DESC)`.
- `ref_*` memiliki `sumber` (wajib), `approved_by/at` bila bernilai kebijakan.

## §6. Konvensi kode

- `declare(strict_types=1);` di semua file PHP aplikasi.
- PSR-12 + Pint preset `laravel` dengan `declare_strict_types`, `strict_comparison`, `no_unused_imports`.
- Nama: Model PascalCase; tabel snake_case plural-free (mengikuti domain Indonesia: `debitur`, `permohonan_kredit`); kolom snake_case; method camelCase; event enum UPPER_SNAKE.
- DTO `final readonly class`; tidak ada array asosiatif liar antar-layer kecuali payload `jsonb` yang skemanya didokumentasikan.
- Relasi selalu diberi return type; collection di service memakai `Collection`.
- Cast wajib untuk tanggal (`date`), uang (`decimal:2`), json (`array`), enum.
- Log: `Log::channel('ledger')` untuk peristiwa, `Log::channel('import')` untuk batch; **tidak boleh** memuat NIK/email/nama lengkap (masking).
- Test: nama file `*Test.php`, satu perilaku satu test, fixture di `tests/Fixtures`.

### §6.5 Zero hardcode (aturan nilai)

| Jenis nilai | Sumber benar | Dilarang |
|---|---|---|
| Threshold DSCR, margin, LTV, rasio balloon, IDIR, bobot 5C, band skor, plafon multiplier, biaya default, sensitivitas, hari kerja, threshold SLIK | `ref_parameter_kebijakan.params` via `PolicyParameters` | literal di `app/**` |
| Kategori agunan + persen, kolektibilitas, status pelunasan, tarif asuransi, konversi bunga, checklist, approval matrix, template dokumen | tabel `ref_*` (+ kolom `sumber`) | array di PHP |
| Produk, cabang, role/permission | tabel `produk`/`cabang`/Spatie | konstanta nama |
| Nama event ledger | `enum LedgerEvent` | string tersebar |
| Format nomor, path storage, batas unggah/import, retensi | `config/loan.php`, `config/import.php`, `config/ledger.php` | literal di service/view |
| Label & pesan | `lang/id.php` | teks langsung di Blade |
| Golden value formula | `tests/fixtures/golden/*.json` hasil `golden:extract` | angka ketik manual |
| Password/kunci | `.env` / one-time | default di repo |
| CHECK constraint | dibangkitkan dari enum | daftar string ganda |

Aturan tambahan:
- `PolicyParameters` gagal keras (`MissingPolicyParameter`) bila kunci tidak ada — **tidak ada** `?? 0.5`.
- Himpunan tertutup boleh enum; himpunan yang bisa berubah saat berjalan **wajib** tabel/parameter.
- `12` (bulan/tahun) boleh `Konstanta::BULAN_PER_TAHUN`; `360`, `30`, `0.5`, `1.0`, `1.25` adalah bisnis → parameter.
- Penegak: `php artisan hardcode:audit` (AST `nikic/php-parser`), `ParameterMutationTest`, `SeedProvenanceTest`, `EnumCheckConstraintTest`, `NoSecretsTest`. Detail alarm & whitelist: [VERIFIKASI.md](VERIFIKASI.md) §7.

## §7. Aturan uang, tanggal, ID

- **Uang:** simpan `numeric(15,2)`; hitung dengan skala 4, bulatkan HALF_UP 2 desimal saat menyimpan; tampilan Rupiah tanpa desimal. Total jadwal disesuaikan pada periode terakhir (true-up) sehingga `Σ pokok = plafon` dan `saldo_akhir = 0`.
- **Toleransi uji:** Rp0,01 dibanding workbook; setiap penyimpangan > toleransi = gagal.
- **Persen/rasio:** simpan desimal (0,18 = 18%), bukan "18".
- **Tanggal:** `date` untuk jatuh tempo/tanggal dokumen; `timestamptz` untuk peristiwa; zona `Asia/Makassar`; jangan menyimpan string tanggal.
- **Nomor:** `no_permohonan` = `{kode_cabang}/{tahun}/{urut}` (pola di config), unik per cabang; `no_rekening_kredit`, `no_akad`, `no_dokumen` mengikuti pola config; nomor dihasilkan dalam transaksi dengan lock urutan.
- **UUID:** v4 dari `HasUuids`; jangan memakai UUID untuk nomor yang dibaca manusia.
- **ID unik bisnis:** NIK 16 digit (validasi struktur), NPWP 15/16, no KK 16.

## §8. Formula permanen & golden value

Sumber kebenaran: MASTER CONTRACT v1.0 + verifikasi langsung ke workbook (Sheet 6 kolom C/E, Sheet 13 kolom D–H, Sheet 5, Sheet 8).

| Komponen | Formula | Verifikasi workbook |
|---|---|---|
| Angsuran Flat | bunga = plafon × rate/12; pokok = plafon/tenor | Sheet 13 C9–C10 |
| Angsuran Anuitas | total = PMT(rate/12, tenor, −plafon); bunga = saldo × rate/12 | Sheet 13 C8–C10; `-PMT(...)` Sheet 6 C17 |
| Grace Period | pokok = 0 kecuali periode terakhir = saldo; bunga = plafon × rate/12 | Sheet 13 (cabang Grace) |
| True-up | periode terakhir: pokok = saldo awal (kecuali Grace) | Sheet 13 D22 `IF(A22=$C$7,C22,…)` |
| RPC | surplus_bulanan × margin_kapasitas | Sheet 6 C7 |
| DSCR | RPC ÷ angsuran; status `>= minimum_dscr` | Sheet 6 C20–C22 |
| Plafon max Flat | RPC ÷ (1/tenor + rate/12) | Sheet 6 C25 |
| Plafon max Anuitas | RPC × (1 − (1+rate/12)^−tenor) ÷ (rate/12) | Sheet 6 C25 |
| Plafon max Grace | RPC ÷ (rate/12) | Sheet 6 (Grace) C25 |
| Skor 5C | Σ(skor × bobot); bobot 0,25/0,30/0,15/0,20/0,10 | Sheet 8 |
| Rating | ≥4,0 Sangat Layak; ≥3,0 Layak; ≥2,0 Layak Bersyarat; <2,0 Tidak Layak | Sheet 8 (contoh 3,6 = Layak) |
| Override BR-01 | SLIK ≥2 macet atau ≥1 kol≥3 → skor = 1,0 | MASTER CONTRACT |
| Balloon | total = plafon + bunga_bulanan; rasio = perkiraan ÷ total; ≥1,10 LAYAK; ≥1,00 PERHATIAN; <1,00 TIDAK LAYAK | Sheet 6 C48–C50 (Grace) |
| Modal kerja | CT = biaya_tahunan ÷ (hari_kerja ÷ periode); WCD = (pembelian_bulanan ÷ 30) × hari_persediaan; rata² = (CT+WCD)÷2; gap = max(0, rata² − mk_tersedia) | Sheet 5 C13–C25 |
| IDIR (TPP) | angsuran ÷ penghasilan_bersih; ambang 0,35 | parameter `idir_threshold_tpp` |
| LTV | plafon_rekomendasi ÷ nilai_diperhitungkan | Sheet 2 F28, Sheet 6 A39 |
| Suku bunga efektif | RATE(tenor, −angsuran, plafon) × 12 | Sheet 6 C43 |

**Golden test:** `php artisan golden:extract` membaca 3 workbook (path di config) dan menulis `tests/fixtures/golden/{tpp,mk,grace}.json`; test membandingkan output service dengan fixture. Fixture **tidak boleh** diketik manual.

**Catatan rekonsiliasi:** workbook menambahkan komponen biaya lain (Sheet 6 C16 = 0) → parameter `biaya_lain_bulanan` default 0; `hari_persediaan` pada Sheet 5 memakai periode perputaran → input `hari_persediaan` tetap ada, prefill dari parameter `hari_persediaan_default` (prefill UI, **bukan** fallback service).

## §9. Business rules

| Kode | Level | Kondisi | Aksi | Titik penegakan |
|---|---|---|---|---|
| BR-01 | HARD | SLIK ≥2 macet atau ≥1 kol≥3 | skor 5C = 1,0; rating Tidak Layak | `Scoring5CService`, `BR01` |
| BR-02 | HARD | DSCR < `minimum_dscr` | status TIDAK LAYAK | `KapasitasService`, `BR02` |
| BR-03 | HARD | LTV > `maksimum_ltv` | status MELEBIHI | `LtvCalculator`, `BR03` |
| BR-04 | HARD | Grace Period tanpa sumber & perkiraan pelunasan | blokir submit | CHECK DB + `StorePermohonanRequest` |
| BR-05 | HARD | Checklist wajib belum lengkap | blokir ke Komite | `ChecklistService`, `BR05` |
| BR-06 | HARD | Approval tidak berurutan sesuai matrix | blokir aksi | `ApprovalService`, `BR06` |
| BR-07 | HARD | Template dokumen bukan APPROVED | exception | `DocumentService`, `BR07` |
| BR-08 | HARD | Analisis tanpa `parameter_kebijakan_id` | exception | kolom NOT NULL + `AnalysisService` |
| BR-09 | HARD | UPDATE/DELETE `audit_log`/`ledger` | ditolak DB | trigger |
| BR-10 | SOFT | plafon > `plafon_kewajaran_multiplier` × MK riil | flag WARNING | `BR10` |
| BR-11 | SOFT | IDIR TPP > `idir_threshold_tpp` | flag WARNING | `BR11` |
| BR-12 | SOFT | skenario terburuk tidak layak | flag WARNING | `BR12` |
| BR-13 | HARD | rasio balloon < 1,00 | flag CRITICAL | `BR13` |
| BR-14 | HARD | TTL (tanggal lahir) kosong saat SPAPP | blokir generate | `DocumentService`, `BR14` |
| BR-15 | HARD | NIK kosong saat consent SLIK | blokir generate | `DocumentService`, `BR15` |

Setiap BR = satu kelas `BusinessRule` (kode, level, `validate()` → `RuleResult`), satu test minimal. Pesan dari `lang/id.php`.

## §10. State machine & otorisasi

**Status permohonan:** `DRAFT → SUBMITTED → ANALYZED → RECOMMENDED → APPROVED | REJECTED → SIGNED → ACTIVE → CLOSED | NPL`; `RETURNED` mengembalikan ke `ANALYZED`.

| Transisi | Aktor | Guard |
|---|---|---|
| DRAFT→SUBMITTED | AO | BR-04, kelengkapan minimum |
| SUBMITTED→ANALYZED | AO | analisis kapasitas tersimpan |
| ANALYZED→RECOMMENDED | AO | scoring + rekomendasi ada |
| RECOMMENDED→APPROVED | role level matrix | BR-05, BR-06, urutan level |
| RECOMMENDED→REJECTED | DIRUT/KOMITE | alasan wajib |
| APPROVED→SIGNED | AO/Legal | 17 dokumen inti ada |
| SIGNED→ACTIVE | Admin | memo pencairan disetujui |
| ACTIVE→CLOSED | Admin | saldo 0 |
| ACTIVE→NPL | Admin | kolektibilitas 5 |

**Matriks akses:** AO hanya cabangnya & data miliknya; Mgr/Koord/Dirut lintas AO dalam cabang; Komite semua cabang (read + approve); Auditor read-only termasuk ledger; Admin konfigurasi & user (tanpa approval). Policy: `DebiturPolicy`, `PermohonanPolicy`, `KreditPolicy`, `DokumenPolicy`, `LedgerPolicy`. Setiap pelanggaran → 403 + catat `AKSES_DITOLAK` pada log (bukan ledger).

## §11. Ledger & simulasi

**`simulasi_kredit`** (append-only): `id`, `cabang_id`, `user_id`, `permohonan_id?`, `debitur_id?`, `label`, `produk_tipe`, `sistem_suku_bunga`, `parameter_kebijakan_id`, `parameter_version`, `engine_version`, `input jsonb`, `input_hash char(64)`, `output jsonb`, kolom ringkas (`plafon`, `rate`, `tenor`, `angsuran`, `dscr`, `plafon_max`, `ltv`, `rasio_balloon`, `idir`, `skor_5c`, `rating`, `status_kelayakan`, `jumlah_red_flag`), `durasi_ms`, `ip_address`, `user_agent`, `ledger_id`, `dibuat_pada`.

**`ledger`** (append-only, immutable): `seq bigserial`, `id uuid`, `occurred_at timestamptz`, `dibuat_pada timestamptz`, `cabang_id?`, `actor_id?`, `event`, `entity_type`, `entity_id?`, `correlation_id uuid`, `idempotency_key? unique`, `payload jsonb`, `prev_hash`, `hash`, `ip_address`, `user_agent`.

**Insert (dalam transaksi data):**
1. `SELECT pg_advisory_xact_lock(hashtext('ledger-chain'))`.
2. Baca baris terakhir (`seq`, `hash`); `prev_hash` = hash tersebut (genesis = 64 karakter `0`).
3. `hash = sha256(prev_hash . "\n" . canonical_json(payload) . "\n" . occurred_at . "\n" . event . "\n" . entity_type . "\n" . (entity_id ?? ''))`.
4. Insert; `idempotency_key` mencegah dobel-posting saat retry.

**Jaminan:** trigger `BEFORE UPDATE OR DELETE` → `RAISE EXCEPTION 'ledger immutable (BR-09)'`; `TRUNCATE` tetap diizinkan untuk `migrate:fresh`/test. `php artisan ledger:verify [--from=] [--to=]` merekonstruksi rantai dan menunjuk `seq` pertama yang putus. Jadwal harian 01:00.

**Replay:** `php artisan simulasi:replay {id}` memuat input + parameter versi + engine version, menjalankan ulang, dan membandingkan output kanonik; beda → exit 1.

**Halaman:** Ledger (filter tanggal/aktor/cabang/event/entity, ekspor CSV/PDF, status verifikasi), Riwayat Simulasi (per debitur/permohonan/AO, diff antar-run), Timeline permohonan.

## §12. Import

**Antarmuka adapter:**

```php
interface ImportAdapter {
    public function jenis(): JenisImport;
    public function detect(string $path): bool;
    public function parse(string $path, array $profil): iterable; // baris mentah
    public function normalize(array $baris, array $profil): array;
    public function validate(array $data): array;                 // daftar error
    public function persist(array $data, ImportBatch $batch): mixed;
}
```

**Pipeline:** unggah (private, hash) → deteksi jenis/profil → `import_batch` (PREVIEW) → `import_baris` per baris (payload, normalized, errors) → pratinjau UI → commit transaksional → `DATA_DIIMPOR` + ledger per target → laporan + `errors.csv`. Rollback hanya pada PREVIEW; setelah COMMITTED koreksi lewat batch pembalik (`IMPORT_DIBATALKAN` + soft delete bila entitas mendukung).

**Adapter:** (1) SLIK Excel/CSV, (2) SLIK PDF teks, (3) SLIK tempel-teks, (4) KTP/KK/NPWP, (5) debitur+aset XLSX, (6) dokumen ZIP, (7) workbook analisis lama, (8) pembayaran angsuran CSV.

**Dedup:** `file_hash` unik per (jenis, profil); baris via kunci alami (NIK; `(lembaga, jenis_fasilitas, plafon)`; `(debitur, urutan aset)`; `(kredit, periode)`). Profil mapping di `import_profil` (DB), bukan kode.

**PII:** file KTP/iDeb disimpan terenkripsi (`PiiStorage`, AES-256-GCM via `APP_KEY`); NIK dimasking di UI/ekspor untuk role tanpa izin; setiap unduhan mencatat `AKSES_PII`.

**OCR:** `OCR_DRIVER=auto|none|tesseract`; `auto` mendeteksi biner; tanpa tesseract, ekstraksi NIK→tanggal lahir/jenis kelamin/provinsi tetap berjalan dan field lain diisi manusia. Hasil disimpan di `ekstraksi_dokumen` (raw text, field, confidence, engine+versi, verifikator) dan **tidak** menjadi data debitur sebelum dikonfirmasi.

## §13. Kontrak API

Prefix `/api/v1`, auth Sanctum (`Authorization: Bearer`), JSON, 401 tanpa token, 403 policy, 422 validasi, 409 state/BR, 201 create.

| Grup | Jumlah | Endpoint inti |
|---|---|---|
| Auth | 3 | `POST /login`, `POST /logout`, `GET /me` |
| Debitur & aset | 8 | `GET/POST /debitur`, `GET/PATCH/DELETE /debitur/{id}`, `POST /debitur/{id}/aset`, `PATCH/DELETE /aset/{id}` |
| Permohonan | 6 | `GET/POST /permohonan`, `GET/PATCH/DELETE /permohonan/{id}`, `POST /permohonan/{id}/submit` |
| SLIK | 6 | `GET /permohonan/{id}/slik`, `POST /permohonan/{id}/slik`, `POST /permohonan/{id}/slik/import`, `PATCH/DELETE /slik/{id}`, `GET /slik/permintaan` |
| Analisis | 18 | GET/PUT neraca, laba-rugi, modal-kerja, sensitivitas, 7-aspek; GET rasio; GET+POST compute kapasitas; GET+POST compute scoring; GET red-flags; GET+POST generate rekomendasi |
| Simulasi | 5 | `POST /simulasi`, `GET /simulasi`, `GET /simulasi/{id}`, `GET /simulasi/bandingkan?ids=`, `POST /simulasi/{id}/replay` |
| Approval | 4 | `GET /approvals/inbox`, `POST /permohonan/{id}/approve`, `/reject`, `/return` |
| Dokumen | 6 | `POST /permohonan/{id}/dokumen/{kode}/generate`, `GET /dokumen/{id}`, `GET /dokumen/{id}/download`, `POST /dokumen/{id}/sign`, `POST /dokumen/upload`, `POST /dokumen/{id}/verify-hash` |
| Kredit | 5 | `GET /kredit/{id}`, `GET /kredit/{id}/jadwal`, `POST /kredit/{id}/cair`, `POST /jadwal/{id}/bayar`, `POST /kredit/{id}/tutup` |
| Pembayaran | 3 | `GET /kredit/{id}/pembayaran`, `POST /pembayaran/{id}/reversal`, `POST /pembayaran/import` |
| Ledger | 4 | `GET /ledger`, `GET /ledger/entity/{type}/{id}`, `GET /ledger/verify`, `GET /ledger/export` |
| Import | 6 | `POST /import/{jenis}`, `GET /import`, `GET /import/{batch}`, `POST /import/{batch}/commit`, `DELETE /import/{batch}`, `GET /import/{batch}/errors` |
| Dashboard & health | 2 | `GET /dashboard`, `GET /health` |
| **Total** | **76** | dihitung dari grup; dijaga `RouteContractTest` |

## §14. Peta UI

| Halaman | Rute | Pola |
|---|---|---|
| Login / ganti sandi | `/login`, `/password` | Blade form + rate limit |
| Dashboard | `/` | KPI, pipeline, red flag CRITICAL, kelengkapan |
| Debitur | `/debitur*` | tabel + filter; wizard 3 langkah (Livewire) |
| Permohonan | `/permohonan*` | wizard 5 langkah, paste-grid SLIK, autosave |
| Analisis | `/permohonan/{id}/analisis/*` | form tab, hitung langsung, pratinjau cetak |
| Simulator | `/simulasi*` | isi cepat, simpan, bandingkan, ekspor PDF |
| Workflow | `/approval*` | inbox, detail, timeline, aksi |
| Dokumen | `/dokumen*` | daftar, generate, unggah, tanda tangan |
| Kredit | `/kredit*` | detail, jadwal, pembayaran, memo |
| Ledger | `/ledger*` | filter, timeline, ekspor, verifikasi |
| Import | `/import*` | unggah, pratinjau, commit, error CSV |
| Admin | `/admin/*` | user, parameter, referensi |

Rincian target 80 rute: dashboard 1 + auth 5 + debitur 7 + permohonan 9 + analisis 18 + simulator 5 + approval 5 + dokumen 5 + kredit 4 + ledger 4 + import 5 + admin 10 + profil 2 = **80** (dijaga `RouteContractTest`). Komponen: `input`, `select`, `money-input`, `status-badge`, `dscr-display`, `kelengkapan-bar`, `paste-grid`, `diff-simulasi`.

## §15. Konfigurasi & environment

```dotenv
APP_NAME="Loan Analytic BPR"
APP_ENV=local
APP_URL=http://loan-analytic-bpr.test
APP_TIMEZONE=Asia/Makassar
APP_LOCALE=id
APP_ENGINE_VERSION=calc-v1.0.0
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=loan_analytic_bpr
DB_USERNAME=fiyyalisanna
DB_PASSWORD=
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
MAIL_MAILER=log
FILESYSTEM_DISK=local
LEDGER_CHAIN=true
IMPORT_MAX_ROWS=5000
IMPORT_MAX_FILE_MB=25
OCR_DRIVER=auto
OCR_LANG=ind
TESSERACT_PATH=
PII_STORAGE=encrypted
ADMIN_INITIAL_PASSWORD=
LOGIN_MAX_ATTEMPTS=5
LOGIN_DECAY_SECONDS=60
PASSWORD_MIN_LENGTH=8
PASSWORD_MIN_LENGTH_PRODUCTION=12
SLOW_QUERY_MS=500
```

`config/loan.php`: `engine_version`, `timezone`, `money` (skala 2, HALF_UP), `nomor` (pola permohonan/kredit/dokumen), `upload` (mime, maks MB), `dokumen` (disk, path), `pii` (disk, path, cipher), `security` (batas login, panjang sandi, ambang slow query).
`config/import.php`: `max_rows`, `max_file_mb`, `allowed_mimes`, `staging_ttl_hours`, kode profil default.
`config/ledger.php`: `enabled`, `chain`, `lock_key`, `verify_schedule`.
Tanpa kunci Redis. `.env.example` memakai placeholder dan **tidak** berisi kredensial nyata.

## §16. Runbook

**Setup (sekali):** `composer install` → salin `.env` → `php artisan key:generate` → `createdb loan_analytic_bpr` (+ `_test`) → `php artisan migrate --seed` → `php artisan storage:link` → `herd link loan-analytic-bpr` (atau `php artisan serve`).

**Harian:** `scripts/verify.sh --quick`; `php artisan ledger:verify` (terjadwal 01:00); `scripts/backup.sh` (pg_dump + tar storage, terjadwal).

**Test:** `php artisan test`; subset: `php artisan test --filter=Angsuran`; gate: `scripts/verify.sh --full`.

**Reset (lokal saja):** `scripts/reset-db.sh` menolak bila `APP_ENV != local`; selalu konfirmasi interaktif.

**Upgrade:** `composer update` → `php artisan migrate` → `php artisan optimize` → `verify.sh --full` → tag rilis.

**Restore:** `scripts/restore.sh <dump>`; uji restore wajib dilakukan di Fase 16 sebelum go-live.

**Deploy produksi (VPS/PC kantor):** PHP 8.3 + PostgreSQL 16 native, nginx/Caddy + php-fpm, `APP_ENV=production`, `APP_DEBUG=false`, role DB terbatas, cron `ledger:verify` + backup. Tanpa Docker.

## §17. Strategi testing

| Jenis | Cakupan | Contoh |
|---|---|---|
| Golden | formula vs workbook | `FormulaGoldenTest` |
| Unit | service, calculator, rule | 15 test BR, boundary DSCR 0,99/1,00/1,25 |
| Mutation | bukti tidak hardcode | `ParameterMutationTest` |
| Feature | API & alur | submit→approve→cair→bayar |
| Import | fixture anonim per adapter | file ganda, baris invalid, rollback pratinjau |
| Guard | dokumen ↔ kode | `SchemaInventoryTest`, `RouteContractTest`, `LedgerEventCatalogTest`, `NoSecretsTest`, `NoForbiddenDependencyTest` |
| Arch | gaya & larangan | strict types, no dd, no env di app, controller tipis |
| Performa | ambang | `/health` <100 ms, list <300 ms, PDF <3 s, jadwal 360 <100 ms |

Tanpa gate coverage (tidak ada xdebug/pcov). Kualitas dijaga golden + enumerasi edge case + mutation test.

## §18. Observability & troubleshooting

`/health` mengembalikan: app, db, cache (roundtrip), storage writable, driver queue, versi PHP/Laravel, status migrasi, status chain ledger. Slow query >500 ms dicatat di channel `performance`.

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| `php` error dyld icu4c | memakai PHP Homebrew | pakai PHP Herd (`which php`) |
| `could not connect to server` | Postgres mati | `brew services start postgresql@16` |
| Migration gagal di CHECK | daftar enum vs DB | regenerasi constraint dari enum |
| `ledger immutable` saat test | trigger aktif | gunakan `migrate:fresh` (TRUNCATE) atau hapus di test khusus |
| Import 0 baris | profil/mapping salah | buka `errors.csv`, sesuaikan profil |
| PDF kosong | template tanpa data | cek view + data wajib (BR-14/15) |
| OCR kosong | tesseract tidak ada | normal; isi manual atau pasang tesseract |
| `MissingPolicyParameter` | parameter v1.0 tidak lengkap | aktifkan parameter / tambah kunci |
| Route 404 setelah cache | `route:cache` + Livewire | `php artisan route:clear` |
| Composer OOM | memory_limit 128M | `COMPOSER_MEMORY_LIMIT=-1 composer install` |
| `jq: command not found` | jq tidak terpasang | hitung dengan `php -r` atau `grep -c`; jangan menambah dependensi |

## §19. Keamanan & kepatuhan

RBAC 8 role + policy per entitas; password bcrypt 12; `must_change_password` untuk akun awal; rate limit login; CSRF aktif; tidak ada PII di log; file PII terenkripsi; NIK dimasking (`**** **** **** 1234`) untuk role tanpa izin; `AKSES_PII` dicatat; retensi mengikuti kebijakan BPR; larangan data pribadi nyata di repo; UU PDP: hak akses, penghapusan terkontrol (soft delete + arsip), jejak audit.

## §20. Glossary

| Istilah | Arti |
|---|---|
| Plafon | batas maksimum kredit |
| Baki debet | saldo pokok terutang |
| Kolektibilitas | kualitas kredit 1–5 (1 Lancar … 5 Macet) |
| SLIK / iDeb | Sistem Layanan Informasi Keuangan OJK / layanan permintaan informasi debitur |
| IDEBKU | portal permintaan informasi debitur OJK |
| LTV | Loan to Value — plafon dibanding nilai agunan |
| DSCR | Debt Service Coverage Ratio — kemampuan bayar terhadap angsuran |
| RPC | Repayment Capacity — surplus × margin kapasitas |
| Balloon | pembayaran pokok sekaligus di akhir (Grace Period) |
| SPK/SPAPP | surat persetujuan kredit / surat persetujuan pemberian kredit |
| PK | perjanjian kredit |
| APHT | Akta Pembebanan Hak Tanggungan |
| Fidusia | pengikatan jaminan benda bergerak |
| TBO | take over / pelunasan kredit bank lain |
| Agunan | jaminan |
| Prinsip 5C | Character, Capacity, Capital, Collateral, Condition |
| NPL | Non Performing Loan |
| TPP | Tambahan Penghasilan Pegawai (produk konsumtif) |
| MK | Modal Kerja |
| Ledger | jurnal peristiwa append-only |
| Hash chain | rantai hash untuk mendeteksi perubahan riwayat |
| Staging | area pratinjau sebelum commit import |
| PII | Personally Identifiable Information |

## §21. Traceability & riwayat dokumen

- Kebutuhan → tabel → service → route → test → dokumen: matriks lengkap di [INVENTARIS_DATA.md](INVENTARIS_DATA.md) §13.
- Perubahan dokumen ini wajib menaikkan versi dan dicatat:

| Versi | Tanggal | Perubahan |
|---|---|---|
| 2.4 | 2026-11-05 | Dokumen awal v2.4: tanpa Docker/Redis, ledger, import, zero hardcode, reality check |
