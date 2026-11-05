# EVD-ADR-MAP-001 — Mapping ADR↔D-01..D-20 & Rencana PR C-002/Money

Tanggal: 2026-11-05 (WITA) | Status: DRAFT

## Tujuan
Meratifikasi keputusan repo existing (ADR-001..026 `[E]`) terhadap D-01..D-20 rencana diperbarui, dan menyusun rencana PR test-first untuk konflik C-002 + migrasi Money/EIR.

## Perubahan
- `project/docs/spec/adr-to-d01-d20-mapping.md` (baru): matriks ADR↔D (selaras/parsial/gap), usulan ADR-027..031, urutan ratifikasi.
- `project/docs/spec/pr-plan-c002-money.md` (baru): PR-A..E bertahap, blocker `[K/H]`, guardrail negatif (no 1,81x, no ubah expected tanpa sumber, no float baru finansial).

## Hasil
Teridentifikasi: ADR kuat di integritas/ledger hash/zero-hardcode/PII/import staging/OCR opsional; gap besar di D-07/D-13..D-20 & kebijakan D-01/D-05/D-06/D-11; konflik C-002+Money/EIR butuh sumber terverifikasi.

## Batas
Tidak ada keputusan `[H]`, tidak ada perubahan kode/test produksi, tidak ada seed ACTIVE.
