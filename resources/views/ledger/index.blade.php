@extends('layouts.app', ['title' => __('ledger.title')])

@section('content')
    <div class="page-head between">
        <h1>{{ __('ledger.title') }}</h1>
        <a class="btn" href="{{ route('ledger.verify') }}">{{ __('ledger.verify') }}</a>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('ledger.seq') }}</th>
                        <th>{{ __('ledger.occurred') }}</th>
                        <th>{{ __('ledger.event') }}</th>
                        <th>{{ __('ledger.entity') }}</th>
                        <th>{{ __('ledger.actor') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ledger as $row)
                        <tr>
                            <td class="num">{{ $row->seq }}</td>
                            <td class="nowrap">{{ $row->occurred_at?->format('d-m-Y H:i') }}</td>
                            <td class="mono small">{{ $row->event }}</td>
                            <td class="mono small">{{ $row->entity_type }}</td>
                            <td>{{ $row->actor?->name ?? '—' }}</td>
                            <td><a href="{{ route('ledger.show', $row) }}">{{ __('ledger.detail') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted">{{ __('ledger.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['paginator' => $ledger])
    </div>
@endsection
