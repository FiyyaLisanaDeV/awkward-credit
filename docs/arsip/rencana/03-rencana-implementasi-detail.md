# 03 — Rencana Implementasi Detail (CommandCode BPR)

Acuan: [README](README.md), [01 inventaris](01-inventaris-excel.md), [02 pemetaan](02-pemetaan-excel-ke-aplikasi.md), [04 keputusan/risiko/gap](04-keputusan-risiko-gap.md). Label: `[E]` bukti Excel/repo, `[A]` desain/usulan, `[K]` keputusan/konfirmasi, `[H]` keputusan bertanda tangan (belum ada kecuali disebut), `[!]` tidak boleh disalin mentah.

## 1. Sasaran produk
Membangun aplikasi analisis kredit BPR yang mereproduksi (dengan koreksi) logika bisnis 3 workbook (`X1 TPP`, `X2 Modal Kerja`, `X3 Grace Period`) dalam satu alur terdigitalisasi: **permohonan/profil → analisis & simulasi → dokumen & checklist → approval → akad/pencairan → angsuran/ledger** `[A]`. Semua hasil analisis **snapshot** (input + versi policy/engine + output) agar dapat diaudit/di-replay `[A]`.

### Cakupan rilis (usulan) `[A]`
| Rilis | Cakupan inti | Sumber Excel |
|---|---|---|
| R1 (TPP end-to-end) | Fondasi (auth/tenant/RBAC/audit/file), master & policy, jadwal Flat+biaya+dana diterima+top-up, IDIR/DSR (SLIK) + scoring TPP (redesign), workflow & dokumen TPP, memo cair → ledger, blokir tabungan, import SLIK/KTP/KK/daftar TPP minimal | X1 (+ referensi X2 master data bila relevan) |
| R2 (Produktif) | Neraca/LR/rasio/MK/DSCR/plafon max/stress/5C/rekomendasi, Flat/Anuitas/Grace + balloon, dokumen MASTER, appraisal/LTV POJK final, verifikasi lapangan | X2, X3 |
| R3 (Servicing lanjut) | Penagihan SP1–3 alur kerja, kolektibilitas/DPD, rekonsiliasi core banking, e-sign/materai, Dukcapil/integrasi (jika diizinkan), pelaporan lanjut | X1 (SPK penagihan), kebutuhan operasional `[K]` |

## 2. Prinsip arsitektur & guardrail implementasi
1. **Monolit modular domain** (`app/Domain/<Modul>`), controller/resource tipis, **logika bisnis murni** di Engine (tanpa Eloquent/HTTP), I/O via DTO readonly `[A]`.
2. **Uang = BIGINT rupiah** (`Money` value object) + `brick/math`/BCMath; **tanpa float**. Satu kebijakan pembulatan (selisih ke angsuran terakhir bila diputuskan) `[K D-05]`.
3. **Policy/parameter sebagai data berversi** (`policy_sets`, `ref_*`), snapshot per analisis; tidak ada konstanta bisnis tersembunyi di kode/dokumen. Provenance `sumber` wajib `[A,E pola repo]`.
4. **Ledger append-only** (financial double-entry + audit event), idempoten, hash chain, trigger immutable; koreksi hanya jurnal pembalik; maker-checker `[A]` (detail D-01).
5. **Tenant/cabang scope** wajib; RBAC + policy per aksi; PII terenkripsi + blind index + masker + audit akses `[A]`.
6. **Golden test sebelum engine/fix**; parity terhadap workbook referensi (toleransi disepakati, mis. ≤ Rp1/angsuran untuk jadwal) `[A]`. **Koreksi wajib diterapkan** (IDIR/penghasilan/rate eksak; tanpa faktor 1,81x) — nilai final dari kasus sumber `[K]`.
7. **Import review-first**: staging/pratinjau, skor keyakinan, provenance, tidak auto-commit di bawah ambang; consent SLIK aktif sebelum akses `[A,E X1]`.
8. **Dokumen dari model**: nominal/tanggal/terbilang dari data snapshot; tanpa angka hardcode/TERBILANG add-in; versi+hash+QR `[A, !T9/T12]`.
9. **Koreksi bug Excel wajib** (lihat §6 & dok 04): jangan port T2–T9/T12/T13/T14 apa adanya.

## 3. Arsitektur target (komponen)
| Layer | Komponen | Catatan |
|---|---|---|
| UI | Livewire/Blade (Filament hanya bila D-04 `[K]`), wizard permohonan, workbench analisis (tab ala sheet), simulator plafon/tenor, layar review import, dashboard KPI/red flag | tanpa build step berat bila mengikuti ADR-008 repo `[E]` |
| API | REST/Sanctum untuk integrasi/internal (opsional R1) | scope tenant/cabang |
| Domain Engines (murni) | `ScheduleEngine` (Flat/Anuitas/Grace), `FeeEngine`, `PayoffEngine`, `TppCapacityEngine` (IDIR+DSR), `CapacityEngine`/`Dscr/MaxLoan`, `WorkingCapitalEngine`, `RatioEngine`, `StressTestEngine`, `CollateralEngine` (nilai pasar/likuidasi/PPKA/LTV), `ScoringEngine` (FSS TPP redesign) & `Scoring5CEngine`, `BalloonEngine`, `DecisionEngine`, `InsuranceEngine`, `Terbilang/Money` | input/output DTO; deterministik; versioned |
| Orkestrasi | `AnalysisSnapshotService`, `ApprovalService`, `DocumentService`, `ImportPipeline`+adapter, `JournalPoster`/`LedgerService`, `AuditRecorder`, `ConsentService`, `FileService` | Eloquent/DB/queue di sini |
| Data | PostgreSQL (numeric untuk uang/rasio, jsonb payload/snapshot), storage privat terenkripsi, migrasi reversible | UUID v7/usulan `[A]`, soft delete sesuai kebutuhan |
| Integrasi (bertahap) | OCR lokal opsional (Tesseract), `pdftotext`, PhpSpreadsheet (impor workbook), DomPDF/PHPWord, core banking via file/rekonsiliasi `[K D-01/D-03/D-10]` | tanpa layanan eksternal default |

## 4. Engine & formula (spesifikasi implementasi) — **dengan koreksi**
> Semua rumus diverifikasi ke golden case sumber **dummy/terverifikasi** sebelum dikunci `[K]`. Nilai ambang dari policy data. (Klarifikasi user 2026-11-05: data input Excel = dummy.)

### 4.1 ScheduleEngine (Flat / Anuitas / Grace) `[E X1,X2,X3]`
- **Flat**: bunga/period tetap = `plafon * rate_tahun / 12`; pokok/period = `plafon / tenor` (penyesuaian pembulatan di periode terakhir) `[K D-05]`; angsuran = pokok+bunga. (X1: provisi/admin terpisah dari jadwal pokok/bunga).
- **Anuitas**: angsuran tetap (PMT) = `P * i / (1-(1+i)^-n)`, `i=rate/12`; bunga_t = saldo_t * i; pokok_t = angsuran − bunga_t; saldo akhir = 0 (koreksi pembulatan akhir).
- **Grace (X3)**: selama masa grace (sesuai definisi tenor grace) **bunga berjalan = saldo*rate/12**, pokok = 0 kecuali periode akhir; **periode akhir** melunasi sisa pokok (balloon) `[E: X3 sheet 13 formula MIN($C$8, saldo) & label pelunasan akhir tenor; sheet 7 kewajiban jatuh tempo grace = pokok+bunga akhir]`. **Asumsi implementasi `[A,K]`**: grace = bunga saja tiap bulan, pokok bullet di akhir (khas grace period X3) — parameterkan jika ada varian “grace sebagian pokok”.
- Output per baris: periode, saldo awal, pokok, bunga, angsuran, saldo akhir, status/flag (pelunasan akhir tenor). **Satu sumber jadwal** untuk semua dokumen `[!T8]`.
- **Bunga efektif**: hitung eksak (IRR/RATE numerik) untuk display; **jangan** pakai konstanta pendekatan 1,81x/0,87x `[! koreksi wajib, E X1 FLAT-ANN-EFF hanya pembanding]`.

### 4.2 FeeEngine & dana diterima `[E X1,X2,X3]`
- Komponen: provisi, administrasi, notaris (tetap/param), asuransi jiwa (tarif‰ × plafon + komponen tetap, per penanggung/umur/masa `[K D-08]`), appraisal, survey, biaya lain, asuransi jaminan/kebakaran/kendaraan, blokir tabungan (**2× angsuran** X1), top-up pelunasan pinjaman lama (pokok+bunga berjalan+penalti). `[E X1 hitungan/memo cair]`
- **Dana diterima** = plafon − total biaya − blokir − pelunasan lama (± perlakuan saldo blokir lama) — **harus eksplisit & konsisten** dengan jurnal (T10 `[K D-06]`); uji penerimaan: saldo rekening tujuan = dana diterima.

### 4.3 PayoffEngine (top-up) `[E X1]`
- `pelunasan_lama = pokok_sisa + bunga_berjalan + penalti (rate% * pokok_sisa, param)`. Penalti di Excel inkonsisten (10% vs 15%) `[!T9/D-06]` → parameter berversi.

### 4.4 Kapasitas — TPP: IDIR & DSR `[E X1, !T1,T2,T4]`
- `penghasilan_bersih = penghasilan_pemohon + penghasilan_pasangan + TPP/pendapatan_lain − pengeluaran − kewajiban_lain` (komponen dari policy/form).
- **IDIR = angsuran_baru / penghasilan_bersih**. Ambang hard-stop dari policy (contoh Excel 80%) `[K D-11]`. **Koreksi angka contoh wajib**: gunakan nilai kasus sumber terverifikasi (IDIR 85,93% / penghasilan bersih 2.948.080 `[K]`), bukan 83%/3,05 jt.
- **DSR total = (angsuran_baru + total_angsuran_eksisting_SLIK) / penghasilan_bersih** `[A perbaikan T2]`. Angsuran eksisting **otomatis dari SLIK** (parser/normalisasi), bukan ketik manual.
- Hard-stop terpisah dari soft-score (FSS/5C) `[A perbaikan T1]`.

### 4.5 Kapasitas — Produktif: RPC/DSCR/Plafon Max `[E X2,X3]`
- `RPC = surplus_bulanan * margin_kapasitas_bayar` (margin dari policy).
- `DSCR = RPC / kewajiban_angsuran_periodik_relevan` (skenario diajukan/direkomendasikan). Status vs `DSCR_min`/`DSCR_ideal` (policy). *X3 grace:* angsuran periodik = bunga berjalan (risiko utama di balloon) `[E X3 sheet 6/7]`.
- Plafon maksimum (policy/engine): **Flat** `≈ RPC / (1/n + i/12)`; **Anuitas** `≈ RPC * (1-(1+i)^-n)/i`; **Grace** `≈ RPC / (i/12)` bila i>0 (kapasitas bayar bunga) `[E X3 sheet 6]` — verifikasi numerik dengan golden.
- Banding plafon rekomendasi vs kebutuhan MK (gap) `[E X2/X3 sheet 6]`.

### 4.6 Modal Kerja & Stress `[E X2,X3, !T7]`
- **Cash Turnover**: biaya operasional tunai/tahun ÷ (hari kerja setahun ÷ periode perputaran). **Working Capital Days**: (pembelian/hari) × hari persediaan. Kebutuhan = rata-rata (atau metode terpilih policy) `[A]`; `gap = max(0, kebutuhan − (aktiva lancar − hutang lancar))`.
- **Perbaikan T7**: biaya operasional/pengeluaran non-angsuran **eksplisit dari baris/komponen LR yang benar** (bukan referensi baris basi “Hiburan”). Golden test pakai **kasus terisi lengkap**.
- **Stress (6 skenario)** `[E]`: dasar; omzet turun ringan/berat; biaya naik; kombinasi (delta dari policy). Output: surplus, RPC, DSCR, status LAYAK/TIDAK.

### 4.7 Neraca, LR, Rasio `[E X2,X3, !T6]`
- LR → surplus/penghasilan operasional. Rasio: current/quick/cash, D/A, D/E, E/A, NPM, ROA/ROE (disetahunkan) `[E X2 sheet 4]`.
- **Perbaikan T6**: klasifikasi aset pakai **enum/kode** (tanpa SUMIF teks yang overlap “Tanah/Kebun”). Ekuitas/modal dari data riil; jika neraca tak balance, tampilkan selisih “tidak diverifikasi” (jangan plug otomatis untuk menaikkan skor Capital).

### 4.8 Agunan, Haircut, LTV, Appraisal `[E X1 apraisal; X2/X3 sheet 1–2, !T13/D-07]`
- Tabel kategori agunan POJK (X2/X3): emas, SHM+HT/fidusia, resi gudang (tenor penilaian), SHM tanpa HT, tanah adat, tempat usaha+kuasa, kendaraan/mesin fidusia, penjaminan BUMN/BUMD, independen ≤1 th, agunan tunai, lain `[E]`. **Persentase & dasar hukum final = [H/POJK/Kepatuhan]** (D-07).
- Pisahkan: **nilai pasar/taksasi**, **nilai likuidasi** (contoh appraisal 80% `[E X1]`), **nilai diperhitungkan/PPKA** (haircut kategori), dan **basis LTV** — hindari double haircut `[!T13]`.
- `LTV = plafon (atau exposure relevan) / basis_nilai_yang_diputuskan` vs `LTV_max` policy. Band skor collateral dari policy `[K]`.

### 4.9 Scoring 5C (produktif) `[E X2/X3, K band]`
- Bobot: Character 25, Capacity 30, Capital 15, Collateral 20, Condition 10 (param). Skor 1–5 per aspek; total tertimbang; rating (≥4 Sangat Layak, ≥3 Layak, ≥2 Layak Bersyarat, else Tidak Layak) `[E X2 sheet 8]`.
- Character/Capacity/Capital/Collateral sebagian terhitung dari SLIK/DSCR/E-A/LTV; **tabel band pemetaan → skor 1–5 wajib diputuskan & berversi** `[K D-02/data]` (Excel hanya contoh/DRAFT). Override SLIK “tidak layak” → skor 1 (sesuai aturan policy) `[E]`. Override manual wajib alasan.

### 4.10 Scoring FSS TPP `[E X1, !T1,T4,T5,D-02]`
- X1 memuat FSS 22 faktor/3 pilar dengan ambang White/Grey/Black dan label indikasi KPR `[E]`. **Jangan port apa adanya**. Rancang model TPP baru sebagai **data berversi** (faktor/bobot/opsi/ambang) disahkan Komite `[K D-02]`; faktor pengalaman kredit/riwayat diturunkan dari SLIK (override beralasan) `[!T4]`.

### 4.11 Balloon (X3) `[E]`
- `kewajiban_akhir = sisa_pokok_jatuh_tempo (+ komponen bunga akhir sesuai definisi grace)`; `perkiraan_penerimaan` dari input (termin/piutang/panen/kontrak) `[E X3 sheet 2]`.
- `rasio_kecukupan = IF(kewajiban_akhir=0,0, perkiraan_penerimaan / kewajiban_akhir)`. Status: input kosong → belum lengkap; **≥1,1** layak buffer; **≥1,0 & <1,1** layak dengan perhatian; **<1,0** tidak layak `[E X3 sheet 6 formula]`. Grace **wajib** sumber+perkiraan pelunasan `[E/A]`.

### 4.12 DecisionEngine `[A, !T1]`
- Satu output keputusan beralasan: **hard-stop** (IDIR/DSR threshold, SLIK fatal, consent/dokumen wajib, balloon < 1 bila kebijakan keras `[K]`, LTV melampaui `[K]`) vs **soft-score** (FSS/5C, stress, mitigasi). Tampilkan konflik (mis. IDIR stop tapi skor tinggi) — tidak disembunyikan `[!T1]`.
- Rekomendasi otomatis (contoh pola X2): DSCR ≥ ideal & skor tinggi → diterima; DSCR ≥ min & skor cukup → syarat; else tolak/mitigasi — **threshold dari policy** `[K]`. Keputusan final = komite/pejabat berwenang (authority limits), bukan engine.

## 5. Model data target (ringkas, selaras X1–X3)
- **Master/policy (versioned):** tenants, branches, products, `policy_sets`, fee_rules, collateral_categories (haircut+pasal/sumber `[H]`), insurers+rate_tables, scoring_models/factors/options, checklist_templates, document_types/letter_templates, institutions/treasurers/collective_agreements, number_sequences, coa_accounts.
- **Parties/consent:** customers (+ identities terenkripsi/blind index, addresses), households/members, employments, businesses, consents (SLIK/PDP, tujuan/masa/bukti).
- **Application/analysis (snapshot):** loan_applications, financial_statements, income_expense_lines, slik_reports/facilities, collaterals, appraisals, verifications, scoring_results, decisions, approvals, `application_snapshots` (jsonb input+output+`policy_version`+`engine_version`+hash).
- **Loan/servicing:** loans, schedules, payments/allocations, blocked_savings, insurance_policies, tpp_batches/items, collection_cases/actions, external_refs.
- **Documents/files/import:** files, generated_documents, import_batches/files/parsed_payloads/field_provenance.
- **Ledger/audit:** ledger_accounts/periods/journals/entries/balances, audit_events, reconciliation_runs/items (append-only + hash chain + immutable trigger).

## 6. Koreksi wajib atas perilaku Excel (jangan di-port) `[E/A]`
| ID | Temuan | Implementasi |
|---|---|---|
| T1 | IDIR vs FSS konflik | DecisionEngine hard-stop vs soft-score + alasan/flag konflik |
| T2,T4 | SLIK tak mengisi IDIR/DSR; skor kredit tak konsisten SLIK | angsuran eksisting & faktor character dari SLIK terstruktur; override beralasan |
| T3 | Anomali data SLIK (duplikat/baki>plafon) | validasi parser + peringatan (bukan silent) |
| T5 | FSS TPP = template KPR | redesign model TPP berversi `[K D-02]` |
| T6 | Neraca double-count/plug | klasifikasi enum, saldo riil, flag tak balance |
| T7 | Referensi baris basi (biaya/stress) | total/komponen eksplisit; golden kasus terisi |
| T8 | PK bisa tak ikut sistem | dokumen dari jadwal/terms aktual (sistem aware) |
| T9,T12 | Angka/terbilang hardcode; TERBILANG eksternal | `DocumentData` + engine terbilang; no hardcode; uji template |
| T10 | Dana diterima vs memo/blokir lama tak jelas | perlakuan blokir lama eksplisit + jurnal seimbang |
| T13 | Double haircut LTV vs PPKA | pisah nilai pasar/likuidasi/PPKA/basis LTV `[K D-07]` |
| T14 | TODAY() ubah umur/hasil; Condition manual | snapshot usia pada `analysis_date`; sub-kriteria Condition |
| PII | X1 berisi data nyata | anonimkan fixture; pemindai PII; no PII di repo/prompt/log |

## 7. Workflow & status `[A]`
`draft → pengumpulan_data/consent → analisis → (verifikasi lapangan) → rekomendasi → komite/approval → disetujui|ditolak|dikembalikan → akad → permintaan_cair (maker-checker) → cair (ledger) → aktif → lunas|restruktur|macet|hapus_buku`. Setiap transisi: service tunggal, policy/authority check, audit event, snapshot referensi bila relevan.

## 8. Ledger & audit (ringkas) `[A, K D-01/D-05/D-06]`
- **Financial**: double-entry, `debit=credit` per jurnal (service+DB guard), idempotency key, `SELECT FOR UPDATE` per loan, period lock; akun awal usulan (kas, piutang pokok, tabungan, tabungan diblokir, titipan premi/notaris, pendapatan bunga/provisi/admin/denda/penalti) **dipetakan ke COA BPR** `[K D-01/D-05]`.
- **Events**: pencairan, potongan biaya, blokir/rilis, top-up, angsuran (alokasi policy), denda, pelunasan dipercepat, pembalik — jurnal eksplisit; audit event untuk status/import/override/akses PII/dokumen.
- **Integrity**: trigger immutable pada `ledger_*`/`audit_events`, hash chain, `ledger:verify` terjadwal/sebelum tutup periode, rekonsiliasi `external_refs` (CIF/No Loan) bila core banking tetap resmi `[K D-01]`.

## 9. Import & data quality `[A,E]`
- Pipeline: upload → validasi (MIME/magic/size/sha256 dedupe) → parse → payload+confidence+provenance → **review berdampingan** → commit. Adapter: SLIK (xlsx/csv/pdf-text/paste), KTP/KK (NIK decode + OCR opsional), workbook legacy, TPP batch, statement, ZIP dokumen, payment CSV.
- Aturan: consent SLIK aktif; validasi SLIK (baki≤plafon flag, duplikat, kolek range, tanggal pull); NIK 16/dekode; deteksi duplikat (NIK/kontak/dokumen/hash). PII tidak ke log/model eksternal tanpa izin `[K D-10]`.

## 10. UI utama (mengarahkan ke sheet Excel) `[A]`
1. **Dashboard kasus** (Cover/0): KPI, red flags, navigasi.
2. **Wizard/Profil** (1): debitur/pasangan/usaha/siklus/aset.
3. **Permohonan & Policy/SLIK** (2): terms, asumsi, SLIK, balloon source (grace).
4. **Workbench Analisis** (3–9): neraca/LR, rasio, MK, kapasitas/DSCR, sensitivitas, 5C, rekomendasi — dengan simulator “plafon/tenor agar IDIR/DSCR lolos”.
5. **Agunan & verifikasi** (10,14): appraisal, foto, checklist lapangan.
6. **Dokumen & approval** (9,11,12,14, X1 dokumen): generate/preview/versi/TTD, komite.
7. **Pencairan & ledger** (memo cair/blokir/jadwal): posting, status, kartu pinjaman.
8. **Import center**: batch, review, provenance, error.

## 11. Keamanan & kepatuhan `[A,K]`
- RBAC least privilege, maker-checker, authority limits (plafon/produk/jabatan). 2FA opsional/bertahap `[K]`.
- PII (NIK/KK/HP/alamat/SLIK) terenkripsi at-rest (app-level/file), masker UI, audit akses/ekspor, retensi/penghapusan `[K D-09]`, consent SLIK/PDP `[E X1]`.
- Storage privat (di luar webroot), signed URL, sha256, backup terenkripsi + uji restore.
- Larangan: PII/data nyata di repo, fixture, prompt, log.

## 12. Strategi testing & golden `[A,K]`
- **Golden vector (sebelum engine):** ≥25 kasus (TPP, Flat, Anuitas, Grace; edge: tenor 1, plafon kecil, usia batas, top-up, grace balloon 1,0/1,1) dari workbook/kasus sumber **dummy & terverifikasi** (sumber sheet/sel jelas). Perbaiki nilai dengan koreksi wajib (IDIR 85,93%/2.948.080/rate eksak) `[K]`. (Klarifikasi user: data Excel = dummy.)
- Unit test engine murni (Schedule/Fee/Capacity/MK/Stress/Ratio/Collateral/Scoring/Balloon/Decision/Terbilang/Money). Toleransi jadwal disepakati (mis. ≤Rp1/period) `[K D-05]`.
- Property/fuzz ledger (seimbang, hash chain, rebuild=idempotent, no UPDATE/DELETE). Concurrency (double payoff).
- Uji dokumen: tanpa angka hardcode; terbilang/nominal cocok snapshot. Uji import: akurasi korpus (target SLIK angka tinggi, KTP NIK layak) `[K D-03/D-10]`.
- Uji otorisasi (tenant/cabang/role) positif-negatif. QA: Pint + Larastan bertahap + Pest + `composer qa` hijau per fase.

## 13. Rencana eksekusi bertahap (detail tugas)
| Fase | Durasi kasar `[A]` | Deliverable kunci | Dependensi/gerbang | Bukti selesai |
|---|---:|---|---|---|
| F0 Persiapan & spec pack | 1 mgg | anonimisasi & spec pack X1–X3, golden vector ≥25, korpus sampel anonim (SLIK/KTP/KK/TPP), keputusan D-01..D-10, scaffold/AGENTS, CI/QA | D-01–D-03,D-05–D-08 minimal; akses sampel `[K]` | spec + keputusan tertulis; QA hijau; golden siap |
| F1 Fondasi | 1.5 | auth, tenant/cabang, RBAC+policy, audit event, file privat+sha256, queue db+scheduler, Money/terbilang/tanggal | F0 | uji isolasi tenant & audit hijau |
| F2 Ledger | 2 | COA mapping, JournalPoster (balance/idempotent/lock/period), trigger immutable, hash chain, `ledger:verify`, rebuild, pembalik maker-checker, laporan dasar, fuzz | D-01,D-05,D-06 | fuzz+verify hijau; no update/delete |
| F3 Master & policy | 1 | products, policy_sets versioned, fee/insurance/haircut POJK, scoring model berversi (termasuk FSS TPP redesign data), checklist/template registry | D-02,D-07,D-08 | seed bersumber/provenance; snapshot policy |
| F4 Engine & parity | 2.5 | semua engine §4 + snapshot + simulator; perbaikan T1–T8,T13,T14 | F0 golden, F3 | **100% golden lulus** (toleransi disepakati) |
| F5 Import | 2.5 | import framework+review, SLIK/KTP/KK, workbook legacy, TPP batch (awal), dedupe/fraud rules | D-03,D-10, korpus | akurasi korpus memenuhi target; provenance |
| F6 Workflow & UI | 2.5 | wizard, workbench analisis, verifikasi lapangan, komite/authority, status engine, dashboard | F1,F3,F4 | kasus TPP & produktif end-to-end (draft→keputusan) |
| F7 Dokumen | 2 | template (TPP+MASTER, batch), numbering/versi/hash/QR, PDF/DOCX legal, uji no-hardcode | F4,F6, review Legal `[K]` | paket dokumen dari snapshot benar |
| F8 Pencairan & servicing | 2.5 | memo cair→ledger, aktivasi+jadwal, angsuran/TPP/blokir/denda/pelunasan, kartu pinjaman, rekonsiliasi bayangan | F2,F4,F6 | dana diterima=saldo; ledger=kartu=rekon sampel |
| F9 Hardening & go-live | 2 | laporan, security/PII review, backup/restore drill, UAT+paralel run ≥30 kasus, SOP/pelatihan | semua | selisih paralel terjelaskan; restore lulus |

> Estimasi ±19–20 minggu (1 dev+agent) atau ±11–12 minggu paralel pasca F2 `[A]` — kalibrasi setelah F1. Jika repo existing sudah melewati sebagian fase (terindikasi 0–11 di PROGRESS `[E]`), lakukan **gap assessment** F1–F11 terhadap rencana ini sebelum melanjutkan.

## 14. Definisi Selesai per PR/fase `[A]`
- Diff kecil & fokus; engine + test; `composer qa` hijau (pint, larastan level bertahap, pest). Tanpa PII.
- Golden/tes relevan lulus; asumsi/pertanyaan dicatat (`docs/questions.md`/PR). Migrasi reversible, seeder idempotent bersumber.
- Fase: bukti verifikasi + parity/ledger/import sesuai gerbang + review silang (model berbeda untuk F2/F4/F5).