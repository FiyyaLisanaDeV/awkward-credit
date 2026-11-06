@extends('layouts.app', ['title' => __('ledger.detail')])

@section('content')
    <div class="page-head">
        <h1>{{ __('ledger.detail') }} #{{ $row->seq }}</h1>
        <a class="btn" href="{{ route('ledger.index') }}">{{ __('form.back') }}</a>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <dl class="review-list">
                <dt>{{ __('ledger.seq') }}</dt><dd>{{ $row->seq }}</dd>
                <dt>{{ __('ledger.occurred') }}</dt><dd>{{ $row->occurred_at?->format('d-m-Y H:i:s') }}</dd>
                <dt>{{ __('ledger.event') }}</dt><dd class="mono">{{ $row->event }}</dd>
                <dt>{{ __('ledger.entity') }}</dt><dd class="mono">{{ $row->entity_type }}</dd>
                <dt>{{ __('ledger.entity_id') }}</dt><dd class="mono">{{ $row->entity_id }}</dd>
                <dt>{{ __('ledger.actor') }}</dt><dd>{{ $row->actor?->name ?? '—' }}</dd>
                <dt>{{ __('ledger.correlation_id') }}</dt><dd class="mono small">{{ $row->correlation_id }}</dd>
                <dt>{{ __('ledger.idempotency_key') }}</dt><dd class="mono small">{{ $row->idempotency_key ?? '—' }}</dd>
            </dl>
        </div>

        <div class="card">
            <h3>{{ __('ledger.payload') }}</h3>
            <pre class="code">{{ json_encode($row->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </div>

    <div class="card">
        <dl class="review-list">
            <dt>{{ __('ledger.prev_hash') }}</dt><dd class="mono small">{{ $row->prev_hash }}</dd>
            <dt>{{ __('ledger.hash') }}</dt><dd class="mono small">{{ $row->hash }}</dd>
        </dl>
    </div>
@endsection
