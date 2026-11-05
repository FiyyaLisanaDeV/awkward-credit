# Spec — APU-PPT & Tata Kelola TI/AI (DRAFT)

Status: DRAFT | `[K]` D-10,D-19,D-20.

## 1. APU-PPT (L7)
- `customer_risk_rating` (low/med/high) menentukan kedalaman CDD/EDD `[K]`; EDD untuk risiko tinggi/PEP sesuai ketentuan `[K]`; CDD berkala berbasis risiko/materialitas.
- Monitoring transaksi: deteksi walk-in > ambang nominal/hari `[K klaim: Rp100 jt, 1x/akumulasi]` via rules engine.
- LTKM: SLA pelaporan `[K klaim: 3 hari kerja]`, status/bukti kirim, overdue→eskalasi.
- Retensi dokumen CDD/identitas min `[K klaim: 5 th sejak hubungan berakhir]`; alert lifecycle.
- SoD: pelaksana vs pengawas APU-PPT dipisah (RBAC). PII/consent/PDP selaras D-09/D-10.

## 2. Tata kelola TI (L8, POJK 34/2025 `[K]`)
- Pusat data & DRP di **wilayah Indonesia** `[K]` — constraint deployment/policy/gate; bukti operasional.
- Audit TI periodik `[K]`: akses, perubahan konfigurasi, insiden, backup, DRP → `audit:report` evidence.
- DRP diuji: simpan RTO/RPO aktual & hasil skenario (`drp_test_results`).
- `third_party_access`: tujuan, masa berlaku, monitoring; expired→alert. OCR/LLM/cloud PII butuh izin DPO `[K D-10]`.

## 3. AI/data alternatif (L9)
- Data alternatif hanya dengan consent aktif per sumber, minimasi, audit.
- Model kredit: **explainable, berversi, reproducible, golden vector**; larang model tak dapat dijelaskan untuk keputusan kredit. Analisis pin `model_version` (bukan “latest”).

## 4. Tes negatif
Walk-in/LTKM/retensi overdue terdeteksi; SoD pelaksana=pengawas ditolak; akses pihak ketiga expired alert; model tanpa golden/explainability ditolak; data alternatif tanpa consent ditolak.
