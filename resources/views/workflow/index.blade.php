@extends('layouts.app', ['title' => __('approval.title')])

@section('content')
    <div class="page-head">
        <h1>{{ __('approval.title') }}</h1>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('permohonan.no') }}</th>
                    <th>{{ __('permohonan.debitur') }}</th>
                    <th class="num">{{ __('permohonan.plafon') }}</th>
                    <th>{{ __('approval.kelengkapan') }}</th>
                    <th>{{ __('permohonan.status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($permohonan as $row)
                    <tr>
                        <td class="mono">{{ $row->no_permohonan }}</td>
                        <td>{{ $row->debitur?->nama_ktp }}</td>
                        <td class="num">Rp {{ number_format((float) $row->plafon_diajukan, 0, ',', '.') }}</td>
                        <td><x-kelengkapan-bar :persen="(float) $row->kelengkapan_persen" /></td>
                        <td><x-status-badge :status="$row->status" :label="__('status_permohonan.'.$row->status)" /></td>
                        <td><a href="{{ route('approval.show', $row) }}">{{ __('form.detail') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">{{ __('approval.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        @include('partials.pagination', ['paginator' => $permohonan])
    </div>
@endsection
