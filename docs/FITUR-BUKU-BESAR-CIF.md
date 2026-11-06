# Fitur: Buku Besar Kredit, Buku Agunan, dan Nomor CIF

Status: **DONE** (2026-11-06). Bukti: `docs/evidence/golive-20261106/register-cif-verify.txt`.

## 1. Nomor CIF nasabah
- Kolom baru `debitur.cif` varchar(20) + unique index `debitur_cif_unique`.
- Format: `CIF-{kode cabang}-{urut 6}` (config `loan.nomor.cif_padding`).
- Alokasi atomik via tabel `nomor_sequence` (upsert `ON CONFLICT ... RETURNING`).
- Ditugaskan di `DebiturService::create()` dalam satu transaksi.
- **Immutable**: guard model `Debitur::updating` menolak perubahan `cif`.
- Backfill: `php artisan cif:backfill` (idempotent); opsi `--finalize` untuk `NOT NULL`.
- Tampil di daftar/detail debitur, detail permohonan, dan snapshot register.

## 2. Buku besar kredit (immutable)
Tabel `buku_besar_kredit` (append-only, hash chain, trigger tolak UPDATE/DELETE):
`seq, id, permohonan_id, kredit_id, cabang_id, tahap, aktor_id, aktor_nama, aktor_role,
occurred_at, ringkasan, snapshot, prev_hash, hash, created_at`.

Tahap: `INISIASI`, `PENGAJUAN`, `ANALISIS`, `PERSETUJUAN`, `REALISASI`, `PERUBAHAN`.

Hook:
| Tahap | Titik |
|---|---|
| INISIASI | `PermohonanService::create()` |
| PENGAJUAN | `PermohonanService::submit()` |
| ANALISIS | `Api/V1/AnalisisController` (kapasitas, scoring) |
| PERSETUJUAN | `ApprovalService::approve()` |
| REALISASI | `KreditService::cairkan()` |
| PERUBAHAN | `ApprovalService::reject()/returnToAo()` |

Snapshot memuat debitur (termasuk CIF), permohonan, kredit, dan agunan saat peristiwa.
UI: `/buku-besar` (filter tahap/no. permohonan) dan `/buku-besar/{permohonan}` (timeline).

## 3. Buku agunan (immutable)
Tabel `buku_agunan` (append-only, hash chain, trigger tolak UPDATE/DELETE):
`seq, id, agunan_key, debitur_id, debitur_aset_id, permohonan_id, kredit_id, cabang_id,
jenis_aset, no_dokumen, nama_pemilik, nilai_taksasi, persen_diperhitungkan,
nilai_diperhitungkan, peristiwa, aktor_id, aktor_nama, occurred_at, snapshot, prev_hash, hash, created_at`.

- `agunan_key = sha256("DOK|"+no_dokumen)` bila nomor dokumen ada; jika tidak,
  `sha256("ASET|"+jenis+"|"+pemilik+"|"+lokasi+"|"+luas)`.
- Dicatat saat permohonan **diajukan** untuk setiap `debitur_aset` non-deleted.
- Deteksi lintas cabang: `distinct(cabang_id) > 1` untuk key yang sama.
- UI: `/buku-agunan` (daftar + badge lintas cabang) dan `/buku-agunan/{key}` (riwayat lengkap).

## 4. Immutability
- Trigger PostgreSQL `buku_besar_kredit_immutable_trigger` dan `buku_agunan_immutable_trigger`
  menolak `UPDATE`/`DELETE` (RAISE EXCEPTION).
- Hash chain memakai `App\Services\Ledger\ChainHasher` (prev + payload + waktu + event).

## 5. Permission
- `register.view` (buku besar), `register.agunan.view` (buku agunan).
- Diberikan ke ADMIN, AO, MGR_PEMASARAN, KOORDINATOR, DIRUT, KOMITE, AUDITOR; LEGAL hanya `register.view`.

## 6. Perintah & verifikasi
```bash
DB_USERNAME=fiyyalisanna DB_PASSWORD= php artisan migrate --force
php artisan db:seed --class="Database\\Seeders\\Reference\\RolePermissionSeeder" --force
php artisan cif:backfill
php artisan test tests/Feature/Debitur/CifTest.php tests/Feature/Register --compact
bash scripts/verify.sh --full
php artisan hardcode:audit
php artisan ledger:verify
```

## 7. Asumsi & di luar scope
- Agunan = seluruh `debitur_aset` non-deleted saat submit (semantik workbook).
- "Manajemen risiko" direpresentasikan oleh level/role approval.
- Pelepasan agunan (`peristiwa=DILEPAS`) disiapkan skemanya, UI belum.
- Backfill register untuk data historis belum dilakukan.
