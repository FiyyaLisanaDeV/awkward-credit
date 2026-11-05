# Spec — CKPN/PPKA/PD-LGD/Write-off, EWS, Stress (DRAFT)

Status: DRAFT | `[K]` D-16,D-17,D-18.

## 1. CKPN/PPKA/PD-LGD (L4)
- Kolektibilitas & PPKA selaras POJK kualitas aset BPR/BPRS berlaku `[K→H]`.
- PD/LGD: histori (periode observasi min `[K]`, migration/expected recoveries), sumber/periode/metode tersimpan; model explainable. Fallback kolektibilitas = DRAFT sampai `[H]`.
- **Arah PPKA↔CKPN** `[K]`: simpan logika & jurnal (`ckpn_transition_journal`) selaras akuntansi/KPMM; **CKPN < PPKA ditolak**.
- **Hapus buku**: non-parsial `[K]` — verifikasi seluruh fasilitas 1 perjanjian, macet `[K]`, cadangan 100% `[K]`, bukti upaya penagihan. Hapus tagih parsial hanya skenario sah `[K]` (restruk/penyelesaian) dengan kontrol.
- **Restrukturisasi**: alasan+dampak+audit; larang penggunaan untuk menghindari penurunan kualitas/CKPN `[K]`.

## 2. EWS tren (L5)
Indikator tiered `[K]`: NPL (absolut + Δ 3 bulan), COF/LDR, rating migrasi, komposisi sektor/NPL kontribusi, pembeli>50% `[K]`, SLIK baru→DSR, tunggakan bunga/pokok `[K]`. Frekuensi per indikator `[K]`.  
Setiap finding → `ews_actions` (owner, due date, status); overdue → eskalasi. `ews_trends` comparable antar periode.

## 3. Stress (L6, melengkapi 6 skenario `[E]`)
Makro `[K]`: nilai tukar, inflasi, BI rate, pengangguran, PDB → proyeksi NPL/ROA/CKPN/CAR/likuiditas (metode explainable, berversi). Skenario baseline/optimis/moderat/terburuk. Idiosinkratik: gagal debitur terbesar → ΔCKPN/CAR. Hasil comparable.

## 4. Tes negatif
Hapus buku parsial ditolak; CKPN<PPKA ditolak; restruktur penghindaran kualitas ditolak/flag; EWS tanpa tindakan/eskalasi terdeteksi; PPKA-CKPN arah salah gagal.
