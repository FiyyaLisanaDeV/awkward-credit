# Bab 10 — Approval, Checklist, Dokumen, Tanda Tangan, dan Notifikasi

## 10.1 Approval berjenjang `[E]` — `ApprovalService` (API + Web)
### Sumber kewenangan: `RefApprovalMatrix`
Matriks versi (seeder `v1.0`, `status=ACTIVE`) berisi entri `{plafon_min, plafon_max, level, role}`:
| plafon_min | plafon_max | level | role |
|---|---|---|---|
| 0 | 10.000.000 | 1 | AO |
| 0 | 10.000.000 | 2 | MGR_PEMASARAN |
| 10.000.000 | 50.000.000 | 3 | KOORDINATOR |
| 50.000.000 | 100.000.000 | 4 | DIRUT |
| 100.000.000 | (null=∞) | 5 | KOMITE |

**Level wajib** untuk sebuah permohonan = seluruh level dari entri matriks yang band `[plafon_min, plafon_max]`-nya memuat `plafon_rekomendasi ?? plafon_diajukan` (band menentukan approver yang relevan; **tidak** memaksa level 1..max). `ApprovalService::requiredLevels()` menjadi satu sumber kebenaran (dipakai juga oleh BR-06). `nextLevel()` = level wajib terkecil yang belum punya `ApprovalLog` aksi `APPROVE`; `roleForLevel()` mengembalikan role entri tsb.

### Alur `approve` (`ApprovalService`, dipakai API & Web)
1. `ensureSameCabang` (API) / `abort_unless` cabang (Web).
2. Validasi `alasan` (nullable).
3. `level = nextLevel(permohonan)`; `null` → `409` (sudah lengkap/matriks tidak tersedia).
4. **Kewenangan ganda**: `ADMIN` bypass; selain itu wajib `hasRole(roleForLevel(level))` **dan** `hasPermissionTo('permohonan.approve_level_{level}')`; gagal → `403`.
5. **SoD**: selain `ADMIN`, `user.id` tidak boleh sama dengan `permohonan.ao_id` (self-approve) → `403`.
6. Catat `ApprovalLog` (`level, role_id, user_id, aksi=APPROVE, alasan`).
7. `final = nextLevel(permohonan->refresh()) === null`; status `APPROVED` bila final, selain itu `RECOMMENDED`.
8. Ledger `PERMOHONAN_DISETUJUI` (`{level, role, final}`) + notifikasi `APPROVAL` ke `ao_id`.

### `reject` / `returnToAo`
- `alasan` **required** (max 500). Set status `REJECTED` / `RETURNED`.
- **Menulis `ApprovalLog`** (aksi `REJECT`/`RETURN`), ledger `PERMOHONAN_DITOLAK` / `PERMOHONAN_DIKEMBALIKAN`, dan notifikasi ke `ao_id`.
- Aturan SoD juga berlaku (pembuat tidak boleh memutus pengajuannya sendiri; `ADMIN` bypass).

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
Sejumlah **17 jenis** dokumen disemai di `ref_template_dokumen` (seeder `TemplateDokumenSeeder`). Fase 14 mengimplementasikan **17 generator** (`Services/Document/Generators`) + 17 template Blade, `DocumentService` (DomPDF), hash SHA-256 **konten HTML** (idempoten, dicek sebelum render PDF), file di disk privat. Template seeder berstatus DRAFT → generate ditolak BR-07 sampai disahkan.

## 10.4 Tanda tangan `[E]` — `signature_log`
`{dokumen_id, pihak(enum PihakSignature), nama, jabatan, ttd_hash, ttd_file_path, ip_address, timestamp}`.

## 10.5 Notifikasi `[E]` — `notifikasi`
`{cabang_id?, user_id, tipe, judul, pesan, entity_type, entity_id, is_read, created_at, read_at}`; index unread parsial. Dipakai approval (approve/reject/return) — kirim ke `ao_id`, `tipe=APPROVAL`. UI: `NotificationBell` (Livewire, `wire:poll.30s`) + `NotifikasiService` (`unreadCount`, `markRead`, `markAllRead`).

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
- **Web approval (Fase 13)**: `/approval` (`approval.inbox`) + `/approval/{permohonan}` (`approval.show`) — inbox, ringkasan, aksi (`Livewire\ApprovalAction`), timeline `ApprovalLog`. Notifikasi bell di navbar.
- **Dokumen (Fase 14)**: generator/service tersedia (API/service); halaman daftar dokumen web belum dibuat.
- Permission terkait: `permohonan.approve_level_1..5` (level 1 ditambahkan untuk role AO), `permohonan.return/reject`, `dokumen.view/generate_draft/upload/sign`, `template.review/approve`, `audit.view`, `ledger.view`, `report.view`.

## 10.9 Gap & rencana `[Rencana/K]`
- Authority limits final (plafon/tenor/produk/cabang/jabatan) = D-12 `[K]`; implementasi saat ini: role matriks + permission level + SoD dasar.
- Template dokumen seeder masih DRAFT; pengesahan template `[K]` sebelum produksi.
- Tanda tangan: `signature_log` + hash diimplementasikan; unggah berkas TTD/PSrE & e-meterai = fase lanjut `[K]`.
