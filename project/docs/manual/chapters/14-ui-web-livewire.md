# Bab 14 — UI Web & Livewire (Wizard, Simulator, Dashboard, Komponen)

## 14.1 Ringkasan `[E]`
UI web memakai **Blade + Livewire 4** tanpa Vite/npm (ADR-008), satu CSS manual. Layout: `layouts/app.blade.php` (terautentikasi), `layouts/guest.blade.php` (login), `layouts/flash.blade.php`.

## 14.2 Route web `[E]` (lihat Bab 4 §4.6)
| Route | Nama | View/Host |
|---|---|---|
| `/` | — | redirect `dashboard` |
| `/dashboard` | `dashboard` | `dashboard.index` |
| `/password` | `password.change/update` | `auth.change-password` |
| `/debitur` | `debitur.index` | `debitur.index` |
| `/debitur/create` | `debitur.create` | `debitur.create` → `DebiturWizard` |
| `/debitur/{debitur}` | `debitur.show` | `debitur.show` |
| `/permohonan` | `permohonan.index` | `permohonan.index` |
| `/permohonan/create` | `permohonan.create` | `permohonan.create` → `PermohonanWizard` |
| `/permohonan/{permohonan}` | `permohonan.show` | `permohonan.show` |
| `/simulasi` | `simulasi.index` | `simulasi.index` |
| `/simulasi/create` | `simulasi.create` | `simulasi.create` → `Simulator` |
| `/simulasi/bandingkan` | `simulasi.bandingkan` | `simulasi.bandingkan` |
| `/simulasi/{simulasi}` | `simulasi.show` | `simulasi.show` |
| `/login`, `/logout`, `/health` | — | auth/health |

## 14.3 Dashboard `[E]`
`resources/views/dashboard/index.blade.php`: 4 kartu KPI (`draft, submitted, approved, active`) yang saat ini menampilkan **placeholder** (`dashboard.kpi.empty`), plus blok `placeholder_title/body`. **Belum menampilkan data nyata** — dashboard API (`/api/v1/dashboard`) mengembalikan data; fase 13 akan menghubungkan `[Rencana]`. Teks dari `lang/id.php` (`__('dashboard.*')`).

## 14.4 `DebiturWizard` (Livewire) `[E]`
Wizard 3 langkah (Bab 5 §5.5). Props publik per field; `next/previous/save`; validasi per langkah; simpan via `DebiturService`. View: `livewire/debitur-wizard.blade.php`. Opsi enum `JenisKelamin` & `StatusPerkawinan` dari `render()`.

## 14.5 `PermohonanWizard` (Livewire) `[E]`
Wizard 5 langkah (Bab 6 §6.6). Fitur:
- Persist draft pada step 2 & 3.
- Grid SLIK: `addSlikRow/removeSlikRow/parsePaste`.
- `saveDraft` & `submit` (menangani `BusinessRuleViolation`).
- Memuat daftar debitur (scope cabang) untuk pemilihan.
View: `livewire/permohonan-wizard.blade.php`.

## 14.6 `Simulator` (Livewire) `[E]`
Wizard/simulator (Bab 8 §8.8): `hitung()` menampilkan `hasil` + `jadwal`; `simpan()` menyimpan `simulasi_kredit` + redirect. `mount(?permohonan)` pra-isi dari permohonan. View: `livewire/simulator.blade.php`; komponen `diff-simulasi` untuk bandingkan.

## 14.7 Komponen Blade `[E]`
| Komponen | Fungsi |
|---|---|
| `components/input.blade.php` | Input teks generik |
| `components/select.blade.php` | Select generik |
| `components/money-input.blade.php` | Input uang (format) |
| `components/paste-grid.blade.php` | Grid tempel (SLIK) |
| `components/kelengkapan-bar.blade.php` | Bar kelengkapan |
| `components/status-badge.blade.php` | Badge status |
| `components/dscr-display.blade.php` | Tampilan DSCR |
| `components/diff-simulasi.blade.php` | Diff 2 simulasi |
| `partials/pagination.blade.php` | Paginasi |

## 14.8 View per modul `[E]`
- `debitur/{index,create,show}`.
- `permohonan/{index,create,show}`.
- `simulasi/{index,create,show,bandingkan}`.
- `auth/{login,change-password}`.
- `dashboard/index`, `welcome`.

## 14.9 UX & aksesibilitas `[?]`
- Bahasa antarmuka: Indonesia (`lang/id.php`, `__('…')`).
- Nominal ditampilkan tanpa desimal (`loan.money.display_decimals=0`).
- Belum ada audit aksesibilitas/uji UI otomatis di luar `UiRenderTest` `[E]`.

## 14.10 Gaya kode & larangan `[E]`
- **Hindari hardcode** teks/angka label; gunakan `__('…')` & policy (ADR-020). `hardcode:audit` mencakup `app/Http`, `app/Livewire`, `app/Services`, `app/Support`, `app/Domain`.
- **Class penuh** disarankan (mis. `Livewire\Component`) `[E]` pada komponen Livewire walau belum di-import eksplisit (perhatian PHPStan `Simulator.php:73`).

## 14.11 Gap & rencana `[Rencana]`
- Fase 12: Blade UI forms + wizard lengkap (drill #3).
- Fase 13: dashboard nyata + workflow + pembayaran.
- Fase 14: dokumen (17) + TTD.
- Preview/analisis UI (form-modal grid) untuk analis = `[Rencana]`.
