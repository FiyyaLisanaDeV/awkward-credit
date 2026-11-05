# Bab 4 — Autentikasi, Otorisasi, Role/Permission, dan Keamanan Akses

## 4.1 Ringkasan `[E]`
Autentikasi **ditulis sendiri** (ADR-009, tanpa Breeze). Dua jalur:
1. **Web session** — `LoginController`, `PasswordController`, middleware `active` & `password.changed`.
2. **API token** — Sanctum (`AuthController`), `Bearer` token.

Otorisasi: **spatie/laravel-permission** (ADR-011), 8 role (seeder), permission granular, dan **scoping cabang** via trait `ScopesByCabang`.

## 4.2 Model User `[E]`
`app/Models/User.php` (atribut `#[Fillable]`/`#[Hidden]` PHP 8 attribute):
- Fillable: `cabang_id, name, username, email, password, jabatan, no_hp, is_active, must_change_password, last_login_at`.
- Default atribut: `is_active=true`, `must_change_password=false`.
- Casts: `password=hashed`, `is_active`/`must_change_password` boolean, tanggal datetime.
- Traits: `HasApiTokens` (Sanctum), `HasFactory`, `HasRoles` (Spatie), `Notifiable`.

## 4.3 Login web `[E]` — `Auth/LoginController`
`store()`:
1. Validasi `email` (email) & `password`.
2. Throttle per-IP `login:<ip>` dengan `login_max_attempts` (default 5) & `login_decay_seconds` (60). Lewat batas → `429` (`auth.throttle`).
3. `Auth::attempt([...credentials, 'is_active' => true], remember)`. Gagal → hit rate limiter + `ValidationException` `auth.failed`.
4. Sukses → clear limiter, `session()->regenerate()`, set `last_login_at=now()`.
5. Redirect ke `dashboard` (intended).
`destroy()`: logout, invalidate session, regenerate token, redirect `login`.

## 4.4 Ganti sandi `[E]` — `Auth/PasswordController`
- `show()` → view `auth.change-password`.
- `update()`: validasi `current_password` (rule `current_password`), `password` (`confirmed`, `Password::defaults()`). Set `password=Hash::make(...)`, `must_change_password=false`, redirect `dashboard`.

## 4.5 Middleware `[E]`
### `EnsureUserIsActive` (alias `active`)
Jika `user !== null && !user->is_active` → logout, invalidate session, regenerate token, redirect `login` dengan error `auth.inactive`.

### `MustChangePassword` (alias `password.changed`)
Jika `user->must_change_password` dan route **bukan** `password.*`/`logout` → redirect `password.change` dengan status `auth.must_change_password`.

## 4.6 Route web & permission `[E]`
Semua route berikut di grup `['auth','active','password.changed']`:
| Permission | Route | Controller |
|---|---|---|
| (semua login) | `/dashboard` | closure → view `dashboard.index` |
| — | `/password` (GET/POST) | `PasswordController` |
| `permohonan.create` | `/debitur/create`, `/permohonan/create` | Web controllers (create) |
| `permohonan.view` | `/debitur`, `/debitur/{debitur}`, `/permohonan`, `/permohonan/{permohonan}` | Web controllers |
| `simulasi.create` | `/simulasi`, `/simulasi/create`, `/simulasi/bandingkan`, `/simulasi/{simulasi}` | Web `SimulasiController` |
Publik: `/health` (`HealthController`), `/login` (GET/POST, grup `guest`), `/logout` (POST, `auth`).

> Catatan `[?]`: middleware `permission:` dari Spatie hanya aktif bila `PermissionMiddleware` didaftarkan. Verifikasi pendaftaran middleware Spatie di `bootstrap/app.php` (saat ini hanya alias `active` & `password.changed` yang terdaftar) — lihat Bab 20 (known issue).

## 4.7 API auth Sanctum `[E]` — `Api/V1/AuthController`
- `POST /api/v1/login` (throttle `10,1`): validasi `email`, `password`, `device_name`. Cari user `is_active=true` + `Hash::check`. Gagal → `auth.failed`. Sukses → buat token (`device_name` default `api`), respons `201` `{token, user}`.
- `GET /api/v1/me` (auth:sanctum): profil `{id,name,email,cabang_id,roles}`.
- `POST /api/v1/logout`: hapus token aktif → `204`.

## 4.8 Scoping cabang `[E]` — `Concerns/ScopesByCabang`
`ensureSameCabang(Model $model, User $user)`:
- Jika user punya role `ADMIN` → lolos.
- Selain itu → `abort_unless($model->cabang_id === $user->cabang_id, 403, 'Akses lintas cabang ditolak.')`.
Dipakai di controller API (debitur, permohonan, analisis, simulasi, approval, kredit) & pengecekan manual di controller Web. **Perilaku `LedgerController` berbeda**: non-ADMIN hanya melihat ledger cabangnya (filter query), tanpa abort.

## 4.9 Role & permission `[E]`
`RolePermissionSeeder` menyemai **8 role**. Role `ADMIN` bersifat lintas cabang. Permission yang dipakai route: `permohonan.create`, `permohonan.view`, `simulasi.create` (lihat Bab 16 untuk daftar lengkap seeder). Otorisasi approval memakai role dari matriks (`RefApprovalMatrix`), lihat Bab 10.

## 4.10 Kesehatan `[E]` — `HealthController`
`GET /health` memeriksa: `app`, `db` (PDO), `cache` (put/get/forget), `storage` (put/exists/delete di disk `loan.dokumen.disk`), `queue`, `php_version`, `laravel_version`, `migrations` (jumlah baris), `ledger_chain` (`n/a`), `timestamp`. Jika `db`/`cache`/`storage` gagal → HTTP `503`.

## 4.11 Praktik keamanan yang berlaku `[E]`
- Rate limit login (web per-IP & API throttle).
- Sesi regenerasi saat login; invalidate saat logout/inactive.
- Password `hashed`; panjang minimum dari config (naik di produksi).
- PII dienkripsi (`loan.pii.cipher=aes-256-gcm`) dan storage privat.
- Audit akses PII lewat event `AKSES_PII` (Bab 12/18).
- Tidak ada PII nyata di test/fixture (guard `NoSecretsTest`).

## 4.12 Matriks akses ringkas
| Aksi | Web | API |
|---|---|---|
| Login | `POST /login` | `POST /api/v1/login` |
| Lihat debitur | `permohonan.view` | `debitur.index/show` (scope cabang) |
| Buat debitur | `permohonan.create` | `debitur.store` (scope) |
| Permohonan | `permohonan.*` | `permohonan.*` (scope) |
| Analisis | (fase API) | `permohonan/{id}/analisis/*` |
| Approval | — | `approvals/inbox`, `approve/reject/return` (role matriks) |
| Ledger | — | `ledger`, `ledger/verify` (ADMIN semua; non-ADMIN cabang) |
