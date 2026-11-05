# Spec — Ledger, Audit, Dokumen, Transparansi (DRAFT)

Status: DRAFT | `[K]` D-01,D-05,D-06.

## 1. Ledger (F2 prasyarat)
Double-entry, `debit=credit` (service+DB guard), idempotency key, `SELECT FOR UPDATE` per loan/exposure relevan, period lock. COA mapping BPR `[K D-01]`. Akun contoh (kas, piutang pokok, tabungan, tabungan diblokir, titipan, pendapatan bunga/provisi/admin/denda/penalti) `[K D-05]` — final via `[H]`.  
Event: pencairan, biaya, blokir/rilis, top-up/payoff, angsuran (alokasi D-06), denda, pelunasan dipercepat, **reversal** (bukan hapus). Recon `external_refs` bila core banking tetap sumber resmi `[K D-01]`.

## 2. Audit & immutability
`audit_events` append-only, hash chain, trigger immutable; koreksi via event/pembalik. Audit: status, import/review, override (RAC/BMPK/Direksi — BMPK non-override hukum `[K]`), akses/ekspor PII, dokumen, model/policy version. Tulis ledger/audit **hanya** via `JournalPoster`/`AuditRecorder`.

## 3. Dokumen & transparansi
Nilai/terbilang/tanggal dari snapshot (`DocumentData`); template versioned + numbering + hash/QR `[A]`. Sertakan disclosure: produk/metode, nominal + estimasi efektif/total biaya `[K V4]`, biaya, dana diterima, jadwal, denda/penalti/pelunasan `[K D-06]`, risiko grace/balloon. Review Legal/Notaris sebelum `[H]`. Fix T8/T9/T12.
