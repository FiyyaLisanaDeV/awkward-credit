# Spec — BMPK, RAC, Konsentrasi Portofolio (DRAFT)

Status: DRAFT | `[K]` D-13,D-14. Semua ambang/persen = klaim independen sampai `[H]`.

## 1. BMPK (L1)
Hard-stop **non-bypass** (bukan sekadar warning). Cakupan `[K]`: pihak terkait, satu peminjam bukan terkait, satu kelompok peminjam; basis modal rujukan `[K]`. Diperlukan definisi `related_party` dan `borrower_group` (beneficial owner/keterkaitan) `[K]`. `ExposureAggregator` menghitung exposure existing+pengajuan sebelum submit/komite.

## 2. RAC pre-committee (L3)
Cek otomatis sebelum Komite: limit sektor `[K]`, 10 debitur terbesar `[K]`, agregat portofolio `[K]`, instansi TPP `[K]`. Over-limit → tolak sistem **atau** persetujuan Direksi dengan **justifikasi tertulis + audit event** (tetap patuhi BMPK hukum: non-override `[K]`). Definisi sektor/kode `[K]`.

## 3. Histori & metrik
`sector_composition_history`, `npl_by_sector`, `borrower_concentrations`, `portfolio_snapshots` per periode. Hitung kontribusi sektor ke NPL dan tren komposisi (antisipasi “angka saat ini menipu”).

## 4. Tes negatif (wajib)
BMPK langgar ditolak; sektor/instansi/debitur/agregat over-limit ditolak; override tanpa justifikasi ditolak; audit tercatat; histori tersimpan & comparable.
