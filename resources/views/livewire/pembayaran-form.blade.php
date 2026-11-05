<div>
    @if ($pesan)
        <div class="alert alert-error">{{ $pesan }}</div>
    @endif

    @if (empty($periodeBelumBayar))
        <p class="muted">{{ __('pembayaran.all_paid') }}</p>
    @else
        @php
            $periodeOptions = collect($periodeBelumBayar)->mapWithKeys(fn ($p): array => [$p => $p])->all();
        @endphp

        <div class="field-row">
            <x-select name="periode" :label="__('pembayaran.periode')" wire:model="periode" :options="$periodeOptions" />
            <x-input name="tanggal_bayar" type="date" :label="__('pembayaran.tanggal')" wire:model="tanggal_bayar" />
        </div>

        <div class="field-row">
            <x-select name="metode" :label="__('pembayaran.metode')" wire:model="metode" :options="$metodeOptions" />
            <x-money-input name="denda" :label="__('pembayaran.denda')" wire:model="denda" />
        </div>

        <button type="button" class="btn btn-primary" wire:click="simpan" wire:loading.attr="disabled">
            {{ __('pembayaran.catat') }}
        </button>
    @endif
</div>
