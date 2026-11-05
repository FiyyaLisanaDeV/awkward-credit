# Bab 8 — Mesin Perhitungan, Jadwal, EIR, dan Simulasi

Bab ini mendokumentasikan seluruh kalkulator (`app/Services/Calculation`), mesin simulasi, jadwal angsuran, dan simulasi/replay. Semua kalkulasi domain finansial saat ini memakai **`float` + `Rounding`** `[E]`; target kontrak = **BIGINT rupiah + `brick/math`** `[K]`.

## 8.1 Konstanta & pembulatan `[E]`
`App\Support\Konstanta`: `BULAN_PER_TAHUN=12`, `HARI_PER_BULAN=30`, `BISECTION_ITERATIONS=80`, `MAX_MONTHLY_RATE=1.0`, `PERSEN=100.0`, `SKOR_MIN=1`, `SKOR_MAKS=5`, `MILI_PER_DETIK=1000`.

`App\Support\Rounding` (dari `config('loan.money')`):
- `money(x)` → round `scale` (2) HALF_UP.
- `ratio(x)` → round `ratio_scale` (4) HALF_UP.
- `schedule(x)` → round `schedule_scale` (2) HALF_UP.
- `canonical_scale` (6) dipakai hashing kanonik (`ChainHasher`).

## 8.2 Angsuran `[E]` — `AngsuranCalculator`
### `hitung(plafon, rateTahunan, tenor, sistem) → {pokok, bunga, total}`
Guard: `plafon>0`, `tenor≥1`. `rateBulan = rateTahunan/12`.
- **Flat**: `pokok = plafon / tenor`; `bunga = plafon × rateBulan`.
- **Anuitas**: `pokok = pmt(rateBulan,tenor,plafon) − plafon×rateBulan`; `bunga = plafon × rateBulan`.
- **Grace Period**: `pokok = 0`; `bunga = plafon × rateBulan`.
Semua dibulatkan `Rounding::money`.

`pmt(rate, nper, pv)`: bila rate 0 → `-pv/nper`; selain itu `(pv*rate)/(1-(1+rate)^-nper)`. (Dikembalikan positif — lihat drift PROGRESS soal tanda Excel.)

### `generateJadwal(plafon, rateTahunan, tenor, sistem, tanggalMulai) → list<baris>`
- `angsuran = pmt(...)`; iterasi periode 1..tenor.
- `bunga`: Flat/Grace = `plafon×rateBulan`; Anuitas = `saldoAwal×rateBulan`.
- `pokok`: Grace → 0 kecuali periode terakhir (=saldoAwal); periode terakhir → saldoAwal; Flat → `plafon/tenor`; Anuitas → `angsuran − bunga`. `max(0,...)`.
- `saldo = max(0, saldoAwal − pokok)`.
- `tanggal_jatuh_tempo = start + periode bulan`.
- **Koreksi pembulatan**: `selisih = plafon − Σ pokok` ditambahkan ke baris terakhir (`angsuran_pokok` & `total_angsuran`).

### DSCR/RPC `[E]` — `DscrCalculator`
- `rpc(surplusBulanan, margin) = Rounding::money(surplus × margin)`.
- `dscr(rpc, angsuran) = angsuran≤0 ? 0 : Rounding::ratio(rpc/angsuran)`.
- `status(dscr, minimum) = dscr≥min ? LAYAK : TIDAK LAYAK`.
- `evaluasi(...)` mengembalikan `{rpc, dscr, status}`.

### IDIR `[E]` — `IdirCalculator`
- `hitung(angsuranBulanan, penghasilanBersih) = penghasilan≤0 ? 0 : Rounding::ratio(angsuran/penghasilan)`.
- `status(idir, threshold) = idir≤threshold ? LAYAK : TIDAK LAYAK`.

### LTV `[E]` — `LtvCalculator`
- `hitung(plafon, nilaiJaminan) = jaminan≤0 ? 0 : Rounding::ratio(plafon/jaminan)`.
- `melebihi(ltv, maks) = ltv > maks`.

### Plafon Maksimum `[E]` — `PlafonMaxCalculator` (semua `Rounding::money`)
- **flat**: `rpc / ((1/tenor) + rateBulan)`.
- **anuitas**: rate≤0 → `rpc×tenor`; else `rpc × ((1-(1+rateBulan)^-tenor)/rateBulan)`.
- **grace**: rate≤0 → error; else `rpc / rateBulan`.
- `semua(...)` → `{flat, anuitas, grace}`.

### Suku Bunga Efektif (EIR) `[E]` — `SukuBungaEfektifCalculator`
`hitung(plafon, angsuranBulanan, tenor)`: **bisection** `rateBulan` di `[0, MAX_MONTHLY_RATE=1.0]` selama `BISECTION_ITERATIONS=80`, cari PV arus kas = plafon.
`presentValue(rateBulan, tenor, angsuran)` = rate 0 → `angsuran×tenor`; else `angsuran × (1-(1+rateBulan)^-tenor)/rateBulan`.
Hasil = `Rounding::ratio(rateBulan × 12)` (tahunan).
> **Penting `[K]`**: definisi arus kas EIR (biaya termasuk/tidak) & disclosure = D-05 `[K]`. Rencana koreksi melarang faktor 1,81x/0,87x (V4). Lihat Bab 20.

## 8.3 Mesin simulasi `[E]` — `SimulationEngine` & `SimulationRunner`
`SimulationEngine::run(input, PolicyParameters) → output` (dipakai baik oleh preview, penyimpanan, maupun replay):
1. `progress` dari `ProdukTipe::from(produk_tipe)`, `sistem` dari `SistemSukuBunga::from(...)`.
2. `angsuran = AngsuranCalculator::hitung(...)`.
3. `rpc = DscrCalculator::rpc(surplus_bulanan, margin_kapasitas_bayar)`; `dscr = dscr(rpc, angsuran.total)`; `status_dscr`.
4. `plafon_max = PlafonMaxCalculator::semua(rpc, tenor, rate)`.
5. `ltv` bila `nilai_jaminan>0`.
6. **IDIR** bila `produk_tipe === TPP` dan `penghasilan_bersih` ada.
7. **Modal kerja** bila `produk_tipe === MODAL_KERJA` dan `biaya_operasional_tahunan` ada.
8. `suku_bunga_efektif = SukuBungaEfektifCalculator::hitung(plafon, angsuran.total, tenor)`.
Output: `{angsuran, rpc, dscr, status_dscr, plafon_max, ltv, idir, status_idir, modal_kerja, suku_bunga_efektif}`.

`SimulationRunner`:
- `preview(input)` → `engine->run(input, resolver->active())`.
- `execute(data, user)` → jalankan (ukur `durasi_ms`) → `SimulationService::record(...)`.
- `diff(awal, akhir)` → selisih kolom numerik (`flatten`), `{awal, akhir, selisih}`.

## 8.4 Penyimpanan simulasi `[E]` — `SimulationService`
`record(...)` dalam transaksi:
- Simpan `simulasi_kredit`: `cabang_id, user_id, permohonan_id?, debitur_id?, label, produk_tipe, sistem_suku_bunga, parameter_kebijakan_id, parameter_version, engine_version, input, input_hash(sha256 canonical), output, plafon, rate, tenor, angsuran, dscr, plafon_max(flat), ltv, idir, status_kelayakan, durasi_ms, dibuat_pada`.
- Catat ledger `SIMULASI_DIJALANKAN` dengan `{simulasi_id, input_hash, engine_version, parameter_version, hasil_ringkas}`.
- Update `simulasi.ledger_id`.

## 8.5 Replay & audit simulasi `[E]`
- API `POST /api/v1/simulasi/{simulasi}/replay` & command `simulasi:replay {id}`: jalankan ulang `engine->run(input, parameter)` dan bandingkan **JSON kanonik** (`ChainHasher::canonicalJson`) dengan `output` tersimpan → `identik: true/false`.
- **Ketentuan**: replay memakai `parameter_kebijakan_id` yang disimpan, sehingga perubahan policy tidak mengubah hasil historis.

## 8.6 Presisi uang `[E]` vs target `[K]`
- **Saat ini** `[E]`: `float` + `Rounding` (scale 2/4/2), DB `decimal`.
- **Target `[K]`**: BIGINT rupiah + `brick/math`; `Rounding` jadi adapter policy (D-05). `brick/math` **belum** ada di `composer.json`.
- Rencana migrasi & larangan (no float baru, no ubah expected golden tanpa sumber): lihat `project/docs/spec/pr-plan-c002-money.md` (Bab 20).

## 8.7 API simulasi `[E]`
| Method | Endpoint | Catatan |
|---|---|---|
| GET | `/api/v1/simulasi` | Daftar cabang; filter `q` (label `ilike`); paginate |
| POST | `/api/v1/simulasi` | Validasi (`SimulasiRules`-setara) → `SimulationRunner::execute` → 201 |
| GET | `/api/v1/simulasi/{simulasi}` | Detail (scope cabang) |
| POST | `/api/v1/simulasi/{simulasi}/replay` | Bandingkan output kanonik |

Validasi `store` (API): `label` required; `produk_tipe`/`sistem_suku_bunga` enum; `plafon>0`; `rate>0 ≤0.5`; `tenor` 1–360; `surplus_bulanan≥0`; field lain nullable ≥0; `permohonan_id`/`debitur_id` uuid exists.

## 8.8 UI simulator `[E]` — `Livewire/Simulator`
- Field: `label, produk_tipe, sistem_suku_bunga, plafon, rate, tenor, surplus_bulanan, penghasilan_bersih, nilai_jaminan, biaya_operasional_tahunan, periode_perputaran_hari, pembelian_bulanan, hari_persediaan, mk_tersedia, permohonan_id`.
- `mount(?permohonan)`: pra-isi dari permohonan bila ada.
- `hitung(runner)`: validasi `SimulasiRules` → `preview` → set `hasil` & `jadwal` (`AngsuranCalculator::generateJadwal`).
- `simpan(runner)`: validasi → `execute` → flash `simulasi.saved` → redirect `simulasi.show`.
- Web `SimulasiController`: `index`, `create`, `show` (scope cabang), `bandingkan` (dipakai `SimulationRunner::diff`).

## 8.9 Golden test `[E]` & konflik C-002 `[K]`
- Command `golden:extract` membaca workbook (`hitungan` E8/E9/E12; `analisis kredit` D30–D34, E28) → fixture `tests/fixtures/golden/tpp.json`.
- `FormulaGoldenTest` mengunci (toleransi 0.01/0.0001): `rate 0.18`, `idir 0.831124…`, `penghasilan_bersih 3.048.080`, angsuran 2.533.333,33 (60 bln, 80jt, Flat).
- **Konflik**: C-002 `[K]` mengoreksi IDIR→85,93%, net→2.948.080, dan menuntut **EIR/IRR numerik tanpa faktor 1,81x**. **Jangan ubah** expected tanpa sumber terverifikasi (lihat Bab 20).

## 8.10 Contoh perhitungan (ilustrasi, `[K]` nilai)
Input: plafon 80.000.000; rate 0,18/tahun; tenor 60; Flat.
- `rateBulan = 0,18/12 = 0,015`.
- `pokok = 80.000.000/60 = 1.333.333,33`.
- `bunga = 80.000.000×0,015 = 1.200.000`.
- `total = 2.533.333,33` (cocok fixture `[E]`).
- IDIR (fixture) = 2.533.333,33 / 3.048.080 = 0,8311… `[E]`; menurut C-002 = 85,93% `[K]`.
