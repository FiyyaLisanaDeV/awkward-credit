@extends('layouts.app', ['title' => __('import.create')])

@section('content')
    <div class="page-head">
        <h1>{{ __('import.create') }}</h1>
        <a class="btn" href="{{ route('import.index') }}">{{ __('form.back') }}</a>
    </div>

    <div class="card">
        <p class="hint">{{ __('import.hint') }}</p>
        <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data">
            @csrf
            <x-input name="file" label="{{ __('import.file') }}" type="file" required />
            <div class="mt-2">
                <button type="submit" class="btn btn-primary">{{ __('import.upload') }}</button>
            </div>
        </form>
    </div>
@endsection
