# Checklist PR — Wajib Saat Fitur/Fungsi Final

- [ ] Fitur/fungsi dinyatakan **final** (scope jelas, bukan eksperimen) dan perilaku diverifikasi.
- [ ] Tes relevan hijau (unit/feature/golden/negatif sesuai domain); angka golden punya sumber `[E/K/H]`.
- [ ] Tidak melemahkan tes/hook; tidak bypass audit/immutability.
- [ ] `composer qa` / pint / phpstan / pest sesuai gate fase (catat hasil/baseline).
- [ ] Migrasi/config/enum terdokumentasi; tidak ada secret/PII nyata.
- [ ] Parameter bisnis dari policy/ref/config/lang (no hardcode bisnis) `[E ADR-020]`.
- [ ] **Manual book diupdate:** bab terkait + `docs/manual/CHANGELOG.md` + `docs/manual/templates/feature-matrix.md`.
- [ ] Evidence ditautkan (test path, command, artifact/evidence file, ADR/keputusan).
- [ ] Jika menyentuh uang/ledger/formula: Money/rounding/EIR diselaraskan dengan D-05/D-06/C-002 `[K/H]`; ledger/audit hanya via service resmi.
- [ ] Unknown/keputusan terbuka dicatat `[K]` di `project/docs/questions.md` (jangan ditebak).
