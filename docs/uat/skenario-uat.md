# Skenario UAT — 3 Cabang

Gunakan **data dummy** (tanpa PII nyata). Catat hasil: `Lulus / Gagal / Catatan`. Setiap cabang diuji oleh AO, pemutus, dan admin cabang.

## Cabang 1 — Alur utama (happy path)
| # | Langkah | Harapan | Bukti |
|---|---|---|---|
| 1 | Login AO; buat debitur (wizard 3 langkah) | debitur tersimpan, muncul di daftar | tangkapan layar |
| 2 | Buat permohonan 5 jt, isi SLIK 2 baris, submit | status `SUBMITTED`; checklist/BR-04/08 lolos | daftar permohonan |
| 3 | Jalankan simulasi Flat & Anuitas | angsuran/jadwal tampil; EIR numerik | halaman simulasi |
| 4 | Admin/approver level 1 setujui | status `RECOMMENDED`; log level 1; notifikasi ke AO | timeline |
| 5 | Approver level 2 setujui | status `APPROVED`; log level 2 | timeline |
| 6 | Pencairan (admin) | kredit + 60 jadwal + memo; `AKAD_DICATAT`, `PENCAIRAN_DICATAT` | detail kredit |
| 7 | Catat pembayaran periode 1 | jadwal `SUDAH_BAYAR`; `ANGSURAN_DIBAYAR` | detail kredit |
| 8 | `php artisan ledger:verify` | `OK` | output terminal |

## Cabang 2 — Negatif & batas
| # | Skenario | Harapan |
|---|---|---|
| 1 | AO mencoba menyetujui pengajuannya sendiri | `403` (SoD) |
| 2 | Approver salah role/permission | `403` |
| 3 | Approve saat sudah lengkap | `409` |
| 4 | Generate dokumen saat template `DRAFT` | `422` (BR-07) |
| 5 | Bayar periode yang sudah dibayar | `409` |
| 6 | Reversal pembayaran | status jadwal kembali `BELUM_BAYAR`; `ANGSURAN_DIREVERSAL` |
| 7 | Akses lintas cabang | `403` |

## Cabang 3 — Peran, notifikasi, audit
| # | Skenario | Harapan |
|---|---|---|
| 1 | Login auditor | hanya baca; tidak ada aksi approve/create |
| 2 | Auditor buka daftar permohonan/ledger/audit | tampil read-only |
| 3 | Approve/reject/return | notifikasi muncul di lonceng AO |
| 4 | Tandai notifikasi dibaca | unread berkurang |
| 5 | Generate DOC-01..17 (template APPROVED) | 17 file; hash stabil saat ulang |
| 6 | `audit_log` UPDATE/DELETE | ditolak (immutable) |
| 7 | `ledger:verify` | `OK` |

## Berita acara (isi saat eksekusi)
| Cabang | Tanggal | Penguji | Lulus/Gagal | Tanda tangan |
|---|---|---|---|---|
| KDI | | | | |
| Cabang 2 | | | | |
| Cabang 3 | | | | |
