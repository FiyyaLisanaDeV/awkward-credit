@php
    $selected = fn (string $key, mixed $fallback = '') => old('payload.'.$key, $payload[$key] ?? $fallback);
@endphp

<p class="hint">{{ __('setting.reference_hint') }}</p>

@if ($section->value === 'PRODUK')
    <div class="field">
        <label for="payload_cabang_id">Cabang <span class="req">*</span></label>
        <select class="input" id="payload_cabang_id" name="payload[cabang_id]" required>
            <option value="">—</option>
            @foreach ($cabang as $item)
                <option value="{{ $item->id }}" @selected($selected('cabang_id') === $item->id)>{{ $item->kode }} · {{ $item->nama }}</option>
            @endforeach
        </select>
        @error('payload.cabang_id')<div class="error">{{ $message }}</div>@enderror
    </div>
@endif

<div class="grid grid-2">
    @foreach ($fields as $field)
        @if ($field['type'] === 'select')
            <div class="field">
                <label for="payload_{{ $field['key'] }}">{{ $field['label'] }}</label>
                <select class="input" id="payload_{{ $field['key'] }}" name="payload[{{ $field['key'] }}]">
                    @foreach ($field['options'] as $option)
                        <option value="{{ $option }}" @selected($selected($field['key']) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                @error('payload.'.$field['key'])<div class="error">{{ $message }}</div>@enderror
            </div>
        @else
            <x-input :name="'payload['.$field['key'].']'" :label="$field['label']" :type="$field['type']"
                :step="$field['step'] ?? null" :value="$selected($field['key'])" />
        @endif
    @endforeach
</div>

@if ($section->value === 'CHECKLIST_DOKUMEN')
    <label class="checkbox">
        <input type="checkbox" name="payload[wajib]" value="1" @checked(old('payload.wajib', $payload['wajib'] ?? true))>
        Wajib
    </label>
@endif

@if (in_array($section->value, ['CHECKLIST_DOKUMEN', 'COLLATERAL_CATEGORY', 'PRODUK'], true))
    <label class="checkbox">
        <input type="checkbox" name="payload[is_active]" value="1" @checked(old('payload.is_active', $payload['is_active'] ?? true))>
        Aktif
    </label>
@endif
