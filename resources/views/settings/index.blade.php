@extends('layouts.app', ['title' => __('setting.title')])

@section('content')
    <div class="page-head between">
        <div>
            <h1>{{ __('setting.title') }}</h1>
            <p class="muted mb-0">{{ __('setting.subtitle') }}</p>
        </div>
        <div class="flex gap-2">
            <a class="btn btn-sm" href="{{ route('admin.inbox') }}">{{ __('setting.inbox') }}</a>
            <a class="btn btn-sm" href="{{ route('admin.history') }}">{{ __('setting.history') }}</a>
        </div>
    </div>

    <div class="grid grid-3">
        @foreach ($sections as $row)
            <div class="card">
                <div class="between">
                    <h2 class="mb-0">{{ $row['label'] }}</h2>
                    @if ($row['pending'] > 0)
                        <span class="badge badge-warning">{{ $row['pending'] }}</span>
                    @endif
                </div>
                <p class="muted">{{ $row['description'] }}</p>
                <div class="small muted">
                    {{ __('setting.section_count') }}: {{ $row['count'] }}
                    @if ($row['active_version'])
                        · {{ __('setting.active_version') }}: <span class="mono">{{ $row['active_version'] }}</span>
                    @endif
                </div>
                <div class="mt-2">
                    <a class="btn btn-sm btn-primary" href="{{ route('admin.section', $row['key']) }}">{{ __('setting.manage') }}</a>
                </div>
            </div>
        @endforeach
    </div>
@endsection
