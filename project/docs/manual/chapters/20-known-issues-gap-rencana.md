# Bab 20 — Known Issues, Gap Assessment, Konflik C-002, dan Rencana L1–L9

Bab ini adalah **daftar terbuka** (hidup). Semua item non-final berlabel `[K]`/`[Rencana]`. Rujukan: `commandcode bpr/project/docs/spec/gap-assessment-existing-repo.md`, `adr-to-d01-d20-mapping.md`, `pr-plan-c002-money.md`, `research/*`, `decisions.md`.

## 20.1 Konflik kritis: golden C-002 `[K]`
| Aspek | Fixture repo `[E]` | Koreksi rencana C-002 `[K]` |
|---|---|---|
| IDIR kasus TPP | `0.831124…` (~83,11%) | **85,93%** — recompute `docs/golden/cases/G-TPP-01.json` |
| Penghasilan bersih | `3.048.080` | **2.948.080** |
| Rate/EIR | `0.18` (Flat) | **`rate_annualized_x12 = 28,88%`; effective annual = 33,02%** (numerik, bukan 1,81x) |
| Faktor konversi | — | **tanpa** 1,81x/0,87x (V4) |

- Fixture lama `tests/Fixtures/golden/tpp.json` tetap ada; fixture koreksi **`tests/Fixtures/golden/c002-recomputed.json`** + `GoldenRecomputedParityTest` (status `RECOMPUTED_DRAFT_NOT_VERIFIED`).
- Fixture `tests/Fixtures/golden/tpp.json` dihasilkan `golden:extract` (sel `hitungan!E8/E9/E12`, `analisis kredit!D30–D34,E28`) dan dikunci `FormulaGoldenTest`.
- **Aturan aman**: **jangan ubah** expected test/angka tanpa **sumber terverifikasi** (sheet/sel/dummy berlabel) & keputusan D-05/V4 `[K→H]`. Rencana PR test-first: `pr-plan-c002-money.md` (PR-A..E). Guardrail: no 1,81x, no ubah expected tanpa sumber, no float baru.

## 20.2 Presisi uang `[E]` vs target `[K]`
- Saat ini: kalkulasi `float` + `Rounding` (scale 2/4/2); DB `decimal`.
- Target: **BIGINT rupiah + `brick/math`** (belum terpasang); `Rounding` → adapter policy D-05 `[K]`.
- Migrasi bertahap (schedule→fee→capacity→ledger), tiap PR dengan golden/property/negatif.

## 20.3 Status QA & drift `[?]`
- Snapshot **PROGRESS v2.9 (Fase 15)**: **138 test** (43 file), pint OK, PHPStan L5+L8 OK, `hardcode:audit` 0, `docs:check --strict` 0, `ledger:verify` OK, `verify.sh --full` exit 0.
- Angka live: **61 migrasi · 77 route · 43 file test**.
- Gap lama (Larastan 1 error `Simulator.php:73`, 11 Feature gagal) **sudah ditutup** (commit `011d856` + fase lanjut).
- Drift dokumen lama (63 route/87 test) sudah disinkronkan ke 77 route/138 test.

## 20.4 Gap vs rencana (L1–L9) `[Rencana]`
| Lapis | Topik | Keputusan | Status |
|---|---|---|---|
| **L1** | BMPK (pihak terkait/satu peminjam/kelompok) & modal rujukan | D-13 `[K]` | **Belum ada** |
| **L2** | Rating migrasi (ICRR/ICRS-like), re-underwriting, pricing linkage | D-15 `[K]` | **Belum ada** (5C band ≠ rating PD) |
| **L3** | RAC portofolio (sektor, 10 debitur, instansi TPP, agregat, histori) | D-14 `[K]` | **Belum ada** |
| **L4** | CKPN/PPKA/PD-LGD, arah PPKA↔CKPN→KPMM, write-off non-parsial, restrukturisasi | D-16 `[K]` | **Belum ada** |
| **L5** | EWS tren + ambang tier/SLA tindakan/eskalasi | D-17 `[K]` | **Belum ada** |
| **L6** | Stress makro (5 variabel/skenario/idiosinkratik) → CKPN/CAR/likuiditas | D-18 `[K]` | **Belum ada** |
| **L7** | APU-PPT (CDD/EDD, monitoring, LTKM, retensi, SoD) | D-19 `[K]` | **Belum ada** |
| **L8** | Tata kelola TI/POJK 34/2025 (residensi/DRP/audit TI/RTO-RPO/third-party) | D-20 `[K]` | **Belum ada** |
| **L9** | Data alternatif / AI explainable / golden | D-20 `[K]` | **Belum ada** |

Yang **sudah parsial** `[E]`: analisis sensitivitas (omzet/biaya) untuk L6-lite; scoring 5C band (bukan L2); red flag (bukan L5-EWS).

## 20.5 Gap data/spec
- Golden: **29 kasus** ada dengan lapisan `recomputed` (Decimal independen), **0 verified**; menunggu RAT-02 (sheet/sel) + tanda tangan `[E/K]`.
- Spec pack & traceability sumber sebagian (glossary, pr-plan, dll.) masih DRAFT `[K]`.
- Korpus sampel SLIK/KTP/KK anonim belum ada (Q4).
- Tarif asuransi/haircut COA final belum ada (D-07/D-08/D-01).

## 20.6 Known issues teknis (yang sudah terlihat) `[E]/[?]`
1. **Hardcode audit scope**: `TPP/MODAL_KERJA/Grace Period/Flat/Anuitas` masuk denylist; enum-value string di service harus dari enum/`lang`, bukan literal (sudah dipatuhi).
2. ~~Dashboard web placeholder~~ → **dashboard nyata** (`DashboardService`, Fase 13).
3. **`docs:check`** bisa gagal karena drift jumlah (Bab 16/19).
4. ~~Middleware Spatie `permission:`~~ → **terdaftar & diuji** (Fase 12).
5. **`simulasi_kredit`** kolom analitik (rasio_balloon/skor_5c/jumlah_red_flag) belum diisi `[E]`.
6. **Ledger** belum COA/jurnal (D-01).
7. **Template dokumen seeder DRAFT** → generate ditolak BR-07 sampai disahkan.
8. **Policy ACTIVE seeder** (IDIR 0,35 / margin 0,5 / LTV 0,7) vs rekomendasi DRAFT (0,80/0,80/0,80) — perlu D-11/D-07.
9. **Halaman web dokumen** belum ada; service/generator (17) sudah tersedia.

## 20.7 Keputusan terbuka (`[K]`) — ringkas D-01..D-20
D-01 ledger resmi/COA; D-02 model TPP+band 5C; D-03 SLIK/consent/korpus; D-04 UI stack; D-05 pembulatan/pengakuan/EIR; D-06 alokasi/denda/penalti/blokir; D-07 PPKA/haircut/basis LTV; D-08 tarif asuransi; D-09 hosting/retensi/DRP; D-10 OCR/LLM cloud; D-11 ambang IDIR/DSR; D-12 authority/SoD; D-13..D-20 lapis L1–L9. Semua **DRAFT**.

## 20.8 Rekomendasi urutan kerja `[A]`
1. Tutup verifikasi C-002 + definisi EIR (V4/D-05) → golden `verified=true`.
2. Pertahankan baseline QA hijau (`verify.sh --full`) tiap fase.
3. Ratifikasi ADR↔D + sinkronkan dokumen (route/enum/tabel).
4. Implement bertahap L1→L3→L2→L5→L4→L6→L7→L8→L9, tes+negatif+evidence per lapis.
5. Money/BIGINT bertahap (PR-D..E).
6. Lanjutkan Fase 16 (hardening), 17 (dokumentasi pengguna/SOP), 18 (UAT), dan 12B (import).
