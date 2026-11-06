# Dokumentasi Loan Analytic BPR

Indeks paket dokumen v2.4 (2026-11-05). Mulai dari sini, lalu pilih sesuai peran.

| Peran | Baca berurutan |
|---|---|
| Pemilik proyek / manajemen | [RENCANA-PRODUKSI-FINAL.md](RENCANA-PRODUKSI-FINAL.md) → [PROGRESS.md](PROGRESS.md) |
| Developer / agent baru | [PROGRESS.md](PROGRESS.md) → [TEKNIS.md](TEKNIS.md) §0,§3,§6,§6.5 → bab domain → rencana eksekusi |
| Operator / admin | [RENCANA-PRODUKSI-FINAL.md](RENCANA-PRODUKSI-FINAL.md) §4 → [VERIFIKASI.md](VERIFIKASI.md) |
| Auditor / legal | [VERIFIKASI.md](VERIFIKASI.md) → [INVENTARIS_DATA.md](INVENTARIS_DATA.md) §9,§12 → [DECISIONS.md](DECISIONS.md) |
| Analis data / AO | [INVENTARIS_DATA.md](INVENTARIS_DATA.md) §3–§7, §10–§11 |

## Daftar dokumen

| Kode | Dokumen | Isi | Sifat |
|---|---|---|---|
| A | [RUANG-LINGKUP-PRODUK.md](RUANG-LINGKUP-PRODUK.md) | ruang lingkup, strategi, keputusan infra, kriteria sukses | stabil |
| A2 | [RENCANA-PRODUKSI-FINAL.md](RENCANA-PRODUKSI-FINAL.md) | rencana eksekusi tunggal: gate, urutan, DoD | aktif |
| B | [TEKNIS.md](TEKNIS.md) | referensi teknis 22 bab: arsitektur, formula, BR, ledger, import, API, runbook | stabil |
| C | [INVENTARIS_DATA.md](INVENTARIS_DATA.md) | 60 tabel, 24 enum, 23 event, 8 adapter import, provenance | berubah saat skema berubah |
| D | [DSH_PROMPTS_SIMPLE.md](arsip/rencana/DSH_PROMPTS_SIMPLE.md) | protokol sesi historis (arsip, read-only) | arsip |
| E | [PROGRESS.md](PROGRESS.md) | papan status, blocker, log sesi, handoff | hidup |
| F | [DECISIONS.md](DECISIONS.md) | 24 ADR append-only | append-only |
| G | [VERIFIKASI.md](VERIFIKASI.md) | reality check, anti-halusinasi, zero hardcode, gate DONE | hidup |
| — | [evidence/](evidence/) | bukti perintah nyata per fase | bukti |

## Fakta kunci (terverifikasi 2026-11-05)

- PHP 8.3.7 (Herd) · PostgreSQL 16.15 · Laravel 13 (rencana) · Tanpa Docker/Redis/MinIO/partisi/npm.
- Baseline bukti: [evidence/00-baseline-2026-11-05.txt](evidence/00-baseline-2026-11-05.txt).
- Angka yang dipakai di seluruh dokumen: 60 tabel bisnis, 34 enum, 23 event, 22 fase, 76 endpoint API, 80 rute web, 55 model, 14 seeder (2 kondisional), 17 dokumen kredit, 13 kategori agunan, 28 checklist.

## Aturan singkat

1. Klaim wajib ber-tag `[E]`/`[H]`/`[A]`/`[?]` (lihat G §2).
2. Fase `DONE` hanya dengan bukti + commit + handoff (G §10).
3. Nilai bisnis tidak boleh di-hardcode (B §6.5, G §7).
4. Setiap sesi berakhir dengan pembaruan PROGRESS (E §4–§5).
