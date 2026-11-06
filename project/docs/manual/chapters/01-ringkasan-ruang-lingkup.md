# Bab 1 — Ringkasan & Ruang Lingkup

## 1.1 Apa aplikasi ini
**Loan Analytic BPR** adalah sistem analisis kredit untuk **PT BPR Bahteramas Kendari** (cabang Bombana & Kolaka) yang menangani tiga produk: **TPP** (kredit pegawai/pensiun), **Modal Kerja** (produktif), dan **Grace Period** (dengan balloon). Aplikasi mencakup siklus: pendaftaran debitur → permohonan kredit → SLIK → analisis (neraca, laba-rugi, rasio, modal kerja, kapasitas bayar, 5C, red flag, rekomendasi) → persetujuan berjenjang → dokumen & tanda tangan → akad/pencairan → kredit aktif, jadwal angsuran, dan pembayaran → **ledger append-only** ber-hash chain sebagai jejak audit.

Sumber utama (`[E]`): `README.md`, `docs/TEKNIS.md`, `docs/INVENTARIS_DATA.md`, `docs/RUANG-LINGKUP-PRODUK.md`, `docs/RENCANA-PRODUKSI-FINAL.md` pada repo `loan-analytic-bpr`.

## 1.2 Ruang lingkup manual ini
Manual ini mendokumentasikan **seluruh fungsi yang terimplementasi** (fase 00–11 `[E]`) secara rinci: arsitektur, konfigurasi, autentikasi/RBAC, seluruh modul domain, mesin perhitungan, business rules, API, UI, CLI, data master/policy, import, keamanan, struktur data, serta **gap dan rencana lapis risiko L1–L9** `[K/Rencana]`.

**Di luar cakupan (belum terimplementasi):** fase 12–18 (Blade UI lanjutan, import adapter penuh, dashboard+workflow pembayaran, dokumen 17 jenis, quality gate, hardening, SOP, UAT/go-live per `docs/PROGRESS.md` §2) dan lapis risiko maksimal L1–L9. Semua ditandai eksplisit.

## 1.3 Status implementasi & bukti
Berdasarkan `docs/PROGRESS.md` (v2.5, 2026-11-05) dan header snapshot `[E]`:
- Fase **00–11 DONE**; berikutnya fase 12 (Blade UI).
- Laravel **13.40.0**, PHP **8.3.7**, PostgreSQL **16.15**, Livewire 4, Pest 4, Larastan 3.
- **61 migrasi**, **55 model**, **35 enum** (di `app/Enums`), **73 route** (42 API v1) — *angka route dihitung via `route:list` saat manual disusun; PROGRESS mencatat 63 pada snapshot fase 11, lihat catatan drift Bab 2 §2.7*.
- **30 file test** pada working tree (snapshot fase 11 = 26 file/87 test); `ledger:verify` tersedia, `hardcode:audit` 0 pelanggaran.
- Repo: branch `main`, commit `1f4e3d2` (dok phase-11), tag `phase-11-done`, **working tree punya 26 berkas belum commit** (fase 12 UI/Livewire).

> **Penting (anti-drift):** angka pada manual adalah **snapshot**. Perbarui lewat prosedur Bab 22 dan catat di `CHANGELOG.md`.

## 1.4 Konvensi label bukti
| Label | Arti |
|---|---|
| `[E]` | Terverifikasi langsung dari kode/route/migrasi/config/test/evidence repo. |
| `[A]` | Usulan/desain operasional (belum diputuskan). |
| `[K]` | Klaim/perlu verifikasi (pasal, angka, ambang, kebijakan, sumber golden). |
| `[H]` | Keputusan bertanda tangan (belum ada; jangan dijadikan ACTIVE). |
| `[Rencana]` | Belum terimplementasi (mis. L1–L9). |

## 1.5 Peta pembaca
- **Analis/AO** → Bab 5, 6, 7, 14.
- **Approver/Manajemen** → Bab 9, 10, 20.
- **IT/DevOps** → Bab 2, 3, 12, 15, 18, 21.
- **Compliance/Risk** → Bab 9, 18, 20.
- **Developer** → Bab 2, 8, 13, 19; lalu 22 (kontribusi).

## 1.6 Batasan & disclaimer
1. Manual **bukan** keputusan kebijakan. Angka/ambang/pasal regulasi berlabel `[K]` **wajib diverifikasi** ke naskah resmi/keputusan `[H]` sebelum produksi.
2. Terdapat **konflik angka golden C-002** (`[K]`) antara fixture repo (`tests/Fixtures/golden/tpp.json`: IDIR 0.831124, penghasilan bersih 3.048.080, rate 0.18) dan koreksi rencana (IDIR 85,93%, net 2.948.080). Lihat Bab 20.
3. Implementasi kalkulasi saat ini memakai **`float` + `Rounding`** `[E]`; target kontrak Money adalah **BIGINT rupiah + `brick/math`** `[K]`. Migrasi belum dilakukan. Lihat Bab 8 & 20.
4. Tidak ada PII nyata yang boleh masuk repo/log/prompt; data contoh Excel adalah **dummy** `[E user]`.
