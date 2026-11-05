@props(['name', 'label' => null, 'hint' => null, 'required' => false])

<div class="field">
    @if ($label)
        <label for="{{ $name }}">{{ $label }}@if ($required)<span class="req">*</span>@endif</label>
    @endif
    <div class="money-wrap">
        <span class="money-prefix">Rp</span>
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="number"
            min="0"
            step="0.01"
            inputmode="decimal"
            @if ($required) required @endif
            {{ $attributes->merge(['class' => 'input money']) }}
        >
    </div>
    @if ($hint)
        <div class="hint">{{ $hint }}</div>
    @endif
    @error($name)
        <div class="error">{{ $message }}</div>
    @enderror
</div>
