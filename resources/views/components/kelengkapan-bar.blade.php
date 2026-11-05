@props(['persen' => 0, 'label' => null])

@php
    $nilai = max(0, min(100, (float) $persen));
@endphp

<div class="kelengkapan">
    <div class="kelengkapan-head">
        <span>{{ $label ?? __('permohonan.kelengkapan') }}</span>
        <strong>{{ number_format($nilai, 0, ',', '.') }}%</strong>
    </div>
    <div class="bar"><div class="bar-fill" style="width: {{ $nilai }}%"></div></div>
</div>
