# Bab 21 — Prosedur Operasional, Backup/Restore, dan Rilis

## 21.1 Operasi harian `[E]`
| Kebutuhan | Perintah |
|---|---|
| Jalankan dev | `php artisan serve` / `herd link loan-analytic-bpr` |
| Cek kesehatan | `GET /health` / `GET /up` |
| Reality check | `php artisan state:check` |
| Cek dokumen | `php artisan docs:check` |
| Cek hardcode | `php artisan hardcode:audit` |
| Verifikasi ledger | `php artisan ledger:verify` |
| Reproduksi simulasi | `php artisan simulasi:replay {id}` |
| Verifikasi menyeluruh | `bash scripts/verify.sh --phase` |

## 21.2 Backup `[E]` — `scripts/backup.sh`
- Dump DB: `pg_dump -Fc -d $DB_NAME -f backups/${DB_NAME}-<TS>.dump`.
- Arsip storage privat: `tar -czf backups/storage-private-<TS>.tar.gz -C storage/app private`.
- **Peringatan**: simpan `APP_KEY` dari `.env` — tanpa itu file PII tidak dapat dibuka.

## 21.3 Restore `[E]` — `scripts/restore.sh <dump>`
- Konfirmasi ketik `RESTORE`; `pg_restore --clean --if-exists --no-owner -d $DB_NAME <dump>`; lalu `php artisan migrate --force`.
- **Destruktif**: menimpa DB target (lihat `<careful_execution>` Bab 22 §22.6).

## 21.4 Reset DB `[E]` — `scripts/reset-db.sh`
- **Hanya** bila `APP_ENV=local`; konfirmasi `RESET`; `migrate:fresh --seed --force` + `optimize:clear`.

## 21.5 Rilis `[E]`
- Fase dikembangkan bertahap (22 unit); tiap fase berakhir dengan bukti di `docs/evidence/`, update `docs/PROGRESS.md`, commit, dan tag `phase-XX-done`.
- Gate: `scripts/verify.sh --phase` (dan `--full` untuk dokumen strict).
- Evidence terakhir tercatat: `docs/evidence/phase-11-post-20261105-024945.txt`; commit `7e11e8e`, tag `phase-11-done`.

## 21.6 Checklist rilis ringkas `[A]`
1. `bash scripts/verify.sh --full` hijau.
2. Migrasi terbaru teruji (`migrate` + `migrate:rollback` di salinan).
3. `state:check` OK; `ledger:verify` OK.
4. Backup sebelum deploy; uji restore.
5. `APP_ENV=production`, `APP_DEBUG=false`, kredensial & role DB least-privilege.
6. Update `PROGRESS.md`, evidence, tag.
7. **Update manual book** (Bab 22) untuk fitur final.

## 21.7 Monitoring & alerting (disarankan) `[A]`
- Probe `/health` (db/cache/storage) → alert bila 503.
- `ledger:verify` terjadwal + alert kegagalan.
- Log `slow_query_ms=500` (config) → review query lambat.
- Pantau impor gagal (`import_batch.gagal`).

## 21.8 Kapasitas & queue `[E]/[K]`
- Queue `sync` (tanpa Redis) — batch TPP besar dapat lambat `[K]`; chunking/`--max-time`/worker DB bila perlu.
- Tanpa partisi tabel (ADR-005); index parsial digunakan untuk query aktif.

## 21.9 Insiden `[A]`
- Ledger tak cocok → jangan edit; investigasi baris, koreksi via reversal, catat temuan.
- Restore darurat → ikuti 21.3 + verifikasi `ledger:verify`.
- Kebocoran kredensial → rotasi `APP_KEY`/token Sanctum (`personal_access_tokens`), audit `AKSES_PII`.
