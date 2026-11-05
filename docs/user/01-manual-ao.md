# Manual AO — Input, Permohonan, SLIK, Simulasi

**Peran:** `AO` (Analis/AO). **Login:** `/login`. Jika diminta, ganti sandi di `/password`.

## 1. Masuk & navigasi `[E]`
1. Buka `/login`, masukkan username & kata sandi.
2. Menu kiri: **Dasbor**, **Debitur**, **Permohonan**, **Simulasi**, **Persetujuan** (jika berwenang), **Kredit**.
3. Lonceng **Notifikasi** di kanan atas menampilkan keputusan atas pengajuan Anda (poll 30 detik).

## 2. Input debitur (wizard 3 langkah) `[E]`
1. **Debitur → + Debitur Baru** (`/debitur/create`).
2. **Langkah 1 — Identitas:** nama KTP, nama ibu kandung, No. KTP (16 digit), No. KK/NPWP (opsional), jenis kelamin, status perkawinan, agama/pendidikan, tempat & tanggal lahir.
3. **Langkah 2 — Kontak & alamat:** No. HP (wajib), WA/email, alamat lengkap (jalan, RT/RW, kelurahan, kecamatan, kabupaten, provinsi), jumlah keluarga/tanggungan.
4. **Langkah 3 — Pekerjaan:** pekerjaan utama, jabatan, perusahaan, lama bekerja, penghasilan bulanan.
5. Klik **Simpan**. Debitur muncul di daftar; klik **Detail** untuk melihat riwayat permohonan.

> No. KTP unik (partial index, soft delete). Data PII ditampilkan sesuai kewenangan; jangan membagikan tangkapan layar berisi PII.

## 3. Membuat permohonan (wizard 5 langkah) `[E]`
1. **Permohonan → + Permohonan Baru** (`/permohonan/create`; dapat dari detail debitur).
2. **Langkah 1 — Debitur & produk:** pilih debitur, produk (mis. TPP/MK/Grace), prioritas.
3. **Langkah 2 — Plafon & asumsi:** plafon diajukan, suku bunga (%/tahun), tenor (bulan), sistem bunga (Flat/Anuitas/Grace), tanggal permohonan/target, kegunaan.
4. **Langkah 3 — Konteks kredit:** sektor, tujuan penggunaan, pinjaman ke-, sumber pembayaran angsuran, sumber & perkiraan penerimaan balloon (wajib untuk grace), catatan AO.
5. **Langkah 4 — SLIK (review-first):** tempel data atau isi baris. Format tempel:
   `NAMA LEMBAGA;JENIS FASILITAS;PLAFON;BAKI DEBET;KOLEKTIBILITAS;ANGSURAN`
   Contoh: `BANK ABC;KPR;150000000;120000000;1;1500000`
   Periksa flag: baki ≤ plafon, duplikat, kolektibilitas 1–5.
6. **Langkah 5 — Ringkasan:** periksa kelengkapan; **Simpan draft** atau **Ajukan**.
   - Submit menjalankan BR-04 (balloon wajib) dan BR-08 (parameter tersimpan).
   - Setelah submit, status `SUBMITTED` dan masuk inbox persetujuan.

## 4. Simulasi kredit `[E]`
1. **Simulasi → + Simulasi** (`/simulasi/create`) atau dari detail permohonan.
2. Isi label, produk, plafon, rate (%/tahun), tenor, surplus bulanan, penghasilan bersih, nilai jaminan (opsional).
3. Klik **Hitung**: tampil angsuran, total bunga, estimasi EIR/IRR numerik, dan jadwal.
4. Pilih sistem **Flat/Anuitas/Grace** dan grace (bulan) bila perlu.
5. **Simpan** untuk menyimpan simulasi + ledger `SIMULASI_DIJALANKAN`; buka **Bandingkan** untuk diff dua simulasi.

> EIR/IRR dihitung numerik (bukan faktor 1,81x). Angka simulasi = ilustrasi; keputusan mengikuti kebijakan `[K]`.

## 5. Yang tidak boleh dilakukan AO `[E]`
- **Self-approve:** pembuat permohonan tidak boleh menyetujui pengajuannya sendiri (SoD) — akan ditolak `403`.
- Menyetujui level yang bukan kewenangannya (role + permission level).
- Mengubah permohonan setelah `SUBMITTED` (hanya `DRAFT` yang dapat diedit).

## 6. Import `[Rencana]`
Adapter import SLIK/ekstraksi (Fase 12B) **belum tersedia** di UI. Saat ini gunakan input manual/tempel SLIK. Korpus contoh harus dummy/anonim.
