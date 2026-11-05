# Panduan Komite / Pemutus — Dashboard, Approval, Ledger, Dokumen

**Peran:** `MGR_PEMASARAN`, `KOORDINATOR`, `DIRUT`, `KOMITE` (sesuai level), `ADMIN` (bypass teknis). **Login:** `/login`.

## 1. Dashboard `[E]`
Buka `/dashboard`. Menampilkan:
- KPI: draft, submitted, approved, kredit aktif.
- Pipeline: submitted/analyzed/recommended; red flag kritis; kelengkapan rata-rata.
- Permohonan terbaru.
Data di-cache 300 detik. Angka mengikuti cabang pengguna.

## 2. Inbox persetujuan `[E]`
1. Buka `/approval` (menu **Persetujuan**). Daftar berisi status `SUBMITTED/ANALYZED/RECOMMENDED` untuk cabang Anda.
2. Klik **Detail** untuk membuka ringkasan: plafon, status, level berikutnya, level wajib, identitas debitur, DSCR, dan **timeline** persetujuan.
3. Panel **Keputusan** berisi tombol **Setujui / Tolak / Kembalikan** + kolom alasan.

## 3. Aturan keputusan `[E]`
| Aksi | Syarat | Efek |
|---|---|---|
| Setujui | Role matriks level **dan** permission `permohonan.approve_level_N`; bukan pembuat (SoD) | `ApprovalLog` + status `RECOMMENDED`/`APPROVED` + ledger + notifikasi |
| Tolak | alasan wajib; berwenang | `ApprovalLog` + `REJECTED` + ledger + notifikasi |
| Kembalikan | alasan wajib; berwenang | `ApprovalLog` + `RETURNED` + ledger + notifikasi |

- **Level wajib** ditentukan band plafon matriks (bukan selalu 1..max).
- **ADMIN** melewati cek role/permission/SoD untuk pemulihan teknis; gunakan terbatas.
- Jika tombol gagal: `403` = kewenangan/SoD; `409` = sudah lengkap/tidak ada level.

## 4. Matriks kewenangan `[E]`
| Plafon | Level | Role |
|---|---|---|
| ≤10 jt | 1 → 2 | AO → MGR_PEMASARAN |
| >10–50 jt | 3 | KOORDINATOR |
| >50–100 jt | 4 | DIRUT |
| >100 jt | 5 | KOMITE |

D-12 (authority limit final) masih `[K]`; matriks dapat berubah lewat versi baru.

## 5. Ledger & audit `[E]`
- Ledger append-only ber-hash chain; event approval/pencairan/angsuran tercatat.
- Periksa integritas: `php artisan ledger:verify` (harus OK).
- API baca: `GET /api/v1/ledger` (filter event/entity) dan `GET /api/v1/ledger/verify`.
- Halaman web ledger `[Rencana]`; gunakan API/CLI untuk audit saat ini.
- `audit_log` immutable (UPDATE/DELETE ditolak) untuk perubahan entitas.

## 6. Dokumen `[E]`
- 17 jenis dokumen (DOC-01..17) dihasilkan `DocumentService` (DomPDF), hash konten HTML (idempoten), file di disk privat.
- **BR-07:** template harus `APPROVED`; jika belum, generate ditolak `422`.
- Tanda tangan dicatat di `signature_log` (pihak, nama, jabatan, hash, waktu) + ledger `DOKUMEN_DITANDATANGANI`.
- Halaman web daftar dokumen `[Rencana]`; gunakan service/API saat ini.

## 7. Yang perlu diperiksa sebelum memutus `[E]`
1. Kelengkapan dokumen/checklist (BR-05).
2. Rasio & kapasitas: IDIR/DSR/DSCR, LTV, balloon (Bab 7).
3. Red flag CRITICAL dan mitigasinya.
4. Skor 5C & rekomendasi sistem; konflik hard-stop vs soft-score wajib terlihat.
5. Riwayat approval (tidak ada level yang dilompati).
