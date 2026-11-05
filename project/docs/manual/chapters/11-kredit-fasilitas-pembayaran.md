# Bab 11 — Kredit, Biaya, Asuransi, Agunan, Jadwal Angsuran, Pembayaran, dan Memo Pencairan

Modul ini mencakup kredit yang sudah berjalan (setelah akad/pencairan) dan komponen turunannya.

## 11.1 Kredit `[E]` — `kredit`
Kolom: `cabang_id, permohonan_id(unique), no_rekening_kredit(50) unique, no_akad, tanggal_akad, tanggal_cair, plafon(15,2), rate(5,4), tenor, sistem_suku_bunga, angsuran_bulanan(15,2), total_bunga(15,2), total_pembayaran(15,2), sumber_pelunasan_balloon, rekening_pencairan, rekening_potong_tpp, tujuan_penggunaan, sektor, status(enum StatusKredit default ACTIVE), closed_at, catatan`.
**Invariant DB `[E]`:** CHECK enum `status` & `sistem_suku_bunga`; `plafon>0`, angsuran/bunga/total ≥0; `rate>0 ≤1`; `tenor` 1–360; index parsial `kredit_active_idx (cabang_id, status) WHERE status='ACTIVE'`.
`StatusKredit` = `ACTIVE | LUNAS | NPL | CLOSED`.

## 11.2 Jadwal angsuran `[E]` — `jadwal_angsuran`
`{id bigIncrements, kredit_id, periode, tanggal_jatuh_tempo, saldo_awal, angsuran_pokok, bunga, total_angsuran, saldo_akhir, status(default BELUM_BAYAR), tanggal_bayar, nominal_dibayar, denda}`; unique `(kredit_id, periode)`; CHECK nilai ≥0 & periode ≥1.
Jadwal dihasilkan `AngsuranCalculator::generateJadwal` (Bab 8).

## 11.3 Biaya kredit `[E]` — `biaya_kredit`
`{kredit_id, komponen, nilai, tipe(enum TipeBiaya PERSEN/NOMINAL), rate?, keterangan}`; unique `(kredit_id, komponen)`; CHECK nilai ≥0, rate 0–1.
Parameter default (policy, `[K]`): `biaya_provisi_default` (0.01), `biaya_administrasi_default` (0.01), `biaya_survey_default` (0.02), `biaya_lain_bulanan` (0.0).

## 11.4 Asuransi `[E]` — `asuransi_kredit`
`{kredit_id, jenis(enum JenisAsuransi), perusahaan, no_polis, premi, manfaat?, usia_peserta, tanggal_mulai, tanggal_berakhir, status(default DRAFT)}`; CHECK premi ≥0.
Master tarif: `ref_rate_asuransi_jiwa`, `ref_rate_asuransi_kebakaran`, `ref_rate_kendaraan` (seeder kondisional — `[K]` bila dokumen tarif resmi belum ada, Q3).

## 11.5 Agunan `[E]` — `kredit_agunan`
`{kredit_id, debitur_aset_id, nilai_jaminan, pengikatan, no_pengikatan, tanggal_ikatan, status(default AKTIF)}`; unique `(kredit_id, debitur_aset_id)`; CHECK nilai ≥0.
Mengaitkan kredit ke `debitur_aset` (Bab 5). Kategori POJK: `ref_kategori_agunan_pojk`.

## 11.6 Memo pencairan `[E]` — `memo_pencairan`
`{kredit_id(unique), no_memo, tanggal_memo, dana_bersih(15,2), terbilang(500), rekening_tujuan, atas_nama, status(default DRAFT), approved_by?, approved_at?}`; CHECK dana_bersih ≥0.
Mencatat dana bersih yang dicairkan + terbilang.

## 11.7 Pemblokiran tabungan `[E]` — `pemblokiran_tabungan`
`{kredit_id, no_rekening, nilai_blokir, jangka_waktu_bulan, alasan, tanggal_blokir, tanggal_buka, status(default AKTIF)}`; CHECK nilai ≥0 & jangka ≥1; index parsial aktif.
Dipakai TPP (blokir sebagai mitigasi/balloon). Perlakuan saldo blokir lama = D-06 `[K]`.

## 11.8 Pembayaran angsuran `[E]` — `pembayaran_angsuran`
`{cabang_id, kredit_id, jadwal_angsuran_id?, periode?, tanggal_bayar, pokok, bunga, denda, total, metode(enum MetodePembayaran), no_bukti?, keterangan?, sumber(default MANUAL), dibatalkan_at?, reversal_of?}`; CHECK nilai ≥0; FK `reversal_of` ke dirinya; index `(kredit_id, tanggal_bayar)`.
`MetodePembayaran` = `TUNAI | TRANSFER | POTONG_TPP | LAINNYA`.
**Pembatalan** memakai pola **reversal** (`reversal_of` + `dibatalkan_at`), bukan hapus — konsisten dengan audit `[E]`. Alur posting pembayaran penuh & alokasi (denda/bunga/pokok) = fase 13 + D-06 `[K/Rencana]`.

## 11.9 API kredit `[E]`
| Method | Endpoint | Perilaku |
|---|---|---|
| GET | `/api/v1/kredit` | Daftar cabang + `permohonan.debitur`; urut `tanggal_cair` desc |
| GET | `/api/v1/kredit/{kredit}` | Detail + `permohonan.debitur, jadwal, biaya, asuransi, agunan` |
| GET | `/api/v1/kredit/{kredit}/jadwal` | Jadwal urut periode |

Semua scope cabang (`ensureSameCabang`).

## 11.10 Event ledger terkait `[E]`
`AKAD_DICATAT`, `PENCAIRAN_DICATAT`, `ANGSURAN_DIBAYAR`, `ANGSURAN_DIREVERSAL`, `DOKUMEN_DIBUAT`, `DOKUMEN_DITANDATANGANI`, `DOKUMEN_DIUNGGAH`, `CHECKLIST_DIVERIFIKASI`, `PEMBLOKIRAN` (via `AKAD_DICATAT`/event terkait).

## 11.11 Workspace planning `[K]`
Rencana L4 (CKPN/PPKA/PD-LGD, write-off non-parsial, restrukturisasi) & golden G-WO-01/G-CKP-01 ada di `commandcode bpr/project/` tetapi **belum terimplementasi** (Bab 20).

## 11.12 Gap & rencana `[Rencana/K]`
- Posting pembayaran, alokasi, denda, penalti, payoff: D-06 `[K]` (T10 blokir lama, penalti 10% vs 15%).
- CKPN/PPKA/PD-LGD, klasifikasi kualitas aset dinamis, write-off, restrukturisasi: L4 `[Rencana]`.
- Fase 13 (dashboard+workflow+pembayaran) & fase 14 (dokumen) belum dikerjakan `[E PROGRESS]`.
