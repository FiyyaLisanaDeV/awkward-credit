@extends('layouts.app', ['title' => __('kredit.cairkan_title')])

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ __('kredit.cairkan_title') }}</h1>
            <p class="muted mb-0">{{ __('kredit.cairkan_hint') }}</p>
        </div>
        <a class="btn" href="{{ route('permohonan.show', $permohonan) }}">{{ __('form.back') }}</a>
    </div>

    <div class="card">
        <dl class="review-list">
            <dt>{{ __('permohonan.no') }}</dt><dd class="mono">{{ $permohonan->no_permohonan }}</dd>
            <dt>{{ __('permohonan.debitur') }}</dt><dd>{{ $permohonan->debitur?->nama_ktp }}</dd>
            <dt>{{ __('permohonan.plafon') }}</dt><dd>Rp {{ number_format((float) $permohonan->plafon_diajukan, 0, ',', '.') }}</dd>
            <dt>{{ __('permohonan.status') }}</dt><dd><x-status-badge :status="$permohonan->status" :label="__('status_permohonan.'.$permohonan->status)" /></dd>
        </dl>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('kredit.cairkan.store', $permohonan) }}">
            @csrf
            <x-input name="tanggal_cair" label="{{ __('kredit.tanggal_cair') }}" type="date" required
                value="{{ old('tanggal_cair', now()->toDateString()) }}" />
            <x-input name="rekening_pencairan" label="{{ __('kredit.rekening') }}" required
                value="{{ old('rekening_pencairan') }}" />
            <x-input name="no_akad" label="{{ __('kredit.no_akad') }}" value="{{ old('no_akad') }}" />
            <div class="mt-2">
                <button type="submit" class="btn btn-primary">{{ __('kredit.cairkan_button') }}</button>
            </div>
        </form>
    </div>
@endsection
