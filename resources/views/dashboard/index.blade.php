@extends('layouts.app')

@section('content')
    <h1>{{ __('dashboard.title') }}</h1>
    <p class="muted">{{ __('dashboard.welcome', ['name' => auth()->user()?->name]) }}</p>

    <div class="grid grid-4">
        @foreach (['draft', 'submitted', 'approved', 'active'] as $kpi)
            <div class="card mb-0">
                <div class="hint">{{ __('dashboard.kpi.'.$kpi) }}</div>
                <h2 class="mb-0">{{ __('dashboard.kpi.empty') }}</h2>
            </div>
        @endforeach
    </div>

    <div class="card mt-2">
        <h3>{{ __('dashboard.placeholder_title') }}</h3>
        <p class="mb-0">{{ __('dashboard.placeholder_body') }}</p>
    </div>
@endsection
