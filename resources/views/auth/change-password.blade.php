@extends('layouts.app')

@section('content')
    <h1>{{ __('auth.change_password_title') }}</h1>
    <p class="muted">{{ __('auth.change_password_hint') }}</p>

    <div class="card" style="max-width: 520px;">
        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <div class="field">
                <label for="current_password">{{ __('auth.current_password') }}</label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
                @error('current_password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password">{{ __('auth.new_password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="new-password">
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">{{ __('auth.confirm_password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary">{{ __('auth.submit_password') }}</button>
        </form>
    </div>
@endsection
