# Bab 22 — Mekanisme Update Berkala Manual Book Saat Fitur Final

Bab ini **wajib** dipatuhi agar manual book tidak basi (anti-drift). Tujuannya: setiap fitur/fungsi/perubahan yang **bersifat final** memicu pembaruan manual secara terukur dan terlacak.

## 22.1 Definisi "final"
Sebuah fitur/fungsi/​perubahan dianggap **final** bila memenuhi **salah satu**:
- sudah **merged** ke branch rilis **dan** ditandai `DONE` di `docs/PROGRESS.md` (memenuhi gate `VERIFIKASI.md`); atau
- telah **di-tag** (`phase-XX-done`); atau
- untuk kebijakan/angka: telah **disetujui `[H]`** (tanda tangan pemutus) di `decisions.md`/ADR.

Selama masih eksperimen/DRAFT ⇒ **tidak** memicu update, tetap dilabeli `[K]/[Rencana]`.

## 22.2 Pemicu (triggers) update manual
| Pemicu | Contoh |
|---|---|
| Fitur/fase selesai | fase 12 Blade UI → update Bab 14, 21, feature-matrix |
| Endpoint/route baru/berubah | API baru → Bab 13 + peta |
| Migrasi/tabel/enum baru | Bab 19 + jumlah di MANIFEST |
| Perubahan aturan/parameter | Bab 9/16 + sumber `[H]` |
| Perubahan rumus/precision | Bab 8 + golden + C-002 status |
| Perubahan role/permission | Bab 4/16 |
| Keputusan `[H]` atas `[K]` | ubah label `[K]`→`[E/H]` pada bab terkait + CHANGELOG |
| Perubahan keamanan/PII | Bab 18 |
| Fix bug berdampak perilaku | bab modul terkait |

## 22.3 Prosedur wajib (langkah)
1. **Identifikasi** bab terdampak (lihat indeks Bab 23 / `templates/feature-matrix.md`).
2. **Ambil bukti live** (bukan ingatan):
   ```bash
   php artisan route:list        # jumlah/isi route
   php artisan migrate:status | tail -1
   php artisan state:check
   php artisan ledger:verify
   ls app/Models | wc -l ; ls app/Enums | wc -l
   bash scripts/verify.sh --phase
   ```
   atau jalankan `bash scripts/manual-snapshot.sh` (lihat 22.7).
3. **Edit bab** yang terdampak; perbarui **status label** (`[E]/[A]/[K]/[H]/[Rencana]`).
4. Update `CHANGELOG.md` (entri tanggal/versi/ruang lingkup/bukti).
5. Isi/ubah baris di `templates/feature-matrix.md` (`Status` → `Final [H/E]`, tanggal, tag/commit, bukti).
6. Perbarui `MANIFEST.md` (versi manual, commit/tag app, snapshot jumlah, tanggal verifikasi).
7. Jalankan `templates/checklist-pr.md` sebelum merge PR.
8. (Jika ada) simpan evidence note (`templates/evidence-note.md`) dan tautkan.

## 22.4 Frekuensi
- **Per fitur final**: segera (bagian dari Definition of Done).
- **Per fase selesai**: sinkronisasi bab terpengaruh.
- **Rutin (mis. bulanan)**: jalankan snapshot (22.7), cek drift angka/label, rekonsiliasi dengan `docs/PROGRESS.md`.
- **Sebelum rilis/audit/UAT**: review penuh (docs:check --strict analog).

## 22.5 Penjaga otomatis (automation)
- Command `state:check`/`docs:check` (di repo app) memverifikasi jumlah & dokumen wajib — jalankan di CI/verify.
- `scripts/manual-snapshot.sh` (di repo planning) menghasilkan snapshot angka untuk dibandingkan dengan `MANIFEST.md`.
- (Usulan `[A]`) tambahkan **CI job** yang membandingkan snapshot dengan `MANIFEST.md` dan gagal bila drift tak tercatat.

## 22.6 Aturan mutu & batasan
- **Tidak menghapus** riwayat: gunakan `CHANGELOG.md` (append).
- **Jangan** memfinalkan angka/pasal tanpa `[H]`; `[K]` tetap `[K]`.
- **Jangan** memasukkan PII nyata ke manual/contoh.
- Perubahan besar → catat juga di `docs/decisions.md`/ADR.
- Tindakan destruktif (restore/reset) hanya di lingkungan terkendali & dengan konfirmasi.

## 22.7 Alat: `scripts/manual-snapshot.sh`
Skrip yang mencetak snapshot (commit, tag, jumlah migrasi/model/enum/route, hasil `state:check` bila bisa) untuk mempercepat pembaruan & deteksi drift. Output disalin ke `MANIFEST.md`. Lihat `project/scripts/manual-snapshot.sh`.

## 22.7b Publikasi wiki (sinkron manual → GitHub Wiki)
Manual ini dipublikasikan sebagai **GitHub Wiki** repo `awkward-credit`.
- Generator: `project/scripts/build-wiki.py` — mengubah seluruh bab + lampiran menjadi halaman wiki (slug `Bab-NN-...`), menulis ke `wiki/` (generated, di-gitignore).
- Publikasi: `GH_TOKEN=<token> bash scripts/publish-wiki.sh` — membangun `wiki/` lalu push ke `awkward-credit.wiki.git` (`Home`, `_Sidebar`, `_Footer`, Bab 1–23, + lampiran).
- **Prasyarat sekali**: wiki harus punya minimal 1 halaman (buat "Home" via web UI sekali), lalu skrip aman dijalankan berulang.
- **Bagian dari DoD**: setelah manual diperbarui (langkah 22.3), jalankan ulang `publish-wiki.sh` agar wiki tidak basi.

## 22.8 Peran & tanggung jawab (`[A]`)
| Peran | Tanggung jawab |
|---|---|
| Developer pembuat perubahan | Update bab + CHANGELOG + feature-matrix dalam PR yang sama |
| Reviewer | Verifikasi bukti & konsistensi label |
| Pemilik produk/Risk | Menyediakan/validasi `[H]` bila menyentuh kebijakan/angka |
| (Opsional) Maintainer manual | Review rutin & rekonsiliasi drift |

## 22.9 Definition of Done (untuk fitur final)
- [ ] Perilaku terverifikasi & tes relevan hijau.
- [ ] Bab manual terdampak diperbarui + `CHANGELOG.md` + `feature-matrix.md`.
- [ ] `MANIFEST.md` diperbarui (bila ada perubahan jumlah/commit).
- [ ] Label bukti benar (`[E]`/`[H]` hanya bila ada dasar).
- [ ] Evidence ditautkan.

> **Catatan penting**: manual ini disusun dari **working tree** repo app `loan-analytic-bpr` yang saat itu **memiliki perubahan belum di-commit** (fase 12 Blade UI & Livewire) — jadi sebagian isi (Bab 5/6/8/14) mencerminkan kode live, bukan hanya commit `7e11e8e`. Jalankan `manual-snapshot.sh` & `git status` saat memperbarui.
