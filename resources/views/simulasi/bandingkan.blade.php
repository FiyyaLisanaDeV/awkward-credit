@extends('layouts.app', ['title' => __('simulasi.compare')])

@section('content')
    <div class="page-head">
        <h1>{{ __('simulasi.compare') }}</h1>
        <a class="btn" href="{{ route('simulasi.index') }}">{{ __('form.back') }}</a>
    </div>

    <form method="GET" class="card card-tight">
        <div class="grid grid-2">
            <div class="field">
                <label for="awal">{{ __('simulasi.awal') }}</label>
                <select class="input" id="awal" name="awal">
                    <option value="">{{ __('form.pilih') }}</option>
                    @foreach ($daftar as $row)
                        <option value="{{ $row->id }}" @selected($awal?->id === $row->id)>{{ $row->label }} — {{ $row->dibuat_pada?->format('d-m-Y H:i') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="akhir">{{ __('simulasi.akhir') }}</label>
                <select class="input" id="akhir" name="akhir">
                    <option value="">{{ __('form.pilih') }}</option>
                    @foreach ($daftar as $row)
                        <option value="{{ $row->id }}" @selected($akhir?->id === $row->id)>{{ $row->label }} — {{ $row->dibuat_pada?->format('d-m-Y H:i') }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('simulasi.bandingkan') }}</button>
    </form>

    <div class="card">
        @if ($awal !== null && $akhir !== null)
            <h3>{{ $awal->label }} → {{ $akhir->label }}</h3>
            <x-diff-simulasi :diff="$diff" />
        @else
            <p class="muted">{{ __('simulasi.pilih_dua') }}</p>
        @endif
    </div>
@endsection
