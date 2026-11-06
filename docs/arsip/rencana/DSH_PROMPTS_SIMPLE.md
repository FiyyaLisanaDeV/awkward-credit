# DSH_PROMPTS_SIMPLE — Loan Analytic BPR v2.4

Paket prompt eksekusi pengganti `DSH_PROMPTS.md` v1.0 (SUPERSEDED). 22 unit fase, tanpa Docker/Redis/MinIO/Puppeteer/npm. Setiap prompt atomik: [A] prasyarat, [B] scan, [C] task, [D] deliverable, [E] verifikasi, [F] rollback, [G] handoff.

---

# PART 0 — CARA PAKAI & PROTOKOL SESI

## P0.1 Urutan paste di sesi baru
1. **PART 1** (Master Contract) — sekali per sesi.
2. **PART 2** (Pre-Flight) — sekali per sesi.
3. **Prompt fase** yang sedang dikerjakan (PART 3), plus kalimat: `Lanjutkan Fase-XX. Baca PROGRESS.md dulu, scan state, jangan asumsikan apa pun.`

## P0.2 Bootstrap sesi (wajib sebelum menyentuh kode)
```bash
git status -sb && git log --oneline -5
php artisan migrate:status | tail -3
php artisan route:list --json | php -r 'echo count(json_decode(stream_get_contents(STDIN), true)), PHP_EOL;'
php artisan test --compact 2>&1 | tail -5
php artisan state:check
```
Bandingkan dengan header [PROGRESS.md](PROGRESS.md). Beda → tulis `DRIFT` di PROGRESS dan selesaikan dulu.

## P0.3 Aturan klaim (anti-halusinasi)
Setiap laporan memakai tag: `[E: perintah → output]` (mesin), `[H: nama, tanggal]` (keputusan manusia), `[A: …]` (asumsi), `[?]` (belum tahu → bertanya). Frasa terlarang: "seharusnya", "mungkin sudah", "telah diuji" tanpa output, "sesuai standar" tanpa pasal. Detail: [VERIFIKASI.md](VERIFIKASI.md).

## P0.4 Bukti
Semua perintah verifikasi disimpan:
```bash
mkdir -p docs/evidence
{ echo "# $(date -Iseconds) — PRE"; <perintah>; } > docs/evidence/phase-XX-pre-$(date +%Y%m%d).txt
```
PROGRESS merujuk nama file bukti, bukan ringkasan bebas.

## P0.5 Handoff akhir sesi (salin ke PROGRESS)
```
SESI: <tanggal> | FASE: <xx> | STATUS: done|in-progress|blocked
BERUBAH: <file>
BUKTI: docs/evidence/<file> (exit 0)
KEPUTUSAN: <ADR-xx / tidak ada>
BLOCKER: <ada/tidak>
NEXT: <1–3 langkah perintah konkret>
```

## P0.6 Zero hardcode (berlaku di semua fase)
- Tidak ada nilai bisnis di `app/**`; semua dari `ref_parameter_kebijakan` (via `PolicyParameters`) atau `ref_*`.
- Tidak ada label di Blade; pakai `__('...')` + `lang/id.php`.
- Tidak ada format/path/batas di service; pakai `config/*`.
- Tidak ada angka golden diketik; pakai `golden:extract`.
- Penegak: `php artisan hardcode:audit` (harus nol), `ParameterMutationTest`.

## P0.7 Bila konteks hampir penuh
Tulis state persis ke PROGRESS (file terakhir, perintah terakhir, langkah berikutnya), baru tutup sesi. Dilarang menutup sesi tanpa handoff.

---

# PART 1 — MASTER CONTRACT v2.4

```
# MASTER CONTRACT — LOAN ANALYTIC BPR v2.4

## IDENTITAS
- Klien: PT BPR Bahteramas Kendari + Bombana + Kolaka
- Produk: TPP, Modal Kerja, Grace Period
- Regulasi: POJK 1/2024 (Kualitas Aset BPR)
- Aktor: AO, Mgr Pemasaran, Koordinator, Dirut, Komite, Admin, Legal, Auditor
- Bahasa UI: Indonesia · Zona: Asia/Makassar · Mata uang: IDR

## STACK (NON-NEGOTIABLE v2.4)
- Laravel 13.x, PHP 8.3.7 (Herd) — TANPA Docker, TANPA Redis
- PostgreSQL 16 native (role lokal), TANPA ekstensi & TANPA partisi
- Cache/Session = database · Queue = sync · Storage = lokal (PII terenkripsi)
- Livewire 4 (composer-only) + satu public/css/app.css (TANPA npm/Vite)
- DomPDF (TANPA Puppeteer) · Pest 4.7 · Pint · Larastan 3 (L5 global, L8 Services)
- Spatie Permission · Sanctum · maatwebsite/excel 4.0.6 · smalot/pdfparser 2.12
- Opsional: tesseract (OCR) — aplikasi tetap jalan tanpa itu

## STRUKTUR
app/Domain/{Debitur,Permohonan,Analisis,Kredit,Workflow,Dokumen,Simulasi}
app/Services/{Calculation,Rules,Scoring,Workflow,Document,Import,Ledger,Policy}
app/Http/{Requests,Resources,Controllers/Api/V1,Controllers/Web,Middleware}
app/{Livewire,Models,Enums,Support,Policies}
resources/views/{layouts,auth,dashboard,debitur,permohonan,analisis,simulasi,workflow,dokumen,admin,components}
routes/{web,api,console}.php · scripts/*.sh · tests/{Unit,Feature,Arch,Fixtures}

## DATA
60 tabel bisnis (lihat INVENTARIS) · UUID PK · cabang_id · soft delete · numeric(15,2)
Tanpa partisi; index sesuai INVENTARIS; audit_log & ledger immutable via trigger.

## FORMULA PERMANEN (ringkas — detail TEKNIS §8)
- Flat: bunga=plafon×rate/12, pokok=plafon/tenor; true-up periode terakhir.
- Anuitas: total=PMT(rate/12,tenor,-plafon); bunga=saldo×rate/12.
- Grace: pokok=0 kecuali periode terakhir=saldo; bunga=plafon×rate/12.
- RPC=surplus×margin; DSCR=RPC/angsuran; Plafon max: flat RPC/(1/tenor+rate/12),
  anuitas RPC×(1-(1+rate/12)^-tenor)/(rate/12), grace RPC/(rate/12).
- 5C: bobot 0,25/0,30/0,15/0,20/0,10; rating ≥4,0/≥3,0/≥2,0.
- Override BR-01: SLIK ≥2 macet atau ≥1 kol≥3 → skor 1,0.
- Balloon: total=plafon+bunga; rasio=perkiraan/total; ≥1,10/≥1,00/<1,00.
- Modal kerja: CT=biaya_tahunan/(hari_kerja/periode); WCD=(pembelian/30)×hari_persediaan;
  rata²=(CT+WCD)/2; gap=max(0,rata²-mk_tersedia).
- IDIR=angsuran/penghasilan_bersih; ambang 0,35.

## BUSINESS RULES BR-01..BR-15
BR-01 HARD SLIK bermasalah → skor 1,0 · BR-02 HARD DSCR<min → TIDAK LAYAK
BR-03 HARD LTV>max → MELEBIHI · BR-04 HARD Grace tanpa sumber balloon → blokir submit
BR-05 HARD Checklist wajib belum lengkap → blokir Komite · BR-06 HARD urutan approval
BR-07 HARD template harus APPROVED · BR-08 HARD analisis simpan parameter versi
BR-09 HARD audit_log/ledger immutable · BR-10 SOFT plafon>120% MK → WARNING
BR-11 SOFT IDIR>35% → WARNING · BR-12 SOFT skenario terburuk → WARNING
BR-13 HARD rasio balloon<1,0 → CRITICAL · BR-14 HARD TTL wajib SPAPP · BR-15 HARD NIK wajib consent SLIK

## ATURAN KERAS
1. Scan dulu; jangan asumsikan state. 2. Jangan hardcode nilai bisnis.
3. Semua aksi penting lewat LedgerService dalam transaksi yang sama.
4. Jangan modifikasi di luar scope fase. 5. Jangan klaim tanpa [E].
6. Gagal verifikasi 2× → BLOCKED, bukan "akali".
7. Setiap fase: SCAN → KERJAKAN → VERIFIKASI → BUKTI → HANDOFF.

## KOMUNIKASI
- Setiap laporan: fase, perintah, file yang disentuh, hasil verifikasi ber-tag.
- Ambigu → ajukan pertanyaan spesifik; jangan mengisi dengan tebakan.
- Akhir fase: tunggu konfirmasi user sebelum fase berikutnya.
```

---

# PART 2 — PRE-FLIGHT PROTOCOL v2.4

```
# PRE-FLIGHT SCAN — WAJIB
1. JANGAN eksekusi sebelum scan selesai. 2. JANGAN lanjut bila ada KONFLIK tanpa keputusan user.
3. JANGAN asumsikan state. 4. Laporkan dengan tag [E]/[H]/[A]/[?]. 5. BERHENTI menunggu konfirmasi.

## A. Sistem
sw_vers; uname -m; df -h . | tail -1; echo $SHELL

## B. PHP & tool
php -v | head -1; php -m | tr '\n' ' '; composer -V | head -1
php -r 'foreach(["xdebug","pcov","pdo_pgsql","bcmath","mbstring","intl","gd","zip"] as $e)
  echo $e.": ".(extension_loaded($e)?"ada":"TIDAK")."\n";'

## C. Database
pg_isready; psql -h 127.0.0.1 -d postgres -tAc "select current_user, version();"
psql -h 127.0.0.1 -d postgres -tAc "select datname from pg_database where datname like 'loan_analytic%';"
psql -h 127.0.0.1 -d postgres -tAc "select name from pg_available_extensions where name in ('pg_partman','pg_trgm','pgcrypto');"

## D. Proyek
pwd; ls -la; [ -f artisan ] && php artisan --version; [ -d .git ] && git status -sb

## E. Port
for p in 5432 8000 8080; do lsof -i :$p -sTCP:LISTEN -t >/dev/null 2>&1 \
  && echo "PORT $p TERPAKAI" || echo "PORT $p FREE"; done

## F. Tripwire (harus sesuai VERIFIKASI §5)
command -v redis-cli docker minio tesseract pdftotext || true   # cek KETIADAAN, bukan pemakaian
/opt/homebrew/bin/php -v 2>&1 | head -1     # harus error -> pakai PHP Herd
```

**Format laporan:** ringkas per bagian A–F + kesimpulan (selesai / belum ada / konflik / rekomendasi) + `⏸️ MENUNGGU KONFIRMASI`. Balasan user: `LANJUT` / `ADJUST: …` / `SKIP` / `ABORT`.

---

# PART 3 — PROMPT FASE (22)

> Konvensi: path proyek `loan-analytic-bpr` di dalam workspace; dokumen disalin ke `<repo>/docs/`. Semua fase diakhiri bukti + handoff.

## FASE 00 — RECONNAISSANCE (read-only)
```
[A] Prasyarat: tidak ada.
[B] Jalankan PART 2 lengkap + tripwire.
[C] TASK (hanya baca):
  - Tentukan path target <workspace>/loan-analytic-bpr (tanpa spasi), domain loan-analytic-bpr.test.
  - Health matrix: PHP 8.3, 9 ekstensi, Postgres 16 running, role, Composer, Herd, disk, port.
  - Catat 3 workbook + contoh KTP lokal sebagai bahan uji (jangan disalin ke repo).
  - Tulis docs/PHASE_00_RECON.md: temuan, konflik, rekomendasi, risiko.
[D] PHASE_00_RECON.md + docs/evidence/phase-00-pre-<ts>.txt
[E] Berkas ada; setiap klaim ber-tag [E]; health matrix ≥8 baris.
[F] rm docs/PHASE_00_RECON.md
[G] Tunggu "APPROVED to FASE-01".
```

## FASE 01 — SCAFFOLD LARAVEL 13
```
[A] Fase 00 approved; PHP 8.3.7; Postgres running.
[B] test -d ../loan-analytic-bpr && echo EXISTS; php -v | head -1; composer diagnose | tail -3
[C] TASK
  1. composer create-project laravel/laravel:^13.0 ../loan-analytic-bpr --prefer-dist
  2. cd ../loan-analytic-bpr && composer require barryvdh/laravel-dompdf:^3.1 \
       livewire/livewire:^4.4 maatwebsite/excel:^4.0.6 smalot/pdfparser:^2.12 \
       spatie/laravel-permission:^6
     composer remove phpunit/phpunit --dev
     composer require --dev pestphp/pest:^4.7 pestphp/pest-plugin-laravel:^4.1 \
       larastan/larastan:^3.14 laravel/pint:^1.32 nikic/php-parser
     ./vendor/bin/pest --init        # inisialisasi Pest (bukan artisan pest:install)
  3. Buat struktur folder (Domain, Services, Enums, Support, Policies, Livewire,
     Http/Requests, Http/Resources, views, tests/{Unit,Feature,Arch,Fixtures}, scripts).
  4. Hapus package.json, vite.config.js, resources/js, resources/css.
     Buat public/css/app.css minimal (token warna, form, tabel, badge).
  5. config: loan.php, import.php, ledger.php (nilai dari TEKNIS §15; tanpa literal bisnis).
  6. .env + .env.example sesuai TEKNIS §15 (tanpa kunci Redis).
  7. scripts/setup.sh, verify.sh (--quick/--phase/--full), reset-db.sh, backup.sh, restore.sh.
     Daftarkan alias composer setup & verify.
  8. Salin ../docs ke ./docs (A–G + evidence).
  9. Skeleton command: state:check, docs:check, hardcode:audit (versi awal: forbidden terms + secrets).
 10. tests penjaga awal: NoSecretsTest, NoForbiddenDependencyTest, StrictTypesTest.
 11. git init -b main; commit; tag phase-01-done.
[D] Proyek jalan + scripts + config + test penjaga.
[E] php artisan --version | grep 13; php artisan route:list | wc -l; php artisan test;
    scripts/verify.sh --quick; php artisan hardcode:audit; curl -I http://localhost:8000
[F] rm -rf ../loan-analytic-bpr
[G] Handoff + APPROVED to FASE-02.
```

## FASE 02 — AUTH + HEALTH
```
[A] Fase 01 done. [B] psql -lqt | grep -c loan_analytic; composer show laravel/breeze (harus tidak ada)
[C] 1. createdb loan_analytic_bpr; createdb loan_analytic_bpr_test
    2. php artisan migrate (tabel sistem)
    3. LoginController, LogoutController, PasswordController, UserAdminController;
       middleware EnsureUserIsActive + MustChangePassword; RateLimiter login.
    4. Views: auth/login, auth/change-password, layouts/guest, layouts/app (tanpa @vite).
    5. GET /health (db, storage, cache roundtrip, queue driver, versi, migrasi, ledger chain).
    6. AppServiceProvider: strict mode non-prod, slow query log, password rules.
    7. Tests: login sukses/gagal/rate-limit, must_change_password, health 200/503.
[D] Auth berfungsi + /health. [E] php artisan test --filter=Auth; curl -s localhost:8000/health;
    php artisan route:list --path=health
[F] git reset --hard HEAD~1; dropdb loan_analytic_bpr loan_analytic_bpr_test
[G] Handoff + APPROVED to FASE-03.
```

## FASE 03 — MIGRASI MASTER/REFERENSI (13)
```
[A] Fase 02 done. [B] ls database/migrations | wc -l; psql -c "\dt" | grep -E "cabang|ref_"
[C] Buat 13 migrasi (urutan sesuai INVENTARIS §1) dengan:
    UUID PK, timestamps, softDeletes bila perlu, kolom sumber + approved_by/at pada ref_*,
    CHECK dibangkitkan dari enum, jsonb params/matrix, partial unique ACTIVE parameter.
    Belum jalankan migrate (tunggu Fase 04).
[D] 13 file migrasi. [E] ls | grep -cE "cabang|produk|ref_"; php artisan migrate --pretend;
    php artisan test --filter=EnumCheckConstraint
[F] Hapus 13 file migrasi tersebut.
[G] Handoff + APPROVED to FASE-04.
```

## FASE 04 — MIGRASI AUTH + DEBITUR (11)
```
[A] Fase 03 done. [B] php artisan migrate:status | tail -3
[C] 1. Publikasi migrasi Spatie (5 tabel) + migrate.
    2. Alter users: cabang_id, username, jabatan, no_hp, is_active, must_change_password, last_login_at.
    3. 6 tabel debitur sesuai INVENTARIS §3 (kolom lengkap), CHECK, unique parsial.
    4. php artisan migrate.
    5. Tests: SchemaInventoryTest subset, NIK unik, cascade pasangan/usaha/aset.
[D] 11 tabel tercatat (6 baru + users + 5 Spatie). [E] psql -c "\dt" | grep -E "debitur|users|roles";
    php artisan test --filter=DebiturSchema; drill #1 cold-start (baca PROGRESS+TEKNIS saja).
[F] php artisan migrate:rollback --step=6; hapus 6 file; restore users via git.
[G] Handoff + APPROVED to FASE-05.
```

## FASE 05 — MIGRASI PERMOHONAN + ANALISIS + SLIK (15)
```
[A] Fase 04 done. [B] psql -c "\dt" | grep -E "permohonan|analisis|slik"  # harus kosong
[C] 15 migrasi: permohonan_kredit (CHECK BR-04, plafon_rekomendasi<=plafon), slik_permintaan,
    permohonan_slik_debitur (baki>=0 saja), permohonan_slik_pasangan, permohonan_riwayat_internal,
    10 tabel analisis (1:1 cascade + parameter_kebijakan_id NOT NULL).
    Index sesuai INVENTARIS. migrate.
[D] 15 migrasi. [E] php artisan migrate:status | tail -15; psql -c "\d permohonan_kredit" | grep -E "CHECK|grace";
    php artisan test --filter=PermohonanSchema
[F] migrate:rollback --step=15; hapus file.
[G] Handoff + APPROVED to FASE-06.
```

## FASE 06 — MIGRASI KREDIT + WORKFLOW + AUDIT (13)
```
[A] Fase 05 done. [B] psql -c "\dt" | grep -E "kredit|dokumen|approval|audit"
[C] 13 migrasi: kredit, jadwal_angsuran (bigserial, unique kredit+periode), biaya_kredit,
    asuransi_kredit, kredit_agunan; dokumen, approval_log, signature_log, checklist_status,
    memo_pencairan, pemblokiran_tabungan; audit_log, notifikasi.
    Trigger BR-09 pada audit_log (before update/delete raise exception). migrate.
[D] 13 migrasi. [E] psql -c "\dt" | wc -l; sql update audit_log harus GAGAL;
    php artisan test --filter=AuditImmutable
[F] migrate:rollback --step=13; hapus file.
[G] Handoff + APPROVED to FASE-06B.
```

## FASE 06B — MIGRASI LEDGER + SIMULASI + IMPORT + PEMBAYARAN (7)
```
[A] Fase 06 done. [B] psql -c "\dt" | grep -E "ledger|simulasi|import|pembayaran"
[C] 7 migrasi sesuai INVENTARIS §7. Trigger ledger immutable. Index chain & entity.
    migrate. Hitung tabel: psql -tAc "select count(*) from information_schema.tables where table_schema='public'"
    (catat angka nyata ke PROGRESS; jangan pakai klaim "60" tanpa angka perintah).
[D] 7 migrasi + angka tabel terverifikasi.
[E] psql -c "\dt" | wc -l; php artisan test --filter=LedgerSchema; php artisan ledger:verify (bila command ada)
[F] migrate:rollback --step=7; hapus file.
[G] Handoff + APPROVED to FASE-07.
```

## FASE 07 — SEEDERS (17)
```
[A] Fase 06B done. [B] psql -c "select count(*) from ref_kategori_agunan_pojk"  # 0
[C] Seeder: Cabang(3), Produk(9), KategoriAgunan(13, sumber Sheet2!34-46),
    Kolektibilitas(5), StatusPelunasan(5), RateJiwa/Jasindo, RateKebakaran(Bumida),
    RateKendaraan(TLO/ALLRISK), KonversiSukuBunga, Checklist(28, sumber Sheet14),
    ParameterKebijakan v1.0 (34 kunci, 4 DRAFT), ApprovalMatrix v1.0, TemplateDokumen(17),
    RolePermission(8), AdminUser (password dari ADMIN_INITIAL_PASSWORD / generate + must_change_password),
    ImportProfil (3 contoh), RefProvinsi.
    Setiap baris ref_* wajib kolom sumber. Jangan mengarang rate/checklist.
[D] 14 seeder referensi (2 kondisional) + data. [E] php artisan db:seed; hitung per tabel; php artisan test --filter=Seed
[F] migrate:fresh --seed=false (DESTRUKTIF, lokal saja)
[G] Handoff + APPROVED to FASE-08.
```

## FASE 08 — MODELS + ENUM + DTO PARAMETER
```
[A] Fase 07 done. [B] ls app/Models | wc -l
[C] 55 model (+ Spatie) sesuai INVENTARIS; 34 enum (nilai persis §8); casts; relasi; soft delete;
    trait Immutable untuk AuditLog/Ledger; PolicyParameters DTO + PolicyParameterKey enum;
    Support: Konstanta, Money, Percentage, NIK, PiiStorage.
[D] Model + enum + DTO. [E] php artisan test --filter=SchemaInventory; php artisan model:show Debitur;
    php artisan hardcode:audit; drill #2
[F] git reset --hard HEAD~1
[G] Handoff + APPROVED to FASE-08B.
```

## FASE 08B — LEDGER SERVICE
```
[A] Fase 08 done. [B] ls app/Services/Ledger (kosong)
[C] LedgerService (advisory lock, prev_hash, canonical JSON, idempotency), ChainHasher,
    commands ledger:verify (+ schedule harian 01:00). SimulationService & replay dipindah ke Fase 09
    karena bergantung pada calculator (ADR-026).
[D] Service + command. [E] 5 event uji → ledger:verify exit 0; baris hash salah → verify gagal;
    insert/update/delete diuji; php artisan test --filter=Ledger
[F] git reset --hard HEAD~1
[G] Handoff + APPROVED to FASE-09.
```

## FASE 09 — CALCULATION ENGINE + SIMULATION SERVICE
```
[A] Fase 08B done. [B] ls app/Services/Calculation (kosong)
[C] 8 service: Angsuran, Dscr, PlafonMax, Ltv, RasioKeuangan, ModalKerja, Idir, SukuBungaEfektif.
    Semua membaca PolicyParameters; pembulatan HALF_UP 2 desimal + true-up.
    SimulationService (normalisasi input → input_hash → output → simulasi_kredit + ledger event)
    dan command simulasi:replay (membandingkan output kanonik).
    Command golden:extract membaca 3 workbook → tests/fixtures/golden/*.json.
    Test: flat/anuitas/grace × tenor 12/24/60, 360 periode, saldo akhir 0, Σpokok=plafon,
    toleransi Rp0,01 vs golden, MissingParameterTest, ParameterMutationTest (≥3).
[D] Service + golden fixture + test. [E] php artisan test --filter=Calculation;
    php artisan golden:extract --check; php artisan hardcode:audit
[F] git reset --hard HEAD~1
[G] Handoff + APPROVED to FASE-10.
```

## FASE 10 — RULES + SCORING
```
[A] Fase 09 done. [B] ls app/Services/Rules app/Services/Scoring
[C] BusinessRule + RuleResult + Engine; 15 rule BR01..BR15; Scoring5CService (bobot & rating dari
    parameter, override BR-01, justifikasi per aspek); RedFlagEvaluator 7 kategori;
    RekomendasiService. Pesan dari lang/id.php.
[D] 15 rule + scoring + red flag. [E] 15 test BR; boundary skor 2,0/3,0/4,0; override;
    ParameterMutationTest bobot; php artisan test --filter=Rules
[F] git reset --hard HEAD~1
[G] Handoff + APPROVED to FASE-11.
```

## FASE 11 — API
```
[A] Fase 10 done. [B] php artisan route:list --path=api | wc -l
[C] php artisan install:api (Sanctum). 15 controller, 15 FormRequest, 10 Resource, 3 Policy,
    routes/api.php (76 endpoint sesuai TEKNIS §13), rate limit + throttle.
    Controller hanya orkestrasi; nilai dari parameter/DB.
[D] API v1. [E] RouteContractTest; 401 tanpa token; login → token → GET /debitur 200;
    validation 422; policy 403; php artisan test --filter=Api
[F] git reset --hard HEAD~1
[G] Handoff + APPROVED to FASE-12.
```

## FASE 12 — BLADE UI FORMS
```
[A] Fase 11 done. [B] ls resources/views | wc -l
[C] layout app/guest/sidebar/navbar; komponen input, select, money-input, status-badge,
    dscr-display, kelengkapan-bar, paste-grid, diff-simulasi;
    view debitur (index/create/show/edit + aset), permohonan (wizard 5 langkah),
    analisis (neraca, laba-rugi, modal-kerja, kapasitas, sensitivitas, scoring, red flag,
    rekomendasi, 7 aspek), simulator + riwayat + bandingkan, autosave 30 dtk,
    skor kelengkapan, pratinjau cetak. Livewire wizard 3 & 5 langkah. Tanpa @vite/npm.
[D] UI form lengkap. [E] php artisan view:cache; php artisan test --filter=Web; render 200 tiap halaman;
    label tidak literal (LabelTest); drill #3
[F] git reset --hard HEAD~1
[G] Handoff + APPROVED to FASE-12B.
```

## FASE 12B — IMPORT & EFISIENSI OPERASI
```
[A] Fase 12 done. [B] ls app/Services/Import; psql -c "select count(*) from import_profil"
[C] ImportPipeline + ImportAdapter interface; 8 adapter (SLIK XLSX/CSV, SLIK PDF teks,
    SLIK tempel-teks, KTP/KK/NPWP + NIK parser + OCR opsional, debitur+aset XLSX,
    dokumen ZIP, workbook lama, pembayaran CSV); profiling di DB; staging import_batch/baris;
    pratinjau + commit + rollback pratinjau + errors.csv; PiiStorage terenkripsi; event ledger;
    UI import. Fixture anonim di tests/Fixtures/import.
[D] Import end-to-end. [E] fixture SLIK 50 fasilitas → ≥95% valid; unggah file sama → ditolak;
    KTP tanpa OCR → NIK/tanggal lahir/jenis kelamin; PII masked; php artisan test --filter=Import
[F] git reset --hard HEAD~1
[G] Handoff + APPROVED to FASE-13.
```

## FASE 13 — DASHBOARD + WORKFLOW
```
[A] Fase 12B done. [B] ls resources/views/dashboard resources/views/workflow
[C] DashboardService (Cache::remember database 300 dtk), KPI per status/cabang, pipeline,
    red flag CRITICAL, kelengkapan; inbox/show/timeline approval; approve/reject/return
    dengan cek level matrix (BR-06); NotificationBell poll 30 dtk; halaman kredit/jadwal/
    pembayaran; event ledger tiap aksi.
[D] Dashboard + workflow + pembayaran. [E] php artisan test --filter=Workflow alur 3 level;
    notifikasi muncul; jadwal & pembayaran tercatat di ledger
[F] git reset --hard HEAD~1
[G] Handoff + APPROVED to FASE-14.
```

## FASE 14 — DOKUMEN (17)
```
[A] Fase 13 done. [B] ls app/Services/Document; composer show barryvdh/laravel-dompdf
[C] DocumentGenerator interface; 17 generator + 17 template Blade dari workbook sumber;
    DocumentService (BR-07 template APPROVED, hash idempoten, disk privat); helper terbilang
    via autoload.files; nomor dokumen dari config/parameter; tanda tangan = unggah + hash.
[D] 17 dokumen. [E] generate DOC-01..17 untuk 1 permohonan uji; file valid; hash sama saat generate
    ulang; template tidak APPROVED → exception (BR-07); BR-14/15 test; php artisan test --filter=Document
[F] git reset --hard HEAD~1
[G] Handoff + APPROVED to FASE-15.
```

## FASE 15 — TESTING & QUALITY GATE
```
[A] Fase 14 done. [B] php artisan test --compact | tail -5
[C] Lengkapi: golden formula, 15 BR, scoring, import, workflow, dokumen, konsistensi DB,
    arch test, performa (health/list/PDF/jadwal), guard test (Schema/Route/EventCatalog/
    Secrets/Forbidden), hardcode:audit nol, docs:check --strict.
[D] Suite lengkap + laporan. [E] scripts/verify.sh --full exit 0; php artisan docs:check --strict;
    drill #4 cold-start; verifier pass 3 klaim acak
[F] git reset --hard HEAD~1
[G] Handoff + APPROVED to FASE-16.
```

## FASE 16 — HARDENING
```
[A] Fase 15 done. [B] scripts/backup.sh --dry-run
[C] Role DB produksi non-superuser + GRANT terbatas; uji restore dari dump; audit PII & masking;
    tuning index; baseline performa; dokumentasikan prosedur deploy (nginx/Caddy + php-fpm).
[D] Catatan hardening + hasil restore. [E] restore ke DB uji cocok jumlah baris; performa sesuai
    ambang; php artisan state:check hijau
[F] rollback perubahan role; restore dump sebelum hardening
[G] Handoff + APPROVED to FASE-17.
```

## FASE 17 — DOKUMENTASI PENGGUNA, SOP & PELATIHAN
```
[A] Fase 16 done. [B] ls docs
[C] Manual AO (input, simulasi, import), manual Admin (parameter, user, backup),
    panduan Komite (dashboard, ledger, dokumen), SOP POJK (checklist, approval, retensi),
    materi pelatihan 3 cabang, FAQ. Semua angka bersumber; tangkapan layar bila ada.
[D] 4 dokumen pengguna + materi. [E] review pemilik proses (tanda tangan/komentar);
    setiap langkah dapat diikuti di aplikasi
[F] hapus dokumen baru
[G] Handoff + APPROVED to FASE-18.
```

## FASE 18 — UAT, GO-LIVE & SERAH TERIMA
```
[A] Fase 17 done. [B] scripts/verify.sh --full
[C] Skrip UAT 3 cabang (skenario nyata), checklist go-live, evidence pack, serah terima
    (kode, dokumen A–G, akses, backup, kontak), jadwal pemeliharaan.
[D] Paket UAT + berita acara. [E] semua butir UAT lulus/tanda tangan; verify.sh --full hijau;
    ledger:verify hijau; hardcode:audit nol
[F] tidak ada (kembali ke fase terkait bila ada temuan)
[G] PROYEK SELESAI — handover.
```

---

# PART 4 — APPENDIX

## P4.1 Cheat sheet
```bash
# setup & jalan
composer setup && herd link loan-analytic-bpr   # atau php artisan serve
php artisan migrate --seed
php artisan test --filter=Calculation
scripts/verify.sh --full

# data
psql -h 127.0.0.1 -d loan_analytic_bpr -c "\dt"
psql -h 127.0.0.1 -d loan_analytic_bpr -c "select count(*) from ledger"
php artisan ledger:verify
php artisan simulasi:replay <id>
php artisan golden:extract

# kualitas
php artisan hardcode:audit
php artisan docs:check --strict
php artisan state:check
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
```

## P4.2 Troubleshooting
| Gejala | Solusi |
|---|---|
| `php` error dyld icu4c | pakai PHP Herd (`which php`), jangan Homebrew |
| Postgres mati | `brew services start postgresql@16` |
| Migration CHECK gagal | regenerasi constraint dari enum |
| `ledger immutable` di test | gunakan `migrate:fresh`; jangan UPDATE langsung |
| Import 0 baris | cek profil & `errors.csv` |
| Route 404 setelah cache | `php artisan route:clear` |
| Composer OOM | `COMPOSER_MEMORY_LIMIT=-1 composer install` |
| PDF kosong | cek BR-14/15 & data wajib |
| OCR kosong | normal tanpa tesseract; isi manual |

## P4.3 Diff v1.0 → v2.4
| Aspek | v1.0 | v2.4 |
|---|---|---|
| Fase | 16 | 22 (tambah 06B, 08B, 12B, 17, 18; 16 dipecah) |
| Event | 22 (klaim) | 23 (hasil enumerasi) |
| Tabel | 50 | 60 bisnis |
| Infra | Docker+Redis+MinIO+partisi | native, tanpa semuanya |
| Auth | Breeze+Sanctum+Spatie | auth tulis sendiri + Sanctum + Spatie |
| PDF | DomPDF+Browsershot | DomPDF |
| Frontend | Vite/Tailwind/npm | Livewire + CSS tunggal |
| Bukti | tidak ada | evidence + tag klaim + gate |
| Hardcode | dilarang (tanpa alat) | dilarang + `hardcode:audit` + mutation test |
| Ledger | audit_log saja | ledger hash chain + simulasi_kredit |
| Import | tidak ada | 8 adapter + staging |
| Multi-sesi | paste ulang manual | PROGRESS + TEKNIS + prompt atomik |

## P4.4 Riwayat
| Versi | Tanggal | Perubahan |
|---|---|---|
| 2.4 | 2026-11-05 | Paket prompt awal 22 fase, berbasis A–G |
