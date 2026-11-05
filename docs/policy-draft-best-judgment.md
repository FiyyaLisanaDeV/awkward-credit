# Policy Draft — Best-Judgment Parameters (NON-ACTIVE)

Status: **DRAFT / NON-ACTIVE** — digunakan hanya untuk lapisan `RECOMPUTED_DRAFT_NOT_VERIFIED` pada golden vectors. **Bukan** kebijakan `[H]`; tidak boleh masuk produksi/seed/`legal_basis` tanpa pengesahan manusia.

> Sumber: riset `research/03` + `research/04` + `ref.md` (label `[A]/[K]`), diambil dengan best judgment untuk membuat kasus golden dapat dihitung deterministik. Setiap nilai di sini adalah **parameter policy yang harus disahkan** sebagai D-xx, bukan klaim final.

## 1. Pembulatan & arus kas (untuk reference calculator)
- Uang: integer rupiah (`Decimal`, `ROUND_HALF_UP`), tanpa float.
- Flat: bunga per bulan `round(P*r/12)` konstan; pokok dasar `floor(P/n)`; sisa pokok ditambahkan ke angsuran terakhir (`LAST_ADJUST`).
- Anuitas: `A = round(P*i/(1-(1+i)^-n))`, `i=r/12`; bunga `round(bal*i)`; pokok `A-bunga`; periode terakhir `pokok=saldo` (lunasi), `angsuran=pokok+bunga`.
- Grace bullet: periode grace bunga berjalan (`round(bal*i)`), pokok 0; pokok sisa lunas pada periode akhir (bullet).
- IRR/EIR: bisection pada arus kas bulanan; laporkan `rate_monthly`, `rate_annualized_x12 = monthly*12`, `eir_effective_annual = (1+monthly)^12 - 1`. **Tidak** memakai faktor 1,81x/0,87x.

## 2. Kapasitas & keputusan (DRAFT)
- IDIR = `angsuran_baru / penghasilan_bersih`.
- DSR total = `(angsuran_baru + total_angsuran_eksisting_SLIK) / penghasilan_bersih`.
- Ambang IDIR: **0.80** (>= dianggap hard-stop) — `[K D-11]`.
- Ambang DSR: **0.50** — `[K D-11]`.
- RPC = `surplus_bulanan * margin_capacity`; margin default **0.80**.
- DSCR = `RPC / angsuran_periodik`; pada plafon maksimum, DSCR = 1.00.
- Plafon maksimum:
  - Flat: `RPC / (r/12 + 1/n)`.
  - Anuitas: `RPC * (1-(1+i)^-n) / i`.
  - Grace: batas bunga periodik `RPC / (r/12)`; balloon divalidasi terpisah.
- Balloon adequacy: `sumber_pelunasan / kewajiban_akhir`; `<1.00` hard-stop/mitigasi, `1.00` perhatian, `>=1.10` buffer — `[K]`.

## 3. Modal kerja, stress, agunan, skoring (DRAFT)
- WC need (cash turnover) = `biaya_operasional_tunai_tahunan * turnover_days / working_days`.
- WC available = `aktiva_lancar - hutang_lancar`; gap = `max(0, need - available)`.
- Stress (best-judgment): omzet turun ringan → surplus 85% baseline; kombinasi terburuk → surplus 60% baseline; baseline dummy = Rp5.000.000/bln.
- LTV basis (DRAFT): paparan / nilai PPKA (basis tunggal); nilai pasar/likuidasi/PPKA dipisah (anti double-haircut). LTV max default 0.80.
- 5C bobot (DRAFT): Character 0.25, Capacity 0.30, Capital 0.15, Collateral 0.20, Condition 0.10. Rating bands (DRAFT): `<1.8` TOLAK, `<2.6` PERHATIAN, `<3.4` LAYAK_BERSYARAT, `<4.2` LAYAK, `>=4.2` LAYAK_PRIMA.
- BMPK limit (DRAFT, untuk kasus RAC): pihak terkait 10% dari modal.

## 4. Status penggunaan
- Seluruh nilai di atas = `RECOMPUTED_DRAFT_NOT_VERIFIED`.
- Pengesahan dilakukan lewat: D-05 (pembulatan/EIR), D-06 (fee/dana/blokir), D-07 (agunan/LTV), D-11 (ambang IDIR/DSR), D-02 (5C/FSS), D-12 (kewenangan), D-13/D-14 (BMPK/RAC), D-16 (CKPN/write-off).
- Setelah `[H]`, `status` naik menjadi `VERIFIED` dengan `verification.verified=true` + bukti.
