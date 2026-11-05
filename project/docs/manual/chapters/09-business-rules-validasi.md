# Bab 9 — Business Rules (BR-01..BR-15) dan Validasi

## 9.1 Kerangka aturan `[E]`
- **Interface** `BusinessRule`: `kode()`, `level()`, `validate(PermohonanKredit): RuleResult`.
- **`AbstractRule`**: menyediakan `params()` (dari `PolicyParameterResolver`) dan helper `pass(message, data)`, `fail(message, data)`.
- **`RuleResult`**: `{passed: bool, message: string, data: array}`.
- **`BusinessRuleEngine`**: `validateAll(permohonan)` → list `{kode, level, result}`; `hardViolations(results)`; `hasHardViolations(results)`.
- Level: **`HARD`** (pemblokir) & **`SOFT`** (peringatan).

## 9.2 Daftar aturan `[E]`
| Kode | Nama | Level | Ringkasan logika | Parameter |
|---|---|---|---|---|
| **BR-01** | SLIK bermasalah | HARD | `kol3 ≥ threshold_slik_kol3` ATAU `macet ≥ threshold_slik_macet` → gagal "skor 5C dipaksa 1,0" | `threshold_slik_kol3` (1), `threshold_slik_macet` (2) |
| **BR-02** | DSCR minimum | HARD | bila kapasitas null → gagal "belum tersedia"; else `dscr ≥ minimum_dscr` | `minimum_dscr` (1.0) |
| **BR-03** | LTV maksimum | HARD | bila LTV null → pass; else `ltv ≤ maksimum_ltv` | `maksimum_ltv` (0.7) |
| **BR-04** | Balloon wajib | HARD | jika Grace Period: `sumber_pelunasan_balloon` & `perkiraan_penerimaan_balloon` wajib; jika bukan → pass | (—) |
| **BR-05** | Checklist wajib | HARD | checklist wajib aktif harus berstatus ADA/NA/TIDAK_WAJIB | (ref checklist) |
| **BR-06** | Urutan approval | HARD | level wajib (dari matriks) harus disetujui berurutan lengkap | (matriks) |
| **BR-07** | Template approved | HARD | tidak boleh ada dokumen memakai template ≠ APPROVED | (template) |
| **BR-08** | Parameter tersimpan | HARD | `parameter_kebijakan_id` wajib terisi | (—) |
| **BR-09** | Audit immutable | HARD | cek fungsi & trigger `audit_log_immutable` ada di DB | (DB) |
| **BR-10** | Kewajaran plafon | SOFT | `plafon > plafon_kewajaran_multiplier × rata_rata_kebutuhan_mk` → gagal | `plafon_kewajaran_multiplier` (1.20) |
| **BR-11** | IDIR TPP | SOFT | produk TPP & IDIR > `idir_threshold_tpp` → gagal | `idir_threshold_tpp` (0.35) |
| **BR-12** | Stress test | SOFT | ada skenario sensitivitas `status_layak=false` → gagal | (—) |
| **BR-13** | Balloon kritis | HARD | `rasio_kecukupan_balloon < rasio_balloon_perhatian` → gagal | `rasio_balloon_perhatian` (1.00) |
| **BR-14** | Tanggal lahir | HARD | debitur wajib punya `tanggal_lahir` (syarat SPAPP) | (—) |
| **BR-15** | NIK consent | HARD | debitur wajib punya `no_ktp` (syarat consent SLIK) | (—) |

> Catatan: sebagian parameter di atas **DRAFT** (`[K]`, seeder `v1.0`, `skor_band_draft=true`). Nilai final menunggu keputusan `[H]` (D-02/D-11).

## 9.3 Titik penegakan (enforcement) `[E]`
| Titik | Aturan yang dijalankan | Efek |
|---|---|---|
| `PermohonanService::submit` | **BR-04**, **BR-08** | gagal → `BusinessRuleViolation` (ditolak submit) |
| `POST /permohonan/{id}/submit` | (via service) | error bisnis |
| DB `permohonan_kredit_balloon_check` | BR-04 | CHECK DB (lapis kedua) |
| `analisis_scoring_5c` compute | BR-01 (override skor=1,0 & rating TIDAK LAYAK) | skor/rating |
| `RedFlagEvaluator` | BR-01/02/03/05/13 dst → red flag | daftar red flag |
| `ApprovalController` | BR-06 (implisit via `nextLevel`/matriks) | level approval |
| `BR-09` | cek trigger immutable | pass/gagal |

> **Catatan implementasi `[?]`**: `BusinessRuleEngine::validateAll` tersedia dan diuji, tetapi **belum semua** aturan dipanggil otomatis pada setiap transisi. Hanya BR-04 & BR-08 yang dipanggil langsung di `submit`. Aturan lain dievaluasi via endpoint/komponen masing-masing (scoring, red flag) atau cek DB. Penegakan menyeluruh (mis. `validateAll` sebelum APPROVED) = **`[Rencana]`/perlu verifikasi**.

## 9.4 Validasi form/API (ringkas) `[E]`
| Sumber | File | Cakupan |
|---|---|---|
Debitur | `Support/Validation/DebiturRules` | identitas, kontak, alamat, pekerjaan (lihat Bab 5) |
| Permohonan | `Support/Validation/PermohonanRules` | plafon/rate/tenor/system/balloon (Bab 6) |
| Simulasi | `Support/Validation/SimulasiRules` | parameter simulator (Bab 8) |
| Aset | `Api/V1/DebiturController::storeAset` | aset (Bab 5) |
| SLIK | `Api/V1/SlikController::store` | fasilitas (Bab 6) |
| Analisis | `Api/V1/AnalisisController::*` | neraca/laba-rugi/scoring (Bab 7) |
| Approval | `Api/V1/ApprovalController::*` | alasan (reject/return required) (Bab 10) |

## 9.5 Invariant DB (lapis pertahanan) `[E]`
Selain validasi aplikasi, DB menegakkan CHECK unik/rentang, mis.:
- `permohonan_kredit_balloon_check` (BR-04), `permohonan_kredit_plafon_check`, `_rate_check`, `_tenor_check`, `_pinjaman_check`, `_kelengkapan_check`.
- `kredit_*_check`, `jadwal_angsuran_nilai_check`, `biaya_kredit_nilai_check`, `asuransi_kredit_nilai_check`, `pembayaran_angsuran_nilai_check`, `simulasi_kredit` enum, `ledger` enum, dll.
- Unique partial: `debitur_no_ktp_unique`, `permohonan_slik_*_urutan_unique`, `dokumen_permohonan_jenis_hash_unique`.

## 9.6 Drill & test terkait `[E]`
- `tests/Feature/Rules/BusinessRuleTest.php`.
- `tests/Feature/Scoring/Scoring5CTest.php`.
- `tests/Feature/Schema/EnumCheckConstraintTest.php`, `PermohonanSchemaTest`, `KreditSchemaTest`, `LedgerSchemaTest`, `DebiturSchemaTest`, `SchemaInventoryTest`.
- Drill #1 & #2 (PROGRESS) PASS.

## 9.7 Gap & rencana `[Rencana/K]`
- Band/bobot/ambang final (5C, IDIR/DSR, LTV, balloon) = `[K]` (D-02/D-11).
- Hard-stop vs soft-score (T1: dua mesin konflik di Excel) → perlu `DecisionEngine` bernarasi & flag konflik `[A/K]`.
- DSR total (slik otomatis) belum ada.
- Aturan BMPK/RAC/EWS/APU-PPT = lapis L1–L9 belum ada (Bab 20).
