# Rencana PR — Resolusi C-002 + Migrasi Money/EIR (TEST-FIRST, BLOCKED ON `[K]`)

Status: DRAFT rencana | **Belum dieksekusi**.
Prasyarat blocker: verifikasi sumber C-002 (workbook/sheet/sel, dummy berlabel) `[K→H]`; definisi EIR/disclosure cashflow (D-05/V4) `[K→H]`; keputusan pembulatan (D-05) `[H]`. Tanpa itu, PR hanya boleh menambah **test pending/kerangka** atau evidence, bukan mengubah expected numerik final.

## 1. Masalah (bukti `[E]`)
- `tests/Fixtures/golden/tpp.json`: `rate 0.18`, `idir 0.8311242924507667`, `penghasilan_bersih 3048080`, angsuran 2.533.333,33 (60 bln, 80jt, Flat).
- `tests/Feature/Calculation/FormulaGoldenTest.php` mengunci nilai tsb (toleransi 0.01/0.0001).
- C-002 `[K]`: IDIR **85,93%**, net **2.948.080**, **tanpa faktor 1,81x/0,87x**, RATE/EIR eksak (contoh **28,88% @ 60 bln**).
- Implementasi kalkulasi dominan `float` + `Rounding::money/ratio/schedule` (round scale config) `[E]`; `brick/math` terpasang tapi belum Money canon.

## 2. Prinsip eksekusi
- **Golden test + tes negatif sebelum kode**. Jangan lemahkan tes/hook. Jangan ubah angka expected ke C-002 tanpa sumber terverifikasi.
- Jika sumber membuktikan fixture existing benar, update C-002/kontrak (`[K]` resolved) — bukan sebaliknya. Jika C-002 benar, update fixture/test + engine via PR terpisah dengan evidence.
- Uang domain finansial → target `Money` (BIGINT rupiah) + `brick/math`; migrasi bertahap, bukan big-bang. `Rounding` menjadi adapter policy (D-05).

## 3. Urutan PR (kecil, reviewable)

### PR-A — Evidence & golden scaffolding (aman sekarang)
- Tambah `tests/golden/cases/G-TPP-01.json` (sudah ada di workspace planning) sebagai referensi target; **jangan** ganti fixture existing.
- Tambah test `@pending`/skipped dengan alasan `UNKNOWN: C-002 source verification` yang mendeskripsikan expected terverifikasi (IDIR/net/EIR numerik, no 1,81x).
- Tambah skrip verifikasi independen `Decimal/BCMath` (dummy) untuk PMT/IRR/IDIR — output disimpan ke evidence, bukan ke production code.
- **Gate:** tidak mengubah perilaku existing; CI tetap hijau.

### PR-B — Kunci definisi EIR & pembulatan `[H/K]`
- Spec final: arus kas EIR (plafon vs dana diterima; biaya upfront mana masuk) `[K V4/D-05]`, rounding rule `[H D-05]`, skala ratio/schedule.
- Update `SukuBungaEfektifCalculator`/helper hanya jika definisi berubah; golden IRR numerik baru dari sumber.
- Tes: EIR numerik kasus terverifikasi; property (IRR konsisten dengan PV cashflows).

### PR-C — Resolusi C-002 TPP (setelah sumber `[H/K]` jelas)
- Regenerasi/isi fixture TPP terverifikasi (dummy berlabel: workbook/sheet/sel, version).
- Update `FormulaGoldenTest`/`tpp.json` **hanya** sesuai sumber; tolerance eksplisit (D-05). Simpan fixture lama sebagai `tpp.legacy-adr-era.json` + catatan provenance bila perlu.
- Sesuaikan `IdirCalculator`/komponen net income jika definisi berubah; snapshot input komponen.

### PR-D — Money value object (fondasi)
- Introduce `App\Domain\Money\Money` (BIGINT rupiah) + konversi/format + `brick/math` ops; adapter `Rounding` policy-aware.
- Uji: konstruksi, add/sub/mul/div, rounding, serialisasi, no float drift. **Belum** ubah semua service.

### PR-E.. — Migrasi bertahap per domain (masing-masing PR kecil)
- E1 Schedule/Angsuran (Flat/Anuitas/Grace, adjustment akhir) + golden `G-SCH-*`.
- E2 Fee/dana diterima/payoff/blokir (D-06) + `G-TPP-04/05`.
- E3 Capacity (IDIR/DSR/RPC/DSCR/LTV/MK/balloon) + golden.
- E4 Ledger amounts (JournalPoster path) — koordinasi D-01/D-05; invariant balanced/idempotent.
- Tiap PR: golden/property/negatif + evidence + `composer qa` hijau.

## 4. Guardrail negatif
- Tolak PR yang: mengubah expected golden tanpa sumber; pakai faktor 1,81x/0,87x; menambah float baru di domain finansial; melemahkan tolerance tanpa D-05; menulis ledger/audit di luar service resmi.

## 5. Unknowns (stop conditions)
- `UNKNOWN`: file workbook/sheet/sel sumber C-002 dan definisi “penghasilan bersih”/arus kas EIR disclosure.
- `UNKNOWN`: keputusan D-05/D-06 (pembulatan, pengakuan, penalti 10% vs 15%, blokir lama).
