<div>
    @php
        $neraca = $permohonan->neraca;
        $labaRugi = $permohonan->labaRugi;
        $kapasitas = $permohonan->kapasitasBayar;
        $rasio = $permohonan->rasio;
        $modalKerja = $permohonan->modalKerja;
        $sensitivitas = $permohonan->sensitivitas;
        $scoring = $permohonan->scoring5c;
        $selisih = (float) ($neraca?->selisih_cek ?? 0);
    @endphp

    <div class="page-head">
        <div>
            <h1>Analisis {{ $permohonan->no_permohonan }}</h1>
            <div class="muted">
                {{ $permohonan->debitur?->nama_ktp ?? '—' }} · {{ $permohonan->produk?->nama ?? '—' }}
            </div>
        </div>
        <a class="btn" href="{{ route('permohonan.show', $permohonan) }}">Kembali</a>
    </div>

    @if ($status !== '')
        <div class="alert alert-warning" role="status">{{ $status }}</div>
    @endif

    @if (abs($selisih) > 0.01)
        <div class="alert alert-warning" role="alert">
            <strong>Perhatian: Neraca belum seimbang.</strong>
            Total aktiva Rp {{ number_format((float) ($neraca?->total_aktiva ?? 0), 2, ',', '.') }}
            berbeda dengan total pasiva Rp {{ number_format((float) ($neraca?->total_pasiva ?? 0), 2, ',', '.') }}.
            Selisih Rp {{ number_format(abs($selisih), 2, ',', '.') }} dicatat pada audit risiko.
        </div>
    @endif

    <div class="card">
        <div class="flex gap-1">
            @foreach (['ringkasan' => 'Ringkasan', 'neraca' => 'Neraca', 'laba-rugi' => 'Laba Rugi', 'rasio' => 'Rasio', 'modal-kerja' => 'Modal Kerja', 'kapasitas' => 'Kapasitas & DSCR', 'sensitivitas' => 'Sensitivitas', 'scoring' => 'Scoring 5C'] as $key => $label)
                <button type="button" class="btn {{ $tab === $key ? 'btn-primary' : '' }}" wire:click="$set('tab', '{{ $key }}')">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    @if ($tab === 'ringkasan')
        <div class="grid grid-3">
            <div class="card">
                <div class="hint">Plafon diajukan</div>
                <h2 class="mb-0">Rp {{ number_format((float) $permohonan->plafon_diajukan, 0, ',', '.') }}</h2>
            </div>
            <div class="card">
                <div class="hint">Tenor</div>
                <h2 class="mb-0">{{ $permohonan->tenor_diajukan }} bulan</h2>
            </div>
            <div class="card">
                <div class="hint">Status permohonan</div>
                <h2 class="mb-0">{{ $permohonan->status }}</h2>
            </div>
        </div>

        <div class="card">
            <h3>Ringkasan analisis</h3>
            <dl class="review-list">
                <dt>Total aktiva</dt>
                <dd>Rp {{ number_format((float) ($neraca?->total_aktiva ?? 0), 2, ',', '.') }}</dd>
                <dt>Total pasiva</dt>
                <dd>Rp {{ number_format((float) ($neraca?->total_pasiva ?? 0), 2, ',', '.') }}</dd>
                <dt>Surplus bulanan</dt>
                <dd>Rp {{ number_format((float) ($labaRugi?->surplus_per_bulan ?? 0), 2, ',', '.') }}</dd>
                <dt>DSCR</dt>
                <dd>{{ $kapasitas?->dscr_rekomendasi !== null ? number_format((float) $kapasitas->dscr_rekomendasi, 4, ',', '.') : '—' }}</dd>
                <dt>IDIR</dt>
                <dd>{{ $kapasitas?->idir !== null ? number_format((float) $kapasitas->idir, 4, ',', '.') : '—' }}</dd>
                <dt>Status IDIR</dt>
                <dd>{{ $kapasitas?->status_idir ?? '—' }}</dd>
                <dt>Total angsuran SLIK</dt>
                <dd>Rp {{ number_format((float) ($kapasitas?->total_angsuran_slik ?? 0), 2, ',', '.') }}</dd>
                <dt>Rating 5C</dt>
                <dd>{{ $scoring?->rating ?? '—' }}</dd>
            </dl>
        </div>
    @endif

    @if ($tab === 'neraca')
        <form class="card" wire:submit="saveNeraca">
            <h3>Neraca (Sheet 3)</h3>
            <div class="grid grid-2">
                <x-money-input name="total_aktiva" label="Total Aktiva" wire:model="total_aktiva" required />
                <x-money-input name="total_pasiva" label="Total Pasiva" wire:model="total_pasiva" required />
                <x-money-input name="jumlah_aktiva_lancar" label="Jumlah Aktiva Lancar" wire:model="jumlah_aktiva_lancar" required />
                <x-money-input name="kas_inventaris" label="Kas / Inventaris" wire:model="kas_inventaris" required />
                <x-money-input name="persediaan" label="Persediaan" wire:model="persediaan" required />
                <x-money-input name="hutang_pendek" label="Hutang Pendek" wire:model="hutang_pendek" required />
                <x-money-input name="jumlah_hutang" label="Jumlah Hutang" wire:model="jumlah_hutang" required />
                <x-money-input name="jumlah_modal" label="Jumlah Modal" wire:model="jumlah_modal" required />
            </div>
            @error('total_aktiva') <div class="hint">{{ $message }}</div> @enderror
            @error('total_pasiva') <div class="hint">{{ $message }}</div> @enderror
            <div class="wizard-actions">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Simpan Neraca</button>
            </div>
        </form>
    @endif

    @if ($tab === 'laba-rugi')
        <form class="card" wire:submit="saveLabaRugi">
            <h3>Laba Rugi (Sheet 3)</h3>
            <div class="grid grid-2">
                <x-money-input name="total_pendapatan" label="Total Pendapatan" wire:model="total_pendapatan" required />
                <x-money-input name="total_pengeluaran" label="Total Pengeluaran" wire:model="total_pengeluaran" required />
            </div>
            @error('total_pendapatan') <div class="hint">{{ $message }}</div> @enderror
            @error('total_pengeluaran') <div class="hint">{{ $message }}</div> @enderror
            <div class="wizard-actions">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Simpan Laba Rugi</button>
            </div>
        </form>
    @endif

    @if ($tab === 'rasio')
        <div class="card">
            <h3>Rasio Keuangan (Sheet 4)</h3>
            <p class="muted">Dihitung dari neraca dan laba rugi yang tersimpan.</p>
            <button type="button" class="btn btn-primary" wire:click="hitungRasio" wire:loading.attr="disabled">Hitung & Simpan Rasio</button>
        </div>

        <div class="card">
            <table class="table">
                <tbody>
                    @foreach (['current_ratio' => 'Current Ratio', 'quick_ratio' => 'Quick Ratio', 'cash_ratio' => 'Cash Ratio', 'debt_to_asset' => 'Debt to Asset', 'debt_to_equity' => 'Debt to Equity', 'equity_to_asset' => 'Equity to Asset', 'net_profit_margin' => 'Net Profit Margin', 'roa_annualized' => 'ROA', 'roe_annualized' => 'ROE'] as $key => $label)
                        <tr>
                            <th>{{ $label }}</th>
                            <td class="num">{{ $rasio?->getAttribute($key) !== null ? number_format((float) $rasio->getAttribute($key), 4, ',', '.') : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($tab === 'modal-kerja')
        <form class="card" wire:submit="saveModalKerja">
            <h3>Modal Kerja (Sheet 5)</h3>
            <div class="grid grid-2">
                <x-money-input name="biaya_operasional_tahunan" label="Biaya Operasional Tahunan" wire:model="biaya_operasional_tahunan" required />
                <x-input name="periode_perputaran_hari" type="number" label="Siklus Usaha (hari)" wire:model="periode_perputaran_hari" required />
                <x-money-input name="pembelian_bulanan" label="Pembelian Bulanan" wire:model="pembelian_bulanan" required />
                <x-input name="hari_persediaan" type="number" label="Hari Persediaan" wire:model="hari_persediaan" required />
                <x-money-input name="mk_tersedia" label="Modal Kerja Tersedia" wire:model="mk_tersedia" required />
                <x-money-input name="omzet_bulanan" label="Omzet Bulanan" wire:model="omzet_bulanan" required />
            </div>
            <div class="wizard-actions">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Hitung & Simpan Modal Kerja</button>
            </div>
        </form>

        <div class="card">
            <dl class="review-list">
                <dt>Perputaran kas/tahun</dt>
                <dd>{{ $modalKerja?->perputaran_kas_per_tahun !== null ? number_format((float) $modalKerja->perputaran_kas_per_tahun, 4, ',', '.') : '—' }}</dd>
                <dt>Kebutuhan cash turnover</dt>
                <dd>Rp {{ number_format((float) ($modalKerja?->kebutuhan_mk_cash_turnover ?? 0), 2, ',', '.') }}</dd>
                <dt>Kebutuhan WCD</dt>
                <dd>Rp {{ number_format((float) ($modalKerja?->kebutuhan_mk_wcd ?? 0), 2, ',', '.') }}</dd>
                <dt>Rata-rata kebutuhan</dt>
                <dd>Rp {{ number_format((float) ($modalKerja?->rata_rata_kebutuhan_mk ?? 0), 2, ',', '.') }}</dd>
                <dt>Gap modal kerja</dt>
                <dd>Rp {{ number_format((float) ($modalKerja?->gap_mk ?? 0), 2, ',', '.') }}</dd>
            </dl>
        </div>
    @endif

    @if ($tab === 'kapasitas')
        <div class="card">
            <h3>Kapasitas & DSCR (Sheet 6)</h3>
            <p class="muted">Hitung RPC, DSCR, plafon maksimum, IDIR, dan total kewajiban SLIK dari data yang tersimpan.</p>
            <button type="button" class="btn btn-primary" wire:click="hitungKapasitas" wire:loading.attr="disabled">Hitung Kapasitas</button>
        </div>

        <div class="card">
            <dl class="review-list">
                <dt>RPC bulanan</dt>
                <dd>Rp {{ number_format((float) ($kapasitas?->rpc_bulanan ?? 0), 2, ',', '.') }}</dd>
                <dt>DSCR</dt>
                <dd>{{ $kapasitas?->dscr_rekomendasi !== null ? number_format((float) $kapasitas->dscr_rekomendasi, 4, ',', '.') : '—' }}</dd>
                <dt>Plafon max flat</dt>
                <dd>Rp {{ number_format((float) ($kapasitas?->plafon_max_flat ?? 0), 2, ',', '.') }}</dd>
                <dt>Plafon max anuitas</dt>
                <dd>Rp {{ number_format((float) ($kapasitas?->plafon_max_anuitas ?? 0), 2, ',', '.') }}</dd>
                <dt>Total angsuran SLIK</dt>
                <dd>Rp {{ number_format((float) ($kapasitas?->total_angsuran_slik ?? 0), 2, ',', '.') }}</dd>
                <dt>IDIR</dt>
                <dd>{{ $kapasitas?->idir !== null ? number_format((float) $kapasitas->idir, 4, ',', '.') : '—' }} ({{ $kapasitas?->status_idir ?? '—' }})</dd>
            </dl>
        </div>
    @endif

    @if ($tab === 'sensitivitas')
        <div class="card">
            <h3>Sensitivitas (Sheet 7)</h3>
            <p class="muted">Enam skenario: baseline, omzet turun 10%/20%, biaya naik 10%, dan kombinasi.</p>
            <button type="button" class="btn btn-primary" wire:click="generateSensitivitas" wire:loading.attr="disabled">Hitung 6 Skenario</button>
        </div>

        <div class="card">
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Skenario</th>
                            <th class="num">Pendapatan</th>
                            <th class="num">Pengeluaran</th>
                            <th class="num">Surplus</th>
                            <th class="num">RPC</th>
                            <th class="num">DSCR</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sensitivitas as $row)
                            <tr>
                                <td>{{ $row->nama_skenario }}</td>
                                <td class="num">Rp {{ number_format((float) $row->total_pendapatan, 2, ',', '.') }}</td>
                                <td class="num">Rp {{ number_format((float) $row->total_pengeluaran_non_angsuran, 2, ',', '.') }}</td>
                                <td class="num">Rp {{ number_format((float) $row->surplus_baru, 2, ',', '.') }}</td>
                                <td class="num">Rp {{ number_format((float) $row->rpc_baru, 2, ',', '.') }}</td>
                                <td class="num">{{ number_format((float) $row->dscr, 4, ',', '.') }}</td>
                                <td>{{ $row->status_layak ? 'LAYAK' : 'TIDAK LAYAK' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="muted">Belum dihitung.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($tab === 'scoring')
        <form class="card" wire:submit="hitungScoring">
            <h3>Scoring 5C (Sheet 8–9)</h3>
            <div class="grid grid-2">
                <x-input name="skor_condition" type="number" step="0.01" min="1" max="5" label="Skor Condition (1–5)" wire:model="skor_condition" required />
            </div>
            @error('skor_condition') <div class="hint">{{ $message }}</div> @enderror
            <div class="wizard-actions">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Hitung & Simpan Scoring</button>
            </div>
        </form>

        <div class="card">
            @if ($scoring === null)
                <p class="muted">Scoring belum dihitung.</p>
            @else
                <dl class="review-list">
                    <dt>Character</dt><dd>{{ $scoring->skor_character }}</dd>
                    <dt>Capacity</dt><dd>{{ $scoring->skor_capacity }}</dd>
                    <dt>Capital</dt><dd>{{ $scoring->skor_capital }}</dd>
                    <dt>Collateral</dt><dd>{{ $scoring->skor_collateral }}</dd>
                    <dt>Condition</dt><dd>{{ $scoring->skor_condition }}</dd>
                    <dt>Skor tertimbang</dt><dd>{{ $scoring->skor_tertimbang }}</dd>
                    <dt>Skor total</dt><dd>{{ $scoring->skor_total }}</dd>
                    <dt>Rating</dt><dd>{{ $scoring->rating }}</dd>
                    <dt>Override SLIK</dt><dd>{{ $scoring->overridden ? 'YA' : 'TIDAK' }}</dd>
                </dl>
            @endif
        </div>
    @endif
</div>
