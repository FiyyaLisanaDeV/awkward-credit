# Bab 17 — Import/Ekstraksi, Profil, OCR, dan Data Quality

## 17.1 Tujuan
Mengimpor data eksternal (SLIK iDeb, KTP/KK, debitur, dokumen, workbook lama, pembayaran) secara aman melalui **staging** dan **profil mapping**, dengan kontrol kualitas data.

## 17.2 Skema `[E]`
### `import_profil`
`{kode(40) unique, nama, jenis(enum JenisImport), versi, mapping jsonb, delimiter?, header_row(default 1), sheet_name?, is_active}`.
### `import_batch`
`{cabang_id, user_id, jenis(enum JenisImport), profil_id?, file_path, file_hash(64), status(enum StatusImport, default Preview), total_baris, sukses, gagal, committed_at?, ledger_id?, catatan}`; **unique `(jenis, file_hash)`** (cegah impor ganda).
### `import_baris`
`{batch_id, nomor_baris, payload jsonb, normalized jsonb?, errors jsonb?, status(enum StatusImportBaris, default Valid), target_type?, target_id?}`.
### `ekstraksi_dokumen`
`{cabang_id, debitur_id?, permohonan_id?, jenis(enum JenisEkstraksi), file_path, file_hash(64), engine, engine_version?, raw_text?, fields jsonb, confidence jsonb?, status(enum StatusEkstraksi, default DRAFT), verified_by?, verified_at?}`.

## 17.3 Konfigurasi impor `[E]` — `config/import.php`
- `max_rows=5000`, `max_file_mb=25`, mimes: `xlsx, xls, csv, pdf, jpg, jpeg, png, zip`; `staging_ttl_hours=24`.
- Profil default (8) seperti di Bab 16.
- OCR: `driver=auto`, `lang=ind`, `binary=TESSERACT_PATH`.

## 17.4 Alur impor (desain) `[E]/[A]`
```
Upload → cek mime & ukuran → simpan file (+hash) → import_batch(status=PREVIEW)
  → parse per baris → import_baris(payload, normalized, errors, status)
  → pratinjau & koreksi (staging TTL 24 jam) → commit
  → tulis entitas target (+target_type/target_id) → ledger DATA_DIIMPOR / SLIK_DIIMPOR
```
Batch unik per `(jenis,file_hash)` → idempoten. `committed_at` & `ledger_id` tercatat.

## 17.5 Profil SLIK tiga jalur `[E]` (ADR-022)
1. `SLIK_XLSX_IDEB` (Excel/CSV).
2. `SLIK_PDF_IDEB` (PDF teks via `smalot/pdfparser`).
3. `SLIK_TEKS` (tempel teks; parser kolom `\t ; |` — Bab 6 §6.7).
Wizard `PermohonanWizard` sudah menyediakan input manual & paste; adapter penuh = fase 12B `[Rencana]`.

## 17.6 OCR `[E]` (ADR-023)
- Driver `auto` (deteksi tesseract) / `none` / `tesseract`; bahasa `ind`.
- Aplikasi **tetap berjalan tanpa tesseract**.
- Hasil OCR → `ekstraksi_dokumen` dengan `confidence` + **wajib verifikasi manusia** (`verified_by/verified_at`).

## 17.7 Kualitas data `[E]/[K]`
Aturan/flag yang direncanakan (`[K/Rencana]`, ADR-016 & T3):
- `baki_debet ≤ plafon` = **peringatan** (bukan CHECK keras).
- Deteksi duplikat baris, baki identik antar baris, `tanggal_data` (pull) wajar.
- NIK decode cross-check; konsistensi identitas.
- Setiap baris punya `errors` + `status` (Valid/Invalid) untuk ditinjau.

## 17.8 Event ledger terkait `[E]`
`SLIK_DIIMPOR`, `KTP_DIEKSTRAKSI`, `DATA_DIIMPOR`, `IMPORT_DIBATALKAN`.

## 17.9 Guard & keamanan `[E]`
- Tanpa PII nyata di repo/test: `tests/Unit/Guard/NoSecretsTest.php`.
- File tersimpan di disk; PII dienkripsi (`loan.pii`); akses berjejak (`AKSES_PII`).
- Batas ukuran/​baris melindungi server.

## 17.10 Gap & rencana `[Rencana/K]`
- Fase 12B: 8 adapter impor & efisiensi (PROGRESS TODO).
- Korpus sampel dummy/anonim 10–20 file (Q4) belum tersedia.
- Mapping JSON per profil masih kosong (`mapping={}`) — perlu diisi saat adapter dibuat.
- Definisi flag fatal/kualitas final = D-03 `[K]`.
