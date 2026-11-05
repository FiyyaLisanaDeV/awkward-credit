# 07 — Rekonsiliasi: Riset Independen (Rencana Maksimal Manajemen Risiko BPR) vs Riset `commandcode bpr/research`

Tanggal: 2026-11-05 (WITA) | Status: **DRAFT kerja**.  
Label: `[E]` bukti di repo/dokumen, `[A]` usulan desain, `[K]` **klaim/perlu verifikasi** (pasal/angka/ambang/kebijakan belum `[H]`), `[H]` keputusan bertanda tangan, `[?]` belum diketahui.

## 0. Premis & batas integritas
- Sumber yang direkonsiliasi: (1) **Riset independen user** “PROMPT NARATIF — Rencana Maksimal Manajemen Risiko Kredit BPR” (2026-11-05, Lapis 1–9, 16 gerbang) `[E user message]`; (2) **Riset internal** `commandcode bpr/research/00–06` + rencana `01–05` `[E repo]`.
- **Semua angka, ambang, pasal, tenggat, dan rujukan regulasi pada riset independen** (contoh non-ekshaustif: BMPK 10/20/30%, limit sektor 20%, 10 debitur terbesar 50%, instansi TPP 30%, premi risiko 5–10%, ambang SLIK/denda/pokok hari, walk-in >Rp100 juta/hari, LTKM 3 hari kerja, retensi 5 tahun, 5 variabel makro, NPL Wald/akurasi studi, dsb.) diperlakukan sebagai **`[K klaim independen — wajib verifikasi naskah resmi POJK/SEOJK/OJK/PPATK/UU/kebijakan internal]`**. **Tidak** dijadikan `[H]` atau seed ACTIVE.
- Jika ada benturan: **keputusan bertanda tangan > spesifikasi > riset independen > riset internal > prompt** `[A selaras kontrak project]`. Excel = referensi struktur, bukan sumber hukum. Data Excel = dummy `[E user]`.

## 1. Ringkasan coverage
| Area | Riset internal `00–06` | Riset independen L1–L9 | Status | Aksi |
|---|---|---|---|---|
| L1 Keputusan debitur (hard-stop, capacity, Condition) | Parsial (IDIR/DSR/DSCR, SLIK→DSR, hard-stop/soft-score, D-07/D-11, T1/T2) | BMPK hard-stop non-bypass `[K]`; capacity 3 sumber (LR, arus kas, stress) + auto-recalc DSR saat SLIK baru `[K]`; Condition wajib sub-kriteria | **Gap parsial** | Tambah modul BMPK/RAC-case, definisi capacity per produk, guard Condition, tes negatif BMPK/submit |
| L2 Rating ICRR/ICRS & migrasi + risk-based pricing | Parsial (5C/FSS berversi, snapshot) | Rating PD-style berversi, migrasi >2 tingkat/periode→re-underwriting, pricing review `[K]` | **Gap** | `scoring_models/ratings/risk_ratings`, migrasi trigger, pricing policy `[K]` |
| L3 Portofolio/RAC (sektor/debitur/instansi/agregat) + histori & stabilitas | Parsial (policy/authority limit kasus) | Pre-committee RAC; limit sektor/debitur/instansi/agregat `[K]`; histori komposisi sektor & NPL-by-sector; 10 debitur terbesar `[K]`; override Direksi teraudit | **Gap besar** | `rac_limits/sector_exposures/portfolio_snapshots/npl_by_sector`, RAC check service, audit override |
| L4 CKPN/PPKA/PD-LGD + KPMM + write-off/restrukturisasi | Parsial tipis (PPKA/haircut/kolektibilitas; ledger) | PD/LGD histori min observasi `[K]`; arah PPKA↔CKPN→modal/laba `[K]`; hapus buku non-parsial, seluruh fasilitas 1 perjanjian, cadangan 100%, bukti penagihan `[K]`; larangan CKPN<PPKA & restrukturisasi menghindari kualitas `[K]` | **Gap besar** | CKPN/PPKA module, histori PD/LGD, transition journal, eligibility checks, tes negatif |
| L5 EWS tren (NPL tier-1, COF/LDR, tindakan/eskalasi) | Parsial tipis (stress 6 skenario, grace risk) | Tren NPL 3 bulan `[K]`, COF/LDR mingguan `[K]`, rating migrasi, komposisi sektor, pembeli>50% `[K]`, SLIK baru→DSR, tunggakan bunga/pokok `[K]`; `ews_findings/actions/trends` + eskalasi | **Gap besar** | EWS engine/scheduler, indikator tiered, action/ownership/SLA, snapshot tren |
| L6 Stress makro + idiosinkratik → CKPN/CAR/likuiditas | Parsial (omzet/biaya/kombinasi `[E]`) | 5 variabel makro `[K]`→proyeksi NPL/ROA; 4 skenario; dampak CKPN/CAR/likuiditas; idiosinkratik debitur terbesar; perbandingan antar periode | **Gap** | `macro_scenarios/npl_projections/stress_test_results`, model terjelaskan/berversi |
| L7 APU-PPT siklus penuh | Parsial (pengkinian sekilas, PDP/consent) | Risk rating nasabah→CDD/EDD `[K]`; walk-in >Rp100 jt/hari `[K]`; LTKM ≤3 hari kerja `[K]`; retensi ≥5 th `[K]`; pemisahan pelaksana/pengawas (RBAC) | **Gap** | `customer_risk_ratings/cdd_edd/transaction_monitoring/ltkm_reports/retention`, rules engine, RBAC SoD |
| L8 Tata kelola TI / POJK 34/2025 | Parsial (keamanan/PII/hosting) | Pusat data/DRP di Indonesia `[K]`; audit TI tahunan; DRP test RTO/RPO; third-party access; CI/CD deployment constraint | **Gap besar** | deployment policy/gates, `third_party_access/drp_test_results`, audit report, ops evidence |
| L9 Data alternatif & AI dapat diaudit | Parsial (consent, golden/model berversi) | Data alternatif berbasis consent `[K]`; model AI **harus explainable/reproducible/golden**; larangan model tidak dapat dijelaskan | **Gap** | consent per sumber, model registry+golden+explainability gate, review |

## 2. Rekonsiliasi detail per lapis (mapping)
| Lapis | Klaim/komponen independen `[K]` | Yang sudah tercakup `[E/A]` | Gap/risiko | Modul/komponen target `[A]` | Data/tabel target `[A]` | Tes/bukti wajib | Keputusan/dependency `[K/H]` |
|---|---|---|---|---|---|---|---|
| L1 | BMPK hard-stop non-bypass (pihak terkait/satu peminjam/kelompok) | Hard-stop IDIR/DSR/SLIK, D-11 | Tidak ada BMPK; “kelompok peminjam” butuh definisi | `BmpkGuard`, `ExposureAggregator`, pre-submit check | `related_parties`, `borrower_groups`, `bmpk_limits`, `exposures` | tes negatif: langgar BMPK ditolak (tanpa override); agregasi grup benar | D-BMPK `[H]` batas/definisi; modal rujukan; D-01 |
| L1 | Capacity 3 sumber (LR, arus kas, stress DSCR) | RPC/DSCR/MK/stress omzet-biaya `[E]` | Arus kas & definisi “debt service” grace belum dikunci | `CashflowDscrEngine`, `CapacityBundle` | `cashflow_statements`, `capacity_snapshots` | golden per sumber; submit produktif wajib evidence `[K]` | definisi komponen income/cashflow `[K]` |
| L1 | DSR auto-recalc saat SLIK fasilitas baru | SLIK→DSR usulan (T2) | Belum mekanisme refresh/trigger | `SlikRefreshService`, DSR recompute | `slik_pulls/facilities`, snapshot diff | tes: fasilitas baru ubah DSR; consent aktif | D-03 consent/akses `[K]` |
| L1 | Condition wajib sub-kriteria | Condition disebut/implicit | Dapat default kosong | form validation + `ConditionRubric` | `condition_assessments` (sub-kriteria) | tes negatif: Condition kosong blokir submit | rubrik `[K/H]` |
| L2 | ICRR/ICRS rating berversi, migrasi >2 tingkat→re-underwriting | 5C/FSS berversi, snapshot | Tidak ada rating migration/EWS trigger | `RatingEngine` (berversi/golden), `RatingMigrationService` | `risk_ratings`, `rating_history`, `scoring_models` | deterministik; migrasi >2 trigger; riwayat immutable ref | skala/rating policy `[H]`; threshold migrasi `[K]` |
| L2 | Risk-based pricing review | Fee/policy ada | Pricing tak linked ke rating | `PricingService` | `pricing_rules`, `pricing_reviews` | perubahan rating memicu review; audit | pricing policy `[K/H]` |
| L3 | RAC pre-committee: sektor/debitur/instansi/agregat; 10 debitur terbesar; histori komposisi & NPL-by-sector | Limit per kasus/authority parsial | Tidak ada portofolio/RAC engine | `RacCheckService`, `PortfolioMetricsJob` | `rac_limits`, `sector_exposures`, `borrower_concentrations`, `institution_exposures` (TPP), `portfolio_snapshots`, `npl_by_sector`, `sector_composition_history` | tes negatif: over-limit ditolak; override Direksi+justifikasi teraudit; 10 debitur/komposisi/NPL sektor terhitung; histori per periode | ambang/definisi sektor/instansi `[K→H]` (D-RAC) |
| L4 | PD/LGD histori (min observasi `[K]`), migration/expected recoveries | PPKA/haircut disinggung | Belum PD/LGD model/data | `PdLgDService` (explainable) | `pd_lgd_historical_data`, `collectibility_history` | sumber/periode/metode tersimpan; recalc berkala; golden bila applicable | metodologi & periode `[K/H]`; fallback kolektibilitas sementara DRAFT |
| L4 | Arah PPKA↔CKPN (beban laba/pengurang modal KPMM) `[K]` | Ledger/COA belum final (D-01) | Logika arah & dampak modal belum | `CkpnPpkaService`, `JournalPoster` entries | `ckpn_calculations`, `ckpn_transition_journal`, COA mapping | tes arah (PPKA>CKPN & sebaliknya); CKPN<PPKA ditolak; jurnal seimbang | D-01/D-05 + akuntansi/KPMM `[K/H]` |
| L4 | Hapus buku non-parsial, seluruh fasilitas 1 perjanjian, macet, cadangan 100%, bukti penagihan; hapus tagih sebagian utk restruk/penyelesaian `[K]` | — | Risiko kepatuhan/audit | `WriteOffEligibilityService`, workflow | `write_off_requests/checks`, `write_off_eligibility_check` | tes negatif: parsial ditolak; verifikasi semua fasilitas; macet+100%+bukti wajib | POJK kualitas aset & kebijakan `[K/H]` |
| L4 | Restrukturisasi tak untuk hindari penurunan kualitas/CKPN `[K]` | restruktur status ada di rencana | Kontrol alasan/dampak belum | `RestructureService` + guard | `restructures` (alasan/dampak/kolek/CKPN impact) | tes negatif: skenario penghindaran ditolak/flag wajib review; alasan+audit | kriteria `[K/H]` |
| L5 | EWS tren & leading/lagging (NPL Δ3 bln tier-1, COF/LDR mingguan `[K]`, migrasi, sektor, pembeli>50%, SLIK baru, tunggakan) | Stress dasar, grace risk | Tidak ada EWS operasional | `EwsEngine` + scheduled jobs, action workflow | `ews_rules`, `ews_findings`, `ews_actions`, `ews_trends`, `indicator_snapshots` | tes trigger per indikator; temuan→action+owner+tenggat; eskalasi overdue; histori tren | definisi indikator/ambang/frekuensi/sumber data `[K/H]` |
| L6 | Stress makro 5 variabel → NPL/ROA; 4 skenario; dampak CKPN/CAR/likuiditas; idiosinkratik debitur terbesar | Stress 6 skenario `[E]` | Model makro & agregasi modal belum | `MacroStressService` (explainable), compare | `macro_scenarios`, `npl_projections`, `stress_test_results` | output angka 4 skenario; delta idiosinkratik; comparable antar periode | variabel/koefisien/metode `[K/H]`; sumber data makro `[K]` |
| L7 | APU-PPT: risk rating→CDD/EDD, walk-in >Rp100 jt/hari `[K]`, LTKM ≤3 hari kerja `[K]`, retensi ≥5 th `[K]`, SoD | Pengkinian/PDP sekilas | Rules & workflow belum | `ApuPptRiskRating`, `CddEddService`, `TransactionMonitoringRules`, `LtkmWorkflow` | `customer_risk_ratings`, `cdd_edd_records`, `tm_alerts`, `ltkm_reports`, retention metadata | tes: rating menentukan CDD/EDD; walk-in terdeteksi (1x/akumulasi hari) `[K]`; LTKM overdue→eskalasi; retensi alert; SoD negatif | ambang/tenggat/retensi/kriteria `[K/H]` (POJK APU-PPT/PPATK) |
| L8 | TI/POJK 34/2025: data/DRP di Indonesia `[K]`, audit TI, DRP RTO/RPO, third-party access, CI/CD gate | Keamanan/backup/restore disinggung | Kontrol operasional/deploy belum | `TiGovernance` checks, DRP/audit jobs/reports | `third_party_access`, `drp_test_results`, audit/access logs | tes/gate: constraint deploy; laporan audit lengkap; DRP test simpan RTO/RPO; akses expired alert | kebijakan hosting/lokasi `[H]`; scope CI/CD `[A]` |
| L9 | Data alternatif consent; AI/model explainable, berversi, golden; larangan model tak-jelas | Consent/golden/model berversi tersentuh | Data alternatif & explainability gate belum | `AlternativeDataConsent`, `ModelRegistry` | `data_consents` (per sumber), `scoring_models` (+explainability/golden) | consent aktif wajib; model tanpa golden/explainability ditolak; analisis pin versi model | sumber data & consent `[K/H]`; standar explainability `[K]` |

## 3. Keputusan baru/ekstensi yang diusulkan (belum `[H]`)
| ID | Keputusan | Mengapa | Status |
|---|---|---|---|
| D-13 | Definisi & batas **BMPK** (pihak terkait, satu peminjam, kelompok) + sumber modal rujukan | L1 hard-stop hukum `[K]` | `[K]` |
| D-14 | **RAC portofolio**: limit sektor/debitur terbesar/instansi TPP/agregat, definisi sektor, histori | L3 | `[K]` |
| D-15 | **Rating & migrasi** (skala, ambang trigger, re-underwriting, pricing linkage) | L2 | `[K]` |
| D-16 | **CKPN/PPKA/PD-LGD/write-off/restrukturisasi**: metodologi, observasi, arah jurnal, eligibilitas | L4 | `[K]` |
| D-17 | **EWS**: indikator tier, ambang, frekuensi, SLA tindakan/eskalasi | L5 | `[K]` |
| D-18 | **Stress makro**: variabel, skenario, sumber data, metode, output CAR/CKPN/likuiditas | L6 | `[K]` |
| D-19 | **APU-PPT operasional**: risk rating/CDD-EDD, monitoring, LTKM, retensi, SoD | L7 | `[K]` |
| D-20 | **Tata kelola TI**: residensi data/DRP, audit TI, RTO/RPO, third-party, batas AI/cloud | L8/L9, ekstensi D-09/D-10 | `[K]` |

## 4. Urutan eksekusi yang direkonsiliasi (dependency-aware)
Prasyarat: **F1/F2/F3 LULUS** (fondasi, ledger, master/policy) + definisi `[K]` minimal bertanda DRAFT jelas.  
1. **L1** (BMPK/capacity/Condition) → gerbang kasus aman.  
2. **L3 (RAC kasus+portofolio dasar)** → cegah konsentrasi sebelum keputusan massal.  
3. **L2** (rating/migrasi) → bahasa risiko bersama.  
4. **L5** (EWS tren + action) → deteksi dini (perlu metrik portofolio/rating).  
5. **L4** (CKPN/PPKA/PD-LGD/write-off) → konsekuensi kualitas aset & modal (perlu kolektibilitas/agunan/ledger).  
6. **L6** (stress makro/idiosinkratik) → agregasi (perlu CKPN/portofolio).  
7. **L7** (APU-PPT) dapat paralel L6, selesai sebelum dashboard final/kepatuhan.  
8. **L8** (TI/DRP/residensi) sebelum go-live.  
9. **L9** (data alternatif/AI explainable) setelah inti stabil.

## 5. Aksi segera (aman, tanpa asumsi `[H]`)
1. **Verifikasi primer** nomor/pasal/ambang di `research/05` + tambah V7 (BMPK/PPKA-CKPN/write-off/APU-PPT/LTKM/retensi/TI residensi) `[K]`.  
2. **Kunci definisi formula & policy-as-data** (D-01..D-20) → DRAFT berversi, **bukan ACTIVE** tanpa `[H]`.  
3. **Golden + tes negatif dulu** per lapis (termasuk BMPK ditolak, hapus buku parsial ditolak, override teraudit).  
4. **Rekonsiliasi ke repo existing** `APP BPR/loan-analytic-bpr` (fase 0–11 `[E]`) via gap assessment, bukan overwrite buta.  
5. Catat unknowns di `project/docs/questions.md`.

*Dokumen rekonsiliasi ini adalah peta kerja; bukan keputusan, bukan nasihat hukum, bukan implementasi.*
