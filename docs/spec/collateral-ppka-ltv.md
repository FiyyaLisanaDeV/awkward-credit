# Spec — Agunan, PPKA/Haircut, Appraisal, LTV (DRAFT)

Status: DRAFT | **Prioritas verifikasi V1** | `[K]` D-07.

## 1. Anti double-haircut (wajib)
Pisahkan dan simpan eksplisit: (1) **nilai pasar/taksasi** (appraisal), (2) **nilai likuidasi** (policy/appraisal), (3) **nilai diperhitungkan/PPKA** sesuai POJK kualitas aset BPR/BPRS berlaku `[K→H]`, (4) **basis LTV** tunggal yang disahkan.  
Larangan: menerapkan haircut PPKA lalu memotong lagi secara implisit via LTV tanpa definisi (fix T13).

## 2. Master kategori (`collateral_categories`, berversi)
Field: `category`, `collateral_type`, `encumbrance` (HT/fidusia/hipotek/kuasa/tunai/dll), `valuation_age_bucket` (mis. independen <=1 th `[K]`), `pct_recognized`, `legal_basis` (nomor POJK/pasal `[H]`), `effective_from/to`, `status`.  
X2/X3 memuat tabel contoh kategori/% `[E]` (emas, SHM+HT/fidusia, resi gudang, tanpa HT, tanah adat, tempat usaha+kuasa, kendaraan/mesin, penjaminan tertentu, independen, tunai, lain) — **bukan angka final**; verifikasi ke naskah resmi.

## 3. Appraisal & kontrol
Metode/penilai/frekuensi revaluasi `[K]`, usia penilaian, bukti foto/dokumen (storage privat+sha256), keabsahan pengikatan, asuransi jaminan bila relevan. LTV contoh X1 likuidasi 80% pasar `[E]` = parameter/policy, bukan konstanta.

## 4. Tes
Negatif: double-haircut terdeteksi/ditolak desain; kategori tanpa `legal_basis [H]` tidak ACTIVE; LTV boundary; appraisal kadaluarsa `[K]`.
