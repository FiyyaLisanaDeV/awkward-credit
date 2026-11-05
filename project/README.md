# CommandCode BPR — Project Workspace (Risk + Loan Analytic)

Tanggal inisiasi: 2026-11-05 (WITA)  
Status: **INIT / DRAFT** — bukan LULUS F1/F2/F3; bukan rilis.

## Tujuan
Workspace perencanaan & implementasi bertahap untuk **Loan Analytic BPR** (X1/X2/X3) + **Rencana Maksimal Manajemen Risiko Kredit BPR** (Lapis 1–9), dengan kepatuhan sebagai lantai dan kontrol terukur sebagai target.

## Sumber kebenaran (precedence)
1. Keputusan bertanda tangan (`docs/decisions.md`, status `[H]`)
2. Spesifikasi (`docs/spec/*.md`)
3. Rencana/riset (`../` plan + `../research/`, termasuk `07-rekonsiliasi-riset-independen-vs-research.md`)
4. Prompt/instruksi kerja

Bila bertentangan, ikuti precedence dan catat di `docs/questions.md`.

## Struktur
- `docs/spec/` — spesifikasi (DRAFT sampai diverifikasi)
- `docs/decisions.md` — keputusan D-01..D-20 (semua **DRAFT/UNKNOWN** saat init)
- `docs/claims.md` — klaim/verifikasi (tanpa angka hukum dari memori)
- `docs/questions.md` — UNKNOWN / butuh sumber/keputusan
- `docs/evidence/` — Kartu Bukti per tugas
- `docs/handoff/` — handoff per fase
- `tests/golden/` — vektor golden (dummy, berlabel sumber sheet/sel)
- `scripts/` — skrip verifikasi independen (Decimal/BCMath, non-produksi)

## Guardrail (mengikat)
- Uang = `Money`/BIGINT rupiah + `brick/math`; **tanpa float**.
- Tidak menebak angka hukum/kebijakan/pasal/versi paket; rujuk sumber atau tulis `UNKNOWN`.
- Seed/parameter **ACTIVE hanya jika `[H]`** bertanda tangan. Default DRAFT.
- Tanpa data nasabah nyata di repo/prompt/log/fixture (data Excel = dummy `[E user]`; fixture dummy berlabel).
- `ledger_*` / `audit_events` hanya via `JournalPoster` / `AuditRecorder`.
- Golden test + tes negatif **sebelum** kode; jangan lemahkan tes/hook.
- Larangan rencana maksimal: no AI kredit tak dapat dijelaskan; no hapus buku parsial; no deploy pusat data/DRP di luar Indonesia `[K verifikasi kebijakan/POJK]`; no bypass BMPK.

## Status init
- [ ] F1/F2/F3 LULUS (prasyarat eksekusi lapis)
- [x] Struktur docs/evidence/handoff/golden dibuat
- [x] Rekonsiliasi riset independen vs riset internal dibuat (`../research/07-...`)
- [ ] Spec pack + golden vectors >= 25
- [ ] D-01..D-20 diverifikasi/ditandatangani
