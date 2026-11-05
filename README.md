# CommandCode BPR — Rencana Detail (berbasis referensi Excel)

Tanggal penyusunan: 2026-11-05 (Asia/Makassar)

Sifat: dokumen perencanaan/analisis referensi. **Bukan** keputusan bertanda tangan, **bukan** spesifikasi final produksi, dan **tidak** menyalin data nasabah (PII).

## Tujuan
Menyusun rencana implementasi detail aplikasi **Loan Analytic BPR** (Laravel monolit) dengan menjadikan **workbook Excel referensi** sebagai sumber struktur, rumus, alur, dan artefak bisnis — lalu memetakannya ke modul, data, engine, workflow, dokumen, ledger, dan rencana eksekusi.

## Sumber referensi (3 workbook bisnis)
Nama berkas, ukuran, dan lokasi workbook **tidak didokumentasikan di repo ini** karena tergolong **data kritikal** — dikecualikan lewat `.gitignore` dan tidak pernah di-commit. Ringkasan peran (label internal X1/X2/X3):

| # | Peran produk | Cakupan |
|---|---|---|
| X1 | Kasus/berkas TPP konsumtif (end-to-end) | analisis, hitungan/biaya/jadwal flat, FSS scoring, SLIK consent, memo cair, akad/surat, asuransi, appraisal, checklist/registrasi |
| X2 | Template MASTER Modal Kerja (Flat/Anuitas) | profil→asumsi→neraca/LR→rasio→modal kerja→kapasitas/DSCR→sensitivitas→5C→rekomendasi→jaminan→perjanjian→jadwal→checklist |
| X3 | Template MASTER Grace Period (superset: Flat/Anuitas/Grace) | sama dengan X2 + field sumber/perkiraan pelunasan pokok (balloon) & blok kecukupan pelunasan |

> Semua fakta struktur bersumber dari enumerasi file + ekstraksi struktural (ZIP/XML) `[E]`. Nilai sel identitas **tidak dicatat**. Data input workbook bersifat **dummy** `[E user]`; fixture/golden diberi label sumber tanpa memuat PII.

## Daftar dokumen perencanaan
1. [01-inventaris-excel.md](01-inventaris-excel.md) — inventaris struktur 3 workbook: sheet, rumus kunci, input/label/dropdown, output, dependency, delta X2↔X3.
2. [02-pemetaan-excel-ke-aplikasi.md](02-pemetaan-excel-ke-aplikasi.md) — pemetaan sheet→modul/domain, field→tabel/DTO, dokumen→template, import→adapter.
3. [03-rencana-implementasi-detail.md](03-rencana-implementasi-detail.md) — arsitektur, prinsip, engine & formula (koreksi wajib), data model target, workflow/status, ledger/audit, import, UI/API, security, testing/golden, rilis bertahap, backlog & DoD.
4. [04-keputusan-risiko-gap.md](04-keputusan-risiko-gap.md) — keputusan terbuka (D-01..), risiko, temuan Excel yang **tidak boleh disalin mentah**, dan gap yang memblokir implementasi/golden.
5. [05-bukti-ekstraksi.md](05-bukti-ekstraksi.md) — metode ekstraksi, keterbatasan, jejak bukti; konfirmasi tidak menyimpan PII.

## Kontrak & guardrail (mengikat rencana ini)
- Uang = `Money`/BIGINT rupiah (`brick/math`), **tanpa float**. `[Kebijakan rencana]`
- Rumus/angka hukum/kebijakan **tidak dari ingatan model**; sumber = workbook/POJK/keputusan bertanda tangan, diberi label `[V]/[E]/[K]/[?]` bila dikutip sebagai angka keputusan. Golden test ditulis **sebelum** kode. `[Kebijakan rencana]`
- Koreksi wajib (pasca review, tercatat di rencana kerja): **IDIR = 85,93% [K: butuh verifikasi kasus sumber]** (bukan 83%); penghasilan bersih **Rp2.948.080 [K]**; **tanpa faktor flat→efektif 1,81x** — pakai RATE eksak (contoh 28,88%/60 bln [K]). Data Excel = **dummy** (klarifikasi user) → fixture/golden tetap diberi label dummy/sumber workbook yang jelas. `[Rencana Kerja §3/kontrak]`
- Excel mengandung temuan yang tidak boleh disalin mentah (lihat [04](04-keputusan-risiko-gap.md)): konflik mesin keputusan TPP, SLIK tidak terhubung ke IDIR, scoring TPP = template KPR, bug neraca/ringkasan/PK di MASTER, angka hardcode/terbilang di dokumen. `[E: ekstraksi/analisis referensi]`
- Stack target (selaras repo existing + keputusan infra): Laravel 13, PHP ≥ 8.3, PostgreSQL, queue/database/sync sesuai keputusan, **tanpa Redis/MinIO** kecuali diputuskan; Livewire/Blade (Filament hanya bila D-04 mengizinkan) — lihat [03](03-rencana-implementasi-detail.md).

## Cara membaca
1. Mulai dari dokumen ini, lalu [01](01-inventaris-excel.md) untuk memahami isi Excel per sheet.
2. Baca [02](02-pemetaan-excel-ke-aplikasi.md) untuk traceability Excel→fitur/data.
3. Baca [03](03-rencana-implementasi-detail.md) sebagai rencana eksekusi detail.
4. Selesaikan [04](04-keputusan-risiko-gap.md) (keputusan & verifikasi) **sebelum** mengunci golden test/engine produksi.

## Manual Book & Wiki
- **Manual book** aplikasi (cakupan seluruh fungsi, 23 bab, label bukti `[E]/[A]/[K]/[H]/[Rencana]`) ada di [`project/docs/manual/`](project/docs/manual/README.md) — mulai dari daftar isi, lalu bab 1–23.
- **Mekanisme update berkala** manual book (saat fitur fungsi menjadi **final**) di [`project/docs/manual/chapters/22-mekanisme-update-manual-book.md`](project/docs/manual/chapters/22-mekanisme-update-manual-book.md), dengan kartu kendali [`MANIFEST.md`](project/docs/manual/MANIFEST.md), [`CHANGELOG.md`](project/docs/manual/CHANGELOG.md), dan [`feature-matrix`](project/docs/manual/templates/feature-matrix.md).
- **Wiki** repo ini memuat manual book (Bab 1–23) + glosarium/changelog/manifest sebagai halaman-halaman wiki — lihat tab **Wiki**.

## Workspace proyek
- [`project/`](project/) — workspace eksekusi (spec, decisions D-01..D-20, questions, evidence, golden vectors, manual book).
- [`research/`](research/) — riset & rekonsiliasi kebijakan/POJK, verifikasi primer (PPKA, SLIK, transparansi).
