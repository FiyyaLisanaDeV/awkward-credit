# EVD-INIT-001 — Inisiasi Workspace Project & Rekonsiliasi Riset

Tanggal: 2026-11-05 (WITA) | Status: DONE (inisiasi non-kode)

## Tujuan
Membuat workspace perencanaan/eksekusi dan merekonsiliasi riset independen (L1–L9 manajemen risiko maksimal) dengan riset internal, tanpa implementasi domain.

## Perubahan
- `research/07-rekonsiliasi-riset-independen-vs-research.md` (baru): coverage/gap L1–L9, mapping modul/tabel/tes, D-13..D-20 `[K]`.
- `project/` (baru): `README`, `AGENTS`, `.gitignore`; `docs/{decisions,questions,claims}.md`; spec DRAFT (schedule-fee-eir, capacity, collateral-ppka-ltv, scoring-decision, rac-portfolio, ckpn-ews-stress, apu-ppt-ti, ledger-audit-docs, golden-vectors-plan, gap-assessment-existing-repo, glossary); evidence/handoff/golden/scripts placeholders.

## Tes
- N/A (inisiasi dokumen). Tes negatif/golden menjadi gate sebelum kode (lihat spec/golden plan).

## Hasil
Struktur siap untuk verifikasi V1–V7, pengisian golden `[K]`, dan eksekusi F1–F3/L1–L9. Tidak ada seed ACTIVE, tidak ada PII, tidak ada ledger/audit code.

## Sumber/label
`[E]` workbook/scan/repo/rencana; `[E user]` dummy data; `[K]` angka/pasal/ambang (klaim/koreksi/policy) — belum `[H]`.

## Risiko / blocker
F1/F2/F3 belum LULUS; D-01..D-20 unsigned; golden terverifikasi belum ada; QA baseline repo existing ada gap; verifikasi regulasi primer pending.

## Pemeriksa
Belum (butuh review manusia/teknis/kepatuhan).
