@extends('layouts.app', ['title' => __('import.title')])

@section('content')
    <div class="page-head between">
        <h1>{{ __('import.title') }}</h1>
        <a class="btn btn-primary" href="{{ route('import.create') }}">{{ __('import.create') }}</a>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('import.status') }}</th>
                    <th class="num">{{ __('import.total') }}</th>
                    <th class="num">{{ __('import.sukses') }}</th>
                    <th class="num">{{ __('import.gagal') }}</th>
                    <th>{{ __('import.detail') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($batch as $row)
                    <tr>
                        <td><x-status-badge :status="$row->status" :label="__('status_import.'.$row->status)" /></td>
                        <td class="num">{{ $row->total_baris }}</td>
                        <td class="num">{{ $row->sukses }}</td>
                        <td class="num">{{ $row->gagal }}</td>
                        <td><a href="{{ route('import.show', $row) }}">{{ __('form.detail') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">{{ __('import.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        @include('partials.pagination', ['paginator' => $batch])
    </div>
@endsection
