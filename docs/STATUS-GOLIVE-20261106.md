# Status Go-Live Pilot — 2026-11-06

> Catatan status sesi. **Bukan** keputusan `[H]`. Bukti perintah ada di `docs/evidence/golive-20261106/`.

## 1. Snapshot

| Item | Nilai |
|---|---|
| Tanggal | 2026-11-06 (WITA) |
| Repo | `APP BPR/loan-analytic-bpr`, branch `main` |
| HEAD sebelum catatan | `38b2364` |
| Lingkungan | macOS + Herd (nginx/dnsmasq), HTTPS `https://loan-analytic-bpr.test` |
| PHP / Laravel | 8.3.7 / 13.40.0 |
| PostgreSQL | 16, role aplikasi `loan_bpr_app` (NOSUPERUSER) |
| Mode aplikasi | `APP_ENV=production`, `APP_DEBUG=false` |
| Cabang pilot | KDI, BOM, KOL (data dummy) |
| Akun pilot | `*.pilot`, `*.pilot.bom`, `*.pilot.kol` — password `Pilot#2026!` |

## 2. Progress selesai (sesi ini)

| Area | Status | Bukti |
|---|---|---|
| P0 Herd (helper, nginx, dnsmasq, HTTPS) | DONE | `PROGRESS.md` baris P0.10–P0.17; `curl /health` 200 |
| P1A UI pencairan (`/kredit/cairkan`) | DONE | commit `ee88674`; `PencairanUiTest` 4 passed |
| P1B UI dokumen (`/dokumen` generate/sign) | DONE | commit `b3bcc85`; `DokumenUiTest` 6 passed |
| P1C UI ledger read-only + verify | DONE | commit `fede060`; `LedgerUiTest` 5 passed |
| P1D import pembayaran CSV (preview) | SEBAGIAN | commit `82707e1`; debitur CSV belum ada |
| P1E smoke peran | DONE | 36/36 PASS (`p1e-smoke.txt`) |
| P2 pilot 3 cabang + 17 template APPROVED | DONE | commit `e7dbfc2`; `p2-isolasi.txt` 20 passed |
| P3A role DB non-superuser | DONE | commit `81acb71`; smoke HTTP 200 |
| P3B backup luar repo + restore drill | DONE | `p3c-verify-full.txt`; count cocok, ledger OK |
| P3C masker PII + scheduler | DONE | commit `4c9fb08`; `MaskerPiiTest` 2 passed |
| P4 materi UAT & lembar keputusan | DONE | commit `3bd5efc`; kolom tanda tangan kosong |
| P5 runbook produksi VPS | DONE (dokumen) | commit `08c8009` |
| Buku Besar Kredit + Buku Agunan (immutable) | DONE | `docs/FITUR-BUKU-BESAR-CIF.md`; test `tests/Feature/Register` |
| Nomor CIF nasabah (unik, immutable) | DONE | `debitur.cif` + `nomor_sequence`; `CifTest` |
| Wiki Bab 25 (komparasi Excel–web) | DONE | `commandcode bpr` commit `de4038c`, wiki 200 |
| Wiki Bab 26 (breakdown workflow Excel) | DONE | `commandcode bpr` commit `af3da2b`, wiki 200 |
| Verifikasi login & halaman peran | DONE | admin KDI/BOM/KOL dashboard 200; matriks peran sesuai |

## 3. Status build (terakhir dijalankan)

| Gate | Perintah | Hasil |
|---|---|---|
| Verifikasi penuh | `bash scripts/verify.sh --full` | **OK** (exit 0) |
| Test | `php artisan test --compact` | **195 passed**, 1993 assertions |
| Route | `php artisan route:list --json` | **104 route** |
| Ledger | `php artisan ledger:verify` | **OK** — 20 baris, rantai utuh |
| Hardcode | `php artisan hardcode:audit` | **0 pelanggaran** |
| Dokumentasi | `php artisan docs:check --strict` | **0 error, 0 peringatan** |
| HTTP | `curl -f https://loan-analytic-bpr.test/health` | **200** |

Artefak: `docs/evidence/golive-20261106/build-status-final.txt`.

## 4. Blocker yang belum teratasi

| # | Blocker | Dampak | Pemilik | Status |
|---|---|---|---|---|
| B1 | UI analisa Sheet 3–9 (Neraca/LR, Rasio, Modal Kerja, Kapasitas/DSCR, Sensitivitas, 5C, Rekomendasi) belum ada | Excel belum bisa diganti penuh; analis masih via API/Excel | Dev + Mgr Pemasaran | `[Rencana]` |
| B2 | Upload foto agunan (Sheet 10) & verifikasi lapangan (Sheet 14) belum ada UI | kelengkapan bukti agunan belum digital | Dev + Kepatuhan | `[Rencana]` |
| B3 | Import debitur (CSV/XLSX) belum ada; hanya pratinjau pembayaran CSV | migrasi data debitur belum otomatis | Dev + AO | `[Rencana]` |
| B4 | Manual pengguna belum memuat 4 UI baru (pencairan/dokumen/ledger/import) | risiko salah pakai saat UAT | Pemilik proses | `[K]` |
| B5 | Policy ACTIVE (0.5/0.35/0.7) vs DRAFT best-judgment (0.80/0.80/0.80) | angka analisa belum final | Dirut + Komite | `[K]` D-07/D-11 |
| B6 | Matriks role `kredit.cairkan` & `dokumen.sign` (saat ini ADMIN) | kewenangan belum disahkan | Dirut + Kepatuhan | `[K]` D-12 |
| B7 | Retensi PII/SLIK/ledger belum terkonfigurasi | kepatuhan PDP belum lengkap | Kepatuhan + IT | `[?]` D-09 |
| B8 | Golden recompute 29, **0 verified** | paritas angka belum ditandatangani | Mgr Pemasaran + Komite | `[K]` RAT-02 |
| B9 | PAT masih tersimpan di 3 file `commandcode bpr` | risiko kredensial | IT | `[K]` |
| B10 | Varian Grace Period & TPP belum diverifikasi field-by-field | paritas varian produk belum pasti | Dev + Produk | `[?]` |
| B11 | Runbook VPS belum dieksekusi (production diblokir) | go-live produksi belum | IT | `[Rencana]` |
| B12 | `herd.yml` (hasil `herd init`) masih untracked, di luar scope | potensi noise repo | IT | `[?]` |
| B13 | Backfill register historis (permohonan lama sebelum fitur aktif) belum dilakukan | buku besar hanya berisi event baru | Dev | `[Rencana]` |

## 5. Langkah berikutnya

1. Review manual & isi keputusan `[K]` di `docs/uat/lembar-keputusan-K.md`.
2. Eksekusi UAT 3 cabang + berita acara (`docs/uat/`).
3. Bangun UI analisa Sheet 3–9 (menutup B1) dan upload agunan/verifikasi (B2).
4. Tambah importer debitur (B3).
5. Rotasi PAT (B9) dan eksekusi runbook VPS setelah `[K]` selesai (B11).

## 6. Catatan verifikasi manual

- Aplikasi berjalan dengan `APP_DEBUG=false`. Bila verifikasi visual menemukan layar 500, cek `storage/logs/laravel.log`. Untuk sementara menampilkan stack trace, set `APP_DEBUG=true` lalu `php artisan optimize:clear`.
- Password pilot disamakan menjadi `Pilot#2026!` untuk 27 user (`*.pilot*`) agar verifikasi konsisten.
