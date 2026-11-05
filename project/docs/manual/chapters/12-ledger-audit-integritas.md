# Bab 12 — Ledger Append-Only, Audit Log, Integritas Hash Chain, dan Replay

Modul ini memastikan **jejak audit yang tidak dapat diubah** dan **reproduksibilitas** hasil.

## 12.1 Dua jejak berbeda `[E]`
| Aspek | `ledger` | `audit_log` |
|---|---|---|
| Isi | Peristiwa domain (business events) + payload | Perubahan field entitas (old/new value) |
| Integritas | **Hash chain** (`prev_hash`/`hash`) + trigger immutable | Trigger immutable + GIN index |
| Urutan | `seq` (bigIncrements) | `timestamp` |
| Idempotensi | `idempotency_key` (unique) | — |
| Pemakaian | `LedgerService::record` | (mekanisme ORM/observer) `[?]` |

## 12.2 Skema `ledger` `[E]`
`{seq bigIncrements, id uuid, occurred_at timestamptz, dibuat_pada timestamptz, cabang_id?, actor_id?, event(40), entity_type(50), entity_id uuid?, correlation_id uuid?, idempotency_key(100) unique?, payload jsonb, prev_hash char(64), hash char(64), ip_address?, user_agent?}`.
Index: `(entity_type, entity_id, occurred_at)`, `(event, occurred_at)`, `(cabang_id, occurred_at)`, `(correlation_id)`.
CHECK enum `event` (`LedgerEvent`). Trigger `ledger_immutable_trigger` → UPDATE/DELETE ditolak ("ledger immutable (BR-09)").

## 12.3 `LedgerService::record` `[E]`
Signature: `record(LedgerEvent $event, string $entityType, ?string $entityId, array $payload, ?Authenticatable $actor, ?string $correlationId, ?string $idempotencyKey, ?Carbon $occurredAt)`.
Dalam transaksi:
1. Bila `idempotencyKey` ada & sudah tersimpan → **kembalikan baris lama** (idempoten).
2. Ambil **advisory lock** `pg_advisory_xact_lock(hashtext(lock_key))` (serialisasi rantai).
3. `prevHash` = `hash` baris terakhir (atau `ChainHasher::genesis()` = 64 nol).
4. `occurred_at` dinormalkan (`startOfSecond`).
5. `hash = ChainHasher::hash(prevHash, payload, timeUtc, event, entityType, entityId)`.
6. Simpan; `ip_address`/`user_agent` diisi kecuali berjalan dari console (null).

## 12.4 `ChainHasher` `[E]`
- `hash(prevHash, payload, occurredAt, event, entityType, entityId)` = `sha256(prevHash \n canonical(payload) \n occurredAt \n event \n entityType \n entityId)`.
- `time(value)` = UTC format `Y-m-d\TH:i:s\Z`.
- `canonicalJson(payload)` = bentuk kanonik: list → `[...]`; object → key di-`ksort`; bool → `true/false`; null → `null`; int → string; float → dibulatkan `canonical_scale` (6) lalu di-trim; string → `json_encode` (unescaped unicode/slashes).
- `genesis()` = 64 karakter `0`.

## 12.5 Verifikasi & replay `[E]`
| Alat | Fungsi |
|---|---|
| `php artisan ledger:verify [--from --to]` | Iterasi `seq`; cek `prev_hash` = hash baris sebelumnya & `hash` = hash isi → error list |
| `GET /api/v1/ledger/verify` | Menjalankan `Artisan::call('ledger:verify')`, kembalikan `{exit, output}` (200/500) |
| `php artisan simulasi:replay {id}` | Jalankan ulang simulasi → bandingkan JSON kanonik |
| `POST /api/v1/simulasi/{simulasi}/replay` | Sama, kembalikan `{identik, expected, actual}` |
| `config/ledger.php verify_schedule='0 1 * * *'` | Jadwal verifikasi harian `[A]` (perlu scheduler aktif) |

## 12.6 Event ledger `[E]` — `LedgerEvent` (23 nilai)
`SIMULASI_DIJALANKAN, ANALISIS_DISIMPAN, PERMOHONAN_DIBUAT, PERMOHONAN_DIAJUKAN, PERMOHONAN_DIKEMBALIKAN, PERMOHONAN_DISETUJUI, PERMOHONAN_DITOLAK, DOKUMEN_DIBUAT, DOKUMEN_DITANDATANGANI, DOKUMEN_DIUNGGAH, CHECKLIST_DIVERIFIKASI, PARAMETER_DIAKTIFKAN, TEMPLATE_DISETUJUI, SLIK_DIIMPOR, KTP_DIEKSTRAKSI, DATA_DIIMPOR, IMPORT_DIBATALKAN, AKAD_DICATAT, PENCAIRAN_DICATAT, ANGSURAN_DIBAYAR, ANGSURAN_DIREVERSAL, AKSES_PII, PENGGUNA_DIAKTIFKAN`.

## 12.7 API ledger `[E]`
| Method | Endpoint | Perilaku |
|---|---|---|
| GET | `/api/v1/ledger` | ADMIN: semua; non-ADMIN: filter `cabang_id`; filter `event`, `entity_type`; urut `seq` desc; paginate `ledger_per_page` (50) |
| GET | `/api/v1/ledger/verify` | Jalankan verifikasi rantai |

## 12.8 Prinsip & batasan
1. **Append-only**: tidak ada UPDATE/DELETE (ditegakkan trigger).
2. **Koreksi** dilakukan dengan menambah baris baru (pola reversal), bukan mengubah.
3. Rantai dilindungi advisory lock → aman untuk penulisan paralel.
4. Idempotensi mencegah duplikasi event.
5. `simulasi_kredit` juga **append-only per run** (ADR-013) dan menyimpan `input_hash`, `engine_version`, `parameter_version`.

## 12.9 Gap `[K]`
- **Ledger resmi vs bayangan + COA/jurnal finansial seimbang** = D-01 `[K]`; saat ini ledger = peristiwa domain, belum posting jurnal akuntansi ber-COA.
- Scheduler verifikasi harian perlu diaktifkan di produksi `[A]`.
- `audit_log` pengisian otomatis (observer) perlu verifikasi `[?]`.
