# Bab 7 — Analisis Kredit: Neraca, Laba Rugi, Rasio, Modal Kerja, Kapasitas, Sensitivitas, 5C, Red Flag, Rekomendasi

Modul analisis menyimpan hasil analisis per permohonan di tabel `analisis_*`, dengan `parameter_kebijakan_id` sebagai referensi versi (memenuhi BR-08). Sebagian penghitungan dilakukan engine (kapasitas, scoring), sebagian diisi manual (neraca, laba rugi, 7 aspek).

## 7.1 Neraca `[E]` — `analisis_neraca`
Tabel `analisis_neraca` (satu baris per permohonan, unique). Kolom `decimal(15,2) default 0`: `kas_inventaris, bank_simpanan, piutang, persediaan, sewa_dibayar_dimuka, biaya_dibayar_dimuka, pajak_dibayar_dimuka, investasi_jangka_panjang, tanah_bangunan, kebun, kendaraan, aset_lain, jumlah_aktiva_lancar, jumlah_aktiva_tetap, total_aktiva, hutang_pendek, hutang_pajak, hutang_gaji, hutang_panjang, pinjaman_bank, pinjaman_lain, prive, jumlah_hutang, rugi_laba_ditahan, ekuitas_modal_disetor, jumlah_modal, total_pasiva, selisih_cek` + `catatan`.

**API** `GET/PUT /api/v1/permohonan/{id}/analisis/neraca`:
- `neracaShow` → kembalikan `permohonan->neraca`.
- `neracaUpdate` → validasi semua kolom `nullable numeric`; `updateOrCreate` dengan `parameter_kebijakan_id` dari permohonan.
Klasifikasi aset **eksplisit per kolom** (menghindari double-count `SUMIF *Tanah*/*Kebun*` pada Excel — T6).

## 7.2 Laba Rugi `[E]` — `analisis_laba_rugi`
Kolom `decimal(15,2)`: `penghasilan_pemohon, penghasilan_pasangan_gaji, penghasilan_lain_pemohon, penghasilan_pasangan_tpp, hpp, biaya_listrik_air, biaya_transport, biaya_sewa, biaya_gaji, biaya_pendidikan, biaya_kesehatan, biaya_konsumsi, biaya_cicilan_lain, biaya_telepon, biaya_kebersihan, biaya_lain, total_pendapatan, total_pengeluaran, laba_kotor, penyusutan, pajak, laba_bersih, angsuran_kredit_berjalan, surplus_per_bulan, surplus_6_bulan` + `jumlah_bulan_berjalan(default 6)`.

**API** `GET/PUT .../laba-rugi`: validasi `jumlah_bulan_berjalan` 1–12; `updateOrCreate`. `surplus_per_bulan` menjadi input kapasitas bayar.

## 7.3 Rasio Keuangan `[E]`
Tabel `analisis_rasio` menyimpan: `current_ratio, quick_ratio, cash_ratio, debt_to_asset, debt_to_equity, equity_to_asset, net_profit_margin, roa_annualized, roe_annualized` (decimal(10,4) nullable).
**Kalkulator** `RasioKeuanganCalculator` menyediakan `currentRatio, quickRatio, cashRatio, debtToAsset, debtToEquity, equityToAsset, netProfitMargin, roa, roe`, dan `semua(array)`. Pembagi ≤0 → hasil `0.0` (aman). Pembulatan `Rounding::ratio` (4 desimal).
> `equity_to_asset` dipakai sebagai `capital` pada scoring 5C (Bab 9).

## 7.4 Modal Kerja `[E]` — `analisis_modal_kerja`
Kolom: `periode_perputaran_hari, biaya_operasional_tahunan, perputaran_kas_per_tahun, kebutuhan_mk_cash_turnover, pembelian_bulanan, hari_persediaan, kebutuhan_mk_wcd, mk_tersedia, rata_rata_kebutuhan_mk, gap_mk, omzet_bulanan, kebutuhan_mk_persen_omzet`.

**Kalkulator** `ModalKerjaCalculator::hitung(biayaOperasionalTahunan, periodePerputaranHari, pembelianBulanan, hariPersediaan, mkTersedia, hariKerjaPerTahun)` menghasilkan:
- `perputaran_kas_per_tahun = hariKerjaPerTahun / periodePerputaranHari` (0 bila periode 0).
- `kebutuhan_cash_turnover = biayaOperasionalTahunan / perputaran` (perputaran 0 → 0).
- `kebutuhan_wcd = (pembelianBulanan / 30) * hariPersediaan`.
- `rata_rata = (cash_turnover + wcd) / 2`.
- `gap = max(0, rata_rata − mkTersedia)`.
`hariKerjaPerTahun` diambil dari policy `hari_kerja_per_tahun` (default 360).

## 7.5 Kapasitas Bayar `[E]` — `analisis_kapasitas_bayar`
Menghitung RPC, DSCR (diajukan & rekomendasi), plafon maksimum (flat/anuitas/grace), LTV, IDIR, balloon, status kelayakan. **API** `POST /api/v1/permohonan/{id}/analisis/kapasitas/compute` (lihat Bab 8 §8.3 untuk rumus & alur). Parameter: `margin_kapasitas_bayar` (0.5), `minimum_dscr` (1.0), `maksimum_ltv` (0.7), `rasio_balloon_layak` (1.10), `rasio_balloon_perhatian` (1.00).

## 7.6 Sensitivitas `[E]` — `analisis_sensitivitas`
Baris per skenario: `urutan`, `nama_skenario`, `delta_omzet(5,4)`, `delta_biaya(5,4)`, `total_pendapatan`, `total_pengeluaran_non_angsuran`, `surplus_baru`, `rpc_baru`, `dscr`, `status_layak`. Unique `(permohonan_id, urutan)`.
Parameter policy terkait: `sensitivitas_omzet_ringan` (0.10), `sensitivitas_omzet_berat` (0.20), `sensitivitas_biaya` (0.10).
Dipakai BR-12 (SOFT, skenario terburuk tidak layak) dan red flag `STRESS_TIDAK_LAYAK`.

## 7.7 Scoring 5C `[E]` — `analisis_scoring_5c` + `Scoring5CService`
Kolom: `skor_character/capacity/capital/collateral/condition` (3,2), `bobot_*` (5,4), `skor_tertimbang`, `skor_total` (5,4), `rating(50)`, `overridden(bool)`, `override_reason`, `justifikasi_*`.
**Algoritma** (`Scoring5CService::hitung(permohonan, skorCondition)`), `skorCondition` wajib 1–5:
1. Hitung `kol3` (SLIK kode ≥3) & `macet` (kode = 5).
2. `bermasalah` = `kol3 ≥ threshold_slik_kol3` **atau** `macet ≥ threshold_slik_macet`.
3. Skor aspek dari **band policy**:
   - `character` = `bandSlik(skor_band_slik, kol3)`.
   - `capacity` = `bandMin(skor_band_dscr, dscr)`.
   - `capital` = `bandMin(skor_band_equity_asset, equity_to_asset)`.
   - `collateral` = `bandMax(skor_band_ltv, ltv)`.
   - `condition` = input manual (1–5).
4. `skor_tertimbang = Σ(skor × bobot)`; `bobot` dari policy (`bobot_character` 0.25, `capacity` 0.30, `capital` 0.15, `collateral` 0.20, `condition` 0.10).
5. **`skor_total = bermasalah ? 1.0 : skor_tertimbang`** (BR-01 override).
6. **Rating**: bila `bermasalah` → `TIDAK LAYAK`; selain itu bandingkan `skor_total` dengan `rating_sangat_layak_min`(4.0)/`rating_layak_min`(3.0)/`rating_bersyarat_min`(2.0) → `SANGAT LAYAK/LAYAK/LAYAK BERSYARAT/TIDAK LAYAK`.
7. `overridden` = `bermasalah`, `override_reason` = `BR-01: riwayat SLIK bermasalah`.

**API** `POST .../scoring/compute` (validasi `skor_condition` 1–5, + justifikasi opsional) → simpan + ledger `ANALISIS_DISIMPAN` (bagian `scoring`).

## 7.8 Red Flags `[E]` — `RedFlagEvaluator` + `analisis_red_flags`
`RedFlagEvaluator::evaluate(permohonan)` menghasilkan daftar:
| Kode | Level | Kondisi |
|---|---|---|
| `SLIK_BERMASALAH` | CRITICAL | kol3/macet ≥ threshold |
| `DSCR_RENDAH` | WARNING | `dscr_rekomendasi < minimum_dscr` |
| `LTV_TINGGI` | WARNING | `ltv > maksimum_ltv` |
| `BALLOON_KURANG` | CRITICAL | `rasio_kecukupan_balloon < rasio_balloon_perhatian` |
| `STRESS_TIDAK_LAYAK` | WARNING | ada sensitivitas `status_layak=false` |
| `CHECKLIST_KURANG` | WARNING | checklist wajib belum lengkap |
| `IDENTITAS_KURANG` | WARNING | debitur null / tanpa tanggal lahir / tanpa no_ktp |

Tabel `analisis_red_flags`: `kode`, `level` (enum `LevelRedFlag` CRITICAL/WARNING/INFO), `pesan`, `sumber(default SISTEM)`, `teratasi`, `mitigasi`, `pemilik_mitigasi`, `tenggat`. Unique `(permohonan_id, kode)`; index parsial CRITICAL.
**API** `GET .../red-flags`.

## 7.9 Rekomendasi `[E]` — `RekomendasiService` + `analisis_rekomendasi`
`generate(permohonan, scoring)`:
1. `rating` → `rekomendasi`: SANGAT LAYAK/LAYAK → `DISETUJUI`; LAYAK BERSYARAT → `DISETUJUI DENGAN SYARAT`; TIDAK LAYAK → `DITOLAK`.
2. `plafon_kandidat = min(plafon_rekomendasi|plafon_diajukan, plafon_max_flat)`.
3. `plafon_final = (DITOLAK ? 0 : Rounding::money(kandidat))`.
4. `tenor_final`, `rate_final`; `angsuran_final` dihitung `AngsuranCalculator` (total) bila plafon&tenor>0.
5. `narasi` menyertakan rating, skor, plafon, tenor, rate.
**API** `POST .../rekomendasi` (butuh scoring 5C; jika belum → 422). Mengisi `analisis_rekomendasi` (`rekomendasi_sistem, narasi, plafon_final, tenor_final, rate_final, angsuran_final, syarat, covenant, catatan`).

## 7.10 7 Aspek `[E]` — `analisis_7aspek`
Kolom teks: `aspek_manfaat, aspek_teknis, aspek_manajemen, aspek_pemasaran, aspek_keuangan, aspek_jaminan, kesimpulan, usul`. (Naratif analis.)

## 7.11 Ringkasan endpoint analisis `[E]`
| Method | Endpoint | Bagian |
|---|---|---|
| GET/PUT | `.../analisis/neraca` | Neraca |
| GET/PUT | `.../analisis/laba-rugi` | Laba rugi |
| GET | `.../analisis/kapasitas` | Kapasitas (baca) |
| POST | `.../analisis/kapasitas/compute` | Kapasitas (hitung & simpan) |
| GET | `.../analisis/scoring` | 5C (baca) |
| POST | `.../analisis/scoring/compute` | 5C (hitung & simpan) |
| GET | `.../analisis/red-flags` | Red flags |
| POST | `.../analisis/rekomendasi` | Rekomendasi |

## 7.12 Catatan & gap
- **Definisi IDIR/DSR & komponen penghasilan** = `[K]` (D-11); golden C-002 konflik (Bab 20).
- Band & bobot 5C seeder bertanda **`skor_band_draft=true`** → DRAFT `[K]` (D-02).
- DSR total (angsuran eksisting SLIK otomatis) belum `[Rencana]` (T2).
- Presisi: kolom `decimal` di DB, tetapi kalkulasi PHP di `float` `[E]`; target Money/BIGINT `[K]`.
