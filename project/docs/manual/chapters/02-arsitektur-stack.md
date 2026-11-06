# Bab 2 — Arsitektur & Stack Teknis

## 2.1 Stack `[E]`
Dari `composer.json` dan `.env.example`:
| Lapisan | Teknologi | Versi | Catatan |
|---|---|---|---|
| Bahasa | PHP | `^8.3` | `.env`/PROGRESS mencatat 8.3.7 |
| Framework | Laravel | `^13.17` | PROGRESS 13.40.0; struktur `bootstrap/app.php` (tanpa HTTP Kernel) |
| DB | PostgreSQL | 16 native | `pdo_pgsql`; CHECK constraint & partial index; trigger |
| API auth | Laravel Sanctum | `^4.0` | token `Bearer` |
| RBAC | spatie/laravel-permission | `^6` | 8 role + permission |
| UI | Livewire | `^4.4` | **tanpa Vite/npm**; satu CSS manual (ADR-008) |
| PDF | barryvdh/laravel-dompdf | `^3.1` | ADR-007 |
| Excel | maatwebsite/excel | `^4.0.6` | impor/ekspor |
| PDF parse | smalot/pdfparser | `^2.12` | ekstraksi SLIK PDF |
| Tinker | laravel/tinker | `^3.0` | |
| Dev: test | pestphp/pest | `^4.7` | + plugin-laravel |
| Dev: static | larastan/larastan | `^3.14` | PHPStan 2 level (ADR-018) |
| Dev: format | laravel/pint | `^1.32` | |
| Dev: AST audit | nikic/php-parser | `^5.9` | `hardcode:audit` |

**Dependensi yang SENGAJA tidak dipakai (ADR):** Docker/Sail (ADR-001), Redis (ADR-004), MinIO/S3 (ADR-006), partisi tabel/pg_partman (ADR-005), Vite/npm (ADR-008), Breeze (ADR-009). Storage = disk lokal; queue = `sync`/database; PII terenkripsi.

> `brick/math` disebut di rencana koreksi `[K]` sebagai target presisi uang, tetapi **tidak ada di `composer.json`** `[E]` — jadi belum menjadi dependensi aktual. Lihat Bab 8 §8.6 & Bab 20.

## 2.2 Struktur direktori `[E]`
```
app/
  Console/Commands/      # DocsCheck, GoldenExtract, HardcodeAudit, LedgerVerify, SimulationReplay, StateCheck
  Domain/Policy/         # PolicyParameters (DTO)
  Enums/                 # 35 enum (status, produk, kolektibilitas, ledger event, dst.)
  Exceptions/            # BusinessRuleViolation, MissingPolicyParameter
  Http/
    Controllers/Api/V1/  # 10 controller API
    Controllers/Auth/    # LoginController, PasswordController
    Controllers/Web/     # DebiturController, PermohonanController, SimulasiController
    Controllers/Concerns/# ScopesByCabang (trait)
    Controllers/         # Controller, HealthController
    Middleware/          # EnsureUserIsActive, MustChangePassword
    Requests/            # Debitur/StoreDebiturRequest, Permohonan/StorePermohonanRequest
  Livewire/              # DebiturWizard, PermohonanWizard, Simulator
  Models/                # 55 model Eloquent
  Services/
    Calculation/         # 8 kalkulator (angsuran, dscr, idir, ltv, modal kerja, plafon max, rasio, EIR)
    Debitur/             # DebiturService
    Ledger/              # LedgerService, ChainHasher
    Permohonan/          # PermohonanService
    Policy/              # PolicyParameterResolver
    Rules/               # BusinessRule, AbstractRule, BusinessRuleEngine, RuleResult, Rules/BR01..BR15
    Scoring/             # Scoring5CService, RedFlagEvaluator, RekomendasiService
    Simulation/          # SimulationEngine, SimulationRunner, SimulationService
    Workflow/            # NomorService
  Support/               # Konstanta, Rounding, Database/CheckConstraint, Validation/*
bootstrap/app.php        # routing + middleware alias + JSON exceptions
config/                  # loan.php, ledger.php, import.php, permission.php, sanctum.php, ...
database/
  migrations/            # 61 migrasi
  seeders/               # DatabaseSeeder + Reference/ (14 seeder)
routes/                  # web.php, api.php, console.php
resources/views/         # Blade (auth, dashboard, debitur, permohonan, simulasi, livewire, components, layouts)
scripts/                 # setup.sh, verify.sh, backup.sh, restore.sh, reset-db.sh
tests/                   # Feature/* , Unit/* (termasuk Guard/*), Fixtures/golden/tpp.json
docs/                    # RUANG-LINGKUP-PRODUK, RENCANA-PRODUKSI-FINAL, TEKNIS, INVENTARIS_DATA, PROGRESS, DECISIONS, VERIFIKASI, ...
```

## 2.3 Bootstrap & middleware `[E]`
`bootstrap/app.php`:
- `withRouting(web, api, commands, health: '/up')`.
- Alias middleware: `active` → `EnsureUserIsActive`, `password.changed` → `MustChangePassword`.
- Exception: `shouldRenderJsonWhen(request is api/* or expectsJson)`.

**Urutan middleware web penting** (Bab 4): grup `['auth','active','password.changed']` melindungi semua halaman kecuali `/login`, `/health`, `/logout`.

## 2.4 Pola arsitektur `[E]`
1. **Controller tipis** → delegasi ke `Services`.
2. **Policy-as-data**: seluruh ambang/bobot dari tabel `ref_parameter_kebijakan` via `PolicyParameters` DTO + `PolicyParameterResolver` (fail-hard `MissingPolicyParameter`). Tidak ada angka bisnis di kode (ADR-020).
3. **Business rules** sebagai kelas terpisah (BR-01..15) dengan level `HARD`/`SOFT`, dijalankan `BusinessRuleEngine`.
4. **Kalkulasi murni** di `Services/Calculation` (dapat diuji tanpa DB).
5. **Ledger append-only** setiap peristiwa bermakna (`LedgerService::record`), dengan hash chain + idempotency + advisory lock.
6. **Snapshot simulasi**: input+output+versi parameter+versi engine disimpan agar reproducible (`SimulationService`, replay).
7. **Scoping cabang** via trait `ScopesByCabang` (ADMIN lintas cabang).

## 2.5 Integrasi data eksternal `[E]`/`[K]`
- **SLIK iDeb** tiga jalur (ADR-022): Excel/CSV, PDF (via `smalot/pdfparser`), tempel-teks (paste-grid). Konsumsi operasional penuh di fase import (12B).
- **Core banking**: hanya referensi (registrasi CIF/No Loan) `[K D-01]`; ledger resmi vs bayangan belum diputuskan.
- **OCR KTP** opsional (ADR-023): driver `auto|none|tesseract`, wajib verifikasi manusia.

## 2.6 Alur data ringkas `[E]`
```
Login → Dashboard
  DebiturWizard ──► debitur (+pasangan, usaha, aset)
  PermohonanWizard ──► permohonan_kredit (+SLIK debitur/pasangan) ──submit(BR04,BR08)──► SUBMITTED
  Analisis (API) ──► neraca, laba_rugi, rasio, modal_kerja, kapasitas_bayar, scoring_5c, red_flags, rekomendasi
  Approval (matriks) ──► APPROVED (multi-level)
  Dokumen & TTD ──► akad, memo pencairan ──► kredit (ACTIVE) + jadwal_angsuran + pembayaran
  Setiap langkah ──► Ledger (hash chain)
  Simulator ──► simulasi_kredit (snapshot) + replay
```

## 2.7 Catatan drift angka (2026-11-05) `[?]`
- PROGRESS §1 mencatat **63 route** (fase 11); saat manual disusun, `php artisan route:list --json` mengembalikan **73 route** (perbedaan bisa berasal dari route internal/framework/Sanctum yang terhitung, atau perubahan setelah snapshot). **Wajib re-verifikasi** dengan `php artisan route:list` sebelum mengutip angka final (lihat Bab 15 & 22).
- PROGRESS mencatat **31 enum** di fase 08 dan **24** di dokumen; `app/Enums` saat ini berisi **35 file** `[E]` (via `ls app/Enums/*.php`). Perbedaan akibat penambahan enum pasca-fase 08.
- PROGRESS mencatat **26 file test** (fase 11); working tree saat ini **30 file** (`find tests -name '*Test.php'`) karena uji fase 12 (UI/Livewire).
- **Baseline QA live (pint/phpstan/pest) belum dijalankan ulang** pada working tree saat manual disusun = `[?]`. Jalankan `composer verify`/`bash scripts/verify.sh --phase` untuk menutup gap ini.
