<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @livewireStyles
</head>
<body>
    @php
        $nav = [
            ['dashboard', 'nav.dashboard'],
            ['debitur.index', 'nav.debitur'],
            ['permohonan.index', 'nav.permohonan'],
            ['simulasi.index', 'nav.simulasi'],
            ['approval.inbox', 'nav.approval'],
            ['dokumen.index', 'nav.dokumen'],
            ['kredit.index', 'nav.kredit'],
            ['ledger.index', 'nav.ledger'],
            ['import.index', 'nav.import'],
            ['admin.index', 'nav.admin'],
        ];
    @endphp

    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">{{ config('app.name') }}</div>
            <nav>
                @foreach ($nav as [$routeName, $label])
                    @if (Route::has($routeName))
                        <a href="{{ route($routeName) }}" class="{{ request()->routeIs($routeName) ? 'active' : '' }}">
                            {{ __($label) }}
                        </a>
                    @endif
                @endforeach
            </nav>
        </aside>

        <div class="main">
            <header class="navbar">
                <div class="muted">{{ $title ?? __('dashboard.title') }}</div>
                <div class="flex items-center gap-2">
                    <span class="small">{{ auth()->user()?->name }}</span>
                    <a class="btn btn-sm" href="{{ route('password.change') }}">{{ __('auth.change_password') }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm">{{ __('auth.logout') }}</button>
                    </form>
                </div>
            </header>

            <main class="content">
                @include('layouts.flash')
                @yield('content')
            </main>

            <footer class="footer">
                {{ config('app.name') }} · {{ now()->format('Y') }}
            </footer>
        </div>
    </div>

    @livewireScripts
</body>
</html>
