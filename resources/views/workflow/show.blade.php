@extends('layouts.app', ['title' => $permohonan->no_permohonan])

@section('content')
    <div class="page-head">
        <h1>{{ $permohonan->no_permohonan }}</h1>
        <a class="btn" href="{{ route('approval.inbox') }}">{{ __('form.back') }}</a>
    </div>

    <div class="grid grid-3">
        <div class="card">
            <div class="hint">{{ __('permohonan.plafon') }}</div>
            <h2 class="mb-0">Rp {{ number_format((float) $permohonan->plafon_diajukan, 0, ',', '.') }}</h2>
        </div>
        <div class="card">
            <div class="hint">{{ __('permohonan.status') }}</div>
            <x-status-badge :status="$permohonan->status" :label="__('status_permohonan.'.$permohonan->status)" />
        </div>
        <div class="card">
            <div class="hint">{{ __('approval.level_berikut') }}</div>
            <b>{{ $nextLevel ?? '—' }}</b>
            <div class="small muted">{{ __('approval.wajib_level') }}: {{ implode(', ', $wajibLevel) ?: '—' }}</div>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h3>{{ __('debitur.identitas') }}</h3>
            <dl class="review-list">
                <dt>{{ __('permohonan.debitur') }}</dt><dd>{{ $permohonan->debitur?->nama_ktp }}</dd>
                <dt>{{ __('debitur.no_ktp') }}</dt><dd class="mono">{{ $permohonan->debitur?->no_ktp }}</dd>
                <dt>{{ __('permohonan.tenor') }}</dt><dd>{{ $permohonan->tenor_diajukan }} {{ __('form.bulan') }}</dd>
                <dt>{{ __('permohonan.sistem') }}</dt><dd>{{ __('enums.sistem_suku_bunga.'.$permohonan->sistem_suku_bunga) }}</dd>
            </dl>

            @if ($permohonan->kapasitasBayar)
                <x-dscr-display :value="(float) $permohonan->kapasitasBayar->dscr_rekomendasi" />
            @endif
        </div>

        <div class="card">
            <h3>{{ __('approval.panel_aksi') }}</h3>
            @livewire('approval-action', ['permohonan' => $permohonan->id])
        </div>
    </div>

    <div class="card">
        <h3>{{ __('approval.timeline') }}</h3>
        <ul class="timeline">
            @forelse ($permohonan->approvalLog as $log)
                <li class="timeline-item">
                    <span class="timeline-dot"></span>
                    <div>
                        <b>{{ __('approval.level') }} {{ $log->level }}</b>
                        <x-status-badge :status="$log->aksi" :label="__('approval.aksi.'.$log->aksi)" />
                        <div class="small muted">
                            {{ $log->user?->name }} · {{ $log->role?->name }} · {{ $log->timestamp?->format('d-m-Y H:i') }}
                        </div>
                        @if ($log->alasan)
                            <div class="small">{{ $log->alasan }}</div>
                        @endif
                    </div>
                </li>
            @empty
                <li class="muted">{{ __('approval.timeline_empty') }}</li>
            @endforelse
        </ul>
    </div>
@endsection
