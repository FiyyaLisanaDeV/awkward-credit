# Gap Assessment — `APP BPR/loan-analytic-bpr` (existing, fase 0–11 `[E]`) vs Rencana Diperbarui

Status: DRAFT | `[E]` dari scan lokal sebelumnya (Laravel 13.40, PHP 8.3.7, PG 16.15, Livewire 4.4.8, no Filament, Pest 4.7.8, Larastan 3.14.0, brick/math 1.0.0, 61 migrations; PROGRESS menyatakan fase 00–11 DONE; QA: pint pass, larastan 1 error, `tests/Feature` 11 gagal; dirty tree). **Perlu verifikasi langsung file PROGRESS/decisions/tests di repo saat akses stabil** (path spesifik tidak terbaca di sesi ini). Gunakan gap assessment, bukan overwrite buta.

## 1. Selaras (indikatif)
- Stack inti sesuai guardrail (Laravel 13/PHP>=8.3/PG/Pest/Larastan/brick/math, tanpa Redis/MinIO kecuali diputuskan).
- Livewire existing → selaraskan dengan D-04 (keputusan stack UI).
- Konsep fase 0–11 & ADR-001..026 ada `[E]` — perlu pemetaan ADR ↔ D-01..D-20 (format berbeda).

## 2. Gap / risiko
| Area | Temuan `[E/K]` | Aksi |
|---|---|---|
| F1 prerequisites | `docs/evidence/F0.md` LULUS, signed D-xx, `docs/spec/` v2, PII scanner proof, golden>=25 belum terpenuhi `[E/K]` | penuhi A1–A12/kontrak sebelum domain baru |
| Golden/angka | `tests/Fixtures/golden/tpp.json` (1 kasus) berpotensi konflik koreksi (IDIR 83.11/3.048.080/rate 0.18) vs `[K C-002]` | **jangan ubah sembarang**; verifikasi sumber, lalu golden dummy berlabel baru |
| QA baseline | larastan 1 error (`Simulator.php:73`), 11 Feature gagal `[E]` | triase non-domain terpisah; `composer qa` hijau per fase |
| D-01..D-20 / L1–L9 | RAC/BMPK/rating migrasi/CKPN-PD-LGD/EWS tren/stress makro/APU-PPT/TI governance kemungkinan belum lengkap | implement bertahap, tes+evidence per lapis, policy DRAFT |
| Spec/traceability | `docs/spec/` & traceability sumber belum `[E]` | generate dari plan+workbook+riset (dummy labels) |

## 3. Keputusan eksekusi `[K]`
- Lanjut di repo existing dengan branch/PR kecil + gap assessment, atau jalur hijau terpisah — **[H] owner/teknis**.
- Semua perubahan angka/formula harus berbasis golden terverifikasi `[K→H]`, bukan asumsi.
