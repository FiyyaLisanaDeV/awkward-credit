@extends('layouts.app', ['title' => __('permohonan.new')])

@section('content')
    <div class="page-head">
        <h1>{{ __('permohonan.new') }}</h1>
        <a class="btn" href="{{ route('permohonan.index') }}">{{ __('form.back') }}</a>
    </div>

    @livewire('permohonan-wizard', ['debitur' => request('debitur')])
@endsection
