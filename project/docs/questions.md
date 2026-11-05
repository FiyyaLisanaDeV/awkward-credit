# Questions / UNKNOWN (init)

Status: aktif. Item memblokir fase terkait sampai resolved `[H]`/sumber tersedia.

## Prasyarat eksekusi
- `UNKNOWN`: Status **F1/F2/F3 LULUS** (Kartu Bukti). Rencana maksimal Lapis 1–9 mensyaratkan F1/F2/F3 LULUS `[E riset independen]`.
- `UNKNOWN`: Apakah implementasi dilanjutkan di repo existing `APP BPR/loan-analytic-bpr` (fase 0–11 `[E]`) dengan gap assessment, atau workspace terpisah? (lihat D-04 & constraint stack).

## Verifikasi regulasi/angka (prioritas, semua `[K]`)
- `UNKNOWN (V1)`: POJK kualitas aset BPR/BPRS yang **berlaku** (nomor/tahun/pasal): kolektibilitas, PPAP, PPKA/agunan diperhitungkan, kategori & %, penilaian agunan, hapus buku/restrukturisasi. (D-07, D-16)
- `UNKNOWN (V2)`: POJK manajemen risiko BPR berlaku: analisis/persetujuan kredit, 5C, limit/kewenangan. (D-02,D-11,D-12,D-13,D-14)
- `UNKNOWN (V3)`: POJK SLIK berlaku: bentuk persetujuan debitur, penggunaan, koreksi, kewajiban pengguna. (D-03)
- `UNKNOWN (V4)`: Transparansi/perlindungan konsumen BPR: nominal/efektif/total biaya, biaya, simulasi, denda/penalti/pelunasan. (D-05,D-06)
- `UNKNOWN (V5)`: Akuntansi/COA/pengakuan/CKPN/KPMM BPR. (D-01,D-05,D-16)
- `UNKNOWN (V6)`: Tarif asuransi resmi, template legal, matriks kewenangan, ambang IDIR/DSR. (D-08,D-11,D-12)
- `UNKNOWN (V7)`: BMPK; APU-PPT/PPATK (walk-in/LTKM/retensi/SOD) `[K klaim independen]`; POJK 34/2025 tata kelola TI/residensi data-DRP `[K klaim independen]`. (D-13,D-19,D-20)

## Golden & data
- `UNKNOWN`: Sumber **kasus golden TPP** terkoreksi (IDIR 85,93%, penghasilan bersih 2.948.080, RATE eksak 28,88%/60 bln `[K]`) — workbook/sheet/sel acuan; data dummy berlabel.
- `UNKNOWN`: >=25 golden cases (dummy, bersumber) di `tests/golden/` belum tersedia.

## Riset independen (ambang = `[K]` sampai `[H]`)
Semua ambang/angka/frekuensi/tenggat pada `../research/07-rekonsiliasi-riset-independen-vs-research.md` (BMPK %, limit sektor/debitur/instansi, NPL/COF/LDR triggers, walk-in/LTKM/retensi, variabel makro, dll.) adalah **DRAFT klaim** — butuh sumber resmi/kebijakan bertanda tangan.

## Verifikasi primer (tindak lanjut 2026-11-05)
- `UNKNOWN (V1a-d)`: lihat `../research/verifikasi-primer/V1-PPKA-kualitas-aset-BPR.md` §7.
- `UNKNOWN (V3a-d)`: lihat `../research/verifikasi-primer/V3-SLIK-IDeb.md` §7.
- `UNKNOWN (V4a-c)`: lihat `../research/verifikasi-primer/V4-transparansi-suku-bunga-biaya.md` §7.
- `ACTION (Compliance/Legal)`: unduh naskah resmi OJK/peraturan.go.id (V1,V3,V4), catat nomor/tahun/pasal/ayat/tanggal berlaku/tautan/tanggal unduh → update `docs/claims.md`, `docs/decisions.md`, spec `legal_basis`.

## Ratifikasi/PR (2026-11-05)
- `UNKNOWN (RAT-01)`: Owner/pemutus ratifikasi ADR-001..026 ↔ D-01..D-20; format register keputusan final (ADR vs `docs/decisions.md`).
- `UNKNOWN (RAT-02/C-002)`: Sumber verifikasi workbook/sheet/sel untuk IDIR 85,93%, net 2.948.080, EIR/RATE eksak (contoh 28,88%@60 bln) dan definisi komponen net income/EIR cashflow disclosure.
- `UNKNOWN (RAT-03)`: Keputusan D-05/D-06 (pembulatan, pengakuan, denda, penalti 10% vs 15%, alokasi bayar, blokir lama) sebelum PR-C/E.
