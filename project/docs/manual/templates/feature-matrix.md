# Feature Matrix — Manual Book (Loan Analytic BPR)

> Peta **fungsi → status → bukti → bab manual**. Setiap fungsi **final** wajib punya baris dengan Status `Final [E/H]` + tanggal/tag + bukti. Perbarui saat fitur final (Bab 22).
> Label: `[E]` terimplementasi & terverifikasi dari kode; `[H]` keputusan bertanda tangan; `[K]` belum final/perlu verifikasi; `[Rencana]` belum ada; `[?]` perlu verifikasi.

## A. Fondasi & Platform
| ID | Fungsi | Status | Bukti | Bab |
|---|---|---|---|---|
| F-PLT-01 | Stack Laravel 13/PG16/Livewire4/Pest (tanpa Docker/Redis/npm) | `[E]` | `composer.json`, ADR-001..008 | 2 |
| F-PLT-02 | Bootstrap routing + alias middleware + JSON exceptions | `[E]` | `bootstrap/app.php` | 2,4 |
| F-PLT-03 | Health check `/health` (db/cache/storage) | `[E]` | `HealthController` | 3,4 |
| F-PLT-04 | Setup/verify/backup/restore scripts | `[E]` | `scripts/*` | 3,21 |
| F-PLT-05 | Zero-hardcode (policy-as-data + AST audit) | `[E]` | `hardcode:audit`, ADR-020 | 16 |

## B. Auth & Otorisasi
| ID | Fungsi | Status | Bukti | Bab |
|---|---|---|---|---|
| F-AUTH-01 | Login web + throttle + inactive guard | `[E]` | `LoginController`, `EnsureUserIsActive` | 4 |
| F-AUTH-02 | Wajib ganti sandi (middleware) | `[E]` | `MustChangePassword`, `PasswordController` | 4 |
| F-AUTH-03 | API token Sanctum (login/logout/me) | `[E]` | `AuthController` | 4,13 |
| F-AUTH-04 | RBAC Spatie (8 role, 27 permission) | `[E]` | `RolePermissionSeeder` | 4,16 |
| F-AUTH-05 | Scoping cabang (trait) | `[E]` | `ScopesByCabang` | 4 |

## C. Debitur
| ID | Fungsi | Status | Bukti | Bab |
|---|---|---|---|---|
| F-DEB-01 | CRUD debitur + validasi (DebiturRules) | `[E]` | `Api/V1/DebiturController`, `DebiturRules` | 5 |
| F-DEB-02 | Wizard debitur 3 langkah | `[E]` | `Livewire/DebiturWizard` | 5,14 |
| F-DEB-03 | Aset/agunan debitur (+nilai_diperhitungkan) | `[E]` | `DebiturController::storeAset` | 5 |
| F-DEB-04 | Pasangan/usaha/siklus (model+relasi) | `[E]` | models | 5,19 |
| F-DEB-05 | Pengkinian data (flow UI) | `[Rencana]` | tabel `debitur_pengkinian` | 5,20 |

## D. Permohonan & SLIK
| ID | Fungsi | Status | Bukti | Bab |
|---|---|---|---|---|
| F-PRM-01 | CRUD permohonan + nomor otomatis | `[E]` | `PermohonanService`, `NomorService` | 6 |
| F-PRM-02 | Wizard permohonan 5 langkah | `[E]` | `Livewire/PermohonanWizard` | 6,14 |
| F-PRM-03 | Submit (BR-04, BR-08) | `[E]` | `PermohonanService::submit` | 6,9 |
| F-SLK-01 | SLIK fasilitas (debitur/pasangan) CRUD | `[E]` | `SlikController` | 6 |
| F-SLK-02 | Paste-grid parse SLIK | `[E]` | `PermohonanWizard::parsePaste` | 6,14 |
| F-SLK-03 | Parser multi-format (xlsx/pdf/teks) + data quality | `[Rencana]` | ADR-022, `import_profil` | 6,17 |

## E. Analisis
| ID | Fungsi | Status | Bukti | Bab |
|---|---|---|---|---|
| F-ANL-01 | Neraca (baca/tulis) | `[E]` | `AnalisisController::neraca*` | 7 |
| F-ANL-02 | Laba-rugi (baca/tulis) | `[E]` | `AnalisisController::labaRugi*` | 7 |
| F-ANL-03 | Rasio keuangan (9 rasio) | `[E]` | `RasioKeuanganCalculator` | 7 |
| F-ANL-04 | Modal kerja (cash turnover/WCD/gap) | `[E]` | `ModalKerjaCalculator` | 7 |
| F-ANL-05 | Kapasitas bayar (RPC/DSCR/plafon/LTV/IDIR/balloon) | `[E]` | `AnalisisController::kapasitasCompute` | 7,8 |
| F-ANL-06 | Sensitivitas | `[E]` | tabel `analisis_sensitivitas` | 7 |
| F-ANL-07 | Scoring 5C (band policy + override BR-01) | `[E]` | `Scoring5CService` | 7,9 |
| F-ANL-08 | Red flags (7 kategori) | `[E]` | `RedFlagEvaluator` | 7 |
| F-ANL-09 | Rekomendasi (plafon final/narasi) | `[E]` | `RekomendasiService` | 7 |
| F-ANL-10 | 7 aspek (naratif) | `[E]` | tabel `analisis_7aspek` | 7 |
| F-ANL-11 | Band/bobot/ambang final | `[K]` | `skor_band_draft=true`, D-02/D-11 | 7,16,20 |

## F. Perhitungan & Simulasi
| ID | Fungsi | Status | Bukti | Bab |
|---|---|---|---|---|
| F-CAL-01 | Angsuran Flat/Anuitas/Grace + jadwal | `[E]` | `AngsuranCalculator` | 8 |
| F-CAL-02 | EIR bisection | `[E]` | `SukuBungaEfektifCalculator` | 8 |
| F-CAL-03 | Plafon maksimum (3 sistem) | `[E]` | `PlafonMaxCalculator` | 8 |
| F-CAL-04 | Engine simulasi + snapshot + replay | `[E]` | `SimulationEngine/Runner/Service` | 8 |
| F-CAL-05 | Golden TPP | `[K]` | `tpp.json` vs C-002 | 8,20 |
| F-CAL-06 | Money/BIGINT + `brick/math` | `[Rencana]` | `pr-plan-c002-money.md` | 8,20 |

## G. Aturan Bisnis
| ID | Fungsi | Status | Bukti | Bab |
|---|---|---|---|---|
| F-RUL-01 | BR-01..15 + engine | `[E]` | `Services/Rules/*` | 9 |
| F-RUL-02 | Penegakan menyeluruh (validateAll) | `[?]` | hanya BR-04/08 di submit | 9,20 |

## H. Approval & Dokumen
| ID | Fungsi | Status | Bukti | Bab |
|---|---|---|---|---|
| F-APR-01 | Approval berjenjang (matriks) | `[E]` | `ApprovalController`, `ApprovalMatrixSeeder` | 10 |
| F-APR-02 | Reject/return + notifikasi | `[E]` | `ApprovalController`, `notifikasi` | 10 |
| F-DOK-01 | Checklist dokumen + BR-05 | `[E]` | `checklist_status` | 10 |
| F-DOK-02 | Dokumen + BR-07 | `[E]` | `dokumen`, template | 10 |
| F-DOK-03 | Generator dokumen (17) + TTD | `[Rencana]` | fase 14 | 10,20 |
| F-APR-03 | Authority/SoD final | `[K]` | D-12 | 10,20 |

## I. Kredit & Pembayaran
| ID | Fungsi | Status | Bukti | Bab |
|---|---|---|---|---|
| F-KRD-01 | Kredit + jadwal/biaya/asuransi/agunan (read) | `[E]` | `KreditController` | 11 |
| F-KRD-02 | Memo pencairan + pemblokiran | `[E]` | tabel terkait | 11 |
| F-KRD-03 | Pembayaran angsuran + reversal (skema) | `[E]` | `pembayaran_angsuran` | 11 |
| F-KRD-04 | Posting/alokasi/denda/penalti | `[Rencana]` | D-06, fase 13 | 11,20 |

## J. Ledger & Audit
| ID | Fungsi | Status | Bukti | Bab |
|---|---|---|---|---|
| F-LED-01 | Ledger hash chain + idempotency + lock | `[E]` | `LedgerService`, `ChainHasher` | 12 |
| F-LED-02 | Immutability trigger (ledger & audit) | `[E]` | migrasi 070/080 | 12 |
| F-LED-03 | `ledger:verify` + API verify | `[E]` | `LedgerVerify` | 12,15 |
| F-LED-04 | COA/jurnal finansial (resmi vs bayangan) | `[K]` | D-01 | 12,20 |

## K. API, UI, CLI
| ID | Fungsi | Status | Bukti | Bab |
|---|---|---|---|---|
| F-API-01 | API v1 (42 endpoint, scope cabang) | `[E]` | `routes/api.php` | 13 |
| F-UI-01 | Dashboard (placeholder) | `[E]`→`[Rencana]` data nyata | `dashboard/index.blade.php` | 14 |
| F-UI-02 | Wizard debitur/permohonan/simulator | `[E]` | `app/Livewire/*` | 14 |
| F-CLI-01 | 6 command (state/docs/hardcode/ledger/replay/golden) | `[E]` | `app/Console/Commands` | 15 |

## L. Lapis Risiko (Rencana) — belum ada
| ID | Fungsi | Status | Bukti/Keputusan | Bab |
|---|---|---|---|---|
| F-RSK-01 | BMPK hard-stop | `[Rencana]` | D-13 | 20 |
| F-RSK-02 | RAC portofolio | `[Rencana]` | D-14 | 20 |
| F-RSK-03 | Rating migrasi + pricing | `[Rencana]` | D-15 | 20 |
| F-RSK-04 | CKPN/PPKA/PD-LGD/write-off | `[Rencana]` | D-16 | 20 |
| F-RSK-05 | EWS tren + SLA | `[Rencana]` | D-17 | 20 |
| F-RSK-06 | Stress makro → CKPN/CAR/likuiditas | `[Rencana]` | D-18 | 20 |
| F-RSK-07 | APU-PPT | `[Rencana]` | D-19 | 20 |
| F-RSK-08 | Tata kelola TI/AI (POJK 34/2025) | `[Rencana]` | D-20 | 20 |

**Cara pakai:** saat sebuah fungsi menjadi **final** → ubah Status menjadi `Final [E]`/`[H]`, isi bukti/tanggal/tag, dan pastikan bab manual + `CHANGELOG.md` + `MANIFEST.md` diperbarui (Bab 22).
