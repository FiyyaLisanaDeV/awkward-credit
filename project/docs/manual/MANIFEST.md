# MANIFEST — Manual Book Loan Analytic BPR

> Berkas ini adalah **kartu kendali versi** manual. Perbarui setiap kali manual/target berubah (lihat Bab 22). Bandingkan dengan output `bash scripts/manual-snapshot.sh`.

## Versi manual
| Item | Nilai |
|---|---|
| Versi manual | **0.1** (DRAFT, init) |
| Tanggal versi | 2026-11-05 |
| Penulis/reviewer | (tim dokumentasi) |
| Cakupan | Fase 00–11 `[E]` + rencana L1–L9 `[K]` |

## Snapshot target (repo app `loan-analytic-bpr`)
| Item | Nilai tercatat | Sumber | Tanggal verifikasi |
|---|---|---|---|
| Branch | `main` | git | 2026-11-05 |
| Commit terakhir | `1f4e3d2` (dok phase-11); tag `phase-11-done` | git | 2026-11-05 |
| Working tree | **26 berkas berubah belum commit** (fase 12 UI/Livewire) | git status | 2026-11-05 |
| Migrasi | 61 | `database/migrations` | 2026-11-05 |
| Model | 55 | `app/Models` | 2026-11-05 |
| Enum | 35 | `app/Enums` | 2026-11-05 |
| Route | 73 (`route:list --json`) | artisan | 2026-11-05 |
| Service | 39 | `app/Services` | 2026-11-05 |
| Livewire | 3 | `app/Livewire` | 2026-11-05 |
| Command | 6 | `app/Console/Commands` | 2026-11-05 |
| File test | 30 | `tests/` | 2026-11-05 |
| `APP_ENGINE_VERSION` | `calc-v1.0.0` | config | 2026-11-05 |
| Baseline QA live | **belum dijalankan ulang** `[?]` | — | — |

## Dokumen & aset manual
| Berkas | Isi |
|---|---|
| `README.md` | Daftar isi + label bukti |
| `CHANGELOG.md` | Riwayat perubahan manual |
| `chapters/01..23-*.md` | Bab manual |
| `templates/feature-matrix.md` | Matriks fitur → status/bukti/bab |
| `templates/fitur-final-entry.md` | Template entri fitur final |
| `templates/checklist-pr.md` | Checklist PR update manual |
| `templates/evidence-note.md` | Catatan bukti |
| `PRD.md` (root repo) | Product Requirements → wiki `PRD` |
| `ERD.md` (root repo) | Entity Relationship (skema data) → wiki `ERD` |

## Prosedur singkat pembaruan
1. `bash scripts/manual-snapshot.sh` → bandingkan dengan tabel Snapshot di atas.
2. Update bab terdampak + `CHANGELOG.md` + `templates/feature-matrix.md`.
3. Perbarui tabel di berkas ini (versi + tanggal + angka).
4. Jalankan `templates/checklist-pr.md`.

## Riwayat versi
| Versi | Tanggal | Ringkasan |
|---|---|---|
| 0.1 | 2026-11-05 | Inisialisasi 23 bab + mekanisme update (templates, CHANGELOG, MANIFEST, snapshot script) |
| 0.1.1 | 2026-11-05 | Publikasi repo + wiki (32 halaman) |
| 0.1.2 | 2026-11-05 | Tambah dokumen produk `PRD.md` + `ERD.md` → wiki (34 halaman) |
| 0.1.3 | 2026-11-05 | Poles `README.md` (lebih informatif & terstruktur) |
| 0.1.4 | 2026-11-05 | Mockup interaktif `mockup-loan-analytic-bpr.html` + golden readiness checker |
