# Bab 25 — Analisis Fungsi: Komparasi Master Excel vs Web (Lengkap & Teknis)

> **Ringkas:** bab ini membandingkan fungsi workbook analisa kredit Excel (sumber operasional lama) dengan aplikasi web BPR yang berjalan saat ini. Fokusnya adalah **kesesuaian fungsi**: apa yang sudah setara, apa yang setara di lapis kalkulasi/API tetapi belum ada UI, dan apa yang belum ada. Label `[E]` = terverifikasi dari file kode/tes/perintah; `[K]` = butuh keputusan/verifikasi manusia; `[A]` = usulan; `[?]` = belum dibaca/ditelusuri penuh.

<details>
<summary><b>Cara membaca matriks (klik)</b></summary>

- **Ada (UI)**: fungsi dapat dijalankan pengguna lewat web.
- **Ada (API/Service)**: kalkulasi/penyimpanan ada di backend, tetapi belum ada layar web khusus.
- **Sebagian**: fungsi ada namun cakupannya belum setara workbook.
- **Belum ada**: belum diimplementasikan.
- Angka kebijakan di workbook adalah **nilai contoh/template**; nilai web mengacu ke `ref_parameter_kebijakan` ACTIVE dan referensi DB `[E]`.
</details>

## 25.1 Tujuan, ruang lingkup, dan sumber bukti

### Tujuan
1. Menjadi acuan analisis fungsi saat migrasi dari Excel ke web.
2. Menjadi dasar *acceptance* fungsional: modul mana yang sudah dapat menggantikan sheet Excel.
3. Mengidentifikasi gap teknis yang masih menghalangi penggantian penuh workbook.

### Sumber bukti `[E]`
| Sumber | Lokasi | Catatan |
|---|---|---|
| Master Excel Modal Kerja | `APP BPR/MASTER_Analisa_Kredit_MODAL_KERJA_BPR_V2_POJK2024_FIXED_F4_V12.xlsx` | Diekstrak ke Markdown; 15 sheet terbaca |
| Master Excel Grace Period | `APP BPR/MASTER_Analisis_Kredit_Grace_Period_V2_POJK2024_FIXED_F4_V12.xlsx` | Varian produk; struktur detail belum dibaca sheet-by-sheet `[?]` |
| Master Excel TPP | `APP BPR/ANALISIS EDDIY (ut kredit TPP).xlsx` | Contoh kasus TPP; struktur detail belum dibaca sheet-by-sheet `[?]` |
| Kode web | `APP BPR/loan-analytic-bpr/` (branch `main`, sesi 2026-11-06) | Controller, service, model, migrasi, route, tes |
| Gate verifikasi | `scripts/verify.sh --full`, `ledger:verify`, `hardcode:audit`, `docs:check --strict` | Exit 0 pada pemeriksaan akhir `[E]` |
| Golden/paritas | `tests/Unit/Calculation/GoldenRecomputedParityTest.php` dll. | 29 golden *recomputed*, **0 verified** `[K]` |

## 25.2 Inventaris workbook master Excel (3 file)

### 25.2.1 `MASTER_Analisa_Kredit_MODAL_KERJA_BPR_V2_POJK2024_FIXED_F4_V12.xlsx` `[E]`
| No | Sheet | Fungsi utama | Klasifikasi cetak (dari workbook) |
|---|---|---|---|
| 0 | Cover & Dashboard | Identitas, key metrics, red flags, navigasi, rekomendasi otomatis | Wajib |
| 1 | Profil Debitur | Data pribadi, pasangan, aset/harta, siklus usaha | Wajib |
| 2 | Data Kredit & Asumsi | Permohonan, parameter kebijakan, referensi agunan POJK, SLIK debitur/pasangan, riwayat internal | Wajib |
| 3 | Neraca & Laba Rugi | Neraca proforma + laba rugi proforma (balance cek) | Wajib |
| 4 | Rasio Keuangan | Likuiditas, solvabilitas, profitabilitas | Opsional (kertas kerja) |
| 5 | Modal Kerja | Cash Turnover + Working Capital Days + gap modal kerja | Opsional |
| 6 | Kapasitas Bayar & DSCR | RPC, skenario diajukan/direkomendasikan, DSCR, plafon maks, EIR transparansi | Wajib |
| 7 | Sensitivitas | 6 skenario stress omzet/biaya | Opsional |
| 8 | Penilaian 5C | Skor/bobot 5C, rating, override SLIK | Wajib |
| 9 | Rekomendasi & Keputusan | Narasi, jaminan, keputusan komite/tanda tangan | Wajib |
| 10 | Dokumentasi Jaminan | Foto jaminan (slot manual) | Wajib |
| 11 | Surat Penyerahan Jaminan | Draf surat/kuasa jaminan | Wajib |
| 12 | Perjanjian Kredit | Draf perjanjian kredit | Wajib |
| 13 | Perhitungan Kredit | Angsuran/jadwal, biaya, EIR, jadwal sampai lunas | Wajib |
| 14 | Checklist Dok & Verifikasi | Dokumen pemohon/agunan, verifikasi lapangan, rekomendasi petugas | Wajib |

### 25.2.2 Varian workbook `[E]` nama file, `[?]` isi detail
- `MASTER_Analisis_Kredit_Grace_Period_V2_POJK2024_FIXED_F4_V12.xlsx`: varian grace period.
- `ANALISIS EDDIY (ut kredit TPP).xlsx`: contoh kasus kredit TPP.

> Catatan: web sudah mendukung sistem suku bunga Flat/Anuitas/Grace dan produk TPP `[E]`; kesesuaian field per sheet varian perlu verifikasi terpisah.

## 25.3 Peta fungsi sheet Excel → modul web (matriks lengkap)

| Domain | Fungsi Excel (sheet) | Web UI | Web API/Service (komponen representatif) | Tabel/model inti | Status | Keterangan teknis |
|---|---|---|---|---|---|---|
| Ringkasan | Cover & Dashboard (0): identitas, key metrics, red flags, rekomendasi | `/dashboard` (KPI pipeline, workload) | `DashboardService` | `permohonan_kredit`, agregasi | **Sebagian** | Dashboard web fokus pipeline; panel red-flag/DSCR/LTV komprehensif ala Excel belum setara |
| Nasabah | Profil debitur, pasangan, alamat, pekerjaan (1) | Wizard Debitur (3 langkah), `/debitur/*` | `DebiturController` (API v1) | `debitur`, `debitur_pasangan`, `debitur_usaha` | **Ada** | Data inti tersedia |
| Aset/Agunan | Tabel aset + kategori pengikatan POJK + nilai diperhitungkan (1,2) | Sebagian pada alur permohonan/agunan | `KreditAgunan`, referensi POJK | `debitur_aset`, `kredit_agunan`, `ref_kategori_agunan_pojk` | **Sebagian** | UI input aset/penilaian belum selengkap tabel Excel |
| Kredit & asumsi | Permohonan, plafon/tenor/rate, sistem bunga, parameter, SLIK, riwayat internal (2) | Wizard Permohonan, SLIK review-first, `/pengaturan` | `PermohonanController`, `SlikController`, `Setting*` | `permohonan_kredit`, `permohonan_slik_*`, `permohonan_riwayat_internal`, `ref_parameter_kebijakan` | **Ada** | Parameter terkonfigurasi; kebijakan ACTIVE/DRAFT perlu keputusan `[K]` |
| Neraca & LR | Neraca proforma + laba rugi proforma (3) | ❌ | `Api/V1/AnalisisController` neraca/laba-rugi | `analisis_neraca`, `analisis_laba_rugi` | **API saja** | Kolom eksplisit (menghindari SUMIF/plug Excel) `[E]` |
| Rasio | Likuiditas/solvabilitas/profitabilitas (4) | ❌ | `RasioKeuanganCalculator`, endpoint rasio | `analisis_rasio` | **API saja** | Termasuk current/quick/cash, D/A, D/E, E/A, NPM, ROA/ROE |
| Modal kerja | Cash Turnover + WC Days + gap (5) | ❌ | `ModalKerjaCalculator` | `analisis_modal_kerja` | **API saja** | Konversi siklus usaha ke hari/basis analisis |
| Kapasitas & DSCR | RPC, DSCR, plafon maks, skenario diajukan/direkomendasi, EIR (6) | Simulator `/simulasi` (jadwal/EIR) | `KapasitasBayar*`, `AngsuranCalculator`, `SimulationService` | `analisis_kapasitas_bayar`, `simulasi_kredit` | **Sebagian** | Kalkulasi DSCR/RPC/plafon maks ada; layar analisa kapasitas ala Sheet 6 belum UI |
| Sensitivitas | 6 skenario stress (7) | ❌ | `AnalisisSensitivitas`, `RedFlagEvaluator` | `analisis_sensitivitas`, `analisis_red_flags` | **API saja** | Skenario omzet/biaya mengikuti parameter |
| 5C & rekomendasi | Skor 5C, override SLIK, narasi/rekomendasi (8,9) | `/approval` (keputusan berjenjang, SoD) | `Scoring5C`, `RekomendasiService` | `analisis_scoring_5c`, `analisis_rekomendasi`, `approval_riwayat` | **Sebagian** | Mesin scoring/rekomendasi ada; UI penilaian 5C/narasi belum lengkap |
| Dokumen jaminan | Foto jaminan (10) | ❌ upload khusus | — | (belum modul foto agunan khusus) | **Belum ada** | Slot foto Excel manual; web belum punya UI upload foto agunan |
| Dokumen hukum | Penyerahan jaminan, perjanjian (11,12) | `/dokumen` generate/sign | `DocumentService`, `DocumentGeneratorRegistry`, `Doc11/Doc04` | `dokumen`, `ref_template_dokumen`, `signature_log` | **Ada** | 17 generator total; BR-07 template APPROVED |
| Jadwal & biaya | Perhitungan kredit, biaya, jadwal sampai lunas (13) | `/simulasi`, `/kredit/cairkan/{permohonan}`, `/kredit/{kredit}` | `KreditService`, `PembayaranService`, `AngsuranCalculator` | `kredit`, `jadwal_angsuran`, `biaya_kredit`, `pembayaran_angsuran` | **Ada** | Pencairan UI baru ditambahkan sesi ini |
| Checklist & verifikasi | Checklist dokumen + verifikasi lapangan (14) | `/pengaturan` checklist, bar kelengkapan | `RefChecklistDokumen`, `ChecklistStatus` | `ref_checklist_dokumen`, `checklist_status` | **Sebagian** | Checklist ada; verifikasi lapangan/foto/bukti belum UI |
| Tata kelola (di luar sheet) | — | Auth/RBAC, pengaturan versioned, ledger, import, audit | middleware permission, `SettingRevisionService`, `LedgerService` | `users/roles/permissions`, `ledger`, `audit_log`, `setting_revisions`, `import_*` | **Lebih dari Excel** | Kontrol integritas & SoD tidak dimodelkan penuh di workbook |

## 25.4 Komparasi parameter kebijakan & referensi POJK

### 25.4.1 Parameter kebijakan (contoh workbook vs ACTIVE web) `[E]`
| Parameter | Workbook (contoh) | Web `ref_parameter_kebijakan` ACTIVE | Status |
|---|---:|---:|---|
| Margin kapasitas bayar | 50% | 0.5 | Setara |
| Minimum DSCR | 1.00x | 1.0 | Setara |
| DSCR ideal | 1.25x | 1.25 | Setara |
| Maksimum LTV | 70% | 0.7 | Setara |
| Hari kerja/tahun | 360 | 360 | Setara |
| Sensitivitas omzet ringan/berat | 10% / 20% | 0.10 / 0.20 | Setara |
| Sensitivitas kenaikan biaya | 10% | 0.10 | Setara |
| Biaya provisi/admin/survey default | 1% / 1% / 2% | 0.01 / 0.01 / 0.02 | Setara |
| Bobot 5C (Character/Capacity/Capital/Collateral/Condition) | 25/30/15/20/10 | tersimpan di parameter | Setara secara konsep |

> **Keputusan `[K]`:** nilai ACTIVE (margin 0.5, idir 0.35, ltv 0.7) tidak diubah sesi ini. Dokumen best-judgment menyebut DRAFT berbeda (0.80/0.80/0.80) `[E]/[K]`; keputusan final mengikuti D-07/D-11.

### 25.4.2 Kategori agunan POJK 1/2024 `[E]`
Workbook memuat tabel persentase nilai agunan (Pasal 17/20, contoh: tunai 100%, emas 85%, HT/Fidusia 80%, resi gudang bertingkat, hingga 0%). Web menyimpan referensi di `ref_kategori_agunan_pojk` (13 baris aktif, `persen_max`, `pasal_pojk`, `sumber`, `is_active`) dan dapat dikelola lewat `/pengaturan/COLLATERAL_CATEGORY` dengan alur versioning.

## 25.5 Komparasi kalkulasi inti (rumus/kontrak teknis)

| Kalkulasi | Kontrak Excel | Kontrak Web (service/test) `[E]` | Kesesuaian |
|---|---|---|---|
| Neraca balance | Rugi/laba ditahan & ekuitas penyeimbang; cek selisih | Kolom eksplisit + `selisih_cek` (tidak di-plug) | **Setara tujuan, lebih ketat** |
| Rasio likuiditas/solvabilitas/profitabilitas | Formula Sheet 4 | `RasioKeuanganCalculator`, rasio 4 desimal; pembagi ≤0 → 0.0 | **Setara** (perlu verifikasi angka kasus nyata `[K]`) |
| Modal kerja | Cash Turnover & Working Capital Days, gap | `ModalKerjaCalculator` (perputaran kas, WCD, rata-rata, gap) | **Setara** |
| Angsuran Flat | Bunga dari plafon awal | `AngsuranCalculator` flat | **Setara** (golden recomputed) |
| Angsuran Anuitas | Angsuran tetap, komposisi berubah | `AngsuranCalculator` anuitas (PMT/faktor PV) | **Setara** |
| Grace period | Varian workbook | Didukung `SimulationService`/engine | **Setara konsep** `[?]` validasi detail sheet |
| Plafon maksimum | RPC × faktor PV (anuitas) / formula flat | Service kapasitas/plafon maks | **Setara** |
| DSCR | RPC / jumlah angsuran | `Dscr*`/kapasitas; ambang parameter | **Setara** |
| LTV | Plafon vs nilai jaminan diperhitungkan | `Ltv*` + kategori POJK PPKA | **Setara** |
| Sensitivitas | 6 skenario omzet/biaya | `AnalisisSensitivitas` + parameter | **Setara** |
| 5C | Bobot 25/30/15/20/10 + override SLIK | `Scoring5C`, `RekomendasiService` | **Setara konsep** `[K]` band final |
| EIR/transparansi | Suku bunga efektif setara | IRR bisection numerik; **tanpa** faktor aproksimasi 1.81×/0.87× | **Lebih presisi numerik** `[E]` |

## 25.6 Komparasi dokumen, workflow, dan integritas

| Aspek | Excel | Web | Dampak |
|---|---|---|---|
| Jumlah dokumen utama | Draf/lembar cetak dalam workbook | 17 generator (`Doc01..Doc17`) + template Blade | Web terstruktur & dapat di-generate per permohonan |
| Persetujuan template | Tidak eksplisit | BR-07: generate hanya jika template `APPROVED` | Kontrol perubahan dokumen |
| Tanda tangan | Kolom tanda tangan cetak | `signature_log` + status DITANDATANGANI + ledger event | Jejak digital |
| Approval | Tanda tangan komite pada sheet | Approval matrix, SoD, riwayat level, notifikasi | Kontrol kewenangan |
| Pencairan | Tidak dimodelkan sebagai transaksi sistem | UI `/kredit/cairkan` → `KreditService::cairkan` (kredit+jadwal+memo+ledger) | End-to-end operasional |
| Audit & integritas | Tidak ada | `audit_log` immutable, `ledger` append-only + `ledger:verify` | Bukti perubahan & rantai hash |
| Multi-cabang | File terpisah per kasus | Cabang-scoped RBAC, 3 cabang pilot | Isolasi data `[E]` |
| Pengaturan | Sel parameter di sheet | `/pengaturan` draft→submit→approve→apply, effective date | Tata kelola perubahan kebijakan |

## 25.7 Gap teknis, risiko, dan ketergantungan keputusan `[K]`

### Gap teknis
1. **UI analisa Sheet 3–9 belum ada** (Neraca/LR, Rasio, Modal Kerja, Kapasitas/DSCR, Sensitivitas, 5C, Narasi Rekomendasi) — kalkulasi sudah ada di API/service.
2. **Upload foto agunan** (Sheet 10) dan **verifikasi lapangan** (Sheet 14) belum ada UI.
3. **Import debitur CSV** belum ada; implementasi saat ini hanya **pratinjau pembayaran CSV**. Profil `DEBITUR_XLSX` tersedia di skema/import profil.
4. **Manual pengguna** belum mencakup 4 UI baru: pencairan, dokumen, ledger, import.
5. **Varian Grace Period & TPP**: dukungan kalkulasi ada secara umum `[E]`, tetapi kesesuaian field/workflow per workbook varian belum diverifikasi penuh `[?]`.

### Risiko/ketergantungan keputusan `[K]`
- Policy ACTIVE (0.5/0.35/0.7) vs DRAFT best-judgment (0.80/0.80/0.80) — D-07/D-11.
- Matriks role pencairan/tanda tangan (saat ini ADMIN) — D-12.
- Retensi PII/SLIK/ledger — D-09.
- Pengesahan template produksi (APPROVED saat ini label pilot dummy) — `[K]`.
- Golden recompute 29, **0 verified** — RAT-02.
- Rotasi PAT di 3 file `commandcode bpr`.

## 25.8 Rekomendasi prioritas penutupan gap `[A]`

| Prioritas | Gap | Usulan implementasi | Prasyarat |
|---|---|---|---|
| P1 | UI analisa Neraca/LR → Rasio → Modal Kerja | Layar Livewire/API-backed per permohonan, simpan ke `analisis_*` | Mapping field Excel final |
| P1 | UI Kapasitas/DSCR + Sensitivitas + 5C + Rekomendasi | Rangkai pipeline analisa, tampilkan RPC/DSCR/plafon maks/red flag/rating | Parameter `[K]` dikunci |
| P2 | Foto agunan + verifikasi lapangan | Upload storage privat + checklist verifikasi | Kebijakan penyimpanan PII/dokumen `[K]` |
| P2 | Import debitur (XLSX/CSV) | Reuse `import_profil`/batch/baris + preview/commit terpisah | Profil mapping disetujui |
| P3 | Paritas golden terverifikasi | Isi/recompute golden dari sumber sah + tanda tangan | Sumber resmi `[K]` |
| P3 | Update manual & materi UAT | Sinkron Bab 14/17/25 + skenario UI baru | Setelah UI analisa stabil |

## 25.9 Jejak verifikasi (perintah bukti)

```bash
# Gate kualitas & integritas
cd "APP BPR/loan-analytic-bpr"
bash scripts/verify.sh --full
php artisan ledger:verify
php artisan hardcode:audit
php artisan docs:check --strict

# Smoke pilot (HTTPS Herd)
curl -f https://loan-analytic-bpr.test/health

# Bukti sesi (read-only)
git log --oneline -n 12
git diff 5048d55..HEAD --stat
```

Hasil pemeriksaan akhir sesi 2026-11-06: `verify --full` exit 0, `ledger:verify OK — 20 baris`, `hardcode:audit 0 pelanggaran`, `docs:check 0 error`, `health 200` `[E]`.
