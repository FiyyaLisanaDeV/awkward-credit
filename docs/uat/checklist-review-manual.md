# Checklist Review SOP / Manual / Pelatihan

Centang setelah ditinjau oleh pemilik proses. Kolom hasil/tanda tangan kosong.

| # | Dokumen | Sesuai alur UI aktual? | Catatan ketidaksesuaian | Ditinjau oleh | Tanggal |
|---|---|---|---|---|---|
| 1 | Manual AO (`docs/user/01`) | | Belum memuat pencairan/dokumen/import web | | |
| 2 | Manual Admin (`docs/user/02`) | | Sudah memuat Pengaturan; belum memuat UI pencairan/dokumen/ledger/import | | |
| 3 | Panduan Komite (`docs/user/03`) | | Perlu tambah akses read-only pengaturan/ledger | | |
| 4 | SOP POJK (`docs/user/04`) | | Perlu tambah prosedur pencairan & tanda tangan digital | | |
| 5 | Materi pelatihan (`docs/user/05`) | | Belum memuat latihan UI baru (pencairan/dokumen/ledger/import) | | |
| 6 | FAQ (`docs/user/06`) | | Perlu tambah troubleshooting import/ledger | | |
| 7 | Pengaturan (`docs/user/07`) | | Sudah sesuai alur draft→approve→apply | | |

## Ketidaksesuaian utama (hasil pemeriksaan kode, [V])
- Manual pengguna belum memuat **4 modul UI baru** yang ditambahkan sesi ini: pencairan (`/kredit/cairkan`), dokumen (`/dokumen`), ledger (`/ledger`), import pembayaran CSV (`/import`).
- Modul tersebut sudah berfungsi dan teruji (lihat `docs/evidence/golive-20261106/`); manual perlu diperbarui sebelum UAT.
