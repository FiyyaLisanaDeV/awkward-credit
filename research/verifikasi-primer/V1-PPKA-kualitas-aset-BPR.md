# V1 — Verifikasi Primer: Kualitas Aset BPR/BPRS, PPKA (Agunan Dapat Diperhitungkan), Haircut, Kolektibilitas, LTV

Tanggal: 2026-11-05 (WITA) | Status: **DRAFT `[K]` — sumber primer resmi belum dikunci ke naskah pasal/ayat final**.  
Pemilik verifikasi: Compliance/Legal BPR + Risk/Akuntansi. **Bukan nasihat hukum.**

## 1. Tujuan
Mengunci acuan regulasi **yang berlaku untuk BPR/BPRS** atas: penilaian kualitas aset/kolektibilitas, Pembentukan Penyisihan Penghapusan Aset (PPAP/CKPN pada kerangka akuntansi yang relevan `[K]`), **agunan yang dapat diperhitungkan (PPKA)**, haircut/kategori pengikatan, penilaian agunan, restrukturisasi/hapus buku — untuk finalisasi **D-07, D-16, spec `collateral-ppka-ltv`, CKPN**.

## 2. Status verifikasi sumber
| Item | Status | Catatan |
|---|---|---|
| Nomor/tahun POJK kualitas aset BPR/BPRS **terbaru berlaku** | `[K]` belum diverifikasi | Workbook X2/X3 melabeli konteks “POJK 2024” `[E]` (nama file). **Nomor, pasal, kategori, % final wajib** diambil dari naskah OJK/peraturan.go.id. Jangan asumsikan nomor dari memori. |
| Naskah pasal (kolektibilitas, PPKA, penilaian agunan, PPAP) | `[K]` | Diunduh/dirujuk oleh Compliance dari portal resmi OJK; simpan `legal_basis` (nomor, tahun, pasal, ayat, tanggal berlaku). |
| Tabel kategori/% PPKA | `[K]` | X2/X3 punya tabel contoh `% diperhitungkan` `[E]` — **bukan final**. Petakan 1:1 ke kategori/pengikatan/usia penilaian di POJK `[H]`. |
| Dampak ke KPMM/akuntansi (arah PPKA↔CKPN) | `[K]` | Koordinasi V5 (akuntansi/COA/KPMM) + D-16. |

**Cara verifikasi (wajib):** (1) Cari POJK terbaru tentang kualitas aset/penilaian kualitas aset **BPR/BPRS** di situs resmi OJK; (2) cek status pencabutan/perubahan; (3) ekstrak pasal relevan ke `legal_basis`; (4) tanda tangani keputusan `[H]`.

## 3. Poin regulasi yang harus dikunci (checklist ekstraksi pasal) `[K]`
| Poin | Yang harus dicari di naskah | Dampak app | Status |
|---|---|---|---|
| Ruang lingkup & definisi | kualitas aset produktif, debitur, agunan, PPAP/PPKA | glossary, enum, snapshot | `[K]` |
| Kolektibilitas | kriteria & faktor (ketepatan bayar, prospek usaha, kondisi keuangan, agunan — sesuai pasal) untuk penetapan kualitas (lancar/dalam perhatian khusus/kurang lancar/diragukan/macet atau istilah setara) `[K]` | `collectibility` master, EWS, CKPN fallback, write-off eligibility | `[K]` |
| PPKA / agunan dapat diperhitungkan | jenis agunan yang diakui, syarat pengikatan (HT/fidusia/hipotek/sesuai pasal), batas pengakuan, hubungan dengan penilaian | `collateral_categories.pct_recognized/legal_basis`, engine | `[K]` |
| Penilaian agunan | metode, penilai (independen/internal sesuai ketentuan), frekuensi/usia penilaian, dokumentasi | appraisal workflow, revaluasi, staleness check | `[K]` |
| PPAP/CKPN | basis pembentukan, komponen, kecukupan, hubungan dengan PPKA | `CkpnPpkaService`, D-16 | `[K]` |
| Restrukturisasi | syarat, batas, larangan untuk menghindari penurunan kualitas/CKPN `[K]` | `RestructureService` guard + audit | `[K]` |
| Hapus buku & hapus tagih | syarat (kualitas, cadangan, upaya penagihan, seluruh fasilitas perjanjian? `[K]`), larangan/ketentuan parsial `[K]` | `WriteOffEligibilityService` (tes negatif) | `[K]` |

## 4. Implikasi desain (mengikat spec, meski angka pasal pending) `[A]`
- **4 nilai terpisah (anti double-haircut)**: pasar/taksasi, likuidasi, **PPKA** (hasil penerapan ketentuan `[H]`), basis **LTV** tunggal `[H D-07]`. Engine menyimpan komponen & `legal_basis_version`.
- **Policy-as-data**: kategori agunan, % PPKA, syarat pengikatan, usia penilaian, LTV max = `collateral_categories/policies` berversi; **non-ACTIVE** tanpa `legal_basis [H]`.
- **Input appraisal ≠ PPKA otomatis tanpa aturan**: PPKA dihitung dari kategori+enkumbransi+usia+ketentuan; LTV memakai basis yang disahkan.
- **Traceability**: setiap angka PPKA/LTV/CKPN pada snapshot menyebut `policy_version`, `category_id`, `legal_basis`.

## 5. Cross-check ke workbook `[E, bukan sumber hukum]`
| Workbook | Bukti `[E]` | Risiko jika di-port mentah |
|---|---|---|
| X2/X3 S1 | aset/jaminan, VLOOKUP ke kategori POJK, % diperhitungkan | kategori/% contoh bisa tidak sesuai pasal terbaru `[K]` |
| X2/X3 S2 | tabel referensi haircut POJK (label kategori + % contoh) | **T13/double haircut**; % harus `[H]` |
| X1 appraisal | nilai pasar/likuidasi contoh (80%) | jadikan parameter, bukan konstanta |

## 6. Output yang dibutuhkan untuk menutup V1
1. Dokumen rujukan resmi (nomor/tahun/tanggal berlaku + tautan OJK/peraturan.go.id) `[H]`.
2. Ekstrak pasal → tabel mapping `kategori/pengikatan/usia/% PPKA/legal_basis` `[H]`.
3. Keputusan **D-07** (basis LTV, kategori, %), **D-16** (CKPN/PPKA/write-off/restrukturisasi) ditandatangani.
4. Golden test PPKA/LTV/CKPN boundary (dummy) + tes negatif double-haircut, CKPN<PPKA `[K]`, write-off parsial `[K]`.

## 7. Pertanyaan terbuka
- `UNKNOWN (V1a)`: Nomor/tahun POJK kualitas aset BPR/BPRS **yang berlaku** dan status perubahan/pencabutan?
- `UNKNOWN (V1b)`: Kategori agunan & %/syarat PPKA final per pengikatan/usia penilaian?
- `UNKNOWN (V1c)`: Metode/frekuensi penilaian agunan dan syarat penilai?
- `UNKNOWN (V1d)`: Syarat hapus buku/hapus tagih/restrukturisasi & larangan terkait `[K]`?
