# Panduan Pengaturan (Settings)

**Peran:** ADMIN (editor/penerap), KOMITE/DIRUT (penyetuju), AUDITOR/LEGAL (pembaca). **URL:** `/pengaturan`.

## 1. Prinsip

1. Data **ACTIVE tidak pernah diedit langsung**. Semua perubahan dibuat sebagai **revisi**.
2. Revisi harus **diajukan → disetujui → diterapkan**. Sebelum diterapkan, perhitungan tetap memakai data ACTIVE.
3. **Pemisahan tugas (SoD):** pembuat revisi tidak boleh menjadi penyetuju revisi yang sama.
4. Semua aksi tercatat di `audit_log`; aktivasi parameter/matriks juga menulis ledger `PARAMETER_DIAKTIFKAN`.

## 2. Izin

| Permission | Role | Kemampuan |
|---|---|---|
| `setting.view` | ADMIN, KOMITE, DIRUT, AUDITOR, LEGAL | Melihat data & riwayat |
| `setting.edit` | ADMIN | Membuat/mengubah draft, mengajukan |
| `setting.approve` | KOMITE, DIRUT | Menyetujui/menolak |
| `setting.apply` | ADMIN | Menerapkan revisi yang disetujui |

## 3. Alur

```
DRAFT ──ajukan──► PENDING ──setujui──► APPROVED ──terapkan──► APPLIED
  │                  │
  └──ubah──┘         └──tolak──► REJECTED
```

- **DRAFT** hanya dapat diubah oleh pembuat (atau ADMIN).
- **PENDING** menunggu approver; notifikasi dikirim ke pemegang `setting.approve`.
- **APPROVED** belum mengubah data; ADMIN harus menekan **Terapkan**.
- **APPLIED** final; tidak dapat diterapkan ulang.
- **REJECTED** menyimpan alasan penolakan pada kolom catatan.

## 4. Bagian yang dapat dikonfigurasi

| Bagian | Data | Perilaku apply |
|---|---|---|
| `POLICY_PARAMETER` | 34 kunci parameter | Versi baru ACTIVE; versi lama ARCHIVED |
| `APPROVAL_MATRIX` | Band plafon, level, role | Versi baru ACTIVE; versi lama ARCHIVED |
| `RATE_JIWA` | Tarif asuransi jiwa | Upsert baris |
| `RATE_KEBAKARAN` | Tarif asuransi kebakaran | Upsert baris |
| `RATE_KENDARAAN` | Tarif asuransi kendaraan | Upsert baris |
| `COLLATERAL_CATEGORY` | Kategori agunan/PPKA | Upsert baris |
| `CHECKLIST_DOKUMEN` | Checklist dokumen | Upsert baris |
| `TEMPLATE_DOKUMEN` | Template & status | Upsert baris |
| `PRODUK` | Produk kredit per cabang | Upsert baris |

Untuk referensi, gunakan **Ubah** pada baris tabel (mengisi target), lalu simpan draft. **Nonaktifkan/Aktifkan** juga melalui revisi.

## 5. Validasi yang harus dipenuhi

### Parameter kebijakan
- Semua 34 kunci wajib ada.
- Total bobot 5C (`character + capacity + capital + collateral + condition`) = **1.0**.
- Total `bobot_kelengkapan` = **100**.
- `rating_sangat_layak_min > rating_layak_min > rating_bersyarat_min`.
- `rasio_balloon_layak > rasio_balloon_perhatian`.
- Band skor: DSCR menurun, LTV menaik, SLIK menaik; skor 1–5.
- Rasio/ambang dalam rentang 0–1; `plafon_kewajaran_multiplier ≥ 1`.

### Matriks persetujuan
- Band dimulai dari plafon **0** dan bersambung tanpa celah/tumpang tindih.
- Hanya band teratas yang boleh `plafon_max` kosong (∞).
- Level unik per band; role harus terdaftar.
- `plafon_max > plafon_min`.

### Referensi
- Rate: `usia_min ≤ usia_max`, `tenor_min ≤ tenor_max`, rate ≥ 0.
- Agunan: `persen_max` 0–1; pasal & sumber wajib.
- Checklist: kode unik; kategori valid; urutan ≥ 1.
- Template: kode unik per versi; status valid; `APPROVED` mencatat approver.
- Produk: kode unik per cabang; rate 0–1; tenor min ≤ max.

## 6. Audit & bukti

- `audit_log` menyimpan `entity_type = setting:<SECTION>`, aksi `SUBMIT`/`APPROVE`/`REJECT`/`APPLY`, nilai lama/baru, pelaku, IP, dan user-agent.
- `ledger` menyimpan `PARAMETER_DIAKTIFKAN` untuk aktivasi parameter/matriks.
- Riwayat UI: `/pengaturan/riwayat` (filter bagian & status).
- Kotak persetujuan: `/pengaturan/persetujuan`.

## 7. Troubleshooting

| Gejala | Penyebab | Tindakan |
|---|---|---|
| Tombol setujui tidak muncul | Tidak punya `setting.approve` atau Anda pembuat revisi | Minta approver lain; SoD berlaku |
| Tidak bisa menerapkan | Status bukan APPROVED | Setujui dulu |
| Validasi bobot gagal | Total 5C ≠ 1.0 / kelengkapan ≠ 100 | Perbaiki nilai, simpan ulang draft |
| Band matriks ditolak | Ada celah/overlap/level duplikat | Urutkan band dari 0 dan hapus duplikat |
| Perubahan belum terlihat | Revisi belum APPLIED | Minta ADMIN menerapkan |

## 8. Perintah verifikasi

```bash
php artisan test tests/Feature/Setting
php artisan hardcode:audit
bash scripts/verify.sh --full
```
