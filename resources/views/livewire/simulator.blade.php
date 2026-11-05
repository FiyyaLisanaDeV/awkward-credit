<div class="grid grid-2">
    <form wire:submit="hitung" class="card">
        <h3>{{ __('simulasi.input') }}</h3>

        <div class="grid grid-2">
            <x-input name="label" :label="__('simulasi.label')" wire:model="label" required />
            <x-select name="produk_tipe" :label="__('simulasi.produk_tipe')" wire:model="produk_tipe" required :options="$produkTipeOptions" />
            <x-select name="sistem_suku_bunga" :label="__('simulasi.sistem')" wire:model="sistem_suku_bunga" required :options="$sistemOptions" />
            <x-money-input name="plafon" :label="__('simulasi.plafon')" wire:model="plafon" required />
            <x-input name="rate" type="number" step="0.0001" :label="__('simulasi.rate')" wire:model="rate" required />
            <x-input name="tenor" type="number" :label="__('simulasi.tenor')" wire:model="tenor" required />
            <x-money-input name="surplus_bulanan" :label="__('simulasi.surplus')" wire:model="surplus_bulanan" required />
            <x-money-input name="penghasilan_bersih" :label="__('simulasi.penghasilan_bersih')" wire:model="penghasilan_bersih" />
            <x-money-input name="nilai_jaminan" :label="__('simulasi.nilai_jaminan')" wire:model="nilai_jaminan" />
        </div>

        @if ($produk_tipe === 'MODAL_KERJA')
            <div class="grid grid-2 mt-2">
                <x-money-input name="biaya_operasional_tahunan" :label="__('simulasi.biaya_operasional')" wire:model="biaya_operasional_tahunan" />
                <x-input name="periode_perputaran_hari" type="number" :label="__('simulasi.periode_perputaran')" wire:model="periode_perputaran_hari" />
                <x-money-input name="pembelian_bulanan" :label="__('simulasi.pembelian')" wire:model="pembelian_bulanan" />
                <x-input name="hari_persediaan" type="number" :label="__('simulasi.hari_persediaan')" wire:model="hari_persediaan" />
                <x-money-input name="mk_tersedia" :label="__('simulasi.mk_tersedia')" wire:model="mk_tersedia" />
            </div>
        @endif

        <div class="wizard-actions">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">{{ __('simulasi.hitung') }}</button>
            <button type="button" class="btn" wire:click="simpan" wire:loading.attr="disabled">{{ __('simulasi.simpan') }}</button>
        </div>
    </form>

    <div class="card">
        <h3>{{ __('simulasi.hasil') }}</h3>

        @if ($hasil === [])
            <p class="muted">{{ __('simulasi.hasil_kosong') }}</p>
        @else
            <dl class="review-list">
                <dt>{{ __('simulasi.angsuran_pokok') }}</dt>
                <dd>Rp {{ number_format((float) ($hasil['angsuran']['pokok'] ?? 0), 2, ',', '.') }}</dd>
                <dt>{{ __('simulasi.angsuran_bunga') }}</dt>
                <dd>Rp {{ number_format((float) ($hasil['angsuran']['bunga'] ?? 0), 2, ',', '.') }}</dd>
                <dt>{{ __('simulasi.angsuran_total') }}</dt>
                <dd><strong>Rp {{ number_format((float) ($hasil['angsuran']['total'] ?? 0), 2, ',', '.') }}</strong></dd>
                <dt>{{ __('simulasi.rpc') }}</dt>
                <dd>Rp {{ number_format((float) ($hasil['rpc'] ?? 0), 2, ',', '.') }}</dd>
                <dt>{{ __('simulasi.dscr') }}</dt>
                <dd>
                    <x-dscr-display :value="$hasil['dscr'] ?? null" :status="$hasil['status_dscr'] ?? null" />
                </dd>
                <dt>{{ __('simulasi.plafon_max_flat') }}</dt>
                <dd>Rp {{ number_format((float) ($hasil['plafon_max']['flat'] ?? 0), 2, ',', '.') }}</dd>
                <dt>{{ __('simulasi.plafon_max_anuitas') }}</dt>
                <dd>Rp {{ number_format((float) ($hasil['plafon_max']['anuitas'] ?? 0), 2, ',', '.') }}</dd>
                <dt>{{ __('simulasi.grace_plafon_max') }}</dt>
                <dd>Rp {{ number_format((float) ($hasil['plafon_max']['grace'] ?? 0), 2, ',', '.') }}</dd>
                <dt>{{ __('simulasi.ltv') }}</dt>
                <dd>{{ $hasil['ltv'] !== null ? number_format((float) $hasil['ltv'], 2, ',', '.') : '—' }}</dd>
                <dt>{{ __('simulasi.idir') }}</dt>
                <dd>{{ $hasil['idir'] !== null ? number_format((float) $hasil['idir'], 2, ',', '.') : '—' }}</dd>
                <dt>{{ __('simulasi.bunga_efektif') }}</dt>
                <dd>{{ number_format((float) ($hasil['suku_bunga_efektif'] ?? 0), 4, ',', '.') }}</dd>
            </dl>
        @endif
    </div>

    @if ($jadwal !== [])
        <div class="card span-2">
            <h3>{{ __('simulasi.jadwal_preview') }}</h3>
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('simulasi.periode') }}</th>
                            <th>{{ __('simulasi.jatuh_tempo') }}</th>
                            <th class="num">{{ __('simulasi.saldo_awal') }}</th>
                            <th class="num">{{ __('simulasi.pokok') }}</th>
                            <th class="num">{{ __('simulasi.bunga') }}</th>
                            <th class="num">{{ __('simulasi.total') }}</th>
                            <th class="num">{{ __('simulasi.saldo_akhir') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwal as $baris)
                            <tr>
                                <td>{{ $baris['periode'] }}</td>
                                <td>{{ $baris['tanggal_jatuh_tempo'] }}</td>
                                <td class="num">{{ number_format((float) $baris['saldo_awal'], 2, ',', '.') }}</td>
                                <td class="num">{{ number_format((float) $baris['angsuran_pokok'], 2, ',', '.') }}</td>
                                <td class="num">{{ number_format((float) $baris['bunga'], 2, ',', '.') }}</td>
                                <td class="num">{{ number_format((float) $baris['total_angsuran'], 2, ',', '.') }}</td>
                                <td class="num">{{ number_format((float) $baris['saldo_akhir'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
