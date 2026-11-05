# Bab 24 — Workflow, Peran, dan Wewenang Pengguna

> **Ringkas:** alur keputusan kredit berjalan dari `DRAFT` sampai `APPROVED`/`REJECTED`, dengan **approval berjenjang** berbasis matriks plafon. Setiap level menuntut **role matriks + permission level** (kewenangan ganda) dan **pemisahan tugas** (pembuat tidak boleh menyetujui miliknya). Semua aksi meninggalkan jejak `ApprovalLog`, `ledger`, dan notifikasi.

<details>
<summary><b>Dasar implementasi (klik)</b></summary>

- `app/Services/Approval/ApprovalService.php` — satu sumber logika approval (dipakai API & Web).
- `app/Http/Controllers/Api/V1/ApprovalController.php` + `app/Http/Controllers/Web/ApprovalController.php`.
- `database/seeders/Reference/ApprovalMatrixSeeder.php` (matriks `v1.0` ACTIVE).
- `database/seeders/Reference/RolePermissionSeeder.php` (role & permission).
- `database/seeders/Reference/AuditorUserSeeder.php` (user auditor read-only).
- Uji: `tests/Feature/Workflow/*`, `tests/Feature/Api/ApiTest.php`.
</details>

## 24.1 State machine permohonan `[E]`
| Dari | Aksi | Ke | Pelaku |
|---|---|---|---|
| `DRAFT` | submit (BR-04, BR-08) | `SUBMITTED` | AO |
| `SUBMITTED` | analisis selesai | `ANALYZED` | Analis |
| `SUBMITTED`/`ANALYZED` | approve level (bukan final) | `RECOMMENDED` | approver level |
| `RECOMMENDED` | approve level final | `APPROVED` | approver level akhir |
| kapan saja | reject | `REJECTED` | approver berwenang |
| kapan saja | return | `RETURNED` | approver berwenang |
| `APPROVED` | akad/pencairan | `ACTIVE` | operasional |
| `ACTIVE` | lunas/bermasalah | `CLOSED`/`NPL` | operasional/risk |

> `return` mengembalikan ke AO untuk diperbaiki; setelah revisi, permohonan dapat diajukan kembali.

## 24.2 Algoritma approval `[E]` — `ApprovalService`
1. **Level wajib** = seluruh level dari entri matriks yang band `[plafon_min, plafon_max]`-nya memuat `plafon_rekomendasi ?? plafon_diajukan`.
2. `nextLevel()` = level wajib terkecil yang **belum** punya `ApprovalLog` aksi `APPROVE`.
3. **Kewenangan ganda:** `ADMIN` bypass; selain itu wajib `hasRole(roleForLevel(level))` **dan** `hasPermissionTo('permohonan.approve_level_{level}')` → jika gagal `403`.
4. **SoD:** selain `ADMIN`, `user.id` tidak boleh sama dengan `permohonan.ao_id` (self-approve) → `403`.
5. Tulis `ApprovalLog` (`level, role_id, user_id, aksi, alasan`).
6. `final = nextLevel(permohonan->refresh()) === null`; status `APPROVED` bila final, selain itu `RECOMMENDED`.
7. Ledger `PERMOHONAN_DISETUJUI` + notifikasi ke `ao_id`.
8. `reject`/`return` juga menulis `ApprovalLog`, ledger, dan notifikasi (bukan hanya ubah status).

<details>
<summary><b>Mengapa level per band, bukan 1..max?</b></summary>

Matriks mendefinisikan approver **per rentang plafon**. Plafon 30 juta hanya memuat entri level 3 (10–50 juta), sehingga approver yang relevan adalah level 3 — bukan memaksa level 1 dan 2 yang diperuntukkan bagi plafon ≤10 juta. `requiredLevels()` dan BR-06 memakai sumber yang sama agar tidak ada lompatan/penolakan palsu.
</details>

## 24.3 Matriks approval ACTIVE `[E]`
| plafon_min | plafon_max | level | role |
|---|---|---|---|
| 0 | 10.000.000 | 1 | `AO` |
| 0 | 10.000.000 | 2 | `MGR_PEMASARAN` |
| 10.000.000 | 50.000.000 | 3 | `KOORDINATOR` |
| 50.000.000 | 100.000.000 | 4 | `DIRUT` |
| 100.000.000 | (∞) | 5 | `KOMITE` |

Perubahan matriks harus lewat versi baru (`ref_approval_matrix`), bukan edit langsung; hanya satu versi ACTIVE.

## 24.4 Peran & permission `[E]`
| Role | Permission utama | Tugas |
|---|---|---|
| `AO` | `permohonan.create/edit_own/view/submit`, `approve_level_1`, `dokumen.view/generate_draft/upload`, `simulasi.create`, `import.*` | Input debitur/permohonan, simulasi, unggah dokumen, ajukan |
| `MGR_PEMASARAN` | `permohonan.view`, `approve_level_2`, `permohonan.return`, `report.view` | Approval level 2, kembalikan |
| `KOORDINATOR` | `permohonan.view`, `approve_level_3`, `permohonan.return`, `report.view` | Approval level 3 |
| `DIRUT` | `permohonan.view`, `approve_level_4`, `permohonan.reject`, `report.view` | Approval level 4, tolak |
| `KOMITE` | `permohonan.view`, `approve_level_5`, `report.view` | Approval level 5 (plafon besar) |
| `ADMIN` | seluruh permission | Administrasi sistem; bypass teknis |
| `LEGAL` | `dokumen.view`, `template.review`, `template.approve` | Review/pengesahan template dokumen |
| `AUDITOR` | `audit.view`, `ledger.view`, `report.view`, `permohonan.view` | **Read-only**: audit, ledger, laporan, baca permohonan |

> Kewenangan final (plafon/tenor/produk/cabang/jabatan) = **D-12 `[K]`**. Implementasi saat ini: role matriks + permission level + SoD.

## 24.5 Wewenang per plafon `[E]`
| Plafon (Rp) | Level wajib | Role approval |
|---|---|---|
| ≤ 10 juta | 1, 2 | AO → MGR_PEMASARAN |
| > 10–50 juta | 3 | KOORDINATOR |
| > 50–100 juta | 4 | DIRUT |
| > 100 juta | 5 | KOMITE |

Semua approver tunduk pada **SoD**: pembuat tidak boleh menyetujui pengajuannya sendiri (kecuali `ADMIN`).

## 24.6 Peran auditor & user auditor `[E]`
- Role `AUDITOR` bersifat **read-only**: `audit.view`, `ledger.view`, `report.view`, `permohonan.view` — **tidak** punya permission approve/reject/return, create/edit, atau posting.
- Seeder `AuditorUserSeeder` membuat user **`auditor`** (nama “Auditor Internal”, cabang KDI) dengan role `AUDITOR`, `is_active=true`, `must_change_password=true`. Password awal acak (non-produksi) atau dari `AUDITOR_INITIAL_PASSWORD`.
- Auditor dapat membuka [Ledger & Audit](Bab-12-Ledger-Audit-Integritas), [Known Issues](Bab-20-Known-Issues-Gap-Rencana), dan daftar permohonan (read-only), tetapi tidak dapat mengubah status.
- Di produksi: aktifkan user auditor, ganti password, dan pastikan permission tetap read-only.

<details>
<summary><b>Cara memverifikasi auditor</b></summary>

```bash
php artisan db:seed --class="Database\\Seeders\\Reference\\AuditorUserSeeder"
php artisan tinker --execute="\$u=App\\Models\\User::where('username','auditor')->first(); dump(\$u?->hasRole('AUDITOR'), \$u?->can('audit.view'), \$u?->can('permohonan.approve_level_2'));"
# harapan: true, true, false
```
</details>

## 24.7 Aksi & efek `[E]`
| Aksi | ApprovalLog | Status | Ledger | Notifikasi |
|---|---|---|---|---|
| approve | `APPROVE` + level | `RECOMMENDED`/`APPROVED` | `PERMOHONAN_DISETUJUI` | ke `ao_id` |
| reject | `REJECT` | `REJECTED` | `PERMOHONAN_DITOLAK` | ke `ao_id` |
| return | `RETURN` | `RETURNED` | `PERMOHONAN_DIKEMBALIKAN` | ke `ao_id` |

Semua aksi memakai transaksi DB; kegagalan kewenangan → `403`, status tidak sah → `409`.

## 24.8 Web & notifikasi `[E]`
| Route | Nama | Isi |
|---|---|---|
| `/approval` | `approval.inbox` | Daftar permohonan menunggu (SUBMITTED/ANALYZED/RECOMMENDED) |
| `/approval/{permohonan}` | `approval.show` | Ringkasan + aksi + timeline |
| komponen | `Livewire\ApprovalAction` | Setujui/tolak/kembalikan + alasan |
| komponen | `Livewire\NotificationBell` | Unread count, daftar, tandai dibaca (`poll 30s`) |

Detail UI ada di [Bab 14](Bab-14-UI-Web-Livewire); dokumen & tanda tangan di [Bab 10](Bab-10-Approval-Dokumen-Workflow).

## 24.9 Uji cepat `[E]`
<details>
<summary><b>Perintah verifikasi workflow</b></summary>

```bash
php artisan test --filter=Workflow
php artisan test --filter=ApprovalAuthorizationTest
php artisan test --filter=NotificationTest
bash scripts/verify.sh --full
```
Berkas: `tests/Feature/Workflow/ApprovalFlowTest.php`, `ApprovalAuthorizationTest.php`, `NotificationTest.php`, `UiRenderWorkflowTest.php`.
</details>

## 24.10 Gap & keputusan `[K]`
- **D-12**: authority limits final (plafon/tenor/produk/cabang/jabatan), maker-checker formal, dan matriks kewenangan belum `[H]`.
- `permohonan.approve_level_1` ditambahkan untuk role `AO` agar aturan kewenangan ganda konsisten (DRAFT, tunduk D-12).
- User auditor disediakan untuk kebutuhan audit; kebijakan retensi/akses audit tetap `[K]` (D-09).
- Band/ambang policy masih ACTIVE-DRAFT; lihat [Bab 20](Bab-20-Known-Issues-Gap-Rencana).
