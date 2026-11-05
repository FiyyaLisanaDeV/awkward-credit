# 08 — Ringkasan Verifikasi Primer V1/V3/V4 (DRAFT)

Tanggal: 2026-11-05 | Status: **`[K]` — ekstraksi dari naskah resmi OJK belum selesai; butuh Compliance/Legal.**

## Cakupan
| Kode | Topik | Dokumen | Keputusan terkait |
|---|---|---|---|
| V1 | Kualitas aset BPR/BPRS, kolektibilitas, **PPKA/haircut**, penilaian agunan, CKPN/restrukturisasi/hapus buku | `V1-PPKA-kualitas-aset-BPR.md` | D-07, D-16 |
| V3 | **SLIK/IDeb**: consent, penggunaan, koreksi, keamanan | `V3-SLIK-IDeb.md` | D-03, D-09, D-10 |
| V4 | **Transparansi & perlindungan konsumen**: suku bunga/biaya/total biaya, jadwal, denda/penalti | `V4-transparansi-suku-bunga-biaya.md` | D-05, D-06 |

## Temuan yang dapat dikunci sekarang (tanpa angka pasal)
- **D-07/T13:** desain wajib **4 nilai terpisah** (pasar/likuidasi/PPKA/basis LTV) + master kategori berversi bersumber `legal_basis [H]`. Tabel % X2/X3 `[E]` ≠ final.
- **D-03/T2–T4:** consent gate keras + pipeline SLIK review-first (parse/validasi/confidence/provenance) + DSR/Character dari data terstruktur; sengketa ≠ mutasi data sumber.
- **D-05/D-06:** disclosure nominal + **EIR/IRR numerik** (tanpa 1,81x) + rincian biaya/dana diterima/jadwal/penalti; satu sumber snapshot→dokumen→ledger (fix T8–T10,T12).
- Semua parameter/ambang/definisi disclosure/PPKA = **DRAFT non-ACTIVE** sampai `[H]`.

## Yang masih memblokir `[H]`
1. Nomor/tahun/pasal/ayat naskah resmi POJK/ketentuan berlaku (BPR) untuk V1, V3, V4 — **diambil dari OJK/peraturan.go.id oleh Compliance/Legal**.
2. Kategori/% PPKA final & basis LTV (D-07).
3. Definisi komponen EIR/total biaya & aturan denda/penalti (D-05/D-06).
4. Bentuk/cakupan consent SLIK yang sah & kanal/format operasional (D-03).
5. Golden cases terverifikasi (dummy) termasuk C-002 `[K]`.

## Next
- Jalankan verifikasi naskah resmi (screenshot/PDF + metadata URL/tanggal unduh) → ubah rujukan ke `[H]`/DRAFT bersumber.
- Turunkan ke `project/docs/spec/*`, `decisions.md`, master data (non-ACTIVE) + golden tests.
