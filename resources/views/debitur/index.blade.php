@extends('layouts.app', ['title' => __('debitur.title')])

@section('content')
    <div class="page-head">
        <h1>{{ __('debitur.title') }}</h1>
        <a class="btn btn-primary" href="{{ route('debitur.create') }}">{{ __('debitur.new') }}</a>
    </div>

    <form method="GET" class="card card-tight">
        <div class="inline-form">
            <input class="input" type="search" name="q" value="{{ $q }}" placeholder="{{ __('debitur.search') }}">
            <button class="btn" type="submit">{{ __('form.search') }}</button>
        </div>
    </form>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('debitur.cif') }}</th>
                    <th>{{ __('debitur.nama_ktp') }}</th>
                    <th>{{ __('debitur.no_ktp') }}</th>
                    <th>{{ __('debitur.no_hp') }}</th>
                    <th class="num">{{ __('debitur.permohonan_count') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($debitur as $row)
                    <tr>
                        <td class="mono">{{ $row->cif ?? '—' }}</td>
                        <td>{{ $row->nama_ktp }}</td>
                        <td class="mono">{{ $row->no_ktp }}</td>
                        <td>{{ $row->no_hp }}</td>
                        <td class="num">{{ $row->permohonan_count }}</td>
                        <td><a href="{{ route('debitur.show', $row) }}">{{ __('form.detail') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">{{ __('debitur.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        @include('partials.pagination', ['paginator' => $debitur])
    </div>
@endsection
