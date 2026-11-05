@extends('layouts.app', ['title' => __('dashboard.title')])

@section('content')
    <div class="page-head">
        <h1>{{ __('dashboard.title') }}</h1>
        <span class="muted">{{ __('dashboard.welcome', ['name' => auth()->user()?->name]) }}</span>
    </div>

    @php
        $approved = collect($per_status)->firstWhere('status', 'APPROVED')['jumlah'] ?? 0;
    @endphp

    <div class="grid grid-4">
        <div class="card">
            <div class="hint">{{ __('dashboard.kpi.draft') }}</div>
            <h2>{{ $draft }}</h2>
        </div>
        <div class="card">
            <div class="hint">{{ __('dashboard.kpi.submitted') }}</div>
            <h2>{{ $pipeline['submitted'] }}</h2>
        </div>
        <div class="card">
            <div class="hint">{{ __('dashboard.kpi.approved') }}</div>
            <h2>{{ $approved }}</h2>
        </div>
        <div class="card">
            <div class="hint">{{ __('dashboard.kpi.active') }}</div>
            <h2>{{ $kredit_aktif }}</h2>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h3>{{ __('dashboard.pipeline') }}</h3>
            <dl class="review-list">
                <dt>{{ __('dashboard.kpi.submitted') }}</dt><dd>{{ $pipeline['submitted'] }}</dd>
                <dt>{{ __('dashboard.analyzed') }}</dt><dd>{{ $pipeline['analyzed'] }}</dd>
                <dt>{{ __('dashboard.recommended') }}</dt><dd>{{ $pipeline['recommended'] }}</dd>
                <dt>{{ __('dashboard.red_flag_kritis') }}</dt><dd>{{ $red_flag_kritis }}</dd>
                <dt>{{ __('dashboard.kelengkapan_rata') }}</dt><dd>{{ number_format((float) $kelengkapan_rata, 0, ',', '.') }}%</dd>
            </dl>
        </div>

        <div class="card">
            <h3>{{ __('dashboard.recent') }}</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('permohonan.no') }}</th>
                        <th>{{ __('permohonan.debitur') }}</th>
                        <th>{{ __('permohonan.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent as $row)
                        <tr>
                            <td class="mono">{{ $row->no_permohonan }}</td>
                            <td>{{ $row->debitur?->nama_ktp }}</td>
                            <td><x-status-badge :status="$row->status" :label="__('status_permohonan.'.$row->status)" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">{{ __('dashboard.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
