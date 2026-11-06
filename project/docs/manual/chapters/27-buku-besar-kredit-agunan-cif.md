# Bab 27 — Buku Besar Kredit, Buku Agunan & Nomor CIF

> **Ringkas:** bab ini mendokumentasikan register bisnis **immutable** untuk kronologi permohonan → realisasi kredit, register agunan untuk mendeteksi pemakaian lintas cabang, dan **Nomor CIF** nasabah yang unik/permanen. Semua tabel register append-only dengan hash chain dan trigger database yang menolak `UPDATE`/`DELETE`. Bukti: `docs/FITUR-BUKU-BESAR-CIF.md`, `tests/Feature/Register`, `tests/Feature/Debitur/CifTest.php`.

<details>
<summary><b>Cara membaca bab ini (klik)</b></summary>

- `[E]` = terverifikasi dari kode/migrasi/test; `[K]` = perlu keputusan manusia.
- Bab ini melengkapi [Bab 12 — Ledger, Audit & Integritas](Bab-12-Ledger-Audit-Integritas) dan [Bab 11 — Kredit, Fasilitas & Pembayaran](Bab-11-Kredit-Fasilitas-Pembayaran).
</details>

## 27.1 Tujuan
- Menyediakan **buku besar** yang menjawab: siapa menginisiasi, menganalisis, menangani risiko, menyetujui, mencairkan; kapan; di cabang mana; dengan data nasabah/keperluan/agunan saat peristiwa.
- Menyediakan **buku agunan** untuk mendeteksi agunan yang pernah dipakai sebagai jaminan (oleh siapa, cabang mana, kapan).
- Memberi **Nomor CIF** unik sebagai kunci manajemen nasabah lintas modul.

## 27.2 Nomor CIF nasabah `[E]`
- Kolom `debitur.cif` varchar(20) + unique index `debitur_cif_unique` (berlaku juga untuk baris soft-deleted → CIF tidak pernah dipakai ulang).
- Format default `CIF-{kode cabang}-{urut 6}`; padding dari `config('loan.nomor.cif_padding')`.
- Alokasi atomik via tabel `nomor_sequence`:
  `INSERT ... ON CONFLICT (scope) DO UPDATE SET last_number = last_number + 1 ... RETURNING last_number`.
- Ditetapkan di `DebiturService::create()` dalam satu transaksi.
- **Immutable**: guard model `Debitur::updating` + trigger DB `debitur_cif_immutable_trigger` (null→nilai tetap boleh untuk backfill).
- Backfill: `php artisan cif:backfill` (idempotent); opsi `--finalize` untuk `NOT NULL`.
- Tampil di `debitur.index/show`, `permohonan.show`, dan `snapshot.debitur.cif` pada register.

## 27.3 Buku besar kredit `[E]`
Tabel `buku_besar_kredit` (append-only, hash chain, trigger tolak `UPDATE`/`DELETE`):

| Kolom | Keterangan |
|---|---|
| `seq` | bigserial, urutan global |
| `id` | uuid baris |
| `permohonan_id`, `kredit_id`, `cabang_id` | relasi |
| `tahap` | `INISIASI`, `PENGAJUAN`, `ANALISIS`, `PERSETUJUAN`, `REALISASI`, `PERUBAHAN` |
| `aktor_id`, `aktor_nama`, `aktor_role` | pelaku + role **saat peristiwa** |
| `occurred_at`, `ringkasan` | waktu & narasi |
| `snapshot` (jsonb) | debitur (termasuk CIF), permohonan, kredit, agunan |
| `prev_hash`, `hash` | SHA-256 chain (`ChainHasher`) |

**Hook lifecycle:**
| Tahap | Titik |
|---|---|
| INISIASI | `PermohonanService::create()` |
| PENGAJUAN | `PermohonanService::submit()` |
| ANALISIS | `AnalisisController` (neraca, laba-rugi, kapasitas, scoring, rekomendasi) |
| PERSETUJUAN | `ApprovalService::approve()` |
| REALISASI | `KreditService::cairkan()` |
| PERUBAHAN | `ApprovalService::reject()/returnToAo()` |

**UI:** `/buku-besar` (filter tahap/no. permohonan) dan `/buku-besar/{permohonan}` (timeline + snapshot).

## 27.4 Buku agunan `[E]`
Tabel `buku_agunan` (append-only, hash chain, trigger tolak `UPDATE`/`DELETE`):
`seq, id, agunan_key, debitur_id, debitur_aset_id, permohonan_id, kredit_id, cabang_id, jenis_aset, no_dokumen, nama_pemilik, nilai_taksasi, persen_diperhitungkan, nilai_diperhitungkan, peristiwa, aktor_id, aktor_nama, occurred_at, snapshot, prev_hash, hash, created_at`.

- `agunan_key` = `sha256("DOK|"+no_dokumen)` bila nomor dokumen ada; jika tidak `sha256("ASET|"+jenis+"|"+pemilik+"|"+lokasi+"|"+luas)`.
- Dicatat saat permohonan **diajukan** untuk setiap `debitur_aset` non-deleted.
- **Deteksi lintas cabang**: `distinct(cabang_id) > 1` untuk `agunan_key` yang sama.
- Detail menampilkan no. kredit melalui relasi `permohonan.kredit` (baris agunan dibuat saat pengajuan).
- **UI:** `/buku-agunan` (badge "Lintas cabang") dan `/buku-agunan/{key}` (riwayat lengkap).

## 27.5 Immutability & integritas `[E]`
- Trigger PostgreSQL `buku_besar_kredit_immutable_trigger` dan `buku_agunan_immutable_trigger` menolak `UPDATE`/`DELETE`.
- Hash chain memakai `App\Services\Ledger\ChainHasher` (prev + payload + waktu + event).
- Penulisan diserialisasi dengan `pg_advisory_xact_lock` (konstanta `REGISTER_LOCK_BUKU_BESAR`/`REGISTER_LOCK_BUKU_AGUNAN`) agar dua penulis tidak membaca `prev_hash` yang sama.
- Grant role aplikasi: `SELECT, INSERT` pada kedua tabel register + `USAGE/SELECT` sequence; `nomor_sequence` penuh (untuk alokasi CIF).

## 27.6 Permission & UI
| Permission | Peran |
|---|---|
| `register.view` | ADMIN, AO, MGR_PEMASARAN, KOORDINATOR, DIRUT, KOMITE, LEGAL, AUDITOR |
| `register.agunan.view` | ADMIN, AO, MGR_PEMASARAN, KOORDINATOR, DIRUT, KOMITE, AUDITOR |

Navigasi sidebar: **Buku Besar** dan **Buku Agunan**. Halaman buku besar dibatasi cabang user; buku agunan bersifat lintas cabang untuk deteksi reuse.

## 27.7 Tes & verifikasi `[E]`
```bash
DB_USERNAME=fiyyalisanna DB_PASSWORD= php artisan migrate --force
php artisan db:seed --class="Database\\Seeders\\Reference\\RolePermissionSeeder" --force
php artisan cif:backfill
php artisan test tests/Feature/Debitur/CifTest.php tests/Feature/Register --compact
bash scripts/verify.sh --full
php artisan hardcode:audit
php artisan ledger:verify
```
Hasil: **11 test fitur** (CIF 4, BukuBesar 3, BukuAgunan 4) dan total **195 test / 1993 assertions**; `verify --full` OK; hardcode 0; ledger 20 baris.

Tes kunci:
- CIF unik/berurutan; perubahan CIF via model **dan** DB ditolak.
- Kronologi INISIASI→PENGAJUAN→PERSETUJUAN→REALISASI.
- Agunan sama di dua cabang → `terpakaiLintasCabang = true`.
- `UPDATE`/`DELETE` register → `QueryException`.

## 27.8 Batas & gap `[K]`
- **Backfill historis** permohonan lama ke buku besar belum dilakukan (event baru otomatis masuk).
- **Pelepasan agunan** (`peristiwa=DILEPAS`) disiapkan skemanya, UI belum.
- **Visibilitas lintas cabang buku agunan**: saat ini AO juga dapat melihat; perlu keputusan bila AO seharusnya hanya cabangnya.
- **Register vs analisis** di endpoint API bukan satu transaksi; jika insert register gagal setelah analisis tersimpan, ada gap.
- **`register:verify`** (verifikasi ulang hash chain) belum ada; trigger DB sudah mencegah perubahan, command verifikasi disarankan sebagai defense-in-depth.
- Snapshot menyimpan PII lengkap; retensi tetap `[K]` (D-09).
- `cif:backfill --finalize` belum dijalankan; kolom `cif` masih nullable (pengisian dijaga service + backfill).
