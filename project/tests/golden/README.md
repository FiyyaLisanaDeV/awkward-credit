# Golden Vectors (DRAFT placeholders)

- **29 kasus dummy** (`manifest.json` + `cases/G-*.json`), berlabel sumber (workbook/sheet/sel) dan status `[K]` placeholder. **No PII nyata**.
- Semua nilai numerik final **`[K]` sampai diverifikasi** (sumber sheet/sel terverifikasi atau hitungan independen Decimal/BCMath, dummy/berlabel). `C-002` = klaim `[K]`, bukan terverifikasi.
- Dilarang faktor flat→efektif **1,81x/0,87x**; gunakan **IRR/EIR numerik**. Money = BIGINT rupiah.
- Kasus `negative_test=true` wajib punya tes negatif eksplisit (BMPK/RAC override audit, CKPN<PPKA, write-off parsial, dll.). Policy/ambang/pasal/angka regulasi hanya `[H]`/sumber resmi; seed non-ACTIVE sampai `[H]`.
- Proses: isi nilai terverifikasi → tulis **golden test + tes negatif sebelum engine** → evidence card per batch.

Generate ulang kerangka (placeholder): `python3 scripts/generate_golden_placeholders.py` (jika dibuat) — **jangan** overwrite nilai yang sudah diverifikasi tanpa kontrol revisi.
