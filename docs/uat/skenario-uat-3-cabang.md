# Skenario UAT — 3 Cabang (data dummy)

Gunakan data dummy (tanpa PII nyata). Isi hasil **setelah** eksekusi; kolom hasil/bukti/penguji/tanggal dikosongkan.

| Cabang | Kode | Fokus |
|---|---|---|
| Kendari | KDI | Happy path end-to-end |
| Bombana | BOM | Negatif & batas |
| Kolaka | KOL | Peran, notifikasi, audit |

## KDI — Happy path
| # | Langkah | Diharapkan | Hasil | Bukti | Penguji | Tanggal |
|---|---|---|---|---|---|---|
| 1 | Login AO, buat debitur | debitur tersimpan | | | | |
| 2 | Buat permohonan, submit | status SUBMITTED | | | | |
| 3 | Simulasi Flat/Anuitas | jadwal + EIR tampil | | | | |
| 4 | Approval level 1 & 2 | RECOMMENDED → APPROVED | | | | |
| 5 | Cairkan via UI (`/kredit/cairkan`) | kredit + jadwal + memo | | | | |
| 6 | Generate dokumen (template APPROVED) | file dokumen dibuat | | | | |
| 7 | Tanda tangani dokumen | status DITANDATANGANI | | | | |
| 8 | Catat pembayaran | jadwal SUDAH_BAYAR | | | | |
| 9 | `ledger:verify` | OK | | | | |

## BOM — Negatif & batas
| # | Skenario | Diharapkan | Hasil | Bukti | Penguji | Tanggal |
|---|---|---|---|---|---|---|
| 1 | AO self-approve | 403 (SoD) | | | | |
| 2 | Approver salah role | 403 | | | | |
| 3 | Cairkan belum APPROVED | 409 | | | | |
| 4 | Generate template DRAFT | 422 (BR-07) | | | | |
| 5 | Bayar periode sudah dibayar | 409 | | | | |
| 6 | Reversal pembayaran | jadwal BELUM_BAYAR | | | | |
| 7 | Akses lintas cabang | 403 | | | | |

## KOL — Peran, notifikasi, audit, import
| # | Skenario | Diharapkan | Hasil | Bukti | Penguji | Tanggal |
|---|---|---|---|---|---|---|
| 1 | Login auditor | read-only | | | | |
| 2 | Auditor buka ledger | tampil, verify | | | | |
| 3 | Notifikasi lonceng | muncul + bisa dibaca | | | | |
| 4 | Import pembayaran CSV valid | batch sukses | | | | |
| 5 | Import CSV rusak/duplikat | baris INVALID + alasan | | | | |
| 6 | `ledger:verify` | OK | | | | |
