# 04 — Matriks Formula → Engine → Regulasi/Policy → Test

Status: DRAFT (2026-11-05). `[E]` Excel, `[A]` desain, `[K]` verifikasi/keputusan `[H]`.

| Area | Formula/Aturan (ringkas) | Sumber Excel `[E]` | Engine/App `[A]` | Rujukan verifikasi `[K]` | Parameter policy (data) | Uji wajib |
|---|---|---|---|---|---|---|
| Jadwal Flat | bunga=P*r/12; pokok=P/n; adjust akhir | X1 hitungan; X2/X3 S13 | `ScheduleEngine` | transparansi biaya; D-05 | rate, tenor, rounding, daycount | golden; sum pokok=P; closing=0 |
| Jadwal Anuitas | PMT; bunga saldo*i; pokok=A-bunga | X2/X3 S13 | `ScheduleEngine` | transparansi; D-05 | rate, tenor, rounding | golden; closing=0; IRR konsisten |
| Jadwal Grace/balloon | bunga berjalan; pokok akhir (bullet) | X3 S6,S13 | `ScheduleEngine`,`BalloonEngine` | manrisk + transparansi risiko | tenor, grace_def, balloon flags | golden grace; balloon_due; sumber wajib |
| Suku bunga efektif/IRR | rate arus kas bersih (numerik) | X1 FLAT-ANN-EFF (banding), X2/X3 RATE | `EirCalculator` | transparansi total biaya `[K]` | convention, include-fees policy | golden; **no 1,81x** |
| Biaya & dana diterima | P − biaya − blokir − payoff (+/- blokir lama) | X1 hitungan/memo cair | `FeeEngine`,`DisbursementService` | transparansi biaya `[K]`; D-06 | fee rules, tax comps, block_mult | dana diterima = jurnal saldo |
| IDIR (TPP) | angsuran_baru / net_income | X1 analisis kredit | `TppCapacityEngine` | kebijakan internal `[K]`; D-11 | income/expense comps, threshold | golden terkoreksi `[K]`; hard-stop |
| DSR total | (angsuran_baru+eksisting_SLIK)/net_income | X1 SLIK, X2/X3 S2 | `TppCapacityEngine`,`SlikService` | SLIK penggunaan `[K]`; D-11 | mapping angsuran SLIK, threshold | SLIK auto (T2/T4) |
| RPC/DSCR | RPC=surplus*margin; DSCR=RPC/debt_service | X2/X3 S6 | `CapacityEngine` | manrisk `[K]` | margin, DSCR min/ideal, grace def | golden; plafon max |
| Plafon max | flat/anuitas/grace formulas | X2/X3 S6 | `MaxLoanEngine` | kebijakan `[K]` | rate, tenor, RPC | golden numerik |
| Modal kerja | cash turnover & WCD; gap | X2/X3 S5 | `WorkingCapitalEngine` | manrisk `[K]` | hari kerja, siklus, metode | golden terisi; T6/T7 fix |
| Stress | delta omzet/biaya/kombinasi | X2/X3 S7 | `StressTestEngine` | manrisk `[K]` | skenario % | 6 skenario; DSCR pass/fail |
| Rasio | current/quick/cash, D/A/D/E/E/A, NPM/ROA/ROE | X2/X3 S4 | `RatioEngine` | praktik `[A]` | denom guards | golden; div-by-zero |
| Agunan/PPKA/LTV | pisah pasar/likuidasi/PPKA; LTV basis | X1 appraisal; X2/X3 S1,S2 | `CollateralEngine` | **POJK kualitas aset BPR/BPRS** `[K/H]` (D-07) | category %, legal_basis, LTV_max, basis | no double haircut (T13); boundary |
| Balloon kecukupan | rasio sumber/kewajiban akhir; 1.0/1.1 | X3 S2,S6 | `BalloonEngine` | manrisk `[K]` | thresholds, required evidence | boundary 1.0/1.1; missing source |
| 5C | bobot/faktor/band/rating | X2/X3 S8 | `Scoring5CEngine` | manrisk 5C `[K]`; D-02 | bands, weights, overrides | boundary; SLIK override audit |
| FSS TPP | (redesign) faktor TPP/SLIK/blokir/PKS | X1 Scoring-konsumtif (referensi saja) | `ScoringEngine` v2 | kebijakan Komite `[K/H]` D-02 | model versioned | golden pasca D-02 |
| Keputusan | hard-stop + soft-score + alasan | X1 konflik IDIR/FSS; X2/X3 S9 | `DecisionEngine` | manrisk/tata kelola `[K]` | thresholds, authority | conflict flag (T1); audit |
| Kolektibilitas/SLIK flags | kol 1–5, fasilitas bermasalah | X2/X3 S2 | `SlikService` | POJK kualitas aset + SLIK `[K]` | mapping, fatal rules | validation (T3); consent gate |
| Ledger/pencairan/angsuran | double-entry, idempoten, immutable | X1 memo/blokir | `JournalPoster` | akuntansi/COA `[K]` D-01,D-05,D-06 | COA map, allocation | fuzz, hash verify, reversal only |
| Dokumen | nilai dari snapshot; terbilang engine | X1/X2/X3 templates | `DocumentService` | transparansi/kepatuhan `[K]` | templates versioned | no hardcode (T9/T12); match snapshot |
