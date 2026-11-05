# Materi Pelatihan — 3 Cabang

**Tujuan:** setiap peserta dapat menjalankan perannya di aplikasi tanpa pendampingan, memahami aturan kunci, dan tahu ke mana harus eskalasi.

## 1. Peserta & jalur
| Peran | Modul wajib | Durasi |
|---|---|---|
| AO/Analis | 1, 2, 3, 4 | 1 hari |
| Admin/IT | 1, 5, 6 | 1 hari |
| Komite/Pemutus | 1, 3, 6 | ½ hari |
| Auditor/Kepatuhan | 1, 6, 7 | ½ hari |

## 2. Modul
1. **Pengenalan & login:** peran, navigasi, notifikasi, ganti sandi.
2. **Debitur & permohonan:** wizard debitur (3 langkah), wizard permohonan (5 langkah), checklist.
3. **Analisis & simulasi:** neraca/LR, rasio, modal kerja, kapasitas (IDIR/DSR/DSCR/LTV), sensitivitas, 5C, red flag, rekomendasi; simulator Flat/Anuitas/Grace.
4. **SLIK review-first:** format tempel, validasi (baki ≤ plafon, duplikat, kolektibilitas), consent.
5. **Approval & SoD:** matriks level, kewenangan ganda, larangan self-approve, aksi tolak/kembalikan, timeline.
6. **Kredit, pembayaran, ledger:** pencairan, jadwal, catat pembayaran, reversal, `ledger:verify`.
7. **Dokumen & audit:** template APPROVED (BR-07), generate 17 dokumen, tanda tangan, audit read-only.

## 3. Latihan (hands-on, data dummy)
| # | Latihan | Kriteria lulus |
|---|---|---|
| L1 | Buat 1 debitur + 1 permohonan 5 jt | status `DRAFT`/`SUBMITTED` benar |
| L2 | Tempel 2 baris SLIK + perbaiki flag | 2 baris tersimpan, flag benar |
| L3 | Simulasi 80 jt/18%/60 Flat & Anuitas | jadwal tampil, total/bunga wajar |
| L4 | Approval berjenjang (admin) | `RECOMMENDED` → `APPROVED`, timeline 2 level |
| L5 | Tolak/kembalikan dengan alasan | status & notifikasi benar |
| L6 | Coba self-approve | ditolak `403` (SoD) |
| L7 | Generate dokumen DOC-01 | file ada; template DRAFT → `422` |
| L8 | Catat pembayaran + reversal | jadwal berubah; ledger event tercatat |

## 4. Penilaian
- **Teori:** 10 pertanyaan pilihan ganda (aturan & alur).
- **Praktik:** L1–L8; minimal 7/8 lulus.
- **Tanda tangan:** trainer & koordinator cabang.

## 5. Jadwal contoh (3 cabang)
| Cabang | Hari | Sesi |
|---|---|---|
| KDI | H-3 | 08.30–12.00 AO; 13.00–15.30 Admin/Komite |
| Cabang 2 | H-2 | idem |
| Cabang 3 | H-1 | idem + simulasi UAT |

## 6. Materi & bukti
- Manual pengguna: `docs/user/01..04`.
- Data latihan: **dummy** (tanpa PII nyata).
- Absensi, hasil penilaian, dan catatan kendala disimpan sebagai bukti pelatihan.
