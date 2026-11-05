<div>
    @if ($pesan)
        <div class="alert alert-error">{{ $pesan }}</div>
    @endif

    <div class="field">
        <label for="alasan">{{ __('approval.alasan') }}</label>
        <input id="alasan" type="text" class="input" wire:model="alasan" placeholder="{{ __('approval.alasan_hint') }}">
        @error('alasan')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="flex gap-2">
        <button type="button" class="btn btn-primary" wire:click="approve" wire:loading.attr="disabled">
            {{ __('approval.approve') }}
        </button>
        <button type="button" class="btn btn-danger" wire:click="reject" wire:loading.attr="disabled">
            {{ __('approval.reject') }}
        </button>
        <button type="button" class="btn" wire:click="returnToAo" wire:loading.attr="disabled">
            {{ __('approval.return') }}
        </button>
    </div>
</div>
