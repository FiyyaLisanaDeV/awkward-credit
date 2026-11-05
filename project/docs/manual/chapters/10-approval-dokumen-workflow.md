# Bab 10 — Approval, Checklist, Dokumen, Tanda Tangan, dan Notifikasi

## 10.1 Approval berjenjang `[E]` — `Api/V1/ApprovalController`
### Sumber kewenangan: `RefApprovalMatrix`
Matriks versi (seeder `v1.0`, `status=ACTIVE`) berisi entri `{plafon_min, plafon_max, level, role}`:
| plafon_min | plafon_max | level | role |
|---|---|---|---|
| 0 | 10.000.000 | 1 | AO |
| 0 | 10.000.000 | 2 | MGR_PEMASARAN |
| 10.000.000 | 50.000.000 | 3 | KOORDINATOR |
| 50.000.000 | 100.000.000 | 4 | DIRUT |
| 100.000.000 | (null=∞) | 5 | KOMITE |

**Max level** untuk sebuah permohonan = level tertinggi pada entri yang memuat `plafon` (memakai `plafon_rekomendasi ?? plafon_diajukan`). `roleForLevel(level)` mengembalikan role entri tsb (default ADMIN).

### Alur `approve`
1. `ensureSameCabang`.
2. Validasi `alasan` (nullable).
3. `level = nextLevel(permohonan)` = `max(level approve yang sudah tercatat) + 1`; bila > max → `409`.
4. `roleName = roleForLevel(level)`; user harus punya role tsb **atau** `ADMIN`, jika tidak → `403`.
5. Catat `ApprovalLog` (`cabang_id, permohonan_id, level, role_id, user_id, aksi=Approve, alasan`).
6. Bila `level === maxLevel` → status `APPROVED`, selain itu `RECOMMENDED`.
7. Ledger `PERMOHONAN_DISETUJUI` (`{level, role, final}`) + notifikasi ke `ao_id` (`tipe=APPROVAL`).

### `reject` / `returnToAo`
- `alasan` **required** (max 500). Set status `REJECTED` / `RETURNED`.
- Ledger `PERMOHONAN_DITOLAK` / `PERMOHONAN_DIKEMBALIKAN`.

### `inbox`
Permohonan cabang user dengan status `SUBMITTED/ANALYZED/RECOMMENDED`, urut `tanggal_permohonan` desc, paginate.

### Endpoint `[E]`
| Method | Endpoint |
|---|---|
| GET | `/api/v1/approvals/inbox` |
| POST | `/api/v1/permohonan/{permohonan}/approve` |
| POST | `/api/v1/permohonan/{permohonan}/reject` |
| POST | `/api/v1/permohonan/{permohonan}/return` |

## 10.2 Checklist dokumen `[E]` — `checklist_status` + `RefChecklistDokumen`
- Master checklist `ref_checklist_dokumen` (seeder 28 baris) memuat flag `wajib` & `is_active`.
- Per permohonan, `checklist_status` menyimpan `{permohonan_id, checklist_id, status(ADA/TIDAK_ADA/NA/TIDAK_WAJIB), nomor_dokumen, tanggal_dokumen, catatan, verified_by, verified_at}`. Unique `(permohonan_id, checklist_id)`.
- **BR-05**: seluruh checklist `wajib=true, is_active=true` harus berstatus ADA/NA/TIDAK_WAJIB.
- **Red flag** `CHECKLIST_KURANG` (WARNING) dihitung dari kelengkapan yang sama.
- Perhitungan `kelengkapan_persen` pada permohonan `[E]` (kolom tersedia; bobot usulan `bobot_kelengkapan` policy `[K]`).

## 10.3 Dokumen `[E]` — `dokumen`
Kolom: `cabang_id, permohonan_id, template_id?, jenis_dokumen(enum JenisDokumen), nomor_dokumen, tanggal_dokumen, file_path, file_hash(64), status_ttd(enum StatusTtd, default DRAFT), tanggal_ttd, ukuran_bytes, sumber(default GENERATE), catatan`, softDeletes.
Unique partial `dokumen_permohonan_jenis_hash_unique (permohonan_id, jenis_dokumen, file_hash)`.
**BR-07**: dokumen tidak boleh memakai template berstatus ≠ APPROVED.
Sejumlah **17 jenis** dokumen disemai di `ref_template_dokumen` (seeder `TemplateDokumenSeeder`); generator PDF = DomPDF (fase 14 `[Rencana]`).

## 10.4 Tanda tangan `[E]` — `signature_log`
`{dokumen_id, pihak(enum PihakSignature), nama, jabatan, ttd_hash, ttd_file_path, ip_address, timestamp}`.

## 10.5 Notifikasi `[E]` — `notifikasi`
`{cabang_id?, user_id, tipe, judul, pesan, entity_type, entity_id, is_read, created_at, read_at}`; index unread parsial. Dipakai approval (kirim ke `ao_id`, `tipe=APPROVAL`).

## 10.6 Audit log `[E]` — `audit_log`
`{cabang_id?, user_id?, entity_type, entity_id?, aksi, old_value(jsonb), new_value(jsonb), ip_address, user_agent, timestamp}` + GIN index + **trigger immutable** (`audit_log_immutable_trigger`) → UPDATE/DELETE ditolak (BR-09).
> Perbedaan `audit_log` (perubahan field entitas) vs `ledger` (peristiwa domain ber-hash chain) — lihat Bab 12.

## 10.7 Alur end-to-end (ringkas)
```
DRAFT ──submit(BR-04,BR-08)──► SUBMITTED
   ──analisis(neraca/laba-rugi/kapasitas/scoring/rekomendasi)──► (ANALYZED)
   ──approve level 1..N (matriks)──► RECOMMENDED ──(level akhir)──► APPROVED
   ──(reject/return kapan saja)──► REJECTED / RETURNED
   ──dokumen & tanda tangan──► SIGNED ──akad/pencairan──► ACTIVE
```

## 10.8 Web & permission `[E]`
- Checklist & dokumen belum punya UI web pada fase 00–11 (`[Rencana]` fase 13/14).
- Permission terkait: `dokumen.view/generate_draft/upload/sign`, `template.review/approve`, `permohonan.approve_level_2..5`, `permohonan.return/reject`, `audit.view`, `ledger.view`, `report.view`.

## 10.9 Gap & rencana `[Rencana/K]`
- Maker-checker/SoD formal, authority limits (plafon/tenor/produk/cabang/jabatan) final = D-12 `[K]`.
- AO tidak boleh self-approve (bukti eksplisit) `[Rencana]`.
- Generator dokumen (17) & alur TTD = fase 14 `[Rencana]`.
