# Spec — Scoring (5C/FSS TPP), Rating, Decision (DRAFT)

Status: DRAFT | `[K]` D-02,D-11,D-12,D-15.

## 1. 5C produktif
Faktor: Character (SLIK terstruktur+verifikasi), Capacity (IDIR/DSR/DSCR+stress), Capital (ekuitas/leverage riil, fix T6), Collateral (PPKA/LTV D-07), Condition (sub-kriteria wajib: tujuan/usaha/siklus/ketergantungan/pembeli `[K]`).  
Bobot/band 1–5/rating = **policy data berversi** `[K→H]` (X2/X3 DRAFT/contoh `[E]`). Override SLIK/score: alasan+user+waktu+otorisasi+audit.

## 2. FSS TPP (redesign)
X1 FSS = template KPR/`#REF!` `[E]` — **jangan port**. Model TPP baru berversi `[H D-02]`: potong TPP/bendahara/PKS, blokir, stabilitas penempatan, SLIK/DSR, rasio. Faktor pengalaman kredit dari SLIK (fix T4).

## 3. Rating & migrasi (L2, `[K D-15]`)
Rating risiko berversi + golden + explainable. Migrasi >2 tingkat/periode `[K]` → trigger re-underwriting/EWS. Perubahan rating → review pricing `[K]`. Analisis pin `model_version`.

## 4. DecisionEngine
Output beralasan + snapshot: **hard-stop** (BMPK `[K]`, IDIR/DSR threshold, SLIK fatal, consent/dokumen wajib, balloon/LTV per policy) vs **soft-score** (5C/FSS/stress/mitigasi). Tampilkan konflik (fix T1). Keputusan final = pejabat berwenang (authority/maker-checker/SoD, D-12); AO no self-approve.

## 5. Tes
Golden boundary skor/rating; negatif: Condition kosong, consent tidak aktif, hard-stop bypass, self-approve, migrasi trigger.
