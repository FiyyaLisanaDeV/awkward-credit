@extends('layouts.app', ['title' => __('debitur.new')])

@section('content')
    <div class="page-head">
        <h1>{{ __('debitur.new') }}</h1>
        <a class="btn" href="{{ route('debitur.index') }}">{{ __('form.back') }}</a>
    </div>

    @livewire('debitur-wizard')
@endsection
