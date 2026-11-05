@props(['name', 'label' => null, 'type' => 'text', 'hint' => null, 'required' => false])

<div class="field">
    @if ($label)
        <label for="{{ $name }}">{{ $label }}@if ($required)<span class="req">*</span>@endif</label>
    @endif
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'input']) }}
    >
    @if ($hint)
        <div class="hint">{{ $hint }}</div>
    @endif
    @error($name)
        <div class="error">{{ $message }}</div>
    @enderror
</div>
