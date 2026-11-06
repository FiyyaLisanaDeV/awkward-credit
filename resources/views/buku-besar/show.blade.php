@extends('layouts.app', ['title' => $permohonan->no_permohonan])

@section('content')
    <div class="page-head">
        <h1>{{ __('buku_besar.title') }} — {{ $permohonan->no_permohonan }}</h1>
        <a class="btn" href="{{ route('buku-besar.index') }}">{{ __('form.back') }}</a>
    </div>

    <div class="grid grid-3">
        <div class="card">
            <div class="hint">{{ __('permohonan.debitur') }}</div>
            <h3 class="mb-0">{{ $permohonan->debitur?->nama_ktp }}</h3>
            <div class="mono small">{{ $permohonan->debitur?->cif ?? '—' }}</div>
        </div>
        <div class="card">
            <div class="hint">{{ __('permohonan.plafon') }}</div>
            <h3 class="mb-0">Rp {{ number_format((float) $permohonan->plafon_diajukan, 0, ',', '.') }}</h3>
        </div>
        <div class="card">
            <div class="hint">{{ __('permohonan.status') }}</div>
            <x-status-badge :status="$permohonan->status" :label="__('status_permohonan.'.$permohonan->status)" />
        </div>
    </div>

    @if ($permohonan->kredit)
        <div class="card">
            <dl class="review-list">
                <dt>{{ __('kredit.no_rekening') }}</dt><dd class="mono">{{ $permohonan->kredit->no_rekening_kredit }}</dd>
                <dt>{{ __('kredit.tanggal_cair') }}</dt><dd>{{ $permohonan->kredit->tanggal_cair?->format('d-m-Y') }}</dd>
                <dt>{{ __('kredit.plafon') }}</dt><dd>Rp {{ number_format((float) $permohonan->kredit->plafon, 0, ',', '.') }}</dd>
            </dl>
        </div>
    @endif

    <div class="card">
        <h3>{{ __('buku_besar.ringkasan') }}</h3>
        @forelse ($entri as $row)
            <div class="timeline-item">
                <div class="flex between">
                    <strong>{{ __('buku_besar.'.$row->tahap) }}</strong>
                    <span class="hint">{{ $row->occurred_at?->format('d-m-Y H:i:s') }}</span>
                </div>
                <div>{{ $row->ringkasan }}</div>
                <div class="hint">{{ $row->aktor_nama ?? '—' }} · {{ $row->aktor_role }}</div>
                @if ($row->snapshot)
                    <details class="small">
                        <summary>{{ __('buku_besar.snapshot') }}</summary>
                        <pre class="code">{{ json_encode($row->snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </details>
                @endif
            </div>
        @empty
            <p class="muted">{{ __('buku_besar.empty') }}</p>
        @endforelse
    </div>
@endsection
