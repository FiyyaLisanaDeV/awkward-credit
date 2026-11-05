@props(['value' => null, 'status' => null, 'minimum' => null])

@php
    $angka = is_numeric($value) ? number_format((float) $value, 2, ',', '.') : '—';
    $tone = match ($status) {
        'LAYAK' => 'success',
        'PERHATIAN' => 'warning',
        'TIDAK LAYAK' => 'danger',
        default => 'neutral',
    };
@endphp

<div class="dscr">
    <div class="dscr-value">{{ $angka }}</div>
    <div class="dscr-meta">
        <span class="badge badge-{{ $tone }}">{{ $status ? __('status_kelayakan.'.$status) : '—' }}</span>
        @if ($minimum !== null)
            <span class="hint">{{ __('simulasi.minimum_dscr') }}: {{ number_format((float) $minimum, 2, ',', '.') }}</span>
        @endif
    </div>
</div>
