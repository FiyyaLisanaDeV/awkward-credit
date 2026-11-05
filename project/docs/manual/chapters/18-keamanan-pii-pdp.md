# Bab 18 — Keamanan, PII/PDP, Consent, Retensi, dan Kontrol Operasional

## 18.1 Prinsip `[E]/[K]`
Sistem menangani **PII** (NIK, KK, NPWP, alamat, kontak, data SLIK, dokumen). Kontrol: least privilege (RBAC), scoping cabang, enkripsi PII, audit jejak, dan larangan PII nyata di repo/test. Target kepatuhan penuh (UU PDP, POJK TI) = `[K]` (D-09/D-10/D-20).

## 18.2 Autentikasi & sesi `[E]` (Bab 4)
- Login web per-IP throttle (5/60s) + regenerasi sesi; user non-aktif dipaksa logout.
- `must_change_password` memaksa ganti sandi.
- API token Sanctum; login rate-limit `10,1`.
- Password `hashed`; minimum dari config (8 dev / 12 produksi).

## 18.3 Otorisasi `[E]`
- RBAC Spatie (8 role, 27 permission).
- Scoping cabang (`ScopesByCabang`); ADMIN lintas cabang.
- Approval berbasis matriks role.

## 18.4 Perlindungan PII `[E]`
- `config(loan.pii)` → disk & path `pii`, cipher `aes-256-gcm`.
- `PII_STORAGE=encrypted`.
- Masking & akses berjejak: event `AKSES_PII`.
- File KTP/debitur path tersimpan (bukan blob) di `debitur`.
- Dummy data di workbook `[E user]`; **no PII nyata** di repo/prompt/log (D-10).

## 18.5 Consent SLIK `[E]/[K]`
- **BR-15**: NIK wajib untuk consent SLIK.
- Consent formal/korpus anonim = D-03 `[K]`; gate operasional consent `[Rencana]`.

## 18.6 Audit & integritas `[E]` (Bab 12)
- `ledger` hash chain append-only (trigger immutable).
- `audit_log` immutable (+GIN).
- Koreksi via reversal, bukan edit.

## 18.7 Kontrol operasional `[E]`
- `state:check`, `docs:check`, `hardcode:audit` sebagai penjaga.
- `ledger:verify` harian (`verify_schedule`).
- Backup/restore (`scripts/backup.sh`, `restore.sh`).
- Guard tests: `NoSecretsTest`, `NoForbiddenDependencyTest`, `StrictTypesTest`.

## 18.8 OCR/LLM/cloud `[E]/[K]` (D-10)
- Default konservatif: OCR **opsional lokal** (tesseract), tanpa cloud/LLM PII.
- OCR hasil wajib verifikasi manusia.
- Bila cloud/LLM diizinkan → butuh izin DPO + redaksi + consent + audit `[K/H]`.

## 18.9 Residensi, retensi, DRP `[K]` (D-09/D-20)
- Residensi data Indonesia, retensi per jenis data, DRP (RTO/RPO), audit TI, third-party = `[K]` POJK TI 34/2025 `[K]` — **belum terimplementasi**.
- Backup terenkripsi & uji restore `[A]`.

## 18.10 Rekomendasi pengerasan `[A]`
1. Aktifkan HTTPS/HSTS + cookie secure di produksi.
2. Rotasi `APP_KEY`/kredensial via secret manager.
3. Role DB produksi least-privilege (bukan superuser).
4. Batasi akses storage PII; enkripsi at-rest.
5. Aktifkan scheduler `ledger:verify` & alerting.
6. Review permission matrix & SoD (`[K]` D-12).
7. Uji restore terjadwal (drill).

## 18.11 Gap ringkas `[Rencana/K]`
- Kebijakan retensi/disclosure final, DRP, residensi, APU-PPT, kontrol TI: D-09/D-10/D-19/D-20 `[K]` (Bab 20).
