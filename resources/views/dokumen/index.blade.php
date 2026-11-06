@extends('layouts.app', ['title' => __('documents.title')])

@section('content')
    <div class="page-head">
        <h1>{{ __('documents.title') }}</h1>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('documents.nomor') }}</th>
                    <th>{{ __('documents.jenis') }}</th>
                    <th>{{ __('permohonan.debitur') }}</th>
                    <th>{{ __('documents.tanggal') }}</th>
                    <th>{{ __('documents.status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dokumen as $row)
                    <tr>
                        <td class="mono">{{ $row->nomor_dokumen }}</td>
                        <td>{{ __('documents.'.$row->jenis_dokumen.'.judul') }}</td>
                        <td>{{ $row->permohonan?->debitur?->nama_ktp }}</td>
                        <td>{{ $row->tanggal_dokumen?->format('d-m-Y') }}</td>
                        <td><x-status-badge :status="$row->status_ttd" :label="__('status_ttd.'.$row->status_ttd)" /></td>
                        <td><a href="{{ route('dokumen.show', $row) }}">{{ __('form.detail') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">{{ __('documents.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        @include('partials.pagination', ['paginator' => $dokumen])
    </div>
@endsection
