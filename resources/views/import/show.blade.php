@extends('layouts.app', ['title' => __('import.detail')])

@section('content')
    <div class="page-head">
        <h1>{{ __('import.detail') }}</h1>
        <a class="btn" href="{{ route('import.index') }}">{{ __('form.back') }}</a>
    </div>

    <div class="grid grid-4">
        <div class="card">
            <div class="hint">{{ __('import.status') }}</div>
            <x-status-badge :status="$batch->status" :label="__('status_import.'.$batch->status)" />
        </div>
        <div class="card">
            <div class="hint">{{ __('import.total') }}</div>
            <h2 class="mb-0">{{ $batch->total_baris }}</h2>
        </div>
        <div class="card">
            <div class="hint">{{ __('import.sukses') }}</div>
            <h2 class="mb-0">{{ $batch->sukses }}</h2>
        </div>
        <div class="card">
            <div class="hint">{{ __('import.gagal') }}</div>
            <h2 class="mb-0">{{ $batch->gagal }}</h2>
        </div>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('import.nomor_baris') }}</th>
                        <th>{{ __('import.status') }}</th>
                        <th>{{ __('import.errors') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batch->baris as $baris)
                        <tr>
                            <td class="num">{{ $baris->nomor_baris }}</td>
                            <td><x-status-badge :status="$baris->status" :label="__('status_import_baris.'.$baris->status)" /></td>
                            <td class="small">
                                @if ($baris->errors)
                                    {{ implode('; ', $baris->errors) }}
                                @else
                                    {{ __('import.no_error') }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">{{ __('import.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
