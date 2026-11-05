# EVD-GOLDEN-001 — Kerangka & Readiness 29 Golden Vector (DRAFT)

Tanggal: 2026-11-05 (WITA) | Status: DRAFT (**nilai numerik belum terverifikasi**)

## Tujuan
Menyediakan >=25 golden case dummy berlabel sumber untuk TPP/Flat/Anuitas/Grace, kapasitas/MK/stress, PPKA/LTV, scoring/decision, RAC/BMPK/CKPN/write-off (termasuk negatif), selaras rencana + rekonsiliasi L1–L9.

## Perubahan
- `project/tests/golden/manifest.json` (29 kasus, schema `bpr-golden-vectors-manifest/v1`, rules).
- `project/tests/golden/cases/G-*.json` (29 file): input/expected placeholder `[K]`, sumber workbook/sheet/cells TBD, `dummy_data=true`, `pii=false`, `verification.verified=false`.
- `project/tests/golden/README.md` diperbarui (readiness + cara isi/verifikasi).
- `project/scripts/verify-golden.py` — checker struktur + invarian jadwal (Decimal), independen, no PII.

## Readiness (otomatis, 2026-11-05)
`python3 project/scripts/verify-golden.py` → **29/29 OK, 0 error**, manifest konsisten, **4 negative_test**, **0 PII**.
- Numerik terisi (partial/claim flags): **6/29** (G-SCH-01,03,06,07,08; G-TPP-01 = claim C-002, `verified=false`).
- Input+expected numerik `null`: **23/29**. **Terverifikasi: 0/29**. → **Gate F4 tertutup**.

## Negatif tests (kerangka)
`G-RAC-01` (BMPK ditolak, non-bypass), `G-RAC-02` (over-limit + override Direksi teraudit), `G-CKP-01` (CKPN<PPKA ditolak, arah PPKA↔CKPN), `G-WO-01` (hapus buku parsial ditolak).

## Hasil
Kerangka + checker siap; **belum ada golden test executable** dan **belum ada nilai terverifikasi**. Angka C-002 tetap klaim `[K]` (bukan terverifikasi).

## Batas/risiko
Nilai numerik/sheet-sel/ambang regulasi = `[K]`; butuh verifikasi sumber (V1–V7, D-01..D-20) dan hitungan independen. Jangan treat sebagai expected final.
