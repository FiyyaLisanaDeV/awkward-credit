# Bab 6 — Permohonan Kredit, Produk, Workflow, dan SLIK

## 6.1 Tujuan modul
Mengelola permohonan kredit: data permohonan (plafon, rate, tenor, sistem suku bunga, sektor, tujuan), nomor permohonan otomatis, status workflow, serta **SLIK** (permintaan + fasilitas debitur/pasangan) yang menjadi bahan analisis.

## 6.2 Model & tabel `[E]`
| Model | Tabel | Isi |
|---|---|---|
| `PermohonanKredit` | `permohonan_kredit` | Data permohonan + status + relasi ke parameter kebijakan |
| `Produk` | `produk` | Produk kredit (tipe: TPP/MODAL_KERJA/GRACE_PERIOD) |
| `SlikPermintaan` | `slik_permintaan` | Permintaan SLIK (tanggal, jenis, tujuan, file, hash, total) |
| `PermohonanSlikDebitur` | `permohonan_slik_debitur` | Fasilitas SLIK debitur (baris per fasilitas) |
| `PermohonanSlikPasangan` | `permohonan_slik_pasangan` | Fasilitas SLIK pasangan |
| `PermohonanRiwayatInternal` | `permohonan_riwayat_internal` | Riwayat internal (pinjaman sebelumnya) |

## 6.3 Struktur `permohonan_kredit` `[E]`
Kolom: `cabang_id`, `no_permohonan(50) unique`, `debitur_id`, `produk_id`, `sektor`, `tujuan_penggunaan(200)`, `plafon_diajukan(15,2)`, `plafon_rekomendasi(15,2) nullable`, `suku_bunga_tahunan(5,4)`, `tenor_diajukan`, `tenor_rekomendasi`, `sistem_suku_bunga(20)`, `kegunaan_pinjaman(text)`, `pinjaman_ke(default 1)`, `sumber_pelunasan_balloon`, `perkiraan_penerimaan_balloon(15,2)`, `sumber_pembayaran_angsuran`, `tanggal_target_realisasi`, `prioritas(default NORMAL)`, `status(default DRAFT)`, `ao_id`, `parameter_kebijakan_id`, `tanggal_permohonan`, `catatan_ao`, `kelengkapan_persen(default 0)`, timestamps, softDeletes.

**Invariant DB `[E]`:**
- CHECK enum `status`, `sistem_suku_bunga`.
- **BR-04 CHECK**: bila `sistem_suku_bunga = 'Grace Period'` maka `sumber_pelunasan_balloon` & `perkiraan_penerimaan_balloon` **wajib NOT NULL**.
- CHECK `plafon_diajukan > 0 AND (plafon_rekomendasi IS NULL OR plafon_rekomendasi <= plafon_diajukan)`.
- CHECK `suku_bunga_tahunan > 0 AND <= 0.5`.
- CHECK tenor 1–360 (diajukan & rekomendasi).
- CHECK `pinjaman_ke >= 1`, `kelengkapan_persen` 0–100.
- Index parsial `permohonan_cabang_status_idx (cabang_id, status, tanggal_permohonan DESC)`.

## 6.4 Status workflow `[E]` — `StatusPermohonan`
`DRAFT → SUBMITTED → ANALYZED → RECOMMENDED → APPROVED / REJECTED / RETURNED → SIGNED → ACTIVE → CLOSED / NPL`.
Transisi yang **diimplementasikan** saat ini `[E]`:
- `DRAFT → SUBMITTED` (`PermohonanService::submit`, validasi BR-04 + BR-08).
- `SUBMITTED/ANALYZED/RECOMMENDED → APPROVED/RECOMMENDED` (approval bertingkat, Bab 10).
- `→ REJECTED`, `→ RETURNED` (approval, Bab 10).
Status `ANALYZED/SIGNED/ACTIVE/CLOSED/NPL` disediakan enum; transisi dokumen/akad `[Rencana]` fase lanjut.

## 6.5 Nomor permohonan `[E]` — `Workflow/NomorService`
Pola dari `config('loan.nomor.permohonan')` = `{cabang}/{tahun}/{urut}`.
`permohonan(Cabang)`:
- `prefix = kode_cabang/tahun/`.
- Hitung jumlah permohonan dengan `no_permohonan LIKE prefix%` (`lockForUpdate()` untuk serialisasi).
- `urut = count+1`, padding `urut_padding` (4) → mis. `BMB/2026/0001`.

## 6.6 Alur web: `PermohonanWizard` (Livewire) `[E]`
Komponen `App\Livewire\PermohonanWizard` — **5 langkah**:
- **Step 1**: `debitur_id`, `produk_id`.
- **Step 2**: `plafon_diajukan, suku_bunga_tahunan, tenor_diajukan, sistem_suku_bunga, tanggal_permohonan, tanggal_target_realisasi, prioritas, kegunaan_pinjaman` → setelah validasi, **persist** (buat/ubah draft).
- **Step 3**: `sektor, tujuan_penggunaan, pinjaman_ke, sumber_pembayaran_angsuran, sumber_pelunasan_balloon, perkiraan_penerimaan_balloon, catatan_ao` → persist; lalu muat `slikRows` dari draft.
- **Step 4**: input SLIK (grid) — tambah/hapus baris, atau **paste** (lihat 6.7).
- **Step 5**: review/submit.

Fitur: `saveDraft()` (persist + flash), `submit()` (persist → `PermohonanService::submit`; bila `BusinessRuleViolation`, tampilkan error), `addSlikRow()`, `removeSlikRow()`, `parsePaste()`.

## 6.7 Parse paste SLIK `[E]` — `parsePaste()`
- Pisah baris per newline; kolom per `\t`, `;`, atau `|`.
- Butuh ≥5 kolom: `nama_lembaga`, `jenis_fasilitas`, `plafon`, `baki_debet`, `kolektibilitas` (kode → `kolektibilitas_id` via `RefKolektibilitas`), opsional `angsuran_bulanan`.
- Bila tak ada baris valid → error `permohonan.paste_invalid`.

## 6.8 SLIK — tiga jalur `[E]` (ADR-022)
1. **Excel/CSV** (profil `SLIK_XLSX_IDEB`).
2. **PDF** (`smalot/pdfparser`; profil `SLIK_PDF_IDEB`).
3. **Tempel-teks** (paste grid; profil `SLIK_TEKS`).
Jalur manual (grid) tersedia di wizard. Adapter/parser penuh = fase 12B `[Rencana]`.

## 6.9 Struktur SLIK `[E]`
- `slik_permintaan`: `permohonan_id`, `debitur_id`, `tanggal_permintaan`, `jenis` (enum `JenisPermintaanSlik`), `tujuan`, `no_referensi`, `operator_id`, `file_path`, `file_hash(64)`, `sumber` (enum `SumberSlik`), `jumlah_fasilitas`, `total_plafon`, `total_baki`, `kualitas_terburuk(1–5)`, `catatan`. CHECK numerik ≥0, kualitas 1–5.
- `permohonan_slik_debitur`: `urutan` (unique per permohonan, partial), `nama_lembaga`, `jenis_fasilitas`, `jenis_penggunaan`, `no_fasilitas`, `plafon`, `baki_debet`, `suku_bunga(0–1)`, `jangka_waktu_bulan`, `angsuran_bulanan`, `kolektibilitas_id`, `kondisi`, `tanggal_mulai`, `tanggal_tunggakan`, `hari_tunggakan`, `frekuensi_tunggakan_12m`, `tunggakan_pokok/bunga`, `jenis_jaminan`, `nilai_jaminan`, `penjamin`, `tanggal_data`, `catatan`. CHECK nilai ≥0.
- `permohonan_slik_pasangan`: subset serupa (tanpa angsuran/rate detail).
- **ADR-016**: `baki_debet ≤ plafon` = **peringatan**, bukan CHECK keras (data SLIK bisa anomali). Validasi/flag di parser `[Rencana]`.

## 6.10 API permohonan & SLIK `[E]`
| Method | Endpoint | Perilaku |
|---|---|---|
| GET | `/api/v1/permohonan` | Daftar cabang; filter `status`; paginate |
| POST | `/api/v1/permohonan` | `StorePermohonanRequest`; cek scope debitur; `PermohonanService::create` → 201 |
| GET | `/api/v1/permohonan/{permohonan}` | Detail + relasi (`debitur, produk, slikDebitur.kolektibilitas, kapasitasBayar, scoring5c, rekomendasi`) |
| PUT/PATCH | `/api/v1/permohonan/{permohonan}` | Update (hanya `DRAFT`) |
| POST | `/api/v1/permohonan/{permohonan}/submit` | Submit (BR-04, BR-08) |
| GET | `/api/v1/permohonan/{permohonan}/slik` | `{permintaan, fasilitas}` |
| POST | `/api/v1/permohonan/{permohonan}/slik` | Tambah fasilitas (urutan = max+1) |
| DELETE | `/api/v1/slik/{slik}` | Hapus fasilitas → 204 |

## 6.11 Validasi permohonan `[E]` — `PermohonanRules`
`plafon_diajukan` gt:0; `plafon_rekomendasi` ≤ plafon_diajukan; `suku_bunga_tahunan` gt:0 lte:0.5; tenor 1–360; `sistem_suku_bunga` enum; `kegunaan_pinjaman` required; `sumber_pelunasan_balloon`/`perkiraan_penerimaan_balloon` **required_if Grace Period**; `pinjaman_ke` ≥1.

## 6.12 Aturan bisnis terkait (ringkas)
- **BR-04** (HARD): Grace Period wajib sumber+perkiraan balloon (juga CHECK DB) — Bab 9.
- **BR-08** (HARD): permohonan wajib mereferensikan `parameter_kebijakan_id` — diisi otomatis dari parameter ACTIVE saat create `[E]`.
- **BR-01** (HARD): SLIK bermasalah → skor 5C dipaksa 1,0.
- **BR-10/13/15** dst (Bab 9).

## 6.13 Web list/detail `[E]`
`Web/PermohonanController`: `index` (filter `status`, paginate), `create` (view host wizard), `show` (`abort_unless` cabang → load relasi). View `resources/views/permohonan/{index,create,show}.blade.php`.

## 6.14 Catatan & gap
- SLIK: consent formal, validasi anomali (baki>plafon, duplikat, tanggal pull), confidence/provenance = `[K/Rencana]` (D-03).
- DSR total (angsuran eksisting dari SLIK otomatis) belum diimplementasikan `[Rencana]` (T2).
- Model/tipe produk mengikuti `ProdukTipe` (`TPP/MODAL_KERJA/GRACE_PERIOD`).
