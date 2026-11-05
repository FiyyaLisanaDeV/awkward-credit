# Golden Vectors Plan (DRAFT, pre-implementation)

Target: **>= 25** kasus dummy, berlabel sumber (workbook/sheet/sel) dan versi koreksi/policy. **No PII**. Semua nilai `[K]` sampai diverifikasi. Tulis golden test **sebelum** engine.

| ID | Produk/Metode | Fokus | Nilai kunci | Status |
|---|---|---|---|---|
| G-TPP-01 | TPP Flat | koreksi kasus utama | IDIR 85,93% / net 2.948.080 / RATE eksak 28,88%@60bln `[K C-002]` | `[K]` verifikasi sheet/sel |
| G-TPP-02 | TPP Flat | DSR total dari SLIK (multi fasilitas) | angsuran eksisting SLIK `[K]` | `[K]` |
| G-TPP-03 | TPP Flat | boundary IDIR hard-stop | =threshold / >threshold `[K D-11]` | `[K]` |
| G-TPP-04 | TPP Flat | top-up/payoff + penalti + biaya | payoff components `[K D-06]` | `[K]` |
| G-TPP-05 | TPP Flat | dana diterima + blokir 2x + blokir lama | konsisten jurnal `[K D-06]` | `[K]` |
| G-SCH-01 | Flat | tenor 1 (edge) | rounding akhir | `[K]` |
| G-SCH-02 | Flat | plafon kecil + rounding Rp1 | adjustment tercatat | `[K]` |
| G-SCH-03 | Anuitas | saldo akhir 0 | PMT/IRR | `[K]` |
| G-SCH-04 | Anuitas | banding Flat, no 1,81x | EIR numerik | `[K]` |
| G-SCH-05 | Grace/bunga-berjalan | bullet akhir | balloon_due | `[K]` |
| G-SCH-06 | Grace | kecukupan 1.10 (buffer) | X3 rule `[E/K]` | `[K]` |
| G-SCH-07 | Grace | kecukupan 1.00 (perhatian) | boundary | `[K]` |
| G-SCH-08 | Grace | kecukupan <1.00 (tidak layak) | hard-stop/mitigasi `[K]` | `[K]` |
| G-CAP-01 | Produktif Flat | RPC/DSCR/plafon max | X2 `[E/K]` | `[K]` |
| G-CAP-02 | Produktif Anuitas | RPC/DSCR/plafon max | X2 `[E/K]` | `[K]` |
| G-CAP-03 | Produktif Grace | DSCR bunga periodik + balloon | X3 `[E/K]` | `[K]` |
| G-MK-01 | MK | cash turnover | komponen eksplisit (fix T7) | `[K]` |
| G-MK-02 | MK | WCD/gap | fix T6/T7 | `[K]` |
| G-STR-01 | Stress | omzet turun ringan | DSCR pass/fail | `[K]` |
| G-STR-02 | Stress | kombinasi terburuk | mitigasi | `[K]` |
| G-COL-01 | PPKA/LTV | no double-haircut (4 nilai) | D-07 `[K]` | `[K]` |
| G-COL-02 | PPKA/LTV | boundary LTV max | D-07 `[K]` | `[K]` |
| G-SCR-01 | 5C | rating boundary | D-02 `[K]` | `[K]` |
| G-SCR-02 | 5C | SLIK override → audit/alasan | T4 | `[K]` |
| G-DEC-01 | Decision | hard-stop IDIR vs soft-score konflik | fix T1 | `[K]` |
| G-RAC-01 | BMPK | exposure langgar → ditolak (negatif) | D-13 `[K]` | `[K]` |
| G-RAC-02 | RAC | over-limit sektor + override Direksi teraudit | D-14 `[K]` | `[K]` |
| G-CKP-01 | CKPN/PPKA | arah PPKA↔CKPN benar; CKPN<PPKA ditolak (negatif) | D-16 `[K]` | `[K]` |
| G-WO-01 | Write-off | parsial ditolak; full+eligibility lolos (negatif) | D-16 `[K]` | `[K]` |

Catatan: 29 kasus (>=25). Nilai numerik diisi hanya dari sumber terverifikasi (script Decimal/BCMath independen atau workbook terverifikasi, keduanya dummy/berlabel).
