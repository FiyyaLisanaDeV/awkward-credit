# Golden Vectors (DRAFT placeholders)

- **29 kasus dummy** (`manifest.json` + `cases/G-*.json`), berlabel sumber (workbook/sheet/sel) dan status `[K]` placeholder. **No PII nyata**.
- Semua nilai numerik final **`[K]` sampai diverifikasi** (sumber sheet/sel terverifikasi atau hitungan independen Decimal/BCMath, dummy/berlabel). `C-002` = klaim `[K]`, bukan terverifikasi.
- Dilarang faktor flat→efektif **1,81x/0,87x**; gunakan **IRR/EIR numerik**. Money = BIGINT rupiah.
- Kasus `negative_test=true` wajib punya tes negatif eksplisit (BMPK/RAC override audit, CKPN<PPKA, write-off parsial, dll.). Policy/ambang/pasal/angka regulasi hanya `[H]`/sumber resmi; seed non-ACTIVE sampai `[H]`.
- Proses: isi nilai terverifikasi → tulis **golden test + tes negatif sebelum engine** → evidence card per batch.

## Readiness (2026-11-05, cek `project/scripts/verify-golden.py`)
- Struktur: **29/29 OK**, manifest konsisten, **0 error**, **4 negative_test**, **0 PII**.
- Numerik terisi (partial/claim flags): **6/29** (G-SCH-01,03,06,07,08; G-TPP-01 menyimpan C-002 sebagai *claim* — `verified=false`). **23/29** input+expected masih `null`.
- Terverifikasi: **0/29**. Gate F4 (engine+parity) **tertutup** sampai nilai diisi & diverifikasi.

## Cara verifikasi/isi
```bash
python3 project/scripts/verify-golden.py   # struktur + invarian jadwal (bila input numerik ada)
```
Isi per kasus: `input.*.value_rp/value` + `expected.*.verified` dari workbook terverifikasi atau hitungan independen Decimal/BCMath; catat `source.cells` & `verification` (who/when/evidence). Jangan overwrite nilai terverifikasi tanpa kontrol revisi. C-002 jangan dijadikan `verified=true` tanpa bukti sheet/sel.

Generate ulang kerangka (placeholder): `python3 scripts/generate_golden_placeholders.py` (jika dibuat) — **jangan** overwrite nilai yang sudah diverifikasi tanpa kontrol revisi.
