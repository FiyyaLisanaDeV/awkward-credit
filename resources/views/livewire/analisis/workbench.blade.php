<div>
    @php
        $neraca = $permohonan->neraca;
        $labaRugi = $permohonan->labaRugi;
        $kapasitas = $permohonan->kapasitasBayar;
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
            @foreach (['ringkasan' => 'Ringkasan', 'neraca' => 'Neraca', 'laba-rugi' => 'Laba Rugi', 'kapasitas' => 'Kapasitas & DSCR', 'scoring' => 'Scoring 5C'] as $key => $label)
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
            </dl>
        </div>
    @endif

    @if ($tab === 'neraca')
        <form class="card" wire:submit="saveNeraca">
            <h3>Neraca (Sheet 3)</h3>
            <div class="grid grid-2">
                <x-money-input name="total_aktiva" label="Total Aktiva" wire:model="total_aktiva" required />
                <x-money-input name="total_pasiva" label="Total Pasiva" wire:model="total_pasiva" required />
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

    @if ($tab === 'scoring')
        <div class="card">
            <h3>Scoring 5C (Sheet 8–9)</h3>
            @if ($permohonan->scoring5c === null)
                <p class="muted">Scoring belum dihitung. Jalankan analisis kapasitas terlebih dahulu.</p>
            @else
                <dl class="review-list">
                    <dt>Skor total</dt>
                    <dd>{{ $permohonan->scoring5c->skor_total }}</dd>
                    <dt>Rating</dt>
                    <dd>{{ $permohonan->scoring5c->rating }}</dd>
                </dl>
            @endif
        </div>
    @endif
</div>
