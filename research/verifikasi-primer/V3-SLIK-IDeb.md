# V3 — Verifikasi Primer: SLIK / IDeb (Sistem Layanan Informasi Keuangan)

Tanggal: 2026-11-05 (WITA) | Status: **DRAFT `[K]` — naskah pasal resmi belum dikunci**.  
Pemilik: Kepatuhan + Kepala Kredit + IT + DPO. **Bukan nasihat hukum.**

## 1. Tujuan
Mengunci ketentuan **persetujuan debitur (consent)**, tujuan penggunaan, hak koreksi/sengketa, kewajiban pengguna/pelapor, kualitas/keamanan data SLIK untuk **D-03, T2–T4, D-09/D-10** dan engine DSR/Character.

## 2. Status verifikasi sumber
| Item | Status | Catatan |
|---|---|---|
| POJK SLIK (nomor/tahun) terbaru berlaku, termasuk perubahan | `[K]` | SLIK menggantikan SID (istilah umum `[A]`); **nomor/pasal final wajib** dari OJK. Jangan pakai nomor dari memori. |
| Ketentuan persetujuan debitur (bentuk, cakupan, masa, tujuan) | `[K]` | X1 memuat “Surat Persetujuan SLIK” `[E]` — konten harus diselaraskan dengan ketentuan berlaku `[H]`. |
| Mekanisme koreksi data/sengketa | `[K]` | Aplikasi tidak boleh “mengedit SLIK resmi”; catat dispute/proses koreksi. |

## 3. Checklist ekstraksi pasal `[K]`
| Poin | Yang harus dicari | Dampak app |
|---|---|---|
| Persetujuan debitur | kapan wajib, bentuk/cakupan/tujuan/masa, bukti, pencabutan `[K]` | `ConsentService`, gate sebelum request/import/akses/tampil; audit |
| Pengguna & tujuan | pihak berwenang menggunakan SLIK dan tujuan sah | RBAC, purpose limitation, audit alasan akses |
| Data & kualitas | cakupan fasilitas (pokok, baki, tunggakan, kolektibilitas, tanggal) `[K]` | parser/normalizer, validasi, confidence, provenance |
| Hak debitur/koreksi | prosedur koreksi/sengketa | workflow dispute flag (read-only terhadap data sumber resmi) |
| Keamanan/kerahasiaan | perlindungan, pembatasan distribusi | enkripsi, mask, akses log, retensi D-09, no cloud/LLM tanpa izin D-10 |
| Sanksi/kepatuhan | konsekuensi pelanggaran | kontrol wajib, alert |

## 4. Implikasi desain (best practice, selaras rencana) `[A]`
- **Consent gate keras**: tanpa consent aktif (sesuai cakupan/tujuan/masa yang disahkan `[H]`) → blokir fetch/import/pemakaian skor/DSR dari SLIK. Simpan bukti (tipe, waktu, kanal, tujuan, scope, user, file/hash).
- **Pipeline review-first** (fix T2/T3/T4): `ingest(file/pdf/text/paste) → parse → normalize → validate → confidence+provenance → human review → commit snapshot`. Validasi: identitas match, duplikat fasilitas, `baki <= plafon` (flag) `[K]`, kolek range, tanggal data/pull, kewajaran angsuran/tenor.
- **DSR total otomatis** dari fasilitas SLIK terstruktur (bukan ketik manual) + internal obligations. **Character/SLIK flags** dari data terstruktur; override wajib alasan+otorisasi+audit.
- **Snapshot & refresh**: simpan `slik_pull_id/date/hash`; fasilitas baru memicu recalc DSR/EWS `[A L1/L5]`. Sengketa/koreksi = status/proses terpisah, bukan mutasi data sumber.
- **PDP**: SLIK = data sensitif; minimasi, enkripsi, masker, audit akses/ekspor, retensi `[K D-09]`.

## 5. Cross-check ke workbook `[E]`
| Sumber | Bukti | Fix |
|---|---|---|
| X1 analisis kredit | tabel SLIK ringkas; consent terpisah | SLIK harus mengisi DSR/Character otomatis (T2,T4) |
| X2/X3 Sheet 2 | 8 baris SLIK pemohon+pasangan; agregat baki/kol bermasalah; flag kelayakan | normalisasi, validasi (T3), definisi flag fatal `[K]` |

## 6. Output penutup V3
1. Rujukan POJK SLIK berlaku + pasal consent/penggunaan/koreksi/keamanan `[H]`.
2. Template/form consent disahkan `[H]` (selaras X1 tapi patuh ketentuan).
3. D-03 `[H]`; korpus sampel **dummy/anonim** 10–20 `[K]`; parser golden tests.

## 7. Pertanyaan terbuka
- `UNKNOWN (V3a)`: Nomor/tahun POJK SLIK terbaru & perubahan?
- `UNKNOWN (V3b)`: Bentuk persetujuan debitur yang sah (fisik/elektronik), cakupan, masa berlaku?
- `UNKNOWN (V3c)`: Format data SLIK yang paling umum diterima operasional (PDF/Excel/CSV/API) dan kanal resmi akses?
- `UNKNOWN (V3d)`: Aturan koreksi/sengketa data yang harus dicerminkan di workflow?
