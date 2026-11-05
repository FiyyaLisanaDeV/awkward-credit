# EVD-GOLDEN-001 — Kerangka 29 Golden Vector Dummy (Placeholder)

Tanggal: 2026-11-05 (WITA) | Status: DRAFT (kerangka, **nilai numerik belum terverifikasi**)

## Tujuan
Menyediakan >=25 golden case dummy berlabel sumber untuk TPP/Flat/Anuitas/Grace, kapasitas/MK/stress, PPKA/LTV, scoring/decision, RAC/BMPK/CKPN/write-off (termasuk negatif), selaras rencana + rekonsiliasi L1–L9.

## Perubahan
- `project/tests/golden/manifest.json` (29 kasus, schema `bpr-golden-vectors-manifest/v1`, rules).
- `project/tests/golden/cases/G-*.json` (29 file): input/expected placeholder `[K]`, sumber workbook/sheet/cells TBD, `dummy_data=true`, `pii=false`, `verification.verified=false`.
- `project/tests/golden/README.md` diperbarui.

## Negatif tests (kerangka)
`G-RAC-01` (BMPK ditolak, non-bypass), `G-RAC-02` (over-limit + override Direksi teraudit), `G-CKP-01` (CKPN<PPKA ditolak, arah PPKA↔CKPN), `G-WO-01` (hapus buku parsial ditolak).

## Hasil
Kerangka siap diisi nilai terverifikasi. **Belum ada golden test executable**; angka C-002 ditandai klaim `[K]` (bukan terverifikasi).

## Batas/risiko
Nilai numerik/sheet-sel/ambang regulasi = `[K]`; butuh verifikasi sumber (V1–V7, D-01..D-20) dan hitungan independen. Jangan treat sebagai expected final.
