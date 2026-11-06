# Runbook Produksi VPS (dokumen — belum dieksekusi)

> Semua perintah di bawah berlabel `[E]`/`[H]` kecuali yang sudah terbukti di pilot (`[V]`).
> Tidak ada IP/kredensial nyata — gunakan placeholder.

## Prasyarat `[K]`
Produksi **diblokir** sampai seluruh keputusan di `docs/uat/lembar-keputusan-K.md` diisi manusia: policy ACTIVE final (D-07/D-11), matriks role (D-12), retensi (D-09), pengesahan template, golden recompute (RAT-02), UAT 3 cabang + berita acara, review SOP/manual, rotasi PAT.

## Arsitektur target
- OS Linux, PHP 8.3 + php-fpm, nginx/Caddy, PostgreSQL 16 native (tanpa Docker) `[E]`.
- Domain + HTTPS (Let's Encrypt) `[E]`.
- Role DB aplikasi non-superuser (pola P3A) `[V]` di pilot.

## Urutan deploy `[E]`
1. Siapkan server: PHP 8.3 + ekstensi (pdo_pgsql, dompdf deps), PostgreSQL 16, git.
2. Clone repo, `composer install --no-dev --optimize-autoloader`.
3. `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<domain>`, DB role terbatas, `SESSION_SECURE_COOKIE=true`, `QUEUE_CONNECTION=database`, `MAIL_*`.
4. Migrasi + seeder referensi dengan **role owner** (bukan role aplikasi) `[E]`.
5. `php artisan key:generate`, `storage:link`, `config:cache`, `route:cache` (cek tidak ada closure), `php artisan about`.
6. nginx/Caddy serve `public/`, php-fpm pool.
7. HTTPS + security headers.
8. `bash scripts/verify.sh --full`, `ledger:verify`, `hardcode:audit`.

## Role DB (dari P3A) `[V]`
- Buat role `NOSUPERUSER NOCREATEDB NOCREATEROLE`; GRANT CONNECT + USAGE schema + DML per tabel; `ledger`/`audit_log` hanya `SELECT, INSERT`.
- Migrasi dijalankan owner terpisah.

## Backup & restore (dari P3B) `[V]` di pilot
- `pg_dump -Fc` ke luar repo, nama berstempel waktu; tar `storage/app/private`.
- Restore hanya ke DB scratch; bandingkan jumlah baris tabel kunci; `ledger:verify` pada salinan; drop scratch.

## Monitoring & pemeliharaan `[E]`
- Cron: `ledger:verify` harian (terdaftar di scheduler) `[V]`.
- Pantau log nginx/php/performance; alert disk/DB.
- Backup harian + uji restore bulanan.

## Rollback `[E]`
1. `php artisan down`.
2. `git checkout <tag>`; `composer install --no-dev`.
3. Restore DB dari dump terverifikasi + `migrate --force`.
4. `optimize:clear`, `php artisan up`, smoke.

## Catatan keamanan
- Jangan commit `.env`, APP_KEY, password DB, token. Rotasi PAT `commandcode bpr` (3 file) `[K]`.
- Jangan jalankan `PilotUatSeeder`/`pii:masker` di produksi tanpa flag eksplisit.
