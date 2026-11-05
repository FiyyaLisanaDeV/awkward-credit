@extends('layouts.app', ['title' => $simulasi->label])

@section('content')
    <div class="page-head">
        <h1>{{ $simulasi->label }}</h1>
        <div class="flex gap-1">
            <a class="btn" href="{{ route('simulasi.index') }}">{{ __('form.back') }}</a>
            <a class="btn" href="{{ route('simulasi.bandingkan', ['awal' => $simulasi->id]) }}">{{ __('simulasi.compare') }}</a>
        </div>
    </div>

    <div class="grid grid-3">
        <div class="card">
            <div class="hint">{{ __('simulasi.plafon') }}</div>
            <h2 class="mb-0">Rp {{ number_format((float) $simulasi->plafon, 0, ',', '.') }}</h2>
        </div>
        <div class="card">
            <div class="hint">{{ __('simulasi.angsuran_total') }}</div>
            <h2 class="mb-0">Rp {{ number_format((float) $simulasi->angsuran, 2, ',', '.') }}</h2>
        </div>
        <div class="card">
            <div class="hint">{{ __('simulasi.dscr') }}</div>
            <h2 class="mb-0"><x-dscr-display :value="$simulasi->dscr" :status="$simulasi->status_kelayakan" /></h2>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h3>{{ __('simulasi.input') }}</h3>
            <dl class="review-list">
                <dt>{{ __('simulasi.produk_tipe') }}</dt><dd>{{ __('enums.produk_tipe.'.$simulasi->produk_tipe) }}</dd>
                <dt>{{ __('simulasi.sistem') }}</dt><dd>{{ $simulasi->sistem_suku_bunga }}</dd>
                <dt>{{ __('simulasi.rate') }}</dt><dd>{{ $simulasi->rate }}</dd>
                <dt>{{ __('simulasi.tenor') }}</dt><dd>{{ $simulasi->tenor }}</dd>
                <dt>{{ __('simulasi.plafon_max_flat') }}</dt><dd>Rp {{ number_format((float) $simulasi->plafon_max, 2, ',', '.') }}</dd>
                <dt>{{ __('simulasi.ltv') }}</dt><dd>{{ $simulasi->ltv !== null ? number_format((float) $simulasi->ltv, 2, ',', '.') : '—' }}</dd>
                <dt>{{ __('simulasi.idir') }}</dt><dd>{{ $simulasi->idir !== null ? number_format((float) $simulasi->idir, 2, ',', '.') : '—' }}</dd>
                <dt>{{ __('simulasi.engine') }}</dt><dd class="mono">{{ $simulasi->engine_version }} / {{ $simulasi->parameter_version }}</dd>
            </dl>
        </div>

        <div class="card">
            <h3>{{ __('simulasi.hasil') }}</h3>
            <pre class="code">{{ json_encode($simulasi->output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
@endsection
