# Manual Admin — User, Parameter, Backup, Verifikasi

**Peran:** `ADMIN`/IT. **Login:** `/login`. Ganti sandi awal di `/password` (admin & auditor wajib ganti saat login pertama).

## 1. User & role `[E]`
Role tersedia: `AO`, `MGR_PEMASARAN`, `KOORDINATOR`, `DIRUT`, `KOMITE`, `ADMIN`, `LEGAL`, `AUDITOR`.
- **Admin** dibuat seeder `AdminUserSeeder` (username `admin`).
- **Auditor** dibuat seeder `AuditorUserSeeder` (username `auditor`, role `AUDITOR`, **read-only**).
- Password awal acak (non-produksi) atau dari env `ADMIN_INITIAL_PASSWORD` / `AUDITOR_INITIAL_PASSWORD`; `must_change_password=true`.
- UI manajemen user **belum ada**; gunakan tinker/CLI:

```bash
php artisan db:seed --class="Database\\Seeders\\Reference\\AuditorUserSeeder"
php artisan tinker --execute="\$u=App\\Models\\User::where('username','auditor')->first(); dump(\$u?->hasRole('AUDITOR'));"
```

> Buat user per cabang dengan `cabang_id` yang benar; jangan memakai role `ADMIN` untuk operasional harian.

## 2. Pengaturan (UI) — parameter, matriks, referensi

- Buka `/pengaturan` (nav **Administrasi**); perlu permission `setting.view`.
- Data **ACTIVE bersifat read-only**. Perubahan lewat alur **Draft → Ajukan → Setujui → Terapkan**.
- `setting.edit` (ADMIN) membuat/mengubah draft; `setting.approve` (KOMITE/DIRUT) menyetujui; `setting.apply` (ADMIN) menerapkan.
- **SoD:** pembuat tidak boleh menyetujui revisinya sendiri.
- Setiap aksi tercatat di `audit_log` (`setting:<SECTION>`); aktivasi parameter/matriks juga menulis ledger `PARAMETER_DIAKTIFKAN`.
- Bagian yang dapat dikonfigurasi: parameter kebijakan (34 kunci), matriks persetujuan, tarif asuransi jiwa/kebakaran/kendaraan, kategori agunan PPKA, checklist dokumen, template dokumen, dan produk.
- Validasi utama: total bobot 5C = 1.0; bobot kelengkapan = 100; urutan rating & rasio balloon; band skor monoton; band matriks bersambung dari 0 dan hanya band teratas tanpa batas maksimum.
- Detail prosedur & contoh: [07-pengaturan.md](07-pengaturan.md).

### 2.1 Fallback seeder `[E]`

```bash
php artisan db:seed --class="Database\\Seeders\\Reference\\ParameterKebijakanSeeder"
php artisan db:seed --class="Database\\Seeders\\Reference\\ApprovalMatrixSeeder"
php artisan test --filter=PolicyParametersTest
```

> Jangan edit `ref_parameter_kebijakan` / `ref_approval_matrix` langsung di DB produksi. Seeder hanya untuk bootstrap.

## 3. Matriks approval `[E]`

- `ref_approval_matrix` versi `v1.0` ACTIVE (level 1–5 per band plafon). Hanya satu versi ACTIVE.
- Ubah melalui `/pengaturan/APPROVAL_MATRIX`; revisi harus disetujui lalu diterapkan. Validasi menolak band bercelah/tumpang tindih, level duplikat per band, dan role yang tidak ada.
- Lihat Bab 24 pada wiki manual book (Workflow, Peran & Wewenang).

## 4. Template dokumen `[E]`

- `ref_template_dokumen` berisi 17 template (DOC-01..17), status awal **DRAFT**.
- **BR-07:** generate dokumen ditolak bila template belum `APPROVED`. Sahkan template sebelum produksi.
- Status template dapat diubah lewat `/pengaturan/TEMPLATE_DOKUMEN` (revisi + persetujuan), atau seeder/CLI untuk bootstrap.
- Generator: 17 generator + `DocumentService` (hash HTML idempoten, disk privat).

## 5. Backup & restore `[E]`
```bash
bash scripts/backup.sh          # dump DB + tar storage/app/private ke backups/
# restore HANYA ke DB uji (jangan ke DB dev/produksi tanpa prosedur):
pg_restore -h 127.0.0.1 -U <user> --no-owner -d <db_uji> backups/<file>.dump
php artisan migrate --force
```
- Simpan `APP_KEY` terpisah — tanpa itu file PII tidak dapat dibuka.
- Uji restore ke DB scratch, bandingkan jumlah baris tabel kunci, lalu hapus DB scratch.
- `scripts/restore.sh` menimpa DB `.env` — gunakan hanya dengan prosedur dan konfirmasi.

## 6. Verifikasi & kesehatan `[E]`
```bash
bash scripts/verify.sh --full     # pint, phpstan, test, hardcode, docs, state
php artisan ledger:verify         # integritas hash chain
php artisan state:check           # ringkasan lingkungan
```
Harapan: exit 0, `ledger:verify` OK, `hardcode:audit` 0.

## 7. Keamanan operasional `[K]`
- Jangan commit `.env`, token, workbook Excel, atau data PII.
- Rotasi token yang pernah terekspos; gunakan PAT scoped.
- Batasi akses DB produksi (role non-superuser + GRANT terbatas) — prosedur hardening `[Rencana]`.
- Retention PII/SLIK/ledger mengikuti kebijakan `[K D-09]`.
