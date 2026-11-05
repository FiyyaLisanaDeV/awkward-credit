# Bab 5 — Debitur, Pasangan, Usaha, Siklus, Aset, dan Pengkinian

## 5.1 Tujuan modul
Mengelola data nasabah (debitur) sebagai entitas pusat yang menampung identitas, kontak/alamat, pekerjaan, pasangan, usaha, siklus usaha, aset/agunan, dan riwayat pengkinian. Debitur menjadi prasyarat permohonan kredit.

## 5.2 Model & tabel `[E]`
| Model | Tabel | Isi |
|---|---|---|
| `Debitur` | `debitur` | Identitas, kontak, alamat, pekerjaan, penghasilan, sumber_data, verifikasi, path foto, soft deletes |
| `DebiturPasangan` | `debitur_pasangan` | Data pasangan (SLIK pasangan bila ada) |
| `DebiturUsaha` | `debitur_usaha` | Data usaha |
| `DebiturSiklusUsaha` | `debitur_siklus_usaha` | Siklus usaha (musiman/pola) |
| `DebiturAset` | `debitur_aset` | Aset/agunan: jenis, kategori POJK, luas/jumlah, taksasi, %diperhitungkan, status kepemilikan |
| `DebiturPengkinian` | `debitur_pengkinian` | Riwayat pengkinian data |

## 5.3 Struktur tabel `debitur` `[E]`
Kolom kunci: `id (uuid)`, `cabang_id`, `nama_ktp(100)`, `nama_ibu_kandung`, `no_ktp(char16)`, `no_kk(char16)`, `npwp`, `jenis_kelamin(char1)`, `status_perkawinan`, `agama`, `pendidikan`, `pekerjaan_utama/jabatan/nama_perusahaan/lama_bekerja_tahun/penghasilan_bulanan`, `email/no_hp/no_wa`, `alamat_jalan/rt/rw/kelurahan/kecamatan/kabupaten/provinsi/kode_pos`, `alamat_tinggal/status_tempat_tinggal/lama_tinggal_tahun/koordinat_rumah`, `jumlah_keluarga/jumlah_tanggungan`, `tempat_lahir/tanggal_lahir`, `sumber_data` (MANUAL/IMPORT/OCR), `verified_by/verified_at`, `foto_ktp_path/foto_debitur_path`, `catatan`, `timestamps`, `softDeletes`.

**Invariant DB `[E]`:**
- CHECK enum: `jenis_kelamin`, `status_perkawinan`, `sumber_data`.
- CHECK `jumlah_keluarga >= 0 AND jumlah_tanggungan >= 0`.
- **Unique partial index**: `debitur_no_ktp_unique ON (no_ktp) WHERE deleted_at IS NULL` — NIK unik antar debitur aktif.

## 5.4 Validasi `[E]` — `Support/Validation/DebiturRules`
Aturan penting: `nama_ktp` required ≤100; `no_ktp` **required digits:16**; `no_kk` nullable digits:16; `jenis_kelamin`&`status_perkawinan` required (enum); `no_hp` required ≤20; alamat inti required (`alamat_jalan/rt/rw/kelurahan/kecamatan/kabupaten/provinsi`); `tempat_lahir` required; **`tanggal_lahir` required date `before:-17 years`** (usia ≥17 tahun). Field pekerjaan/penghasilan/alamat tambahan umumnya nullable.

Rules ini dipakai oleh `StoreDebiturRequest` (API) dan `DebiturWizard` (web) — **satu sumber aturan** (DRY).

## 5.5 Alur web: `DebiturWizard` (Livewire) `[E]`
Komponen `App\Livewire\DebiturWizard` — **wizard 3 langkah**:
- **Step 1** (identitas): `nama_ktp, nama_ibu_kandung, no_ktp, no_kk, npwp, jenis_kelamin, status_perkawinan, agama, pendidikan, tempat_lahir, tanggal_lahir`.
- **Step 2** (kontak/alamat): `no_hp, no_wa, email, alamat_*`, `status_tempat_tinggal, lama_tinggal_tahun, koordinat_rumah, jumlah_keluarga, jumlah_tanggungan`.
- **Step 3** (pekerjaan): `pekerjaan_utama, jabatan, nama_perusahaan, lama_bekerja_tahun, penghasilan_bulanan`.
- `next()` memvalidasi **hanya field langkah aktif** (`Arr::only(DebiturRules::rules(), fields[step])`); `previous()` mundur; `save()` validasi penuh → `DebiturService::create($payload, auth()->user())` → flash `debitur.created` → redirect `debitur.show`.
- Nilai string kosong dikonversi ke `null` sebelum simpan (`payload()`).

## 5.6 Alur API debitur `[E]`
`Api/V1/DebiturController` (semua di `auth:sanctum`, scope cabang):
| Method | Endpoint | Perilaku |
|---|---|---|
| GET | `/debitur` | Daftar debitur cabang; filter `q` (nama `ilike`); paginate `per_page` |
| POST | `/debitur` | `StoreDebiturRequest` → `DebiturService::create` → 201 |
| GET | `/debitur/{debitur}` | Detail + relasi `pasangan, usaha, siklusUsaha, aset` (scope cabang) |
| PUT/PATCH | `/debitur/{debitur}` | Update via `DebiturService::update` (scope cabang) |
| POST | `/debitur/{debitur}/aset` | Tambah aset; hitung `nilai_diperhitungkan = nilai_taksasi * persen_diperhitungkan` |
| PATCH | `/aset/{aset}` | Update aset (scope via `aset->debitur`); rehitung nilai bila taksasi & persen berubah |
| DELETE | `/aset/{aset}` | Hapus aset → 204 |

### Aturan aset `[E]`
- Validasi `storeAset`: `urutan` required ≥1, `jenis_aset` required, `kategori_pojk_id` (exists), `luas_jumlah>0`, `harga_taksasi_satuan≥0`, `nilai_taksasi≥0`, `persen_diperhitungkan` antara 0–1, `status_kepemilikan`, opsional `nama_pemilik/hubungan_pemohon/no_dokumen`.
- `nilai_diperhitungkan` = `nilai_taksasi × persen_diperhitungkan` (dihitung server, bukan input langsung).

## 5.7 Service `[E]`
`DebiturService::create(data, user)` → `Debitur::create(data + {cabang_id: user->cabang_id, sumber_data: MANUAL})`.
`DebiturService::update(debitur, data)` → update + `fresh()`.

## 5.8 UI web daftar/detail `[E]`
- `Web/DebiturController::index` — daftar + `withCount('permohonan')`, filter `q` (nama `ilike`/`no_ktp like`), paginate + `withQueryString`.
- `create` — view `debitur.create` (host `DebiturWizard`).
- `show` — `abort_unless(cabang_id sama, 403)` → load `pasangan, usaha, aset, permohonan`.
View: `resources/views/debitur/{index,create,show}.blade.php`.

## 5.9 Relasi model `[E]`
`Debitur` berelasi: `hasOne` pasangan/usaha, `hasMany` siklusUsaha/aset/permohonan/kredit; karena `permohonan` dipakai `withCount`. Semua relasi diberi anotasi generics (`@return BelongsTo<...>`) untuk PHPStan (lihat drift PROGRESS).

## 5.10 Aturan & batasan
1. NIK unik (partial index) → duplikasi ditolak di DB.
2. Usia minimum 17 tahun via validasi `tanggal_lahir`.
3. Debitur terikat cabang (`cabang_id`) → akses lintas cabang ditolak (kecuali ADMIN).
4. Soft delete: data tidak hilang fisik.
5. Pengkinian data `[E]`: tabel `debitur_pengkinian` tersedia; alur UI-nya termasuk fase lanjut `[Rencana]`.
6. Sumber data OCR/import `[Rencana]` (fase 12B).

## 5.11 Contoh alur end-to-end (web) `[E]`
1. AO buka `/debitur/create` (permission `permohonan.create`).
2. Isi 3 langkah wizard → `save()` → `debitur` tersimpan (`sumber_data=MANUAL`).
3. Redirect `/debitur/{id}` → detail dengan daftar permohonan (kosong).
4. Lanjut `/permohonan/create?debitur={id}` (Bab 6).
