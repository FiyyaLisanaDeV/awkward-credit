# Bab 7 — Analisis Kredit: Neraca, Laba Rugi, Rasio, Modal Kerja, Kapasitas, Sensitivitas, 5C, Red Flag, Rekomendasi

> **Ringkas:** modul ini mengubah data permohonan menjadi **bukti kelayakan** (rasio, kapasitas, skor, red flag) lalu **rekomendasi**. Satu permohonan = satu rangkaian baris `analisis_*` dengan `parameter_kebijakan_id` berversi (BR-08). Angka contoh di bab ini **dummy**; ambang/band final berstatus `[K]` sampai D-02/D-11 diputuskan.

<details>
<summary><b>Cara membaca bab ini (klik)</b></summary>

- Tabel dan rumus adalah **kontrak** yang dipakai engine; rujukan file ada di tiap bagian.
- Blok **“Contoh”** memakai angka dummy yang bisa dihitung ulang.
- Label `[E]` = terverifikasi dari kode/test; `[K]` = perlu keputusan/verifikasi; `[Rencana]` = belum ada.
- Untuk status & gap terkini, lihat [Bab 20](Bab-20-Known-Issues-Gap-Rencana).
</details>

## 7.1 Peta alur analisis (permohonan → rekomendasi)
| # | Langkah | Input utama | Engine / tabel | Output | Aturan |
|---|---|---|---|---|---|
| 1 | Neraca & Laba Rugi | saldo akun, pendapatan, biaya | `analisis_neraca`, `analisis_laba_rugi` | total aktiva/pasiva, surplus | BR-08 |
| 2 | Rasio keuangan | neraca & LR | `RasioKeuanganCalculator` → `analisis_rasio` | current/quick/cash, D/A, D/E, E/A, NPM, ROA, ROE | — |
| 3 | Modal kerja | biaya operasional, perputaran, persediaan | `ModalKerjaCalculator` → `analisis_modal_kerja` | kebutuhan MK, gap | BR-10 |
| 4 | Kapasitas bayar | surplus, angsuran, plafon, jaminan | `Idir/Dscr/PlafonMax/Ltv` → `analisis_kapasitas_bayar` | RPC, DSCR, IDIR, LTV, plafon max, balloon | BR-02/03/11/13 |
| 5 | Sensitivitas | delta omzet/biaya | `analisis_sensitivitas` | surplus/RPC/DSCR per skenario | BR-12 |
| 6 | Scoring 5C | SLIK, DSCR, E/A, LTV, condition | `Scoring5CService` → `analisis_scoring_5c` | skor aspek, total, rating | BR-01 |
| 7 | Red flag | seluruh hasil | `RedFlagEvaluator` → `analisis_red_flags` | daftar CRITICAL/WARNING | — |
| 8 | Rekomendasi | rating + plafon max | `RekomendasiService` → `analisis_rekomendasi` | plafon/tenor/rate final + narasi | — |
| 9 | Approval | dokumen & hasil | `ApprovalService` (Bab 10/24) | status permohonan | BR-05/06/07 |

## 7.2 Neraca & Laba Rugi `[E]`
**Neraca** (`analisis_neraca`, satu baris per permohonan): `kas_inventaris, bank_simpanan, piutang, persediaan, sewa_dibayar_dimuka, biaya_dibayar_dimuka, pajak_dibayar_dimuka, investasi_jangka_panjang, tanah_bangunan, kebun, kendaraan, aset_lain, jumlah_aktiva_lancar, jumlah_aktiva_tetap, total_aktiva, hutang_pendek, hutang_pajak, hutang_gaji, hutang_panjang, pinjaman_bank, pinjaman_lain, prive, jumlah_hutang, rugi_laba_ditahan, ekuitas_modal_disetor, jumlah_modal, total_pasiva, selisih_cek, catatan`.

**Laba rugi** (`analisis_laba_rugi`): `penghasilan_pemohon, penghasilan_pasangan_gaji, penghasilan_lain_pemohon, penghasilan_pasangan_tpp, hpp, biaya_listrik_air, biaya_transport, biaya_sewa, biaya_gaji, biaya_pendidikan, biaya_kesehatan, biaya_konsumsi, biaya_cicilan_lain, biaya_telepon, biaya_kebersihan, biaya_lain, total_pendapatan, total_pengeluaran, laba_kotor, penyusutan, pajak, laba_bersih, angsuran_kredit_berjalan, surplus_per_bulan, surplus_6_bulan, jumlah_bulan_berjalan`.

<details>
<summary><b>Kenapa kolom eksplisit, bukan SUMIF? (T6)</b></summary>

Excel lama memakai `SUMIF *Tanah*` dan `*Kebun*` sehingga aset bisa **terhitung ganda** dan ekuitas menjadi “plug”. Di aplikasi, setiap komponen punya kolom sendiri; total dijumlah eksplisit. `selisih_cek` menyimpan selisih neraca (tidak di-plug) agar analis melihat ketidakseimbangan.
</details>

**API:** `GET/PUT /api/v1/permohonan/{id}/analisis/neraca` dan `.../laba-rugi` (`updateOrCreate`; validasi `jumlah_bulan_berjalan` 1–12). **`surplus_per_bulan`** menjadi input kapasitas.

## 7.3 Rasio keuangan `[E]`
`RasioKeuanganCalculator` (pembagi ≤ 0 → `0.0`, pembulatan `Rounding::ratio` 4 desimal):
| Rasio | Rumus |
|---|---|
| `current_ratio` | aktiva lancar / hutang pendek |
| `quick_ratio` | (aktiva lancar − persediaan) / hutang pendek |
| `cash_ratio` | kas & bank / hutang pendek |
| `debt_to_asset` | total hutang / total aktiva |
| `debt_to_equity` | total hutang / ekuitas |
| `equity_to_asset` | ekuitas / total aktiva |
| `net_profit_margin` | laba bersih / pendapatan |
| `roa` / `roe` | laba bersih / total aktiva · laba bersih / ekuitas (annualized) |

`equity_to_asset` dipakai sebagai **Capital** pada 5C (Bab 9/24).

## 7.4 Modal kerja `[E]`
`ModalKerjaCalculator::hitung(...)`:
- `perputaran_kas_per_tahun = hari_kerja / periode_perputaran` (0 bila periode 0)
- `kebutuhan_cash_turnover = biaya_operasional_tahunan / perputaran`
- `kebutuhan_wcd = (pembelian_bulanan / 30) × hari_persediaan`
- `rata_rata = (cash_turnover + wcd) / 2`
- `gap = max(0, rata_rata − mk_tersedia)`

<details>
<summary><b>Contoh perhitungan modal kerja (dummy)</b></summary>

Biaya operasional Rp120.000.000/tahun · periode perputaran 60 hari · hari kerja 360 · pembelian Rp10.000.000/bulan · persediaan 15 hari · MK tersedia Rp10.000.000.

- perputaran = 360/60 = **6×/tahun**
- cash turnover = 120.000.000/6 = **Rp20.000.000**
- WCD = (10.000.000/30)×15 = **Rp5.000.000**
- rata-rata kebutuhan = (20.000.000+5.000.000)/2 = **Rp12.500.000**
- gap = 12.500.000 − 10.000.000 = **Rp2.500.000**

Hasil ini yang dibandingkan BR-10 (kewajaran plafon) dan menjadi bahan mitigasi.
</details>

## 7.5 Kapasitas bayar: RPC, DSCR, IDIR, LTV, plafon maksimum `[E]`
| Istilah | Rumus / kode | Catatan |
|---|---|---|
| **RPC** | `surplus_bulanan × margin_kapasitas_bayar` (`DscrCalculator::rpc`) | margin ACTIVE seeder = 0,5 `[K D-11]` |
| **DSCR** | `RPC / angsuran_bulanan` (`DscrCalculator::dscr`) | minimum ACTIVE = 1,0; `≥` layak |
| **IDIR** | `angsuran_bulanan / penghasilan_bersih` (`IdirCalculator`) | ambang ACTIVE `idir_threshold_tpp` = 0,35 `[K D-11]`; `≤` layak |
| **LTV** | `plafon / nilai_jaminan` (`LtvCalculator`) | maksimum ACTIVE = 0,7; `>` melanggar |
| **Plafon max flat** | `RPC / (1/n + r/12)` (`PlafonMaxCalculator::flat`) | n=tenor, r=rate/tahun |
| **Plafon max anuitas** | `RPC × (1 − (1+i)^−n) / i` (`PlafonMaxCalculator::anuitas`) | `i = r/12` |
| **Plafon max grace** | `RPC / (r/12)` (`PlafonMaxCalculator::grace`) | batas bunga periodik; balloon terpisah |

<details>
<summary><b>Contoh A — kasus C-002 (dummy): IDIR tinggi</b></summary>

Net income Rp2.948.080 · angsuran Rp2.533.333 (flat 80jt/18%/60).
- IDIR = 2.533.333 / 2.948.080 = **0,8593 → 85,93%**.
- Ambang ACTIVE 0,35 → **BR-11 (SOFT) gagal**; dengan DRAFT 0,80 pun tetap di atas.
- RPC (margin 0,5) = 2.948.080 × 0,5 = **Rp1.474.040**; DSCR = 1.474.040 / 2.533.333 = **0,582 → BR-02 (HARD) gagal**.
- Kesimpulan: kasus ini **tidak layak**; IDIR/DSR adalah **hard/soft gate**, bukan sekadar angka.
</details>

<details>
<summary><b>Contoh B — kasus aman (dummy)</b></summary>

Net income Rp5.000.000 · angsuran Rp1.500.000 · margin 0,5 · plafon 80jt · jaminan 120jt.
- RPC = **Rp2.500.000**; DSCR = 2.500.000/1.500.000 = **1,667** (≥1,0 layak).
- IDIR = 1.500.000/5.000.000 = **30%** (≤0,35 layak).
- LTV = 80.000.000/120.000.000 = **0,6667** (≤0,7 layak).
- Plafon max flat (n=60, r=18%, RPC 2,5jt) = **Rp78.947.368**; anuitas = **Rp98.450.672**.
</details>

**API:** `GET .../analisis/kapasitas`, `POST .../analisis/kapasitas/compute`. **UI:** Bab 14.

## 7.6 Sensitivitas `[E]`
Baris per skenario (`urutan`, `nama_skenario`, `delta_omzet`, `delta_biaya`, `surplus_baru`, `rpc_baru`, `dscr`, `status_layak`). Policy ACTIVE: omzet ringan 0,10 · berat 0,20 · biaya 0,10. Dipakai **BR-12 (SOFT)** dan red flag `STRESS_TIDAK_LAYAK`.

## 7.7 Scoring 5C `[E]`
`Scoring5CService::hitung(permohonan, skorCondition 1–5)`:
1. Hitung `kol3` (SLIK kode ≥3) dan `macet` (kode =5).
2. `bermasalah` = `kol3 ≥ threshold_slik_kol3` **atau** `macet ≥ threshold_slik_macet`.
3. Skor aspek dari band policy: Character (`skor_band_slik`), Capacity (`skor_band_dscr`), Capital (`skor_band_equity_asset`), Collateral (`skor_band_ltv`), Condition (input).
4. `skor_tertimbang = Σ(skor × bobot)`; bobot ACTIVE 0,25/0,30/0,15/0,20/0,10.
5. `skor_total = bermasalah ? 1,0 : skor_tertimbang` (**BR-01 override**).
6. Rating: `bermasalah → TIDAK LAYAK`; selain itu bandingkan dengan `rating_sangat_layak_min` 4,0 · `rating_layak_min` 3,0 · `rating_bersyarat_min` 2,0.

<details>
<summary><b>Contoh scoring (dummy)</b></summary>

Skor: Character 4 · Capacity 3 · Capital 4 · Collateral 3 · Condition 3.
Bobot: 0,25/0,30/0,15/0,20/0,10.
`skor_tertimbang = 4(0,25)+3(0,30)+4(0,15)+3(0,20)+3(0,10) = 1,00+0,90+0,60+0,60+0,30 = **3,40**` → **LAYAK** (≥3,0 dan <4,0).

Jika SLIK bermasalah (kol3 ≥ threshold), `skor_total` dipaksa **1,0** → **TIDAK LAYAK** walau skor aspek lain tinggi (BR-01).
</details>

**API:** `GET .../analisis/scoring`, `POST .../analisis/scoring/compute` (validasi `skor_condition` 1–5; simpan + ledger `ANALISIS_DISIMPAN`).

## 7.8 Red flag `[E]` — `RedFlagEvaluator`
| Kode | Level | Kondisi |
|---|---|---|
| `SLIK_BERMASALAH` | CRITICAL | kol3/macet ≥ threshold |
| `DSCR_RENDAH` | WARNING | `dscr_rekomendasi < minimum_dscr` |
| `LTV_TINGGI` | WARNING | `ltv > maksimum_ltv` |
| `BALLOON_KURANG` | CRITICAL | `rasio_kecukupan_balloon < rasio_balloon_perhatian` |
| `STRESS_TIDAK_LAYAK` | WARNING | ada sensitivitas `status_layak=false` |
| `CHECKLIST_KURANG` | WARNING | checklist wajib belum lengkap |
| `IDENTITAS_KURANG` | WARNING | debitur null/tanpa tanggal lahir/tanpa no_ktp |

Tabel: `kode`, `level` (`CRITICAL/WARNING/INFO`), `pesan`, `sumber`, `teratasi`, `mitigasi`, `pemilik_mitigasi`, `tenggat`; unique `(permohonan_id, kode)`; index parsial CRITICAL.

## 7.9 Rekomendasi `[E]` — `RekomendasiService`
1. `rating → rekomendasi`: SANGAT LAYAK/LAYAK → `DISETUJUI`; LAYAK BERSYARAT → `DISETUJUI DENGAN SYARAT`; TIDAK LAYAK → `DITOLAK`.
2. `plafon_kandidat = min(plafon_rekomendasi|plafon_diajukan, plafon_max_flat)`.
3. `plafon_final = DITOLAK ? 0 : Rounding::money(kandidat)`; `tenor_final`, `rate_final`, `angsuran_final`.
4. `narasi` memuat rating, skor, plafon, tenor, rate.
**API:** `POST .../analisis/rekomendasi` (butuh scoring; jika belum → 422).

## 7.10 7 Aspek `[E]` — `analisis_7aspek`
Naratif analis: `aspek_manfaat, aspek_teknis, aspek_manajemen, aspek_pemasaran, aspek_keuangan, aspek_jaminan, kesimpulan, usul`.

## 7.11 Endpoint & UI
| Method | Endpoint | Bagian |
|---|---|---|
| GET/PUT | `/api/v1/permohonan/{id}/analisis/neraca` | Neraca |
| GET/PUT | `.../analisis/laba-rugi` | Laba rugi |
| GET | `.../analisis/kapasitas` | Kapasitas (baca) |
| POST | `.../analisis/kapasitas/compute` | Kapasitas (hitung & simpan) |
| GET | `.../analisis/scoring` | 5C (baca) |
| POST | `.../analisis/scoring/compute` | 5C (hitung & simpan) |
| GET | `.../analisis/red-flags` | Red flags |
| POST | `.../analisis/rekomendasi` | Rekomendasi |

**UI:** workbench analisis + simulator ada di [Bab 14](Bab-14-UI-Web-Livewire) dan [Bab 8](Bab-08-Perhitungan-Jadwal-EIR-Simulasi).

## 7.12 Uji cepat & bukti (jalankan sendiri)
<details>
<summary><b>Perintah verifikasi</b></summary>

```bash
# kalkulator & golden
php artisan test --filter=Calculation
php artisan test --filter=GoldenRecomputedParityTest

# aturan & scoring
php artisan test --filter=BusinessRuleTest
php artisan test --filter=Scoring5CTest

# seluruh gate
bash scripts/verify.sh --full
```
Berkas kunci: `app/Services/Calculation/*`, `app/Services/Scoring/*`, `app/Services/Rules/Rules/BR0*.php`, `tests/Feature/Calculation/*`.
</details>

## 7.13 Batas & gap `[K]`
- Definisi **IDIR/DSR** dan komponen penghasilan bersih = `[K]` (D-11); golden C-002 konflik (Bab 20).
- **DSR total** (angsuran eksisting SLIK otomatis) belum diimplementasikan `[Rencana/T2]`.
- Band & bobot 5C seeder bertanda `skor_band_draft=true` → **DRAFT** `[K D-02]`.
- Presisi: kolom DB `decimal`, kalkulasi PHP `float`; target Money/BIGINT `[K D-05]`.
- Ambang policy ACTIVE (0,35/0,5/0,7) vs rekomendasi DRAFT (0,80/0,80/0,80) → putuskan lewat D-11/D-07.
