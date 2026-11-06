@extends('layouts.app', ['title' => __('setting.inbox')])

@section('content')
    <div class="page-head between">
        <div>
            <div class="small muted"><a href="{{ route('admin.index') }}">{{ __('setting.title') }}</a> / {{ __('setting.inbox') }}</div>
            <h1>{{ __('setting.inbox') }}</h1>
            <p class="muted mb-0">{{ __('setting.pending_revisions') }}</p>
        </div>
        <a class="btn btn-sm" href="{{ route('admin.history') }}">{{ __('setting.history') }}</a>
    </div>

    <div class="card">
        @if ($revisions->isEmpty())
            <p class="muted">{{ __('setting.empty_pending') }}</p>
        @else
            @include('settings.partials.revisions', ['revisions' => $revisions])
            @include('partials.pagination', ['paginator' => $revisions])
        @endif
    </div>
@endsection
