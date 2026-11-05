# FAQ — Pertanyaan Umum

## Akun & akses
**Tidak bisa login.** Pastikan username/email & sandi benar; akun aktif; jika `must_change_password`, ganti sandi dulu. Hubungi Admin bila akun terkunci/nonaktif.

**Lupa sandi.** Admin mereset sandi (tanpa UI khusus saat ini) atau membuat ulang; pengguna wajib mengganti saat login.

**Role saya apa?** AO, MGR_PEMASARAN, KOORDINATOR, DIRUT, KOMITE, ADMIN, LEGAL, AUDITOR. Auditor **read-only**.

## Permohonan & analisis
**Mengapa submit gagal?** Periksa BR-04 (balloon wajib untuk grace) dan BR-08 (parameter kebijakan tersimpan), serta kelengkapan checklist (BR-05).

**Mengapa IDIR/DSCR saya tinggi/rendah?** IDIR = angsuran/penghasilan bersih; DSCR = RPC/angsuran, RPC = surplus × margin. Periksa komponen surplus dan margin policy `[K]`.

**Angka berbeda dengan Excel?** Aplikasi memakai formula/ambang berversi dan koreksi T1–T14; golden terverifikasi masih 0/29. Jangan mengubah expected tanpa sumber `[K]`.

## Approval
**Tombol setujui menolak (403).** Role matriks atau permission level tidak sesuai, atau Anda pembuat permohonan (SoD). ADMIN dapat bypass teknis.

**Muncul 409.** Persetujuan sudah lengkap atau matriks tidak tersedia.

**Tidak ada notifikasi.** Lonceng poll 30 detik; pastikan Anda `ao_id` permohonan. Notifikasi dibuat saat approve/reject/return.

**Mengapa level saya bukan 1?** Level wajib mengikuti band plafon matriks, bukan urutan 1..max.

## Dokumen
**Generate dokumen gagal (422).** Template belum `APPROVED` (BR-07). Sahkan template lebih dulu.

**Dokumen berubah saat generate ulang?** Hash dihitung dari konten HTML/snapshot; data berubah → hash/versi baru. Data sama → dokumen yang sama.

## Kredit & pembayaran
**Pembayaran ditolak 409.** Periode sudah dibayar; gunakan periode lain atau reversal bila salah input.

**Reversal vs hapus?** Gunakan reversal (audit trail), bukan hapus.

## Ledger & audit
**Bagaimana memastikan ledger utuh?** `php artisan ledger:verify` → OK. Jangan mengubah/menghapus baris ledger.

**Di mana melihat audit?** `audit_log` (immutable) dan `ledger` via API/CLI; halaman web `[Rencana]`.

## Backup & data
**Apakah backup sudah termasuk file PII?** Backup DB + `storage/app/private`. Simpan `APP_KEY` terpisah; tanpa itu file PII tidak dapat dibuka.

**Boleh memakai data nasabah asli untuk latihan?** Tidak. Gunakan data **dummy/anonim**; jangan menyalin PII ke repo/prompt/log.

## Lainnya
**Import SLIK/ekstraksi?** Belum tersedia (Fase 12B); gunakan input manual/tempel.

**Siapa yang boleh mengubah parameter/matriks?** Admin/komite melalui versi baru + pengesahan; bukan edit DB langsung.
