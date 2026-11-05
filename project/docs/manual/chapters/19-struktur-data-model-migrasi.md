# Bab 19 — Struktur Data (Model/Migrasi/Enum) dan Peta Domain

## 19.1 Ringkasan `[E]`
- **61 migrasi** (`database/migrations`), **55 model** (`app/Models`), **35 enum** (`app/Enums`).
- Konvensi: PK `uuid` (kecuali tabel tertentu seperti `users/roles/ledger.seq/jadwal_angsuran.id`), `cabang_id` FK, `CHECK` dari enum via `Support\Database\CheckConstraint`, index parsial untuk status aktif, trigger immutable untuk `ledger` & `audit_log`.

## 19.2 Peta tabel → domain `[E]`
### Master/Referensi
`cabang`, `produk`, `ref_kategori_agunan_pojk`, `ref_kolektibilitas`, `ref_status_pelunasan_internal`, `ref_rate_asuransi_jiwa`, `ref_rate_asuransi_kebakaran`, `ref_rate_kendaraan`, `ref_konversi_suku_bunga`, `ref_checklist_dokumen`, `ref_parameter_kebijakan`, `ref_approval_matrix`, `ref_template_dokumen`, `permissions/roles/model_has_*` (Spatie).
### Auth/User
`users` (+`add_auth_fields`, `add_profile_fields`), `personal_access_tokens` (Sanctum), `cache`, `jobs`.
### Debitur
`debitur`, `debitur_pasangan`, `debitur_usaha`, `debitur_siklus_usaha`, `debitur_aset`, `debitur_pengkinian`.
### Permohonan & SLIK
`permohonan_kredit`, `slik_permintaan`, `permohonan_slik_debitur`, `permohonan_slik_pasangan`, `permohonan_riwayat_internal`.
### Analisis
`analisis_neraca`, `analisis_laba_rugi`, `analisis_rasio`, `analisis_modal_kerja`, `analisis_kapasitas_bayar`, `analisis_sensitivitas`, `analisis_scoring_5c`, `analisis_red_flags`, `analisis_rekomendasi`, `analisis_7aspek`.
### Kredit & turunan
`kredit`, `jadwal_angsuran`, `biaya_kredit`, `asuransi_kredit`, `kredit_agunan`, `memo_pencairan`, `pemblokiran_tabungan`, `pembayaran_angsuran`.
### Dokumen & workflow
`dokumen`, `approval_log`, `signature_log`, `checklist_status`, `notifikasi`.
### Ledger/simulasi/impor
`ledger`, `simulasi_kredit`, `import_profil`, `import_batch`, `import_baris`, `ekstraksi_dokumen`, `audit_log`.

## 19.3 Model `[E]` (55)
`Analisis7aspek, AnalisisKapasitasBayar, AnalisisLabaRugi, AnalisisModalKerja, AnalisisNeraca, AnalisisRasio, AnalisisRedFlags, AnalisisRekomendasi, AnalisisScoring5c, AnalisisSensitivitas, ApprovalLog, AsuransiKredit, AuditLog, BiayaKredit, Cabang, ChecklistStatus, Debitur, DebiturAset, DebiturPasangan, DebiturPengkinian, DebiturSiklusUsaha, DebiturUsaha, Dokumen, EkstraksiDokumen, ImportBaris, ImportBatch, ImportProfil, JadwalAngsuran, Kredit, KreditAgunan, Ledger, MemoPencairan, Notifikasi, PembayaranAngsuran, PemblokiranTabungan, PermohonanKredit, PermohonanRiwayatInternal, PermohonanSlikDebitur, PermohonanSlikPasangan, Produk, RefApprovalMatrix, RefChecklistDokumen, RefKategoriAgunanPojk, RefKolektibilitas, RefKonversiSukuBunga, RefParameterKebijakan, RefRateAsuransiJiwa, RefRateAsuransiKebakaran, RefRateKendaraan, RefStatusPelunasanInternal, RefTemplateDokumen, SignatureLog, SimulasiKredit, SlikPermintaan, User`.

## 19.4 Enum `[E]` (35)
`AksiApproval, JenisAsuransi, JenisDokumen, JenisEkstraksi, JenisImport, JenisKelamin, JenisPermintaanSlik, JenisPertanggungan, KategoriChecklist, Kolektibilitas, LedgerEvent, LevelRedFlag, MetodePembayaran, PihakSignature, PolicyParameterKey, ProdukTipe, RatingKredit, RekomendasiSistem, SatuanPeriode, SistemSukuBunga, StatusChecklist, StatusEkstraksi, StatusImport, StatusImportBaris, StatusKelayakan, StatusKredit, StatusPelunasanInternal, StatusPerkawinan, StatusPermohonan, StatusTemplate, StatusTtd, StatusVersi, SumberData, SumberSlik, TipeBiaya`.

Nilai penting:
- `SistemSukuBunga` = `Flat | Anuitas | Grace Period`.
- `Kolektibilitas` (int) = 1 Lancar, 2 DPK, 3 KurangLancar, 4 Diragukan, 5 Macet.
- `ProdukTipe` = `TPP | MODAL_KERJA | GRACE_PERIOD`.
- `StatusPermohonan` (11) lihat Bab 6.
- `StatusKredit` = `ACTIVE | LUNAS | NPL | CLOSED`.
- `RatingKredit` = `SANGAT LAYAK | LAYAK | LAYAK BERSYARAT | TIDAK LAYAK`.
- `StatusKelayakan` = `LAYAK | PERHATIAN | TIDAK LAYAK`.
- `LevelRedFlag` = `CRITICAL | WARNING | INFO`.
- `StatusChecklist` = `ADA | TIDAK_ADA | NA | TIDAK_WAJIB`.
- `LedgerEvent` (23) lihat Bab 12.

## 19.5 Relasi & kardinalitas `[E]`
- `PermohonanKredit` hasMany `slikDebitur`, `sensitivitas`; hasOne `neraca/labaRugi/rasio/modalKerja/kapasitasBayar/scoring5c/rekomendasi`; belongsTo `debitur/produk/cabang`.
- `Kredit` belongsTo `permohonan`; hasMany `jadwal/biaya/asuransi/agunan`; hasOne? `memo`.
- `Debitur` hasMany `permohonan/aset/usaha/siklusUsaha`; hasOne `pasangan`.
- `User` belongsTo `cabang` (implisit); `HasRoles`.

## 19.6 Invariant penting (rekap) `[E]`
| Tabel | Invariant |
|---|---|
| `debitur` | NIK unik aktif; enum gender/perkawinan/sumber |
| `permohonan_kredit` | BR-04 balloon CHECK; plafon/rate/tenor; index cabang+status |
| `kredit` | plafon>0; rate 0–1; tenor 1–360; index aktif |
| `jadwal_angsuran` | nilai ≥0; unique (kredit,periode) |
| `ledger` | event enum; trigger immutable |
| `audit_log` | trigger immutable; GIN |
| `dokumen` | unique (permohonan,jenis,file_hash) |
| `import_batch` | unique (jenis,file_hash) |
| `simulasi_kredit` | enum produk/sistem; input_hash |

## 19.7 Catatan drift `[?]`
- `docs:check` mengharapkan 34 enum & 60 tabel bisnis di `INVENTARIS_DATA.md`; angka aktual `app/Enums`=35, migrasi=61. Sinkronisasi dokumen (Bab 22) diperlukan.
- Kolom `simulasi_kredit` menyediakan `rasio_balloon/skor_5c/rating/jumlah_red_flag/ip_address/user_agent` yang belum diisi `SimulationService` saat ini `[E]` → `[Rencana]`.
