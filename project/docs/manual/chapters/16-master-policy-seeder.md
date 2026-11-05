# Bab 16 — Data Master, Parameter Kebijakan, Seeder, dan Zero-Hardcode/Provenance

## 16.1 Prinsip zero-hardcode `[E]` (ADR-020)
Semua **nilai bisnis** (ambang, bobot, tarif, checklist, matriks, template) disimpan sebagai **data** di tabel referensi/policy, bukan di kode. `hardcode:audit` (Bab 15) menegakkan ini. Hanya **konstanta mekanis** (bulan/tahun, iterasi bisection) yang ada di `Support\Konstanta`.

## 16.2 Parameter kebijakan `[E]` — `ref_parameter_kebijakan`
Satu baris versi dengan `params` (jsonb) berisi ~34 kunci. Diakses via `PolicyParameters` DTO + `PolicyParameterResolver::active()` (fail-hard bila tak ada ACTIVE).

**Seeder `v1.0` (`ParameterKebijakanSeeder`)** — nilai (DRAFT `[K]`):
| Kunci | Nilai | Kunci | Nilai |
|---|---|---|---|
| `margin_kapasitas_bayar` | 0.5 | `minimum_dscr` | 1.0 |
| `dscr_ideal` | 1.25 | `maksimum_ltv` | 0.7 |
| `hari_kerja_per_tahun` | 360 | `sensitivitas_omzet_ringan` | 0.10 |
| `sensitivitas_omzet_berat` | 0.20 | `sensitivitas_biaya` | 0.10 |
| `bobot_character` | 0.25 | `bobot_capacity` | 0.30 |
| `bobot_capital` | 0.15 | `bobot_collateral` | 0.20 |
| `bobot_condition` | 0.10 | `rating_sangat_layak_min` | 4.0 |
| `rating_layak_min` | 3.0 | `rating_bersyarat_min` | 2.0 |
| `threshold_slik_macet` | 2 | `threshold_slik_kol3` | 1 |
| `threshold_slik_nominal` | 1.000.000 | `rasio_balloon_layak` | 1.10 |
| `rasio_balloon_perhatian` | 1.00 | `idir_threshold_tpp` | 0.35 |
| `plafon_kewajaran_multiplier` | 1.20 | `biaya_provisi_default` | 0.01 |
| `biaya_administrasi_default` | 0.01 | `biaya_survey_default` | 0.02 |
| `biaya_lain_bulanan` | 0.0 | `hari_persediaan_default` | 0 |
| `skor_band_dscr` | `[≥1.5→5, ≥1.25→4, ≥1.0→3, ≥0.75→2, else→1]` | | |
| `skor_band_equity_asset` | `[≥0.5→5, ≥0.3→4, ≥0.2→3, ≥0.1→2, else→1]` | | |
| `skor_band_ltv` | `[≤0.5→5, ≤0.6→4, ≤0.7→3, ≤0.8→2, ≤1.0→1]` | | |
| `skor_band_slik` | `[kol3≤0→5, ≤1→3, ≤2→2, else→1]` | | |
| `skor_band_draft` | `true` (**penanda band masih DRAFT**) | | |
| `bobot_kelengkapan` | `{debitur:25, permohonan:15, slik:15, neraca:20, kapasitas:10, scoring:10, dokumen:5}` | | |

> **Semua nilai di atas = `[K]` DRAFT.** Nilai final menunggu keputusan Komite/Direksi (D-02/D-11). `skor_band_draft=true` menandai band belum final.

## 16.3 Matriks approval `[E]` — `ref_approval_matrix`
Seeder `v1.0` (lihat Bab 10 §10.1): 5 entri plafon→level/role. `status=ACTIVE`.

## 16.4 Seeder referensi `[E]` — `database/seeders/Reference/` (14)
| Seeder | Isi |
|---|---|
| `CabangSeeder` | 3 cabang: KDI (Kendari), BOM (Bombana), KOL (Kolaka) |
| `ProdukSeeder` | Produk per cabang: TPP, MK, GRACE (`rate_default=0.18`, tenor 1–60) |
| `KategoriAgunanPojkSeeder` | 13 kategori agunan POJK |
| `KolektibilitasSeeder` | 5 kolektibilitas (1–5) |
| `StatusPelunasanSeeder` | 5 status pelunasan internal |
| `RateAsuransiSeeder` | Tarif asuransi (kondisional bila dokumen resmi ada — Q3) |
| `KonversiSukuBungaSeeder` | Konversi suku bunga (kondisional) |
| `ChecklistDokumenSeeder` | 28 checklist dokumen |
| `ParameterKebijakanSeeder` | Parameter `v1.0` (~34 kunci) |
| `ApprovalMatrixSeeder` | Matriks approval `v1.0` |
| `TemplateDokumenSeeder` | 17 template dokumen |
| `RolePermissionSeeder` | 8 role + permission |
| `AdminUserSeeder` | 1 admin (sandi dari `ADMIN_INITIAL_PASSWORD`) |
| `ImportProfilSeeder` | 8 profil import |

## 16.5 Role & permission `[E]`
8 role: `AO, MGR_PEMASARAN, KOORDINATOR, DIRUT, KOMITE, ADMIN, LEGAL, AUDITOR`.
Permission (27): `permohonan.create/edit_own/view/submit`, `permohonan.approve_level_2..5`, `permohonan.return/reject`, `dokumen.view/generate_draft/upload/sign`, `template.review/approve`, `audit.view`, `ledger.view`, `report.view`, `parameter.activate`, `user.manage`, `simulasi.create`, `import.slik/debitur/dokumen/pembayaran`.
Pemetaan ringkas: AO (buat/edit/submit/simulasi/import), MGR_PEMASARAN (approve L2), KOORDINATOR (L3), DIRUT (L4/reject), KOMITE (L5), LEGAL (template), AUDITOR (audit/ledger/report), ADMIN (semua).

## 16.6 Profil import `[E]` — `import_profil` (8)
`SLIK_XLSX_IDEB, SLIK_PDF_IDEB, SLIK_TEKS, KTP_OCR, DEBITUR_XLSX, DOKUMEN_ZIP, WORKBOOK_LAMA, PEMBAYARAN_CSV`. Kode profil disimpan di DB (mapping jsonb, delimiter, header_row, sheet). Lihat Bab 17.

## 16.7 Provenance seeder `[E]`
- `tests/Feature/Seed/SeedProvenanceTest.php` memverifikasi asal/kelengkapan data seeder.
- ADR-025: seeder bersumber; tarif asuransi/konversi **tidak dikarang** — dilewati bila sumber belum ada (Q3) `[E]`.
- PROGRESS mencatat "12 aktif + 2 kondisional".

## 16.8 `docs:check` sebagai penjaga `[E]`
Konsistensi jumlah (event=23, enum=34, tabel=60, prompt=22, papan=22) dijaga command ini; menjalankan `docs:check --strict` menjadi bagian `verify.sh --full`.

## 16.9 Catatan drift
- `docs:check` mengharapkan **34 enum** (INVENTARIS) sementara `app/Enums` saat ini **35 file** `[E]` — perbedaan ini dapat memicu temuan `docs:check`; perlu sinkronisasi dokumen `INVENTARIS_DATA.md` (lihat Bab 20/22).
- Nilai `skor_band_*` & ambang = **DRAFT `[K]`**; jangan jadikan dasar keputusan final.
