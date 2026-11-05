@extends('layouts.app', ['title' => __('simulasi.new')])

@section('content')
    <div class="page-head">
        <h1>{{ __('simulasi.new') }}</h1>
        <a class="btn" href="{{ route('simulasi.index') }}">{{ __('form.back') }}</a>
    </div>

    @livewire('simulator', ['permohonan' => $permohonan])
@endsection
