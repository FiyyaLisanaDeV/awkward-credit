# Checklist Go-Live

Centang hanya bila bukti tersedia. Item `[K]` memerlukan keputusan/pengesahan manusia.

## Teknis
- [ ] `.env` produksi benar (DB, `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, timezone, locale).
- [ ] Migrasi & seeder dijalankan (`php artisan migrate --force`; seeder referensi).
- [ ] Storage link & permission storage/cache.
- [ ] Queue/scheduler sesuai kebutuhan; backup terjadwal.
- [ ] HTTPS aktif; security headers; akses DB terbatas (role non-superuser `[K]`).
- [ ] `scripts/verify.sh --full` → exit 0.
- [ ] `php artisan ledger:verify` → OK.
- [ ] `php artisan hardcode:audit` → 0.
- [ ] `php artisan state:check` → hijau.

## Data & konfigurasi
- [ ] Cabang, produk, checklist, kolektibilitas, parameter, matriks approval ACTIVE.
- [ ] 17 template dokumen **APPROVED** (BR-07) `[K]`.
- [ ] User & role per cabang (AO, MGR, KOORDINATOR, DIRUT, KOMITE, ADMIN, LEGAL, AUDITOR).
- [ ] Password awal diganti; `must_change_password` teruji.
- [ ] Auditor read-only teruji.

## Operasional
- [ ] Backup awal + uji restore ke DB scratch (jumlah baris cocok).
- [ ] Kontak eskalasi & jadwal pemeliharaan disepakati.
- [ ] SOP & manual pengguna ditinjau pemilik proses `[K]`.
- [ ] UAT 3 cabang lulus & ditandatangani `[K]`.

## Catatan risiko
- Golden terverifikasi masih **0/29**; keputusan produksi atas angka tertentu tetap `[K]` (lihat Bab 20).
- Kebijakan retensi/PII, ambang, dan authority limit final = `[K]` (D-09/D-11/D-12).
