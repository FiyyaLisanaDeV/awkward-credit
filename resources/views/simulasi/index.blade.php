@extends('layouts.app', ['title' => __('simulasi.title')])

@section('content')
    <div class="page-head">
        <h1>{{ __('simulasi.title') }}</h1>
        <div class="flex gap-1">
            <a class="btn" href="{{ route('simulasi.bandingkan') }}">{{ __('simulasi.compare') }}</a>
            <a class="btn btn-primary" href="{{ route('simulasi.create') }}">{{ __('simulasi.new') }}</a>
        </div>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('simulasi.label') }}</th>
                    <th class="num">{{ __('simulasi.plafon') }}</th>
                    <th class="num">{{ __('simulasi.tenor') }}</th>
                    <th class="num">{{ __('simulasi.angsuran_total') }}</th>
                    <th class="num">{{ __('simulasi.dscr') }}</th>
                    <th>{{ __('simulasi.tanggal') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($simulasi as $row)
                    <tr>
                        <td>{{ $row->label }}</td>
                        <td class="num">{{ number_format((float) $row->plafon, 0, ',', '.') }}</td>
                        <td class="num">{{ $row->tenor }}</td>
                        <td class="num">{{ number_format((float) $row->angsuran, 2, ',', '.') }}</td>
                        <td class="num">{{ $row->dscr !== null ? number_format((float) $row->dscr, 2, ',', '.') : '—' }}</td>
                        <td>{{ $row->dibuat_pada?->format('d-m-Y H:i') }}</td>
                        <td><a href="{{ route('simulasi.show', $row) }}">{{ __('form.detail') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">{{ __('simulasi.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        @include('partials.pagination', ['paginator' => $simulasi])
    </div>
@endsection
