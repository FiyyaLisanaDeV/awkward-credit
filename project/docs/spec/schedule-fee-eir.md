# Spec — Schedule, Fee, Dana Diterima, EIR (DRAFT)

Status: DRAFT | `[K]` sampai golden terverifikasi + D-05/D-06 `[H]`.  
Sumber: X1 hitungan/memo cair; X2/X3 Sheet 13 `[E]`; `research/03,04,06`.

## 1. Prinsip
- Money = BIGINT rupiah + `brick/math`; tanpa float. Rounding rule tunggal (D-05) + `rounding_adjustment` tercatat.
- Satu `ScheduleEngine` (murni/DTO) → `schedule_lines` + `cashflows` → dokumen/ledger (fix T8/T9/T12).
- **EIR/IRR numerik** dari arus kas bersih aktual; **dilarang faktor 1,81x/0,87x**. Definisi arus kas disclosure (biaya termasuk/tidak) `[K V4/D-05]`.

## 2. Metode
| Metode | Formula inti `[A]` | Guard/test |
|---|---|---|
| Flat | `bunga = P*r/12`; `pokok = round_policy(P/n, adj akhir)`; `angsuran=pokok+bunga` | `sum(principal)=P±adj`, deterministik |
| Anuitas | `A = P*i/(1-(1+i)^-n), i=r/12`; `bunga_t=saldo*i`; `pokok_t=A-bunga_t` | closing_n=0±adj, golden |
| Grace (X3) | grace: `bunga=saldo*i`, `pokok=0` (varian `[K]`); akhir: pelunasan sisa pokok (balloon) | `balloon_due>0`, sumber pelunasan wajib, `kecukupan` `[K]` |

Parameter `[K]`: day-count/convention, due-date shift (libur), grace definition, rounding, compounding.

## 3. Fee & dana diterima
Komponen `[E X1]`: provisi, admin, notaris, asuransi (tarif berversi D-08), appraisal, survey/lain, asuransi jaminan, **blokir tabungan (2× angsuran, param `[K]`)**, top-up pelunasan lama (pokok+bunga berjalan+penalti D-06).  
`dana_diterima = P - biaya - blokir - payoff ± perlakuan_blokir_lama` — eksplisit dan konsisten dengan jurnal/memo cair (fix T10).

## 4. EIR / disclosure
Output: nominal rate, metode, estimasi efektif/total biaya `[K V4]`, rincian biaya, dana diterima, jadwal, denda/penalti/pelunasan `[K D-06]`. Golden: RATE eksak (contoh 28,88%/60 bln `[K C-002]`) — **hanya** setelah verifikasi kasus sumber.

## 5. Tes (sebelum kode)
Golden per metode + edge (tenor 1, plafon kecil, rounding boundary, top-up, grace+balloon 1.0/1.1). Property: sum pokok, saldo akhir, determinisme snapshot, `no 1,81x`.
