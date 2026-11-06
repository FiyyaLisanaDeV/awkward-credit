@extends('layouts.app', ['title' => __('ledger.verify_title')])

@section('content')
    <div class="page-head">
        <h1>{{ __('ledger.verify_title') }}</h1>
        <a class="btn" href="{{ route('ledger.index') }}">{{ __('form.back') }}</a>
    </div>

    <div class="card">
        @if ($ok)
            <div class="alert alert-success">{{ __('ledger.verify_ok') }}</div>
        @else
            <div class="alert alert-error">{{ __('ledger.verify_fail') }}</div>
        @endif

        <pre class="code">{{ $output }}</pre>
    </div>
@endsection
