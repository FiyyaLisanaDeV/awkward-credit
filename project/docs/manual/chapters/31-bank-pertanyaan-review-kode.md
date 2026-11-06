# Bab 31 — Bank Pertanyaan Review Kode & Peningkatan Mutu

> **Ringkas:** bank pertanyaan untuk reviewer kode dan tim coder. Setiap pertanyaan menyertakan **insight** (apa yang dipelajari) dan **sinyal gagal** (apa yang harus diperbaiki). Fokus: menemukan risiko nyata, bukan sekadar checklist.

## 31.1 Cara memakai

- Pilih area yang berubah pada PR.
- Jawab dengan bukti: file, baris, test, query, atau screenshot.
- Jika jawaban “tidak”, tulis tindakan perbaikan + pemilik.
- Prioritaskan pertanyaan yang menyentuh uang, audit, PII, dan multi-cabang.

## 31.2 Arsitektur & layering

| ID | Pertanyaan | Insight untuk coder/reviewer | Sinyal gagal |
|---|---|---|---|
| AR-01 | Apakah controller hanya validasi/otorisasi/panggil service? | Menjaga logika bisnis tetap di service | Controller berisi query/rumus |
| AR-02 | Apakah calculator bebas Eloquent/DB? | Kalkulasi bisa diuji murni | Calculator memanggil model/DB |
| AR-03 | Apakah service stateless? | Aman untuk request paralel | Service menyimpan state request |
| AR-04 | Apakah transaksi ditutup di layer orkestrasi? | Mencegah partial write | Service dalam service membuka transaksi sendiri |
| AR-05 | Apakah ada duplikasi rumus antar service? | Satu sumber kebenaran | Dua tempat menghitung hal sama |
| AR-06 | Apakah exception domain dipakai konsisten? | Pesan/HTTP code stabil | `abort()` acak atau `Exception` generik |
| AR-07 | Apakah parameter policy selalu lewat resolver? | Grandfathering terjaga | Rule memakai `active()` langsung |
| AR-08 | Apakah audit/ledger dipanggil di transaksi yang sama? | Tidak ada perubahan tanpa jejak | Ledger di luar transaksi |
| AR-09 | Apakah DTO/array contract terdokumentasi? | Mudah direfactor | Array tanpa bentuk jelas |
| AR-10 | Apakah dependency mengarah ke dalam (domain)? | Domain tidak bergantung UI | Domain mengimpor HTTP/Blade |
| AR-11 | Apakah config dipakai, bukan `env()`? | Konsisten di produksi | `env()` di service |
| AR-12 | Apakah angka bisnis ada di policy/konstanta? | Tidak ada hardcode | Literal 0.1/0.35/80 |
| AR-13 | Apakah ada interface untuk integrasi eksternal? | Mudah diganti/mock | `new Client()` langsung di service |
| AR-14 | Apakah ada circular dependency? | Menghindari bootstrap rumit | A butuh B, B butuh A |
| AR-15 | Apakah kegagalan satu modul terisolasi? | Sistem tetap jalan | Satu error mematikan semua |
| AR-16 | Apakah idempotency dipertimbangkan? | Aman untuk retry | Submit ganda membuat data ganda |
| AR-17 | Apakah perubahan ini bisa diuji tanpa HTTP? | Test cepat/stabil | Test harus boot seluruh app |
| AR-18 | Apakah dokumen arsitektur diperbarui? | Pengetahuan tidak hilang | Kode berubah, TEKNIS basi |

## 31.3 Domain & kalkulasi

| ID | Pertanyaan | Insight | Sinyal gagal |
|---|---|---|---|
| DO-01 | Apakah uang memakai decimal/integer, bukan float? | Menghindari drift | `float` untuk rupiah |
| DO-02 | Apakah pembulatan punya kebijakan eksplisit? | Hasil bisa direproduksi | `round()` acak |
| DO-03 | Apakah selisih pembulatan jatuh ke periode terakhir? | Jadwal balance | Selisih hilang |
| DO-04 | Apakah total pokok = plafon? | Invariant jadwal | Selisih > Rp1 |
| DO-05 | Apakah closing balance = 0? | Jadwal lunas | Sisa saldo |
| DO-06 | Apakah PMT memakai rate bulanan? | Anuitas benar | Rate tahunan dipakai langsung |
| DO-07 | Apakah tenor 1 diuji? | Edge case | Pembagian nol/aneh |
| DO-08 | Apakah rate 0 diuji? | Edge case | Division by zero |
| DO-09 | Apakah tenor besar (360) diuji? | Performa/rounding | Melambat/overflow |
| DO-10 | Apakah EIR dihitung numerik? | Bukan aproksimasi | 1,81× |
| DO-11 | Apakah grace period pokok 0? | Sesuai produk | Pokok muncul saat grace |
| DO-12 | Apakah bullet akhir = plafon + bunga? | Sesuai X3 | Bullet salah |
| DO-13 | Apakah rasio balloon memakai kewajiban benar? | Status tepat | Basis salah |
| DO-14 | Apakah IDIR tepat di 35/80 diuji? | Boundary | `>` vs `>=` salah |
| DO-15 | Apakah DSCR memakai total kewajiban? | SLIK ikut | Hanya kredit baru |
| DO-16 | Apakah RPC = surplus × margin? | Konsisten | Margin hardcode |
| DO-17 | Apakah plafon max Flat/Anuitas diuji? | Batas kredit | Formula tertukar |
| DO-18 | Apakah biaya provisi/admin/survey dihitung? | Dana diterima benar | Biaya hilang |
| DO-19 | Apakah payoff/penalti memakai basis benar? | Top-up benar | Penalti dari sisa |
| DO-20 | Apakah LTV memakai basis PPKA? | Tidak double haircut | Pasar×PPKA×LTV |
| DO-21 | Apakah kategori agunan dari POJK? | Kepatuhan | Persen karangan |
| DO-22 | Apakah band 5C data-driven? | Bisa diubah policy | If-else hardcode |
| DO-23 | Apakah override SLIK punya alasan/audit? | Bisa diaudit | Override diam-diam |
| DO-24 | Apakah stress test memakai delta policy? | Skenario konsisten | Delta hardcode |

## 31.4 Database & migrasi

| ID | Pertanyaan | Insight | Sinyal gagal |
|---|---|---|---|
| DB-01 | Apakah migrasi reversible? | Rollback aman | `down()` kosong |
| DB-02 | Apakah FK/index untuk kolom query? | Performa/integritas | Full scan |
| DB-03 | Apakah unique constraint benar? | Cegah duplikat | Duplikat bisa masuk |
| DB-04 | Apakah partial index memakai `deleted_at`? | Soft delete benar | Baris terhapus ikut unik |
| DB-05 | Apakah uang `numeric(15,2)`? | Presisi | `float`/`real` |
| DB-06 | Apakah JSONB punya bentuk stabil? | Snapshot bisa dibaca | JSON campur tipe |
| DB-07 | Apakah snapshot immutable? | Grandfathering | Bisa di-update |
| DB-08 | Apakah trigger immutability diuji? | Audit kuat | Trigger tidak ada |
| DB-09 | Apakah sequence atomik? | Tanpa race | `count()` |
| DB-10 | Apakah backfill idempoten? | Aman diulang | Duplikat saat rerun |
| DB-11 | Apakah backfill menaikkan/menurunkan sequence? | Tidak reset | `last_number` turun |
| DB-12 | Apakah timezone konsisten? | Hash/tanggal stabil | UTC vs WITA campur |
| DB-13 | Apakah CHECK constraint untuk enum? | Data valid | String bebas |
| DB-14 | Apakah index mendukung filter cabang/status? | UI cepat | Table scan |
| DB-15 | Apakah query memakai eager loading? | Hindari N+1 | Query per baris |
| DB-16 | Apakah pagination dipakai? | Memori aman | `get()` semua |
| DB-17 | Apakah lock/advisory dipakai di titik rawan? | Konsistensi | Race condition |
| DB-18 | Apakah transaksi mencakup ledger+data? | Atomik | Partial write |
| DB-19 | Apakah seed referensi punya provenance? | Bisa diaudit | Angka tanpa sumber |
| DB-20 | Apakah skema cocok dengan INVENTARIS? | Dokumen akurat | Drift skema |

## 31.5 API, service & error contract

| ID | Pertanyaan | Insight | Sinyal gagal |
|---|---|---|---|
| AP-01 | Apakah validasi di FormRequest/service? | Input aman | Validasi di UI saja |
| AP-02 | Apakah scoping cabang dicek? | Tidak bocor | IDOR |
| AP-03 | Apakah permission dicek? | RBAC | Endpoint terbuka |
| AP-04 | Apakah error code stabil? | Client bisa handle | Pesan berubah-ubah |
| AP-05 | Apakah operasi tulis idempoten? | Retry aman | Duplikat |
| AP-06 | Apakah transaksi rollback saat gagal? | Konsisten | Partial commit |
| AP-07 | Apakah response tidak membocorkan PII? | Privasi | NIK di JSON |
| AP-08 | Apakah null/empty ditangani? | Tidak 500 | Null deref |
| AP-09 | Apakah tanggal/jam memakai timezone? | Konsisten | Off-by-hour |
| AP-10 | Apakah concurrency diuji? | Tidak bentrok | Last write wins |
| AP-11 | Apakah API versioning jelas? | Kompatibilitas | Breaking change diam-diam |
| AP-12 | Apakah logging tidak memuat PII? | Aman | NIK di log |
| AP-13 | Apakah partial failure ditangani? | Retry/compensate | Data setengah jadi |
| AP-14 | Apakah contract test ada? | Client aman | Perubahan tak terdeteksi |
| AP-15 | Apakah dokumentasi endpoint diperbarui? | Mudah dipakai | Dokumentasi basi |

## 31.6 UI, Livewire & UX

| ID | Pertanyaan | Insight | Sinyal gagal |
|---|---|---|---|
| UI-01 | Apakah state Livewire minimal? | Mudah diuji | State tersebar |
| UI-02 | Apakah validasi server-side? | Tidak bisa bypass | Validasi HTML saja |
| UI-03 | Apakah isolasi cabang di `mount()`? | Tidak bocor | Komponen lintas cabang |
| UI-04 | Apakah permission route dicek? | RBAC | Route terbuka |
| UI-05 | Apakah loading state ada? | UX jelas | Tombol dobel klik |
| UI-06 | Apakah empty state menjelaskan? | User paham | Layar kosong |
| UI-07 | Apakah error state informatif? | Bisa recovery | Pesan “error” saja |
| UI-08 | Apakah aksesibilitas (label/focus/aria)? | Inklusif | Hanya warna |
| UI-09 | Apakah CSS native dipakai? | Tanpa build tool | Kelas Tailwind tidak ada |
| UI-10 | Apakah format angka Indonesia? | Tidak salah baca | 1,000 vs 1.000 |
| UI-11 | Apakah konfirmasi untuk aksi irreversible? | Cegah salah | Submit langsung |
| UI-12 | Apakah redirect/flash konsisten? | Alur jelas | Balik ke halaman salah |
| UI-13 | Apakah query UI tidak N+1? | Cepat | Lazy load per baris |
| UI-14 | Apakah tabel panjang dipaginasi? | Memori aman | Render semua |
| UI-15 | Apakah UI dites (render/aksi/403)? | Regresi tertangkap | Hanya manual |

## 31.7 Keamanan, PII & RBAC

| ID | Pertanyaan | Insight | Sinyal gagal |
|---|---|---|---|
| SE-01 | Apakah permission mengikuti matriks role? | Kewenangan sah | Role salah |
| SE-02 | Apakah SoD (maker-checker) ditegakkan? | Cegah self-approve | Pembuat menyetujui |
| SE-03 | Apakah PII dienkripsi/masking? | Kepatuhan PDP | PII plaintext |
| SE-04 | Apakah akses PII diaudit? | Traceable | Tidak ada jejak |
| SE-05 | Apakah token/secret tidak di repo? | Kredensial aman | `.txt` token |
| SE-06 | Apakah CSRF aktif? | Form aman | `VerifyCsrfToken` dimatikan |
| SE-07 | Apakah mass assignment dibatasi? | Tidak bisa override | `$guarded=[]` |
| SE-08 | Apakah query aman dari injection? | Data aman | String SQL concat |
| SE-09 | Apakah output di-escape? | XSS aman | `{!! !!}` tanpa sanitasi |
| SE-10 | Apakah upload divalidasi? | File aman | MIME/ukuran bebas |
| SE-11 | Apakah akses lintas cabang ditolak? | Isolasi | 200 lintas cabang |
| SE-12 | Apakah audit log immutable? | Bukti kuat | Bisa update/delete |
| SE-13 | Apakah session/password aman? | Akun aman | Password lemah |
| SE-14 | Apakah rate limit login? | Anti brute force | Tanpa limit |
| SE-15 | Apakah backup terenkripsi? | Data aman | Dump plaintext |

## 31.8 Testing & QA

| ID | Pertanyaan | Insight | Sinyal gagal |
|---|---|---|---|
| TS-01 | Apakah golden test benar-benar verified? | Kontrak sah | `recomputed` dianggap verified |
| TS-02 | Apakah boundary diuji (tepat/atas/bawah)? | Bug ambang tertangkap | Hanya happy path |
| TS-03 | Apakah negative test ada? | Aturan benar | Pelanggaran lolos |
| TS-04 | Apakah property test untuk kalkulator? | Invariant kuat | Hanya contoh |
| TS-05 | Apakah concurrency test ada? | Race tertangkap | Hanya sekuensial |
| TS-06 | Apakah integration test end-to-end? | Alur nyata | Unit saja |
| TS-07 | Apakah UI test render/403? | Regresi UI | Tidak ada |
| TS-08 | Apakah test deterministik? | Tidak flaky | Waktu/acak |
| TS-09 | Apakah fixture bebas PII? | Aman | Data nasabah |
| TS-10 | Apakah test memakai DB terisolasi? | Tidak saling ganggu | Data bocor |
| TS-11 | Apakah failure message jelas? | Cepat debug | “Expected true” |
| TS-12 | Apakah test baru mengunci bug? | Regresi | Bug bisa kembali |
| TS-13 | Apakah test tidak melemahkan assertion? | Mutu | Assert dihapus |
| TS-14 | Apakah performance smoke ada? | Tidak lambat | Timeout produksi |
| TS-15 | Apakah CI menjalankan gate yang sama? | Konsisten | Lokal hijau, CI merah |

## 31.9 Performance & reliability

| ID | Pertanyaan | Insight | Sinyal gagal |
|---|---|---|---|
| PF-01 | Berapa query per halaman? | Deteksi N+1 | 100+ query |
| PF-02 | Apakah index dipakai? | Query cepat | Seq scan |
| PF-03 | Apakah pagination/eager load? | Memori aman | Load semua |
| PF-04 | Apakah cache punya invalidasi? | Data segar | Cache basi |
| PF-05 | Apakah jadwal 360 periode efisien? | UI responsif | Timeout |
| PF-06 | Apakah PDF generation berat? | Queue/memori | 500/timeout |
| PF-07 | Apakah batch import chunked? | Tidak OOM | Semua di memori |
| PF-08 | Apakah lock contention diukur? | Throughput | Deadlock |
| PF-09 | Apakah slow query dipantau? | Proaktif | Baru tahu saat down |
| PF-10 | Apakah health check tersedia? | Monitoring | Tidak tahu status |
| PF-11 | Apakah retry/backoff aman? | Resilien | Retry badai |
| PF-12 | Apakah load test dilakukan? | Kapasitas | Tidak tahu batas |

## 31.10 Operasional & rilis

| ID | Pertanyaan | Insight | Sinyal gagal |
|---|---|---|---|
| OP-01 | Apakah backup sebelum migrasi? | Recovery | Tidak ada backup |
| OP-02 | Apakah restore drill diuji? | Backup valid | Backup rusak |
| OP-03 | Apakah rollback plan ada? | Aman | Tidak bisa kembali |
| OP-04 | Apakah urutan migrasi aman? | Tidak downtime | Lock lama |
| OP-05 | Apakah seeder idempoten? | Re-run aman | Duplikat |
| OP-06 | Apakah env produksi benar? | Tidak debug | `APP_DEBUG=true` |
| OP-07 | Apakah log/alert ada? | Deteksi dini | Silent failure |
| OP-08 | Apakah scheduler berjalan? | Ledger verify | Tidak pernah |
| OP-09 | Apakah disk/memori cukup? | Stabil | Penuh |
| OP-10 | Apakah deployment terdokumentasi? | Konsisten | Langkah lisan |
| OP-11 | Apakah versi/tag rilis jelas? | Traceable | Tanpa tag |
| OP-12 | Apakah UAT & sign-off ada? | Kesiapan | Belum diuji |

## 31.11 Checklist review cepat (20 ya/tidak)

| # | Pertanyaan |
|---|---|
| 1 | Apakah perubahan uang memakai decimal/integer? |
| 2 | Apakah boundary IDIR/DSR/LTV diuji? |
| 3 | Apakah snapshot parameter tidak bisa berubah? |
| 4 | Apakah rule memakai `forPermohonan`, bukan `active()`? |
| 5 | Apakah submit tidak mengevaluasi aturan yang datanya belum ada? |
| 6 | Apakah approve mengecualikan BR-06? |
| 7 | Apakah penomoran memakai upsert atomik? |
| 8 | Apakah scope penomoran sama dengan backfill? |
| 9 | Apakah SLIK dedupe menangani `no_fasilitas` null? |
| 10 | Apakah DSCR memakai total kewajiban? |
| 11 | Apakah workbench menolak lintas cabang? |
| 12 | Apakah setiap analisis menulis ledger/audit? |
| 13 | Apakah trigger immutability aktif? |
| 14 | Apakah PII tidak masuk log/JSON publik? |
| 15 | Apakah token/secret tidak ada di repo? |
| 16 | Apakah test baru mengunci bug? |
| 17 | Apakah `verify.sh --full` hijau? |
| 18 | Apakah `hardcode:audit` 0? |
| 19 | Apakah backup/restore diuji? |
| 20 | Apakah UAT/sign-off ada? |
