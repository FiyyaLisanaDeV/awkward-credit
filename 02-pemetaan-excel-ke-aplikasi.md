# 02 — Pemetaan Excel → Aplikasi (traceability)

Konvensi label: `[E]` fakta dari ekstraksi workbook; `[A]` usulan desain/pemetaan; `[K]` perlu konfirmasi/keputusan; `[!]` temuan risiko/bug Excel.

## 1. Pemetaan produk & sistem
| Workbook | Produk | Sistem bunga `[E]` | Keputusan utama `[E]` | Rilis target `[A]` |
|---|---|---|---|---|
| X1 TPP | Kredit Konsumtif TPP (potong gaji/TPP, bendahara/dinas) | Flat | IDIR (hard stop 80% di sheet) + FSS 3 pilar (soft) | R1 |
| X2 Modal Kerja | Kredit Produktif Modal Kerja | Flat, Anuitas (dropdown) | DSCR + 5C + stress + plafon/MK/LTV | R2 |
| X3 Grace Period | Kredit Produktif Grace Period (superset) | Flat, Anuitas, Grace Period (dropdown) | sama + kecukupan pelunasan balloon | R2 |

## 2. Pemetaan sheet → modul/fitur
### 2.1 X1 — TPP (25 sheet)
| Sheet `[E]` | Fitur/UI | Domain/service `[A]` | Data utama `[A]` | Dokumen output `[A]` |
|---|---|---|---|---|
| `analisis kredit` | Workbench analisis TPP (penghasilan/pengeluaran, SLIK ringkas, IDIR, ringkasan) | `TppCapacityEngine` (IDIR, DSR total dari SLIK), `ConsentService` | permohonan, income/expense, slik summary, consent, snapshot | lembar analisis TPP |
| `hitungan` | Kalkulator angsuran/biaya & simulasi dana diterima | `ScheduleEngine(Flat)`, `FeeEngine`, `PayoffEngine` (pinjaman lama/top-up) | terms, fees, insurance, appraisal, blocked_saving, payoff | rincian hitungan, jadwal |
| `MEMO CAIR` | Pengajuan & persetujuan pencairan | `DisbursementService` → `JournalPoster` | disbursement, fee lines, blocked, payoff | memo pencairan (PDF) |
| `PEMBLOKIRAN TABUNGAN` | Blokir/rilis tabungan (2× angsuran) | `BlockedSavingService`, ledger 2101↔2102 | blocked_savings | surat pemblokiran |
| `SPK` / `PK` / `kuasa` / `Surat Kuasa` / `Surat Pernyataan` | Paket akad & kuasa | `DocumentService`, numbering, template versioning | generated_documents, signatures/consents | SPK/PK/kuasa/pernyataan |
| `Surat Persetujuan Slik` | Consent SLIK (gate sebelum akses/import SLIK) | `ConsentService`, `AuditRecorder` | consents, audit (akses PII) | surat persetujuan SLIK |
| `LEMBAR KONFIRMASI` | Konfirmasi AO (pasangan/penjamin/bendahara) | `VerificationService` | verifications | lembar konfirmasi |
| `FORM PENGKINIAN` | APU-PPT/pengkinian data | `CustomerKycService` | customer updates/snapshots | form pengkinian |
| `REGISTRASI` | Checklist berkas (registrasi) | `ChecklistService` | checklist_status (template 29 butir `[?]`) | checklist registrasi |
| `Scoring-konsumtif` | Skoring FSS TPP (hasil + alasan + override) | `ScoringEngine` (model berversi, **redesign D-02** `[K]`) | scoring_model, factor scores, snapshot | lembar scoring |
| `ASS AL AMIN`/`SUPER ASS`/`ASS JIWA`/`AS. JAMINAN`/`BUMIDA` | Asuransi jiwa/kebakaran/kendaraan/jaminan | `InsuranceEngine`, tarif master per penanggung/produk/umur/masa `[K D-08]` | insurers, rate tables, policies | SPAPP/daftar peserta/pernyataan |
| `apraisal` | Penilaian jaminan (tanah/bangunan) | `AppraisalService` | appraisals, collaterals | laporan appraisal |
| `FOTO` | Bukti foto lapangan/jaminan | `FileService` (privat, thumbnail, sha256) | files | lampiran foto |
| `FLAT-ANN-EFF` | Referensi konversi (read-only) | master data/reporting; **bukan konstanta 1,81x** | reference_rates (opsional) | tabel pembanding |
| `Sheet1`,`ANALISA&USULAN`,`REK LISTRIK` | — | — | — | **tidak dimigrasi** (referensi/arsip) `[A]` |

### 2.2 X2/X3 — MASTER 0–14 (paralel)
| Sheet `[E]` | Fitur/UI | Domain/service `[A]` | Data utama `[A]` |
|---|---|---|---|
| `0.Cover & Dashboard` | Ringkasan kasus, KPI, navigasi/red flag | agregasi snapshot + `RedFlagService` | snapshot, flags |
| `1.Profil Debitur` | Profil pemohon/pasangan/pekerjaan/usaha, tabel aset 10 baris + kategori POJK | `CustomerService`, `BusinessService`, `CollateralService` | customers, households, employments, businesses, collaterals |
| `2.Data Kredit & Asumsi` | Permohonan, parameter kebijakan, haircut POJK, SLIK pemohon/pasangan, riwayat internal; **X3:** sumber & perkiraan pelunasan pokok | `PolicyService` (versioned), `SlikService`, `CollateralCategoryService` | applications, policy_sets, slik_reports/facilities, collateral_categories; X3 balloon fields |
| `3.Neraca & Laba Rugi` | Neraca & LR proforma (input terstruktur) | `FinancialStatementService` | financial_statements, lines — **[!] perbaiki T6: klasifikasi aset enum, bukan SUMIF teks; ekuitas riil, plug ditandai** |
| `4.Rasio Keuangan` | Rasio likuiditas/solvabilitas/profitabilitas + interpretasi | `RatioEngine` | ratio outputs (snapshot) |
| `5.Modal Kerja` | Cash turnover & WCD, gap MK, % omzet | `WorkingCapitalEngine` — **[!] perbaiki T7: total biaya operasional eksplisit** | MK outputs |
| `6.Kapasitas Bayar & DSCR` | RPC, angsuran skenario, DSCR, plafon max (Flat/Anuitas/Grace), kecukupan balloon (X3) | `CapacityEngine`, `DscrCalculator`, `MaxLoanEngine`, `BalloonEngine` (X3) | capacity outputs, balloon ratio/status |
| `7.Sensitivitas` | 6 skenario stress (omzet/biaya/kombinasi) | `StressTestEngine` — **[!] perbaiki T7** | stress outputs |
| `8.Penilaian 5C` | Skor 5C, bobot, rating, override SLIK | `Scoring5CEngine` (band mapping = data/keputusan `[K D-02/band]`) | scoring snapshot, overrides (alasan wajib) |
| `9.Rekomendasi & Keputusan` | Rekomendasi sistem, narasi, keputusan komite berjenjang | `DecisionEngine`, `ApprovalService` (maker-checker, authority limits) | recommendations, approvals |
| `10.Dokumentasi Jaminan` | Foto jaminan (slot) | `FileService` | files |
| `11.Surat Penyerahan Jaminan` | Surat + kuasa jaminan (draf) | `DocumentService` | generated_documents — **[!] review Legal** |
| `12.Perjanjian Kredit` | Draft perjanjian (Flat/Anuitas/Grace) | `DocumentService` — **[!] T8: angsuran/ketentuan harus ikut sistem & jadwal, bukan flat hardcode** | generated_documents |
| `13.Perhitungan Kredit` | Biaya, bunga efektif RATE, dana bersih, jadwal 360 periode | `ScheduleEngine` (Flat/Anuitas/Grace), `FeeEngine` | schedules, fees, effective rate |
| `14.Checklist Dok & Verifikasi` | Dokumen pemohon/jaminan, verifikasi lapangan, kesimpulan | `ChecklistService`, `FieldVerificationService` (GPS/foto opsional) | checklists, verifications |

## 3. Pemetaan field/input Excel → entitas data (target) `[A]`
> Nama tabel final mengikuti konvensi repo/migrasi yang diputuskan; ini peta konsep.

| Kelompok field Excel `[E]` | Entitas/kolom target `[A]` | Catatan/validasi |
|---|---|---|
| Identitas debitur (nama, KTP/NIK, TTL, JK, agama, status kawin, alamat, HP, pendidikan, tanggungan) — X1/X2 `Profil/analisis` | `customers`, `customer_identities` (NIK terenkripsi + blind index), `customer_addresses`, `households` | NIK 16 digit; TTL cocok dekode NIK (perempuan +40) `[K]`; masker tampilan; consent wajib akses SLIK/PII |
| Pasangan/penjamin/pihak terkait | `households`, `household_members` | X1 butuh persetujuan suami/istri & konfirmasi bendahara |
| Pekerjaan/dinas/instansi & TPP, usaha & siklus | `employments`, `institutions/treasurers/agreements` (TPP), `businesses` | X2/X3 siklus belanja/modal per siklus; TPP → batch potong gaji (R1/R3) |
| Permohonan (plafon, rate, tenor, sistem, tujuan, pinjaman ke, AO, cabang) | `loan_applications` (+ `products`, `branches`) | sistem ∈ {Flat,Anuitas,Grace Period}; grace butuh sumber+perkiraan pelunasan `[E X3]` |
| Parameter kebijakan (margin, DSCR min/ideal, LTV maks, hari kerja, sensitivitas) | `policy_sets` (versioned, effective date) | **snapshot per analisis**; nilai dari keputusan/data, bukan konstanta kode |
| Neraca & laba rugi, rasio, modal kerja, kapasitas, sensitivitas | `financial_statements`, `income_expense_lines`, `application_snapshots` (output JSON + versi engine/policy) | [!] T6/T7 diperbaiki di engine; snapshot immutable |
| Aset/agunan (jenis, luas/jumlah, harga, kategori POJK, % diperhitungkan, nilai, pemilik) | `collaterals`, `collateral_categories` (haircut POJK, versioned) | [!] T13/D-07: pisah nilai pasar, likuidasi, pengurang PPKA, basis LTV |
| SLIK pemohon/pasangan (fasilitas: lembaga, plafon, baki, rate, tenor, angsuran, kolek, tunggakan) | `slik_reports`, `slik_facilities` | [!] T2–T4: angsuran eksisting → DSR total otomatis; validasi baki≤plafon, duplikat, tanggal pull; consent aktif; skor character dari SLIK |
| Scoring (FSS 22 faktor/5C, bobot, pilar, rating, override) | `scoring_models/factors/options` (versioned), skor per aplikasi/snapshot | FSS TPP redesign `[K D-02]`; band 5C `[K]`; override wajib alasan+persetujuan |
| Keputusan/approval (rekomendasi, komite, kewenangan) | `approvals`, `authority_limits` | maker-checker; AO tidak self-approve |
| Biaya, asuransi, appraisal, blokir, top-up/pelunasan lama | `fee_lines/rules`, `insurance_policies/rate_tables`, `appraisals`, `blocked_savings`, payoff items | tarif asuransi per penanggung/umur/masa `[K D-08]`; blokir 2× angsuran (param) |
| Jadwal angsuran & balloon | `loan_schedule_items` (+ loan) | selisih pembulatan ke angsuran terakhir (kebijakan) `[K D-05]`; grace: bunga berjalan, pokok akhir |
| Pembayaran/TPP, penagihan (SP1–3, segel/sita) | `loan_payments/allocations`, `tpp_batches/items`, `collection_cases/actions` | ambang hari dari parameter (X1 SPK) `[K]` |
| Dokumen, checklist, verifikasi lapangan, foto | `generated_documents`, `checklist_templates/status`, `verifications`, `files` | angka/terbilang dari model; GPS/foto terkompres; PII mask |
| Import & provenance | `import_batches/files`, `parsed_payloads`, `field_provenance` | tidak auto-commit di bawah keyakinan; sumber field tercatat |
| Ledger & audit, ref core banking | `ledger_*` (append-only), `audit_events`, `external_refs`, `reconciliation_*` | imutabel, idempoten, hash chain, COA mapping `[K D-01/D-05]` |

## 4. Pemetaan dokumen Excel → template aplikasi `[A, perlu verifikasi Legal]`
| Kelompok | Dokumen dari Excel `[E]` | Prioritas |
|---|---|---|
| Analisis/kalkulasi | analisis kredit TPP, hitungan & jadwal, ringkasan MASTER (dashboard/kapasitas/5C/rekomendasi), perhitungan kredit & jadwal | R1/R2 |
| Persetujuan/consent | Surat Persetujuan SLIK, Lembar Konfirmasi, (persetujuan suami/istri/bendahara) | R1 |
| Akad & kuasa | SPK, PK/Perjanjian (Flat/Anuitas/Grace), Surat Kuasa potong TPP, kuasa jual sukarela, penyerahan jaminan, Surat Pernyataan | R1/R2 |
| Pencairan & tabungan | Memo Cair, Pemblokiran Tabungan | R1 |
| Asuransi | ASS AL AMIN, SUPER ASS, ASS JIWA, AS. JAMINAN, BUMIDA/daftar peserta | R1/R2 |
| Compliance & checklist | FORM PENGKINIAN (APU-PPT), REGISTRASI, Checklist Dok & Verifikasi, dokumentasi jaminan/foto, appraisal | R1/R2 |
| Keputusan | Memo usulan & keputusan komite (MASTER sheet 9 / usulan) | R2 |

**Aturan template `[A]`:** semua nominal/tanggal/nama/terbilang dari `DocumentData` (model+snapshot); terbilang dihitung engine (bukan add-in Excel); penomoran versi+QR+hash; DOCX untuk dokumen legal bila diperlukan; review/approval Legal/Notaris sebelum produksi `[K]`.

## 5. Pemetaan import → adapter `[A]` (selaras X1/X2/X3)
| Sumber | Mengisi (dari sheet Excel) | Adapter | Validasi inti |
|---|---|---|---|
| SLIK iDeb (Excel/CSV/PDF teks/tempel) | X1 tabel SLIK/analisis, X2/X3 Sheet 2 SLIK 8 baris pemohon+pasangan | `Slik*Importer` | consent aktif, baki≤plafon (atau flag), kolek 1–5, duplikat, tanggal pull; `file_hash` dedupe |
| KTP (+ KK/NPWP opsional) | X1/X2 identitas | `KtpImporter` (NIK parser selalu, OCR opsional `[K D-10]`) | NIK 16, dekode wilayah/TTL/JK, masker, verifikasi manusia |
| Workbook analisis lama (3 template) | seluruh input kasus X1/X2/X3 | `WorkbookLegacyImporter` | best-effort + mapping wizard + review; cocokkan total/rumus kunci ke engine (parity) |
| Daftar TPP bulanan instansi | potong angsuran TPP | `TppBatchImporter` (CSV/XLSX) | kecocokan NIK/NIP/kredit aktif, alokasi |
| Rekening koran | cross-check cashflow | `StatementImporter` (pluggable) | konsistensi saldo/rata-rata |
| Dokumen massal ZIP | lampiran/dokumen per debitur | `DocumentZipImporter` | konvensi nama, hash, status DRAFT |
| Pembayaran angsuran CSV | jadwal/realisasi | `PaymentImporter` | rekonsiliasi, reversal via jurnal/entri terkontrol |

## 6. Traceability cepat (kebutuhan → Excel → engine → uji)
| Kebutuhan | Bukti Excel `[E]` | Engine/fitur `[A]` | Bukti uji wajib `[A]` |
|---|---|---|---|
| Jadwal Flat/Anuitas/Grace | X1 hitungan/FLAT-ANN-EFF; X2/X3 Sheet 13 | `ScheduleEngine` | golden jadwal, saldo akhir 0, grace pokok akhir |
| IDIR/DSR TPP | X1 analisis kredit (IDIR), SLIK tabel | `TppCapacityEngine` + DSR dari SLIK | golden IDIR/DSR; [!] T2/T4 ditutup |
| DSCR, RPC, plafon max | X2/X3 Sheet 6 | `CapacityEngine`/`MaxLoanEngine` | golden per sistem |
| Modal kerja & stress | X2/X3 Sheet 5,7 | `WorkingCapitalEngine`,`StressTestEngine` | golden; [!] T6/T7 diperbaiki |
| 5C & rekomendasi | X2/X3 Sheet 8,9 | `Scoring5CEngine`,`DecisionEngine` | boundary/rating; band [K] |
| FSS TPP | X1 Scoring-konsumtif | `ScoringEngine` (redesign) | golden setelah D-02 [K] |
| Balloon/kecukupan pelunasan | X3 Sheet 2,6,13 | `BalloonEngine` | rasio 1,0/1,1 boundary |
| LTV & haircut POJK | X2/X3 Sheet 1–2 | `CollateralEngine` | [!] T13/D-07; persen [H] |
| Biaya & dana diterima | X1 hitungan; X2/X3 Sheet 13 | `FeeEngine` | cocok memo cair/fixture |
| Ledger pencairan/angsuran/blokir | (alur X1) | `JournalPoster` | invarian, idempoten, verify hash |