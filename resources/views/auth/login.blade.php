@extends('layouts.guest')

@section('content')
    <h1 class="auth-title">{{ config('app.name') }}</h1>

    <div class="card">
        <h2 class="mb-0">{{ __('auth.login_title') }}</h2>
        <p class="hint">{{ __('auth.login_hint') }}</p>

        @include('layouts.flash')

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="field">
                <label for="email">{{ __('auth.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                       required autofocus autocomplete="username">
            </div>

            <div class="field">
                <label for="password">{{ __('auth.password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>

            <div class="field checkbox">
                <input id="remember" name="remember" type="checkbox" value="1">
                <label for="remember" class="mb-0">{{ __('auth.remember') }}</label>
            </div>

            <button type="submit" class="btn btn-primary w-full">{{ __('auth.submit_login') }}</button>
        </form>
    </div>
@endsection
