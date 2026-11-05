# Spec — Kapasitas Bayar: TPP (IDIR/DSR) & Produktif (RPC/DSCR/MK/Balloon) (DRAFT)

Status: DRAFT | `[K]` D-03/D-11/D-16.

## 1. TPP
- `penghasilan_bersih = Σincome_komponen_policy - Σexpense_komponen_policy - kewajiban_lain_policy` (komponen eksplisit, versioned).
- **IDIR** = `angsuran_baru / penghasilan_bersih`. Hard-stop threshold `[K D-11]`. Golden koreksi `[K C-002: 85,93% / 2.948.080 / RATE eksak]` — verifikasi sheet/sel sumber dulu.
- **DSR total** = `(angsuran_baru + kewajiban_eksisting) / penghasilan_bersih`. `kewajiban_eksisting` dari **SLIK terstruktur** (parse→validasi→confidence/provenance→review→commit, consent aktif D-03) + internal. Fix T2/T3/T4. Validasi: duplikat, baki<=plafon flag `[K]`, kolek range, tanggal pull; joint/penjamin `[K]`.
- DSR auto-recalc jika SLIK pull/fasilitas baru `[A L1]`.

## 2. Produktif
- **RPC** = `surplus_bulanan * margin_kapasitas` (margin policy). Surplus dari komponen LR/cashflow eksplisit (fix T6/T7).
- **DSCR** = `RPC / debt_service_periodik`. Threshold min/ideal `[K]`. Grace: debt service periodik = bunga berjalan; **risiko bullet diuji balloon** — definisi konsisten & diungkap `[K]`.
- **Plafon max** (flat/anuitas/grace) — golden numerik `[K]`.
- **MK**: cash turnover + WCD; `gap = max(0,kebutuhan - (aktiva_lancar-hutang_lancar))`. Klasifikasi aset enum (fix T6), komponen biaya eksplisit (fix T7).
- **Stress**: 6 skenario `[E]` + skenario makro/idiosinkratik rencana maksimal `[K D-18]` → dampak DSCR/CKPN/CAR/likuiditas `[K]`.

## 3. Balloon (X3)
`kecukupan = if(kewajiban_akhir=0,0, penerimaan_terverifikasi/kewajiban_akhir)`. Threshold contoh 1.0/1.1 `[E X3, K]`. Wajib sumber/bukti pelunasan; stress keterlambatan/shortfall. Hard-stop/mitigasi per policy `[K]`.

## 4. Tes
Golden IDIR/DSR/DSCR/plafon/MK/balloon (dummy). Negatif: DSR tanpa consent SLIK ditolak; SLIK anomali ter-flag; balloon < threshold; komponen income kosong.
