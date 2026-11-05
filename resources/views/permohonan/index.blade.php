@extends('layouts.app', ['title' => __('permohonan.title')])

@section('content')
    <div class="page-head">
        <h1>{{ __('permohonan.title') }}</h1>
        <a class="btn btn-primary" href="{{ route('permohonan.create') }}">{{ __('permohonan.new') }}</a>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('permohonan.no') }}</th>
                    <th>{{ __('permohonan.debitur') }}</th>
                    <th>{{ __('permohonan.produk') }}</th>
                    <th class="num">{{ __('permohonan.plafon') }}</th>
                    <th>{{ __('permohonan.tanggal') }}</th>
                    <th>{{ __('permohonan.status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($permohonan as $row)
                    <tr>
                        <td class="mono">{{ $row->no_permohonan }}</td>
                        <td>{{ $row->debitur?->nama_ktp }}</td>
                        <td>{{ $row->produk?->nama }}</td>
                        <td class="num">Rp {{ number_format((float) $row->plafon_diajukan, 0, ',', '.') }}</td>
                        <td>{{ $row->tanggal_permohonan?->format('d-m-Y') }}</td>
                        <td><x-status-badge :status="$row->status" :label="__('status_permohonan.'.$row->status)" /></td>
                        <td><a href="{{ route('permohonan.show', $row) }}">{{ __('form.detail') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">{{ __('permohonan.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        @include('partials.pagination', ['paginator' => $permohonan])
    </div>
@endsection
