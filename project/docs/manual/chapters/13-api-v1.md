# Bab 13 — Referensi API v1 (Lengkap per Endpoint)

Semua endpoint berprefix `/api/v1`. Autentikasi: Sanctum `Authorization: Bearer <token>` kecuali `login`. Semua respons JSON (mismatch → JSON via `shouldRenderJsonWhen(api/*)`).

## 13.1 Auth `[E]` — `Api/V1/AuthController`
| Method | Path | Middleware | Body/Param | Respons |
|---|---|---|---|---|
| POST | `/login` | `throttle:10,1` | `email, password, device_name?` | `201 {token, user}`; gagal `422 auth.failed` |
| POST | `/logout` | `auth:sanctum` | — | `204` |
| GET | `/me` | `auth:sanctum` | — | `{id,name,email,cabang_id,roles}` |

`user` = `{id, name, email, cabang_id, roles[]}`.

## 13.2 Debitur `[E]` — `Api/V1/DebiturController`
| Method | Path | Body | Catatan |
|---|---|---|---|
| GET | `/debitur` | `q?` | Daftar cabang; `q` filter nama `ilike`; paginate |
| POST | `/debitur` | `StoreDebiturRequest` (DebiturRules) | `201` |
| GET | `/debitur/{debitur}` | — | Load `pasangan, usaha, siklusUsaha, aset` |
| PUT/PATCH | `/debitur/{debitur}` | DebiturRules | update |
| POST | `/debitur/{debitur}/aset` | aset (lihat Bab 5) | `201`; hitung `nilai_diperhitungkan` |
| PATCH | `/aset/{aset}` | subset field aset | rehitung nilai |
| DELETE | `/aset/{aset}` | — | `204` |

## 13.3 Permohonan `[E]` — `Api/V1/PermohonanController`
| Method | Path | Catatan |
|---|---|---|
| GET | `/permohonan` | filter `status`; paginate |
| POST | `/permohonan` | `201`; scope debitur |
| GET | `/permohonan/{permohonan}` | load `debitur, produk, slikDebitur.kolektibilitas, kapasitasBayar, scoring5c, rekomendasi` |
| PUT/PATCH | `/permohonan/{permohonan}` | hanya DRAFT |
| POST | `/permohonan/{permohonan}/submit` | BR-04, BR-08 |

## 13.4 SLIK `[E]` — `Api/V1/SlikController`
| Method | Path | Catatan |
|---|---|---|
| GET | `/permohonan/{permohonan}/slik` | `{permintaan[], fasilitas[]}` |
| POST | `/permohonan/{permohonan}/slik` | tambah fasilitas; `urun=max+1`; `201` |
| DELETE | `/slik/{slik}` | `204` |

## 13.5 Analisis `[E]` — `Api/V1/AnalisisController`
| Method | Path | Body/Param |
|---|---|---|
| GET | `/permohonan/{permohonan}/analisis/neraca` | — |
| PUT | `/permohonan/{permohonan}/analisis/neraca` | kolom neraca (Bab 7) |
| GET | `/permohonan/{permohonan}/analisis/laba-rugi` | — |
| PUT | `/permohonan/{permohonan}/analisis/laba-rugi` | kolom laba rugi + `jumlah_bulan_berjalan` |
| GET | `/permohonan/{permohonan}/analisis/kapasitas` | — |
| POST | `/permohonan/{permohonan}/analisis/kapasitas/compute` | (butuh laba-rugi; 422 bila belum) |
| GET | `/permohonan/{permohonan}/analisis/scoring` | — |
| POST | `/permohonan/{permohonan}/analisis/scoring/compute` | `skor_condition` 1–5 + `justifikasi_*?` |
| GET | `/permohonan/{permohonan}/analisis/red-flags` | — (array `{kode, level, pesan}`) |
| POST | `/permohonan/{permohonan}/analisis/rekomendasi` | (butuh scoring; 422 bila belum) |

## 13.6 Simulasi `[E]` — `Api/V1/SimulasiController`
| Method | Path | Catatan |
|---|---|---|
| GET | `/simulasi` | filter `q` (label); paginate |
| POST | `/simulasi` | `201`; `SimulationRunner::execute` |
| GET | `/simulasi/{simulasi}` | scope cabang |
| POST | `/simulasi/{simulasi}/replay` | `{identik, expected, actual}` |

## 13.7 Approval `[E]` — `Api/V1/ApprovalController`
| Method | Path | Body |
|---|---|---|
| GET | `/approvals/inbox` | — |
| POST | `/permohonan/{permohonan}/approve` | `alasan?` |
| POST | `/permohonan/{permohonan}/reject` | `alasan` (required ≤500) |
| POST | `/permohonan/{permohonan}/return` | `alasan` (required ≤500) |

## 13.8 Kredit `[E]` — `Api/V1/KreditController`
| Method | Path |
|---|---|
| GET | `/kredit` (filter tidak ada; urut tanggal_cair) |
| GET | `/kredit/{kredit}` |
| GET | `/kredit/{kredit}/jadwal` |

## 13.9 Ledger `[E]` — `Api/V1/LedgerController`
| Method | Path | Catatan |
|---|---|---|
| GET | `/ledger` | filter `event`, `entity_type`; non-ADMIN scope cabang |
| GET | `/ledger/verify` | `{exit, output}` |

## 13.10 Dashboard `[E]` — `Api/V1/DashboardController`
`GET /dashboard` → `{per_status[], recent[], kredit_aktif, draft}` (scope cabang; `recent` limit `dashboard_recent=10`).

## 13.11 Konvensi umum `[E]`
- **Scope cabang**: mayoritas endpoint memvalidasi `cabang_id` (403 lintas cabang; ADMIN bebas).
- **Pagination**: Laravel paginator; `per_page` dari `config('loan.pagination.per_page')` (20), ledger 50.
- **Validasi** gagal → `422`; tidak ditemukan → `404`; otorisasi → `403`; konflik state → `409`; throttle → `429`.
- **Error JSON**: karena `shouldRenderJsonWhen` untuk `api/*`.

## 13.12 Kontrak & contoh `[E]` (ilustratif)
```http
POST /api/v1/login
Content-Type: application/json

{ "email": "admin@bpr.local", "password": "secret", "device_name": "web" }

HTTP/1.1 201 Created
{ "token": "1|xxxx", "user": { "id":1, "name":"Admin", "email":"admin@bpr.local", "cabang_id":"…", "roles":["ADMIN"] } }
```
```http
POST /api/v1/permohonan/{id}/analisis/scoring/compute
Authorization: Bearer 1|xxxx

{ "skor_condition": 4, "justifikasi_condition": "prospek baik" }

HTTP/1.1 200 OK
{ "skor_total": 3.8, "rating": "LAYAK", "overridden": false, ... }
```

## 13.13 Catatan & gap
- Rate limit `login` API = `10,1`; login web per-IP 5/60s (Bab 4).
- Belum ada versi API selain `v1`; dokumentasi OpenAPI `[Rencana]`.
- Endpoint import/dokumen/pembayaran belum ada (fase lanjut `[Rencana]`).
