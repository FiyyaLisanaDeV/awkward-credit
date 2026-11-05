# 05 — Rencana Verifikasi Primer & Penyempurnaan Implementasi

Status: DRAFT (2026-11-05). Tujuan: mengubah riset `[K/A]` menjadi keputusan `[H]` dan perubahan terukur di engine/policy/test.

## A. Verifikasi primer (Compliance/Legal + Tim Kredit)
| # | Bukti yang dibutuhkan | Untuk | Output |
|---|---|---|---|
| V1 | Naskah POJK kualitas aset BPR/BPRS yang berlaku (pasal kolektibilitas, agunan diperhitungkan/PPKA, penilaian, kategori/% jika ada) | D-07, T13, master agunan, LTV | tabel `collateral_categories` bersumber pasal `[H]`; definisi basis LTV |
| V2 | POJK manajemen risiko BPR (analisis kredit/5C, kebijakan, limit) | D-02, D-11, D-12 | policy scoring/threshold/authority `[H/DRAFT→H]` |
| V3 | POJK SLIK (consent, penggunaan, koreksi) | D-03, T2–T4, consent | flow consent + audit + validasi parser |
| V4 | Ketentuan transparansi/perlindungan konsumen BPR (suku bunga/biaya/total biaya/denda/penalti) | D-05, D-06, dokumen/simulasi | format pengungkapan; EIR/total biaya; aturan denda/penalti |
| V5 | Kebijakan akuntansi/COA BPR | D-01, D-05 | mapping COA; basis pengakuan; jurnal |
| V6 | Tarif asuransi, template legal, matriks kewenangan, ambang IDIR/DSR final | D-08, D-11, D-12, dokumen | master data berversi + approval |

## B. Penyempurnaan formulasi (backlog teknis, dependency-aware)
1. **Golden package** `[G]`: ≥25 kasus dummy terverifikasi (sumber sheet/sel) + koreksi (IDIR 85,93% / 2.948.080 / RATE eksak, no 1,81x) `[K]`. Tes ditulis dulu.
2. **ScheduleEngine**: Flat/Anuitas/Grace + rounding policy (D-05) + EIR/IRR numerik + metadata arus kas. Property: saldo akhir, sum pokok, determinisme snapshot.
3. **Fee/Disbursement**: komponen biaya transparan; dana diterima konsisten ledger; blokir lama eksplisit (D-06).
4. **Capacity**: IDIR + DSR total dari SLIK (parser+validasi+confidence); komponen income/expense eksplisit; simulator plafon/tenor.
5. **Productive engines**: RPC/DSCR/plafon max (grace definisi konsisten), MK (cash turnover+WCD, fix T6/T7), rasio, stress.
6. **Collateral/PPKA/LTV**: 4 nilai terpisah; kategori/% dari V1 `[H]`; basis LTV (D-07); appraisal metadata.
7. **Balloon**: rasio 1.0/1.1 parameter; sumber/bukti wajib; hard-stop/mitigasi policy.
8. **Scoring**: 5C band data-driven; FSS TPP redesign berversi (D-02); override beralasan+audit; Character dari SLIK.
9. **DecisionEngine**: hard-stop vs soft-score, conflict flags (T1), narasi alasan, authority check.
10. **Docs & disclosure**: template dari snapshot, terbilang engine, no hardcode; ringkasan transparansi (nominal/efektif/biaya/dana diterima/jadwal).
11. **Ledger/audit**: idempoten, immutable, hash verify, maker-checker, recon (D-01).
12. **Compliance controls**: consent SLIK/PDP, enkripsi/mask, audit akses, retensi (D-09/D-10).

## C. Kriteria penerimaan (gate)
- Setiap formula kritis memiliki: sumber (workbook/pasal/kebijakan), parameter policy, golden test + negative test, dan output snapshot/audit.
- Selisih angka terhadap workbook hanya diterima jika: (a) workbook salah (temuan T*) atau (b) koreksi wajib disetujui `[K/H]` — terdokumentasi.
- Tidak ada PII nyata di repo/fixture/prompt/log; data uji = dummy berlabel.
