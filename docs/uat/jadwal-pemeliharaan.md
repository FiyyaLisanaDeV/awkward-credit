# Jadwal Pemeliharaan

## Harian
- Cek `php artisan state:check` (lingkungan, DB, migrasi, route).
- Cek antrean notifikasi & error log.
- Backup otomatis (DB + storage privat) berjalan; verifikasi file ada.

## Mingguan
- `php artisan ledger:verify` → OK.
- Tinjau audit log aktivitas sensitif (akses PII, approval override).
- Cek kapasitas disk/DB dan performa endpoint utama.
- Tinjau dokumen/template baru; pastikan hanya template `APPROVED` dipakai.

## Bulanan
- Uji restore backup ke DB scratch (jumlah baris cocok), lalu hapus scratch.
- Rotasi kredensial/token yang relevan; tinjau akses user & role.
- Tinjau parameter kebijakan/matriks approval; perubahan lewat versi baru + pengesahan.
- Tinjau retensi PII/SLIK/ledger sesuai kebijakan `[K D-09]`.
- Perbarui dokumentasi jika ada perubahan alur/skema.

## Triwulanan
- Review SLO/performa; tuning index bila perlu.
- Simulasi insiden (restore, kehilangan akses, ledger mismatch) dan catat hasil.
- Review kepatuhan (POJK/PDP) bersama Kepatuhan/DPO `[K]`.

## Penanggung jawab
| Area | PIC |
|---|---|
| Aplikasi/DB | IT/Admin |
| Kredit/approval | Komite/Kredit |
| Kepatuhan/PII | Kepatuhan/DPO |
| Backup/DR | IT/Ops |
