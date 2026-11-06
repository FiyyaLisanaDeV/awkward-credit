# Bab 30 — Daftar Pertanyaan Verifikasi Golden & Fungsi Aplikasi

> **Ringkas:** daftar pertanyaan kerja untuk ahli kredit/Produk, reviewer independen, dan QA. Bagian A memverifikasi 29 golden vector; bagian B memverifikasi fungsi aplikasi yang baru dibangun. Setiap jawaban wajib punya bukti dan status `LULUS`/`GAGAL`/`UNKNOWN`.

## 30.1 Cara memakai

- Jawab per pertanyaan, jangan per kasus secara umum.
- Isi `Jawaban`, `Bukti` (file/sel/perintah), `Status`, `PIC`, `Tanggal`.
- `LULUS` hanya jika bukti bisa direproduksi orang lain.
- `UNKNOWN` berarti kasus belum boleh `verified=true`.
- Data nasabah/PII tidak boleh masuk formulir.

Format jawaban:

| ID | Pertanyaan | Jawaban ahli | Bukti | Status | PIC | Tanggal |
|---|---|---|---|---|---|---|
| A1.1 | Sumber sel? | | | | | |

## 30.2 Bagian A — Golden Vectors

### A1. Dua belas pertanyaan wajib untuk setiap kasus

| ID | Pertanyaan |
|---|---|
| A1.1 | Workbook, sheet, dan sel mana yang menjadi sumber utama? |
| A1.2 | Input apa saja yang dipakai, termasuk satuan (rupiah, persen, bulan)? |
| A1.3 | Formula/langkah hitung persisnya, urut dari input ke output? |
| A1.4 | Pembulatan apa yang dipakai di setiap langkah, dan di periode mana selisih ditempatkan? |
| A1.5 | Nilai expected lengkap (angka, status, boundary) dan mana yang final? |
| A1.6 | Apakah data benar-benar dummy/anonim dan bebas PII? |
| A1.7 | Hitung ulang independen memakai Decimal/BCMath/Python: hasilnya sama? |
| A1.8 | Uji boundary: tepat di ambang, sedikit di atas, sedikit di bawah—apa statusnya? |
| A1.9 | Uji negatif: input pelanggaran harus ditolak; apakah benar ditolak? |
| A1.10 | Apakah kasus ini berkonflik dengan kasus lain atau dengan policy ACTIVE? |
| A1.11 | Keputusan: terima nilai, koreksi, atau `UNKNOWN`? Siapa yang memutuskan? |
| A1.12 | Bukti apa yang disimpan (`verified_by`, `verified_at`, `evidence`)? |

### A2. Pertanyaan spesifik per kasus

#### TPP

| ID | Pertanyaan |
|---|---|
| G-TPP-01 | 1) Sel plafon 80 jt/rate 18%/tenor 60/net 2.948.080? 2) Angsuran 2.533.333,33 = pokok 1.333.333,33 + bunga 1.200.000? 3) IDIR 85,93% dari 2.533.333,33 ÷ 2.948.080? 4) EIR 28,88% = RATE×12 atau effective annual? 5) Buktikan tidak memakai 1,81×. |
| G-TPP-02 | 1) Fasilitas SLIK debitur/pasangan mana yang masuk? 2) Dedupe memakai kunci apa? 3) Fasilitas baki 0/macet ikut? 4) Angsuran SLIK masuk denominator DSR/IDIR? 5) Nilai DSR yang benar? |
| G-TPP-03 | 1) Hard stop `>80%` atau `>=80%`? 2) Status tepat 80,00%? 3) Status 80,01%? 4) Warning 35% memakai basis yang sama? 5) Tunjukkan formula Excel/sel. |
| G-TPP-04 | 1) Komponen payoff (pokok, bunga berjalan, penalti, biaya)? 2) Penalti 10% dari pokok lama atau sisa? 3) Bunga berjalan dihitung sampai tanggal apa? 4) Dana bersih diterima? 5) Jurnal seimbang? |
| G-TPP-05 | 1) Blokir 2× dihitung dari angsuran baru? 2) Blokir lama dikurangi/ditambah? 3) Biaya apa yang dipotong? 4) Dana diterima = plafon − biaya − blokir − payoff? 5) Jurnal seimbang? |

#### Schedule

| ID | Pertanyaan |
|---|---|
| G-SCH-01 | 1) Tenor 1: pokok dan bunga? 2) Closing balance 0? 3) Pembulatan? 4) Biaya memengaruhi jadwal? 5) Nilai expected? |
| G-SCH-02 | 1) Plafon kecil: sisa pembagian ke periode mana? 2) Adjustment tercatat? 3) Toleransi Rp1? 4) Closing balance 0? 5) Bukti? |
| G-SCH-03 | 1) PMT anuitas memakai rate bulanan? 2) Closing balance tepat 0? 3) Bunga dibulatkan per periode? 4) Nilai PMT? 5) Bukti hitung? |
| G-SCH-04 | 1) Metode IRR/bisection? 2) Flat 18% menghasilkan EIR berapa? 3) Annualized×12 atau effective compounding? 4) Mengapa bukan 1,81×? 5) Bukti arus kas? |
| G-SCH-05 | 1) Grace months berapa? 2) Pokok 0 sampai periode ke berapa? 3) Bunga dari plafon? 4) Bullet akhir = plafon + bunga terakhir? 5) Nilai expected? |
| G-SCH-06 | 1) Kewajiban balloon = plafon + bunga akhir? 2) Sumber pelunasan? 3) Status 1,10×? 4) Buffer wajib? 5) Bukti? |
| G-SCH-07 | 1) Status tepat 1,00×? 2) Apakah PERHATIAN? 3) Mitigasi yang wajib? 4) Sumber wajib? 5) Bukti? |
| G-SCH-08 | 1) Status 0,99×? 2) Hard stop atau mitigasi? 3) Sumber pelunasan wajib? 4) Apakah bisa lolos dengan mitigasi? 5) Bukti? |

#### Capacity & Modal Kerja

| ID | Pertanyaan |
|---|---|
| G-CAP-01 | 1) RPC = surplus × margin berapa? 2) DSCR denominator hanya angsuran baru atau +SLIK? 3) Formula plafon max Flat? 4) Status? 5) Nilai expected? |
| G-CAP-02 | 1) PMT anuitas? 2) DSCR pada plafon max? 3) Formula plafon max Anuitas? 4) Status? 5) Nilai expected? |
| G-CAP-03 | 1) DSCR memakai bunga periodik atau total kewajiban? 2) Balloon ratio dihitung terpisah? 3) Status kombinasi? 4) Sumber pelunasan? 5) Nilai expected? |
| G-MK-01 | 1) Cash turnover = biaya operasional ÷ (hari kerja/siklus)? 2) Hari kerja 360? 3) Komponen biaya eksplisit? 4) Nilai kebutuhan? 5) Pembulatan? |
| G-MK-02 | 1) WCD = pembelian/30 × hari persediaan? 2) Rata-rata = (CT+WCD)/2? 3) Gap = max(0, rata-rata − MK)? 4) Klasifikasi aset/ekuitas? 5) Nilai expected? |

#### Collateral & Scoring

| ID | Pertanyaan |
|---|---|
| G-COL-01 | 1) Empat nilai: pasar, likuidasi, PPKA, basis LTV? 2) LTV dihitung dari basis apa? 3) Anti double-haircut bagaimana? 4) Kategori POJK? 5) Nilai expected? |
| G-COL-02 | 1) Kategori agunan dan persen POJK? 2) LTV = exposure ÷ basis? 3) Status tepat di batas? 4) Apakah ada penuaan agunan macet? 5) Nilai expected? |
| G-SCR-01 | 1) Bobot 25/30/15/20/10? 2) Band DSCR/equity/LTV/SLIK? 3) Skor Character dari SLIK? 4) Rating boundary? 5) Skor total expected? |
| G-SCR-02 | 1) Kapan Character dipaksa 1? 2) Alasan wajib? 3) Otorisasi siapa? 4) Audit event apa? 5) Nilai expected? |

#### Stress & Decision

| ID | Pertanyaan |
|---|---|
| G-STR-01 | 1) Delta omzet ringan? 2) Baseline surplus? 3) RPC/DSCR baru? 4) Status? 5) Mitigasi? |
| G-STR-02 | 1) Kombinasi delta terburuk? 2) DSCR baru? 3) Status? 4) Mitigasi/hard stop? 5) Bukti? |
| G-DEC-01 | 1) Konflik IDIR hard stop vs soft score: mana menang? 2) Rekomendasi plafon = MIN(pengajuan, max, LTV)? 3) Alasan/audit? 4) Siapa memutuskan? 5) Nilai expected? |

#### Negatif

| ID | Pertanyaan |
|---|---|
| G-RAC-01 | 1) Exposure dan limit BMPK? 2) Related party/borrower group? 3) Bypass ditolak? 4) Hard stop? 5) Nilai expected? |
| G-RAC-02 | 1) Limit sektor? 2) Over-limit berapa? 3) Override Direksi + justifikasi? 4) Audit? 5) Nilai expected? |
| G-CKP-01 | 1) Arah PPKA↔CKPN? 2) CKPN < PPKA ditolak? 3) Jurnal/transition? 4) Siapa otorisasi? 5) Nilai expected? |
| G-WO-01 | 1) Partial write-off ditolak? 2) Full write-off syarat eligibility? 3) Audit? 4) Nilai expected? 5) Bukti? |

## 30.3 Bagian B — Verifikasi fungsi aplikasi

### B1. Snapshot parameter & grandfathering

| ID | Pertanyaan | Bukti yang diminta |
|---|---|---|
| B1.1 | Apakah `parameter_snapshot` diisi saat permohonan dibuat? | baris DB + test |
| B1.2 | Apakah perubahan policy ACTIVE tidak mengubah permohonan lama? | `GrandfatheringTest` |
| B1.3 | Apakah permohonan baru memakai policy baru? | test |
| B1.4 | Apakah `forPermohonan()` pernah fallback ke `active()`? | code review |
| B1.5 | Apakah snapshot tidak bisa diubah setelah create? | test RuntimeException |
| B1.6 | Apakah permohonan lama tanpa snapshot memakai FK historis? | test/DB |
| B1.7 | Apakah rule memakai snapshot, bukan ACTIVE? | code review 15 rule |
| B1.8 | Apakah UI/analisis memakai snapshot? | `AnalisisController`/`KapasitasService` |
| B1.9 | Apa yang terjadi bila snapshot corrupt? | `MissingPolicyParameter` |
| B1.10 | Di mana bukti audit perubahan policy? | `DECISIONS.md`/`setting_revisions` |

### B2. Stage-aware rule engine

| ID | Pertanyaan | Bukti |
|---|---|---|
| B2.1 | Aturan apa saja yang dievaluasi saat submit? | `BusinessRuleEngine::STAGE_SUBMIT` |
| B2.2 | Apakah submit tanpa analisis tetap lolos? | test |
| B2.3 | Aturan apa saja yang dievaluasi saat analisis? | `STAGE_ANALISIS` |
| B2.4 | Apakah hasil analisis dicatat ke audit? | `AnalisisRuleAuditService` |
| B2.5 | Aturan HARD apa yang menahan approve? | `STAGE_APPROVE` |
| B2.6 | Apakah BR-06 dikecualikan dari engine approve? | code review |
| B2.7 | Apakah authorization dicek sebelum hard gate? | `ApprovalService::approve` |
| B2.8 | Apakah IDIR 85% gagal approve? | test |
| B2.9 | Apakah soft warning tidak memblokir? | test |
| B2.10 | Apakah pesan exception membawa kode aturan? | `BusinessRuleViolation` |

### B3. IDIR dua tingkat

| ID | Pertanyaan | Bukti |
|---|---|---|
| B3.1 | Apakah 35,00% LAYAK dan 35,01% PERHATIAN? | test |
| B3.2 | Apakah 80,00% lolos dan 80,01% ditolak? | test |
| B3.3 | Apakah `status_idir` disimpan? | DB |
| B3.4 | Apakah simulasi memakai dua ambang? | `SimulationEngine` |
| B3.5 | Apakah `idir_hard_stop_tpp` ada di policy/validator/registry? | code |
| B3.6 | Apakah `BR-11H` hanya untuk TPP? | code |
| B3.7 | Apakah IDIR null saat approve ditolak? | test/code |
| B3.8 | Apakah G-TPP-03 konsisten dengan Excel? | fixture |

### B4. Penomoran atomik & backfill

| ID | Pertanyaan | Bukti |
|---|---|---|
| B4.1 | Apakah penomoran memakai SQL upsert? | `NomorService` |
| B4.2 | Apakah scope lowercase dan konsisten dengan backfill? | code/DB |
| B4.3 | Apakah tiga format benar? | `AtomicNomorServiceTest` |
| B4.4 | Apakah backfill mengambil suffix tertinggi? | seeder |
| B4.5 | Apakah backfill tidak menurunkan sequence? | `GREATEST` |
| B4.6 | Apakah backfill idempoten? | test |
| B4.7 | Apakah dua proses bersamaan tidak duplikat? | test konkurensi |
| B4.8 | Apakah nomor lama tetap unik? | DB unique |

### B5. SLIK & DSCR

| ID | Pertanyaan | Bukti |
|---|---|---|
| B5.1 | Apakah hanya `baki_debet > 0` yang masuk? | service |
| B5.2 | Apakah dedupe `no_fasilitas` benar? | test |
| B5.3 | Bagaimana fallback bila `no_fasilitas` null? | service |
| B5.4 | Apakah `angsuran_bulanan` null diestimasi? | test |
| B5.5 | Apakah `is_angsuran_estimated` disimpan? | DB |
| B5.6 | Apakah total SLIK disimpan per debitur/pasangan? | DB |
| B5.7 | Apakah DSCR = RPC ÷ (angsuran baru + SLIK)? | test |
| B5.8 | Apakah IDIR tetap hanya angsuran baru? | code |
| B5.9 | Apakah BR-02 membaca DSCR baru? | rule |
| B5.10 | Apakah dampak ke approval diuji? | UAT |

### B6. Workbench UI

| ID | Pertanyaan | Bukti |
|---|---|---|
| B6.1 | Apakah route `/permohonan/{id}/analisis` ada? | route:list |
| B6.2 | Apakah hanya user berizin/cabang sama? | test 403 |
| B6.3 | Apakah tab Ringkasan/Neraca/Laba Rugi/Rasio/Modal Kerja/Kapasitas/Sensitivitas/Scoring ada? | UI |
| B6.4 | Apakah banner neraca tidak seimbang muncul? | UI test |
| B6.5 | Apakah neraca/laba rugi tersimpan? | DB test |
| B6.6 | Apakah rasio dihitung dari data tersimpan? | DB test |
| B6.7 | Apakah modal kerja menghitung CT/WCD/gap? | DB test |
| B6.8 | Apakah sensitivitas menghasilkan 6 skenario? | DB test |
| B6.9 | Apakah scoring menyimpan skor/rating/override? | DB test |
| B6.10 | Apakah CSS native tanpa Tailwind? | code review |

### B7. Ledger, audit, dan immutability

| ID | Pertanyaan | Bukti |
|---|---|---|
| B7.1 | Apakah setiap analisis menulis ledger? | DB |
| B7.2 | Apakah buku besar mencatat tahap ANALISIS? | DB |
| B7.3 | Apakah hasil rule analisis masuk audit_log? | DB |
| B7.4 | Apakah trigger menolak UPDATE/DELETE? | test |
| B7.5 | Apakah `ledger:verify` OK? | command |
| B7.6 | Apakah aktor/cabang tercatat? | DB |
| B7.7 | Apakah payload tidak memuat PII berlebih? | code review |
| B7.8 | Apakah audit tidak bisa dihapus? | trigger |

### B8. Policy & settings

| ID | Pertanyaan | Bukti |
|---|---|---|
| B8.1 | Apakah key baru terdaftar di enum/validator/registry/seeder? | code |
| B8.2 | Apakah parameter lama dimigrasikan? | migration |
| B8.3 | Apakah alur draft→approve→apply tetap? | test |
| B8.4 | Apakah versi ACTIVE baru tidak menimpa lama? | DB |
| B8.5 | Apakah label UI muncul? | UI |
| B8.6 | Apakah perubahan policy tercatat di audit? | DB |

### B9. QA & rilis

| ID | Pertanyaan | Bukti |
|---|---|---|
| B9.1 | Apakah `verify.sh --full` OK? | log |
| B9.2 | Berapa test/assertions? | log |
| B9.3 | Apakah hardcode audit 0? | command |
| B9.4 | Apakah docs:check 0? | command |
| B9.5 | Apakah ledger 20 baris utuh? | command |
| B9.6 | Apakah `/health` 200? | curl |
| B9.7 | Apakah backup/restore drill lulus? | bukti |
| B9.8 | Apakah UAT 3 cabang lulus? | berita acara |
| B9.9 | Apakah rollback diuji? | bukti |
| B9.10 | Apakah commit/tag rilis ada? | git |

### B10. Keamanan & PII

| ID | Pertanyaan | Bukti |
|---|---|---|
| B10.1 | Apakah file token GitHub sudah dihapus/dirotasi? | bukti rotasi |
| B10.2 | Apakah data uji bebas PII? | manifest |
| B10.3 | Apakah file agunan disimpan privat? | storage |
| B10.4 | Apakah akses lintas cabang ditolak? | test |
| B10.5 | Apakah audit akses PII tercatat? | DB |
| B10.6 | Apakah tidak ada SQLite/ledger lokal? | code review |

### B11. Performance & reliability

| ID | Pertanyaan | Insight | Bukti |
|---|---|---|---|
| B11.1 | Berapa query per halaman workbench? | Deteksi N+1 | query log |
| B11.2 | Apakah index dipakai untuk filter cabang/status? | Query cepat | EXPLAIN |
| B11.3 | Apakah jadwal 360 periode tetap responsif? | UI tidak timeout | profiling |
| B11.4 | Apakah PDF/dokumen berat memakai memori wajar? | Tidak 500 | memory log |
| B11.5 | Apakah import memakai chunk? | Tidak OOM | kode |
| B11.6 | Apakah cache punya invalidasi? | Data segar | test |
| B11.7 | Apakah lock contention diukur? | Throughput | test beban |
| B11.8 | Apakah slow query dipantau? | Proaktif | log |
| B11.9 | Apakah retry aman (idempoten)? | Tidak duplikat | test |
| B11.10 | Apakah health check tersedia? | Monitoring | curl |

### B12. Integritas data & migrasi

| ID | Pertanyaan | Insight | Bukti |
|---|---|---|---|
| B12.1 | Apakah migrasi reversible? | Rollback aman | `down()` |
| B12.2 | Apakah FK/index baru lengkap? | Integritas | skema |
| B12.3 | Apakah unique constraint tidak bentrok soft delete? | Data valid | index |
| B12.4 | Apakah backfill idempoten? | Aman diulang | test |
| B12.5 | Apakah snapshot/ledger tidak bisa diubah? | Audit | trigger |
| B12.6 | Apakah timezone konsisten? | Hash/tanggal | test |
| B12.7 | Apakah data lama tetap terbaca? | Kompatibilitas | query |
| B12.8 | Apakah seed referensi punya provenance? | Audit | seeder |
| B12.9 | Apakah skema cocok dengan INVENTARIS? | Dokumen akurat | diff |
| B12.10 | Apakah rollback diuji di DB salinan? | Aman | log |

### B13. Deployment & rollback

| ID | Pertanyaan | Insight | Bukti |
|---|---|---|---|
| B13.1 | Apakah backup dijalankan sebelum migrasi? | Recovery | log |
| B13.2 | Apakah restore drill lulus? | Backup valid | log |
| B13.3 | Apakah rollback plan ada? | Aman | runbook |
| B13.4 | Apakah urutan migrasi aman? | Tidak downtime | log |
| B13.5 | Apakah seeder backfill dijalankan sebelum aktif? | Tidak duplikat | log |
| B13.6 | Apakah env produksi benar? | Tidak debug | config |
| B13.7 | Apakah versi/tag rilis jelas? | Traceable | git |
| B13.8 | Apakah deployment terdokumentasi? | Konsisten | runbook |
| B13.9 | Apakah health check pasca-deploy? | Cepat deteksi | curl |
| B13.10 | Apakah UAT/sign-off ada? | Kesiapan | berita acara |

### B14. Observability & operasional

| ID | Pertanyaan | Insight | Bukti |
|---|---|---|---|
| B14.1 | Apakah error penting masuk log? | Debug | log |
| B14.2 | Apakah log tidak memuat PII? | Privasi | sample |
| B14.3 | Apakah alert untuk kegagalan ledger? | Deteksi | config |
| B14.4 | Apakah scheduler `ledger:verify` berjalan? | Integritas | log |
| B14.5 | Apakah job gagal bisa di-retry? | Resilien | kode |
| B14.6 | Apakah disk/memori dipantau? | Stabil | monitoring |
| B14.7 | Apakah audit akses PII tercatat? | Kepatuhan | DB |
| B14.8 | Apakah notifikasi kegagalan sampai ke admin? | Respons | test |
| B14.9 | Apakah runbook insiden ada? | Reaksi cepat | dokumen |
| B14.10 | Apakah waktu operasi tercatat? | SLA | log |

### B15. UX, aksesibilitas & pelatihan

| ID | Pertanyaan | Insight | Bukti |
|---|---|---|---|
| B15.1 | Apakah label form selalu terlihat? | Tidak salah isi | UI |
| B15.2 | Apakah error validasi jelas? | Bisa diperbaiki | UI |
| B15.3 | Apakah status memakai teks+ikon, bukan warna saja? | Aksesibel | UI |
| B15.4 | Apakah angka diformat Indonesia? | Tidak salah baca | UI |
| B15.5 | Apakah tombol utama jelas? | Alur cepat | UI |
| B15.6 | Apakah konfirmasi untuk aksi irreversible? | Cegah salah | UI |
| B15.7 | Apakah loading state mencegah double submit? | Data tidak ganda | UI |
| B15.8 | Apakah empty state memberi arahan? | User mandiri | UI |
| B15.9 | Apakah manual/FAQ memperbarui fitur baru? | Adopsi | docs |
| B15.10 | Apakah pelatihan/UAT mencakup alur baru? | Kesiapan | berita acara |

## 30.4 Pertanyaan lintas-kasus (A3)

| ID | Pertanyaan | Insight |
|---|---|---|
| A3.1 | Apakah semua kasus memakai satuan yang sama (rupiah/persen/bulan)? | Mencegah salah tafsir |
| A3.2 | Apakah ada kasus yang saling bertentangan? | Deteksi kontradiksi |
| A3.3 | Apakah boundary IDIR konsisten di semua kasus? | Bug ambang |
| A3.4 | Apakah EIR di semua kasus memakai metode yang sama? | Konsistensi |
| A3.5 | Apakah kasus grace memakai definisi kewajiban yang sama? | Salah basis |
| A3.6 | Apakah DSCR selalu memasukkan SLIK? | DSCR optimistis |
| A3.7 | Apakah biaya kredit konsisten antar produk? | Dana diterima |
| A3.8 | Apakah penalti/payoff konsisten? | Top-up |
| A3.9 | Apakah LTV memakai basis yang sama? | Double haircut |
| A3.10 | Apakah bobot 5C sama di semua kasus? | Skor drift |
| A3.11 | Apakah override SLIK punya aturan sama? | Inconsistency |
| A3.12 | Apakah stress test memakai delta sama? | Skenario |
| A3.13 | Apakah kasus negatif benar-benar ditolak? | False pass |
| A3.14 | Apakah nilai “0” dan “null” dibedakan? | Bug data |
| A3.15 | Apakah pembulatan memengaruhi status? | Boundary |
| A3.16 | Apakah kasus dengan plafon kecil diuji? | Rounding |
| A3.17 | Apakah tenor 1/360 diuji? | Edge |
| A3.18 | Apakah rate 0 diuji? | Division |
| A3.19 | Apakah hasil bisa direproduksi di mesin lain? | Determinisme |
| A3.20 | Apakah semua kasus punya pemilik & tanggal? | Accountability |

## 30.5 Status & keputusan

| Status | Arti |
|---|---|
| LULUS | Jawaban + bukti cocok, bisa direproduksi |
| GAGAL | Jawaban/bukti tidak cocok |
| UNKNOWN | Belum ada sumber/keputusan; kasus tidak boleh verified |

Aturan:
1. Golden `verified=true` hanya jika A1.1–A1.12 dan 5 pertanyaan spesifik LULUS.
2. Fungsi aplikasi dianggap siap rilis hanya jika B1–B10 tidak ada GAGAL pada item kritis.
3. `UNKNOWN` pada golden atau backup/UAT berarti status rilis tetap **PARTIAL**.

## 30.6 Bukti yang harus dilampirkan

- Salinan formula/sel (tanpa PII) atau screenshot yang dianonimkan.
- Skrip hitung ulang independen + output.
- Hasil test/command dengan tanggal.
- Formulir tanda tangan ahli/reviewer.
- Daftar konflik/koreksi yang disetujui.

## 30.7 Larangan

- Menjawab “sesuai standar” tanpa pasal/sumber.
- Mengisi `verified=true` tanpa nama/tanggal/evidence.
- Memakai 1,81×/0,87×.
- Mengubah expected agar test hijau.
- Menyalin PII ke wiki/formulir.

## 30.8 Versi web kuisioner

Formulir ini tersedia sebagai web app mandiri di repo aplikasi:

- File: `public/kuisioner-golden.html`
- URL lokal: `https://loan-analytic-bpr.test/kuisioner-golden.html`
- 9 bagian, 72 pertanyaan berkonteks.
- Opsi A–C sudah dibakukan; opsi D wajib diisi bebas.
- Progress per bagian, simpan lokal (fallback memori), ekspor JSON, cetak.
- Tanpa Node/npm/Vite; tidak mengirim data ke server.
