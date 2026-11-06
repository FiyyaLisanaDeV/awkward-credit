@extends('layouts.app', ['title' => __('setting.history')])

@php
    $sectionLabels = [
        'POLICY_PARAMETER' => 'setting_section.policy',
        'APPROVAL_MATRIX' => 'setting_section.approval_matrix',
        'RATE_JIWA' => 'setting_section.rate_jiwa',
        'RATE_KEBAKARAN' => 'setting_section.rate_kebakaran',
        'RATE_KENDARAAN' => 'setting_section.rate_kendaraan',
        'COLLATERAL_CATEGORY' => 'setting_section.collateral',
        'CHECKLIST_DOKUMEN' => 'setting_section.checklist',
        'TEMPLATE_DOKUMEN' => 'setting_section.template',
        'PRODUK' => 'setting_section.produk',
    ];
@endphp

@section('content')
    <div class="page-head between">
        <div>
            <div class="small muted"><a href="{{ route('admin.index') }}">{{ __('setting.title') }}</a> / {{ __('setting.history') }}</div>
            <h1>{{ __('setting.history') }}</h1>
        </div>
        <a class="btn btn-sm" href="{{ route('admin.inbox') }}">{{ __('setting.inbox') }}</a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.history') }}" class="flex gap-2 items-center">
            <select class="input" name="section">
                <option value="">— {{ __('setting.sections') }} —</option>
                @foreach (\App\Enums\SettingSection::cases() as $case)
                    <option value="{{ $case->value }}" @selected($section === $case)>{{ __($sectionLabels[$case->value]) }}</option>
                @endforeach
            </select>
            <select class="input" name="status">
                <option value="">— {{ __('setting.status') }} —</option>
                @foreach (\App\Enums\StatusRevision::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case)>{{ __('status_revision.'.$case->value) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm">{{ __('form.filter') }}</button>
        </form>
    </div>

    <div class="card">
        @if ($revisions->isEmpty())
            <p class="muted">{{ __('setting.empty_history') }}</p>
        @else
            @include('settings.partials.revisions', ['revisions' => $revisions])
            @include('partials.pagination', ['paginator' => $revisions])
        @endif
    </div>
@endsection
