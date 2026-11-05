# Handoff
Isi per fase: ringkasan, ambang+sumber policy, tes negatif, delta vs bare minimum, pertanyaan Direksi/Komite, rekomendasi berikutnya.

## 2026-11-05 — Golden readiness (F0/F1)
- **Selesai:** 29 golden case (dummy, berlabel, no PII) + checker independen `project/scripts/verify-golden.py` (Decimal, invarian jadwal). Evidence: `docs/evidence/EVD-GOLDEN-001.md`.
- **Hasil:** 29/29 OK, 0 error, 4 negative_test. 6/29 partial (claim/flag, incl. C-002 `verified=false`), **23/29 null**, **0/29 verified**. Gate F4 **tertutup**.
- **Delta vs bare minimum:** sudah ada checker + manifest konsisten; belum ada golden test executable & nilai terverifikasi.
- **Pertanyaan (Direksi/Komite):** RAT-02 (sumber sheet/sel C-002), RAT-03 (D-05/D-06) — blokir isi nilai golden.
- **Rekomendasi berikutnya:** verifikasi primer V1/V3/V4 (aksi Compliance/Legal, `questions.md#29`) → ratifikasi D-05/D-06/D-07 → isi 8 kasus SCHEDULE dari workbook terverifikasi/hitungan independen → tulis golden test + tes negatif sebelum engine.
