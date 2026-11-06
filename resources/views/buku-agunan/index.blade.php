@extends('layouts.app', ['title' => __('buku_agunan.title')])

@section('content')
    <div class="page-head">
        <h1>{{ __('buku_agunan.title') }}</h1>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('buku_agunan.jenis') }}</th>
                        <th>{{ __('buku_agunan.no_dokumen') }}</th>
                        <th>{{ __('buku_agunan.pemilik') }}</th>
                        <th class="num">{{ __('buku_agunan.pemakaian') }}</th>
                        <th class="num">{{ __('buku_agunan.cabang') }}</th>
                        <th>{{ __('buku_agunan.terakhir') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agunan as $row)
                        <tr>
                            <td>{{ $row->jenis_aset }}</td>
                            <td class="mono">{{ $row->no_dokumen ?? '—' }}</td>
                            <td>{{ $row->nama_pemilik ?? '—' }}</td>
                            <td class="num">{{ $row->pemakaian }}</td>
                            <td class="num">
                                {{ $row->cabang_count }}
                                @if ((int) $row->cabang_count > 1)
                                    <span class="badge badge-warning">{{ __('buku_agunan.lintas_cabang') }}</span>
                                @endif
                            </td>
                            <td class="nowrap">{{ \Illuminate\Support\Carbon::parse($row->terakhir)->format('d-m-Y H:i') }}</td>
                            <td><a href="{{ route('buku-agunan.show', $row->agunan_key) }}">{{ __('form.detail') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="muted">{{ __('buku_agunan.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['paginator' => $agunan])
    </div>
@endsection
