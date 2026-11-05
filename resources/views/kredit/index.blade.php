@extends('layouts.app', ['title' => __('kredit.title')])

@section('content')
    <div class="page-head">
        <h1>{{ __('kredit.title') }}</h1>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('kredit.no_rekening') }}</th>
                    <th>{{ __('permohonan.debitur') }}</th>
                    <th class="num">{{ __('kredit.plafon') }}</th>
                    <th class="num">{{ __('kredit.angsuran') }}</th>
                    <th>{{ __('kredit.status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kredit as $row)
                    <tr>
                        <td class="mono">{{ $row->no_rekening_kredit }}</td>
                        <td>{{ $row->permohonan?->debitur?->nama_ktp }}</td>
                        <td class="num">Rp {{ number_format((float) $row->plafon, 0, ',', '.') }}</td>
                        <td class="num">Rp {{ number_format((float) $row->angsuran_bulanan, 0, ',', '.') }}</td>
                        <td><x-status-badge :status="$row->status" :label="__('status_kredit.'.$row->status)" /></td>
                        <td><a href="{{ route('kredit.show', $row) }}">{{ __('form.detail') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">{{ __('kredit.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        @include('partials.pagination', ['paginator' => $kredit])
    </div>
@endsection
