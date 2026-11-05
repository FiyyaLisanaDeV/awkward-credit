# Serah Terima — Kode, Dokumen, Akses, Backup, Kontak

## 1. Kode & repositori
| Item | Nilai |
|---|---|
| Repo aplikasi | `loan-analytic-bpr` (Laravel 13) |
| Branch lokal | `main` |
| Remote | `origin` → `awkward-credit`, branch **`app`** (+tags `phase-01-done`..`phase-15-done`) |
| Commit terakhir | lihat `git log -1` saat serah terima |
| Repo dokumen/manual | `commandcode bpr` → `awkward-credit` branch `main`; wiki GitHub |

## 2. Dokumen
- [ ] Dokumen A–G (rencana, teknis, inventaris, PRD, ERD, manual book 24 bab, wiki).
- [ ] Manual pengguna `docs/user/01..06`.
- [ ] SOP POJK `docs/user/04-sop-poik.md`.
- [ ] Materi pelatihan `docs/user/05-pelatihan-3-cabang.md`.
- [ ] Paket UAT `docs/uat/`.
- [ ] Evidence pack fase & UAT.

## 3. Akses
- [ ] Akun Admin & Auditor (password diganti, `must_change_password` dinonaktifkan setelah ganti).
- [ ] Akun per cabang sesuai role.
- [ ] Akses DB (role terbatas) `[K]`.
- [ ] Akses server/hosting, HTTPS, deployment `[K]`.
- [ ] Token/credential diserahkan via kanal aman (bukan repo/chat).

## 4. Backup & pemulihan
- [ ] Backup DB + storage privat + `APP_KEY` terpisah.
- [ ] Uji restore ke DB scratch (jumlah baris cocok).
- [ ] Lokasi penyimpanan backup & retensi `[K]`.
- [ ] Kontak penanggung jawab backup.

## 5. Kontak
| Peran | Nama | Kontak |
|---|---|---|
| Product owner | | |
| Kredit/Komite | | |
| IT/Admin | | |
| Kepatuhan/DPO | | |
| Vendor/pengembang | | |

## 6. Berita acara
| Pihak | Nama | Tanggal | Tanda tangan |
|---|---|---|---|
| Penyerah (IT/Pengembang) | | | |
| Penerima (BPR) | | | |
| Saksi (Kepatuhan) | | | |

> Item bertanda `[K]` memerlukan keputusan/pengesahan manusia; lihat [Checklist Go-Live](checklist-go-live.md).
