# Rencana Research — Menyelesaikan Bloker Tersisa

Status: **DRAFT (rencana kerja)** | Tanggal baseline: **2026-11-05** (Asia/Makassar)  
**Kemajuan live:** lapisan golden `RECOMPUTED_DRAFT_NOT_VERIFIED` telah diisi (29/29) oleh `project/scripts/compute-golden-recomputed.py` + diverifikasi struktural `project/scripts/verify-golden.py` (29 ok, 0 fail); parameter best-judgment tercatat di `docs/policy-draft-best-judgment.md` (NON-ACTIVE). Nilai tetap **belum verified** `[H]`.  
Label: `[E]` bukti teramati, `[A]` usulan, `[K]` perlu konfirmasi/keputusan/pemutus, `[H]` keputusan/nilai terverifikasi bertanda tangan/sumber primer. Dokumen ini **bukan** keputusan dan **bukan** hasil verifikasi regulasi/angka.

**Acuan data (aman):** workbook master hanya dirujuk pada level **struktur/nama sheet/alur** (tanpa isi sel, tanpa rumus, tanpa PII/data nasabah). Data Excel adalah **dummy** `[E user]`. Larangan: menyalin PII/isi sel ke repo/prompt/log; menandai kasus golden/angka sebagai terverifikasi atau keputusan sebagai `[H]` tanpa bukti; memakai faktor konversi flat→efektif 1,81x / 0,87x (EIR/IRR = numerik) `[K]`.

**Sumber bukti baseline `[E]`:** `04-keputusan-risiko-gap.md` (D-01..D-12, T1–T15, gap §5, pertanyaan §6); `docs/questions.md` (V1–V7, V1a–d/V3a–d/V4a–c, RAT-01..03, C-002, prasyarat); `docs/decisions/register-d01-d20.md` (29 kasus register DRAFT/UNKNOWN); `docs/golden/manifest.json` + `docs/golden/cases/` (**29 kasus, 29 unverified, `pii:false`, `dummy_data:true`**, 4 `negative_test` = G-RAC-01/02, G-CKP-01, G-WO-01); `docs/spec/*` (kerangka DRAFT `[K]`); `project/docs/spec/gap-assessment-existing-repo.md`; `research/` (peta regulasi/sintesis/rekonsiliasi/verifikasi-primer V1,V3,V4); struktur sheet master (X1 TPP/EDDIY: analisis kredit, hitungan, Sheet1, MEMO CAIR, SPK, … Scoring-konsumtif, FLAT-ANN-EFF, ANALISA&USULAN; X2 Modal Kerja V12 F4 & X3 Grace Period V12 F4: 15 sheet 0.Cover & Dashboard s.d. 14.Checklist Dok & Verifikasi). **QA existing repo** `APP BPR/loan-analytic-bpr` (live, sesi ini): Larastan hijau setelah perbaikan; `UiRenderTest` 5 passed; run penuh terakhir: **6 failed, 93 passed (1566 assertions)** — residual non-UiRender perlu triase terpisah (teknis, bukan bloker regulasi/golden).

---

## 1. Inventaris bloker tersisa (dipetakan ke pemilik riset)

| # | Bloker | Bukti `[E]` | Memblokir | Jalur riset | Output riset |
|---|---|---|---|---|---|
| B1 | **Golden case terverifikasi < target** (29 struktur ada, **0 terverifikasi**); nilai C-002 masih klaim `[K]` | `docs/golden/*` unverified=29; `04` §5/§3 | F4 parity, scoring/IDIR, PR-C/E | R-GOLD | ≥25 kasus dummy **terverifikasi** (`verification.verified=true`, `verified_by/at/evidence`) berbasis rujukan sheet/sel atau script numerik independen berlabel |
| B2 | **RAT-02/C-002**: rujukan workbook/sheet/**sel** + definisi komponen net income & EIR/arus kas disclosure untuk IDIR 85,93%, net 2.948.080, RATE/EIR eksak (contoh 28,88% @60 bln) | `docs/questions` RAT-02/C-002; `04` §3 | B1 (G-TPP-01..05), PR-C | R-GOLD | lembar verifikasi kasus (sheet/sel, rumus/komponen, definisi) `[K→H bila dikonfirmasi]` |
| B3 | **D-01..D-12** belum `[H]` (register DRAFT/UNKNOWN) + **RAT-01** (ratifikasi ADR-001..026 ↔ D-01..D-20, format register) | `docs/decisions/register-d01-d20.md`; `docs/questions` RAT-01 | F2–F8 sesuai scope, produksi, penutupan spec | R-DEC | keputusan bertanda tangan per D-xx (tanggal, pemutus, bukti, dampak, versi policy) + matriks ADR↔D |
| B4 | **RAT-03 / D-05–D-06**: pembulatan, pengakuan pendapatan, alokasi bayar, denda, penalti (10% vs 15%), perlakuan saldo blokir lama | `docs/questions` RAT-03; `04` D-05/D-06,T9/T10 | golden jadwal/jurnal/dana diterima, PR-C/E, ledger posting | R-DEC,R-GOLD | kebijakan berversi `[H]` + golden G-TPP-04/05, G-SCH-* selaras |
| B5 | **Verifikasi primer regulasi V1,V3,V4** (nomor/tahun/pasal/ayat/berlaku/tautan/unduh) — klaim riset = `[K]` | `docs/questions` V1,V3,V4,V1a–d,V3a–d,V4a–c; `research/verifikasi-primer/*` | D-07 (PPKA/LTV/haircut), D-03 (consent SLIK), D-05/D-06 (transparansi/EIR/biaya), spec `legal_basis` | R-REG | evidence note per V (sumber resmi OJK/peraturan.go.id, kutip pasal relevan) → update `docs/claims/decisions/spec` |
| B6 | **Regulasi/rujukan lain `[K]`**: V2,V5,V6,V7 (manajemen risiko/5C, akuntansi-COA-CKPN-KPMM, tarif asuransi/template legal/matriks/ambang, BMPK/APU-PPT/PPATK/POJK 34-2025 TI) | `docs/questions` V2,V5,V6,V7; `04` D-02,D-07,D-08,D-11..D-20 | D-01,D-02,D-07,D-08,D-11..D-20, layer L1–L9 | R-REG | evidence note + keputusan/policy `[H]` per domain |
| B7 | **Spec pack bernilai & traceability** (kerangka ada, nilai/pasal `legal_basis` kosong/`[K]`) | `docs/spec/*` README; `04` §5 | konsistensi implementasi, kepatuhan | R-SPEC (bergantung R-REG,R-DEC,R-GOLD) | spec terisi referensi sumber (sheet/sel **tanpa isi data**) + `legal_basis` terverifikasi + peta traceability |
| B8 | **Korpus sampel SLIK/KTP/KK/daftar TPP anonim** (di luar git) | `04` §5; D-03,D-10 | akurasi parser F5 | R-DATA | 10–20 sampel **dummy/anonim** + manifest + checklist anonimisasi/consent; tanpa OCR/LLM cloud kecuali D-10 `[H]` |
| B9 | **Tarif asuransi resmi, tabel haircut % + pasal, COA mapping** | `04` D-07,D-08,D-01,§5 | F3/F4/F7/F8 | R-DATA,R-DEC | master berversi (disahkan oleh/versi/pasal) bertanda DRAFT→`[H]` |
| B10 | **QA residual repo existing** (6 failed, 93 passed; 1566 assertions) `[E]` | run `php artisan test --compact` (APP BPR/loan-analytic-bpr) | baseline rilis/gate fase terkait | R-QA (paralel, teknis) | daftar kegagalan + akar masalah + perbaikan terpisah; `composer qa`/suite hijau per scope |
| B11 | **Ambiguitas jalur eksekusi** (lanjut `APP BPR/loan-analytic-bpr` dengan gap assessment vs terpisah) & prasyarat F1/F2/F3 LULUS | `docs/questions` prasyarat; `docs/spec/gap-assessment-existing-repo.md` | semua implementasi lanjutan | R-DEC | keputusan `[H]` jalur + status gate F1–F3 (Kartu Bukti) |

**Dependensi kunci:** B2 → B1 (subset C-002); B5/B6 + B3/B4 + B9 → B7; B8 → F5 (terpisah git, dummy/anonim); B10 paralel; B11 mengarahkan *di mana* B1/B7 diterapkan.

---

## 2. Workstream riset (metode, pertanyaan, sumber, deliverable)

### R-GOLD — Verifikasi golden case (≥25) & C-002 (RAT-02)
- **Pertanyaan riset:** (a) Untuk G-TPP-01 (C-002), komponen numerik mana (dari sheet/sel mana, **tanpa menyalin PII**) yang menghasilkan IDIR 85,93%, penghasilan bersih 2.948.080, dan RATE/EIR eksak contoh 28,88% @60 bln? (b) Definisi **IDIR/DSR** (basis: angsuran baru saja vs total+SLIK) dan komponen **net income** (apa termasuk/keluar) yang disahkan? (c) Definisi arus kas **EIR/IRR** (biaya/fee/blocked saving/dana diterima ikut? timing?) untuk disclosure (V4/D-05)? (d) Nilai terverifikasi 28 kasus lain per fokus (jadwal 3 metode, grace bullet/kecukupan, RPC/DSCR/plafon, MK, stress, PPKA 4-lapis no double-haircut, 5C boundary/override, decision hard-stop, RAC/BMPK, CKPN/PPKA arah, write-off) — sumber workbook sheet/sel **atau** perhitungan ulang script (Python Decimal/BCMath-equivalent) berlabel `dummy`.
- **Metode:** (1) *Verifikasi primer kasus* — pemilik domain membuka workbook master, catat **hanya** rujukan `workbook/sheet/sel` + rumus/komponen + status (dummy), kirim lembar verifikasi (tanpa screenshot ber-PII). (2) *Rekalkulasi independen* — hitung ulang dengan engine referensi numerik (IRR Newton-Raphson/bisection; BIGINT rupiah; kebijakan pembulatan sesuai D-05 bila sudah `[H]`, jika belum: catat asumsi + varian). (3) *Konsiliasi* — bandingkan nilai; selisih → investigasi (T1–T14), bukan menimpa. (4) Isi `expected.verified`/`verification` per `docs/golden/cases/G-*.json`; tandai sumber.
- **Sumber:** workbook master (struktur diketahui `[E]`: X1 sheet `analisis kredit/hitungan/Scoring-konsumtif/FLAT-ANN-EFF/MEMO CAIR`; X2/X3 sheet 2,3,4,5,6,7,8,13,14) — **sel TBD `[K]` di fixture**; kebijakan internal `[H]` (D-05/D-06/D-11); rujukan primer V4 untuk EIR disclosure.
- **Kriteria terima:** ≥25 kasus `verification.verified=true` + bukti rujukan; C-002 `claimed` diganti/dikonfirmasi oleh `verified` atau dinyatakan “tidak terverifikasi/berbeda” dengan bukti; **tanpa 1,81x/0,87x**; invarian jadwal (Σpokok=plafon, saldo akhir 0, debit=kredit untuk jurnal terkait) teruji; negative_test punya asersi negatif.
- **Pemilik `[K]`:** Kredit/Analis + Akuntansi (definisi komponen) + Dev/QA (rekalkulasi/script) + Reviewer.

### R-REG — Verifikasi primer regulasi (V1,V3,V4 prioritas; V2,V5,V6,V7 lanjutan)
- **Pertanyaan riset (prioritas):**
  - **V1/D-07,D-16:** POJK kualitas aset **BPR/BPRS yang berlaku** (nomor/tahun/pasal/ayat/tanggal berlaku): kategori agunan & bobot/PPKA (persentase, hair cut, pengurang), penilaian agunan (independen/frekuensi), hapus buku/restrukturisasi — **bukan** meneruskan persentase dari riset sekunder tanpa naskah resmi. (V1a–d `research/verifikasi-primer/V1-PPKA-kualitas-aset-BPR.md`).
  - **V3/D-03:** POJK/pedoman SLIK yang berlaku: bentuk persetujuan debitur, dasar penggunaan/pembagian, kewajiban koreksi, larangan/retensi — untuk desain consent, review-first, audit. (V3a–d).
  - **V4/D-05,D-06:** aturan transparansi/suku bunga/biaya BPR yang berlaku: kewajiban pengungkapan nominal/efektif/total biaya, komponen biaya, denda/penalti/pelunasan, simulasi — mendefinisikan arus kas EIR disclosure. (V4a–c).
  - **V2,V5,V6,V7:** manajemen risiko/5C/kewenangan; akuntansi/COA/CKPN-KPMM; tarif asuransi/template legal/ambang; BMPK/APU-PPT (LTKM/retensi/SoD `[K klaim independen]`)/POJK 34/2025 TI-residensi-DRP `[K klaim independen]`.
- **Metode:** (1) Ambil naskah **resmi** (ojk.go.id/peraturan.go.id/JDIH OJK) — catat nomor, tahun, pasal/ayat, tanggal berlaku/perubahan, URL, tanggal unduh; simpan metadata evidence (bukan mengunggah konten berhak cipta yang tidak perlu; kutip minimal relevan). (2) Isi template evidence note per V (`research/verifikasi-primer/*` + `docs/claims.md` bila dipakai). (3) Petakan “pasal → aturan engine/UI/policy” dan “belum diatur → `[K]`/kebijakan internal”. (4) **Tandai bentrok** klaim riset independen (ambang/angka/frekuensi) sebagai `[K]` sampai naskah/policy `[H]` mengonfirmasi.
- **Sumber:** situs resmi OJK/peraturan.go.id; internal Compliance/Legal/DPO (kebijakan, template, tarif). **Larangan:** mengutip nomor/pasal/angka regulasi dari ingatan model — hanya dari naskah/evidence.
- **Kriteria terima:** tiap V prioritas punya evidence note lengkap (metadata + pasal relevan + dampak ke D-xx/spec) atau dinyatakan “tidak ditemukan/berubah” dengan jejak pencarian; `docs/spec/*legal_basis` menunjuk evidence.
- **Pemilik `[K]`:** Compliance/Legal (lead), Risk/Akuntansi/IT/DPO per domain.

### R-DEC — Penutupan keputusan D-01..D-20 & RAT-01/RAT-03 (paket rapat)
- **Pertanyaan riset/putusan:** setiap D-xx di register + RAT-01 (ADR↔D, format final) + RAT-03 (D-05/D-06). Termasuk ambang **IDIR/DSR** (D-11, definisi komponen+hard-stop), **band 5C/FSS TPP** (D-02), **authority limits/maker-checker** (D-12), **PPKA/LTV basis tunggal** (D-07, pasca V1), **COA & ledger resmi/bayangan** (D-01), **consent/format SLIK** (D-03 pasca V3), **hosting/retensi/OCR-LLM cloud** (D-09/D-10).
- **Metode:** (1) Susun *decision brief* per klaster (Kredit/Risk: D-02,D-07,D-11,D-12,D-13–D-18; Akuntansi: D-01,D-05,D-06,D-16; Kepatuhan/DPO/IT: D-03,D-09,D-10,D-19,D-20; Tarif/ops: D-08) berisi opsi `[A]`, dampak, ketergantungan regulasi (hasil R-REG), dan default aman (DRAFT/non-ACTIVE, hard-stop hanya `[H]`). (2) Rapat putusan → catat `tanggal, pemutus, bukti/tautan, ringkasan, dampak, versi policy` dan ubah status ke `[H]`/ACTIVE hanya yang disahkan. (3) Ratifikasi ADR↔D (RAT-01).
- **Sumber:** `docs/decisions/register-d01-d20.md`, `docs/questions.md`, hasil R-REG, kebijakan/tarif internal.
- **Kriteria terima:** D-xx yang memblokir fase target bertanda `[H]`; sisanya tetap DRAFT eksplisit; policy berversi (angka/band/ambang) tersimpan sebagai data, bukan hardcode.
- **Pemilik `[K]`:** Direksi, Komite Kredit, Risk, Akuntansi, Kepatuhan/DPO, IT, HR/Operasional (per D-xx).

### R-SPEC — Konsolidasi spec pack & traceability (setelah R-REG/R-DEC/R-GOLD cukup)
- **Pertanyaan riset:** apakah tiap modul/rumus UI dapat ditelusur ke (sumber workbook sheet — **tanpa isi data**, atau golden case id) + `legal_basis` + versi policy `[H]`?
- **Metode:** isi `docs/spec/*` dari template; peta `T1–T14` → kontrol/tes; tautkan D-xx & V-evidence; perbarui `adr-to-d01-d20-mapping.md`.
- **Kriteria terima:** setiap aturan numerik/regulasi di spec punya status (`[E]/[K]/[H]`) dan rujukan; tidak ada angka regulasi tanpa sumber.
- **Pemilik `[K]`:** Dev/Analis + reviewer domain.

### R-DATA — Korpus dummy/anonim & master berversi (D-03,D-07,D-08,D-01)
- **Pertanyaan riset:** format SLIK riil yang diterima & variasi field; kebutuhan field KTP/KK (parse lokal, NIK decode cross-check); struktur tarif asuransi & COA inti.
- **Metode:** kumpulkan 10–20 sampel **dummy/anonim** (redaksi/rekayasa, bukan data nyata) di **luar repo publik**; buat manifest + checklist anonimisasi; definisikan master tarif/haircut/COA sebagai data berversi bertanda sumber/pengesah (nilai disahkan → `[H]`). Default **tanpa** OCR/LLM cloud (D-10) kecuali `[H]`.
- **Kriteria terima:** korpus lolos checklist (no PII), parser diuji pada korpus; master punya `disahkan_oleh/versi/pasal/tanggal`.
- **Pemilik `[K]`:** Kepala Kredit, IT, Kepatuhan/DPO, Operasional/Keuangan.

### R-QA — Triase baseline teknis (paralel, non-bloker regulasi)
- **Bukti `[E]`:** 6 failed, 93 passed (1566 assertions) pada `APP BPR/loan-analytic-bpr` (sesi ini). Larastan & UiRenderTest sudah ditangani terpisah.
- **Metode:** jalankan ulang, kelompokkan kegagalan (env/DB-test, route/middleware/permission, Livewire state, data seed, flake), perbaiki per PR kecil tanpa mengubah asumsi domain/angka `[K]`; jaga hijau per scope.
- **Pemilik `[K]`:** Dev/QA.

---

## 3. Urutan eksekusi & dependensi (gelombang)

| Gelombang | Fokus | Unblocks | Gate keluar (bukti) |
|---|---|---|---|
| **W0 (segera, paralel)** | **RAT-02** (lembar rujukan sheet/sel C-002, tanpa data) + **RAT-01** (keputusan format/jalur) + mulai **R-REG V1,V3,V4** (unduh naskah resmi, evidence note) + **R-QA** triase 6 failure | arah verifikasi, kepatuhan prioritas, stabilitas teknis | lembar RAT-02 terisi rujukan; evidence V1/V3/V4 (metadata+pasal) atau log pencarian; daftar akar masalah QA |
| **W1** | **R-DEC** klaster D-05/D-06/D-11 (RAT-03) + D-01/D-03/D-07/D-10 (pasca V1/V3/V4) + **R-GOLD** G-TPP-01..05 & G-SCH-* (rekalkulasi independen) | definisi komponen & kebijakan hitung; subset golden inti | D-05/D-06/D-11 `[H]` (atau DRAFT eksplisit); ≥8 kasus inti terverifikasi berjejak |
| **W2** | **R-GOLD** sisa ke ≥25 (CAPACITY/COLLATERAL/SCORING/DECISION/RAC/CKPN/WO) + **R-DEC** D-02,D-08,D-12 (+ D-13–D-20 bertahap) + **R-DATA** korpus dummy/anonim & master DRAFT | F4 parity, scoring, kontrol risiko | ≥25 `verified` + 4 negative_test aktif; master tarif/haircut/COA berversi (DRAFT/`[H]`) |
| **W3** | **R-SPEC** konsolidasi + **R-REG** V2,V5,V6,V7 + ratifikasi ADR↔D + pemutakhiran `04`/`docs/questions` status | traceability, gate fase terkait, produksi | spec `legal_basis` terisi; pertanyaan resolved dicentang dengan bukti; register `[H]` untuk D-xx yang dipakai |

*Gelombang boleh tumpang tindih; yang tidak boleh tumpang tindih: menandai `[H]`/verified tanpa bukti.*

---

## 4. Artefak yang dihasilkan & lokasi

| Artefak | Lokasi rencana | Aturan |
|---|---|---|
| Lembar verifikasi C-002 / per kasus | `docs/golden/evidence/` (baru, saat dieksekusi) atau `project/tests/golden/*` | rujukan sheet/sel + komponen; **no PII, no isi sel data**; dummy berlabel |
| Fixture golden terisi | `docs/golden/cases/G-*.json`, `manifest.json` | `verification.verified/verified_by/verified_at/evidence` terisi hanya dengan bukti |
| Evidence note regulasi V1..V7 | `research/verifikasi-primer/*` + ringkas ke `docs/claims.md`/spec | metadata resmi lengkap; kutip minimal; nomor/pasal dari naskah |
| Keputusan `[H]` | `docs/decisions/register-d01-d20.md` (atau format RAT-01) + `project/docs/decisions.md` | bertanda tangan/log putusan |
| Spec pack final-draft | `docs/spec/*` | status `[E/K/H]` per aturan; traceability |
| Korpus SLIK/KTP/KK (dummy/anonim) | **di luar repo publik** (lokasi disepakati D-09) | lolos anonimisasi; manifest di repo boleh, data tidak |
| Laporan QA residual | issue/PR + `docs/spec/gap-assessment-existing-repo.md` diperbarui | bukti run hijau per scope |

---

## 5. Risiko riset & mitigasi
- **Regulasi/pasal dikutip dari ingatan** → mitigasi: R-REG wajib naskah resmi + metadata; bila tak ditemukan, tulis `UNKNOWN` + jejak cari.
- **PII/isi workbook bocor ke artefak** → mitigasi: hanya sheet/sel/struktur; korpus dummy/anonim; review; patuhi `.gitignore` (`*.xlsx`).
- **Golden “diverifikasi” tanpa sumber** → mitigasi: field `verification` wajib bukti; selisih rekalkulasi = investigasi (T1–T14), bukan paksaan angka.
- **Mengunci angka/band DRAFT jadi seolah final** → mitigasi: hard-stop/produksi hanya pada `[H]`; policy DRAFT non-ACTIVE.
- **Konflik T1 (IDIR hard-stop vs soft-score)** → mitigasi: R-GOLD/R-DEC wajib menguji G-DEC-01 & merumuskan aturan konflik (ditandai, bukan disembunyikan).

## 6. Definisi “bloker riset” selesai (per item)
- **B1/B2:** ≥25 golden verified berjejak; C-002 terkonfirmasi/terkoreksi dengan definisi komponen.
- **B3/B4/B11:** D-xx pemblokir fase bertanda `[H]`; RAT-01/03 selesai.
- **B5/B6:** V prioritas (min. V1,V3,V4) punya evidence note resmi; V lain berstatus jelas.
- **B7:** spec dapat ditelusur end-to-end (modul↔sumber↔legal↔policy↔tes).
- **B8/B9:** korpus dummy/anonim siap uji & master berversi bertanda sumber.
- **B10:** suite/QA scope relevan hijau dan terdokumentasi (atau sisa diisolasi eksplisit).

**Langkah langsung (W0, butuh manusia):** konfirmasi rujukan **sheet/sel** C-002 (RAT-02, tanpa data sensitif), putuskan **jalur repo** (B11/D-04), dan tugaskan Compliance untuk unduh + evidence **V1,V3,V4** dari sumber resmi. Setelah itu saya dapat lanjut mengisi artefak (R-GOLD fixture, spec traceability, triase QA) sesuai bukti yang diberikan.
