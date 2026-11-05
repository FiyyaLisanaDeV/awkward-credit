# Status Eksekusi Detail — Bloker Tersisa (baseline 2026-11-05, live)

Label: `[E]` terverifikasi dari filesystem/command output, `[K]` butuh konfirmasi manusia/pemutus, `[H]` bertanda tangan/terverifikasi. Tidak menyalin isi sel/rumus/PII workbook (acuan hanya struktur/nama sheet); data dummy; tanpa faktor 1,81x/0,87x; tidak mengklaim golden/keputusan terverifikasi.

## Ringkasan (evidence live)
- **Golden fixture:** 29 kasus, **29 recomputed (DRAFT, belum terverifikasi)**, **0 verified**, `pii:false`, `dummy_data:true`, 4 `negative_test` (`docs/golden/manifest.json` + `cases/`). Lapisan `recomputed` dihasilkan oleh `project/scripts/compute-golden-recomputed.py` (Decimal independen) + `project/scripts/verify-golden.py` → **29 ok, 0 fail**. Parameter best-judgment tercatat di `docs/policy-draft-best-judgment.md` (NON-ACTIVE).
- **Keputusan & regulasi:** D-01..D-20 DRAFT/UNKNOWN (`docs/decisions/register-d01-d20.md`); V1–V7/RAT-01..03/C-002 terbuka (`docs/questions.md`); spec pack kerangka DRAFT (`docs/spec/*`); rujukan status klaim regulasi direkonsiliasi di `docs/ref.md` (label `[V*]` untuk klaim tanpa bukti tersimpan).
- **QA existing repo `APP BPR/loan-analytic-bpr` (eksekusi teknis selesai untuk run deterministik):** `vendor/bin/pest --compact` (setelah `migrate:fresh --seed` DB test) → **99 passed (1596 assertions)** `[E]`. `vendor/bin/phpstan analyse --memory-limit=512M` → **no errors** `[E]`. *Catatan*: menjalankan `php artisan test --compact` (parallel/Pest) pada DB test bersama memicu race migrasi; `phpunit.xml` gagal diparse oleh `php -l` (XML, bukan PHP). Baseline sehat bila run deterministik (`pest`/single-process + DB test segar) — bukan bukti perilaku paralel.

## Perbaikan teknis yang dieksekusi `[E]` (repo existing, non-domain)
1. **Larastan `Simulator.php` (nullsafe.neverNull)** — akses `produk` eksplisit; Larastan hijau.
2. **Spatie permission middleware belum terdaftar** (`Target class [permission] does not exist`; 403 tak terpaksa) — alias `permission/role/role_or_permission` di `bootstrap/app.php`. `UiRenderTest` izin-negatif distabilkan (clear cache + actingAs fresh).
3. **Tabrakan nama route web vs API** (`permohonan.index`/`debitur.index` resolve ke `/api/v1/...` → view test menerima JSON) — `Route::apiResource`/route API diberi namespace nama `api.*` di `routes/api.php`; route web tetap.
4. **Livewire `Simulator` validasi** (`No property found for validation: [debitur_id]`) — tambah properti `debitur_id`, `input()` eksplisit & filter `''→null`; tetap validasi via `SimulasiRules::rules()` (properti ada). `SimulatorTest` lulus (9 assertions).
5. **`NomorService::permohonan`** — `lockForUpdate()->count()` (agregat, gagal di PG: `FOR UPDATE is not allowed with aggregate functions`) diganti `lockForUpdate()->get('id')->count()`; unblocks wizard/submit.
6. **`DebiturWizard` default NOT NULL** — `jumlah_keluarga/tanggungan` default `'0'` + normalisasi null→0 pada field jumlah; `DebiturWizardTest` lulus.
7. **`PermohonanWizardTest` asersi SLIK** — `kolektibilitas_id` dibandingkan sebagai string. `PermohonanWizardTest` lulus.

File diubah: `APP BPR/loan-analytic-bpr/app/Livewire/Simulator.php`, `bootstrap/app.php`, `routes/api.php`, `app/Services/Workflow/NomorService.php`, `app/Livewire/DebiturWizard.php`, `tests/Feature/UiRenderTest.php`, `tests/Feature/PermohonanWizardTest.php`.

## Status bloker vs rencana riset (eksekusi detail)

| Bloker | Eksekusi dilakukan (bisa tanpa manusia) | Masih tertahan `[K]/[H]` (evidence dibutuhkan) |
|---|---|---|
| B1 Golden ≥25 terverifikasi (29 struktur, 0 verified) `[E]` | Struktur 29 kasus + manifest tervalidasi; R-GOLD/C-002 plan & template verifikasi di `docs/spec/*`, `docs/research-plan-blockers.md` | **Isi `expected.verified`** per kasus butuh rujukan **sheet/sel** atau rekalkulasi independen disahkan; tanpa itu tidak boleh ditandai verified |
| B2 RAT-02/C-002 (IDIR 85,93% / net 2.948.080 / EIR eksak 28,88%@60; definisi komponen net/EIR) | G-TPP-01..05 placeholder + klaim C-002 disimpan **hanya sebagai claimed `[K]`, verified=null**; pertanyaan & lembar verifikasi disiapkan | **Rujukan workbook/sheet/sel** (tanpa data sensitif) + definisi komponen net income & arus kas EIR disclosure dari pemilik domain/akuntansi |
| B3 D-01..D-12 belum `[H]`, RAT-01 | `docs/decisions/register-d01-d20.md` (DRAFT/UNKNOWN) + `docs/questions.md` siap putusan | **Keputusan/penandatanganan `[H]`** per D-xx + ratifikasi ADR↔D (format/jalur) |
| B4 RAT-03 / D-05–D-06 (pembulatan, pengakuan, alokasi, denda, penalti 10% vs 15%, blokir lama) | Terpetakan di plan; dampak ke golden/jurnal diidentifikasi | **Kebijakan berversi `[H]`** (Akuntansi/Direksi) |
| B5 V1,V3,V4 verifikasi primer | Kerangka `research/verifikasi-primer/{V1,V3,V4}.md` + daftar tindakan di questions | **Unduh naskah resmi OJK/peraturan.go.id** & catat nomor/tahun/pasal/ayat/berlaku/tautan/tanggal unduh (Compliance/Legal) |
| B6 V2,V5–V7 & regulasi lanjutan `[K klaim independen]` | Terpetakan (BMPK/APU-PPT/PPATK/POJK 34-2025 TI dll. = `[K]`) | Evidence resmi / kebijakan `[H]` |
| B7 Spec pack bernilai & traceability | Kerangka `docs/spec/*` (modul↔alur sheet) lengkap sebagai DRAFT | Isi `legal_basis` + nilai/pasal/policy `[H]` + traceability (bergantung B1–B6,B9) |
| B8 Korpus SLIK/KTP/KK anonim | Spesifikasi/checklist anonimisasi direncanakan; D-10 default no cloud/LLM | **10–20 sampel dummy/anonim** di luar repo + persetujuan D-03/D-10 |
| B9 Tarif asuransi, haircut % + pasal, COA mapping | D-07/D-08/D-01, DRAFT master direncanakan | **Tarif/tabel/pasal/COA resmi disahkan `[H]`** |
| B10 QA residual repo existing | Triase + perbaikan; **pest 99 passed (1596), phpstan no errors** (deterministik) | Keputusan stabilisasi paralel/CI (`php artisan test` parallel vs DB test) bila diperlukan `[K]` |
| B11 Jalur eksekusi & gate F1–F3 | `docs/spec/gap-assessment-existing-repo.md` | **Keputusan `[H]`** (lanjut `APP BPR/loan-analytic-bpr` gap-assessment vs jalur terpisah) + status Kartu Bukti F1–F3 |

## Langkah eksekusi berikut (gated, urutan W0→W3)
1. **W0 (manusia):** kirim lembar RAT-02 (rujukan sheet/sel C-002, definisi net/EIR, tanpa PII) + putuskan RAT-01/B11 + Compliance evidence V1,V3,V4.
2. **W1:** ratifikasi D-05/D-06/D-11 (RAT-03) → rekalkulasi independen & isi verified G-TPP-01..05, G-SCH-*.
3. **W2:** verifikasi sisa kasus ke ≥25 + D-02/D-07/D-08/D-12 (+ D-13–D-20) + korpus/master dummy.
4. **W3:** konsolidasi spec traceability + V2,V5–V7 + ratifikasi ADR↔D.
5. **Teknis (opsional, `[K]`):** jika tim ingin `php artisan test --compact` hijau paralel, isolasi migrasi/DB per proses (tanpa mengubah asumsi domain).

**Batas:** bagian `[K]/[H]` tidak dapat dieksekusi otonom tanpa bukti manusia/sumber resmi; mengeksekusi dengan mengarang verifikasi akan melanggar aturan proyek.
