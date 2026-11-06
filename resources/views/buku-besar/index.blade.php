@extends('layouts.app', ['title' => __('buku_besar.title')])

@section('content')
    <div class="page-head">
        <h1>{{ __('buku_besar.title') }}</h1>
    </div>

    <form method="GET" class="card card-tight">
        <div class="inline-form">
            <input class="input" type="search" name="q" value="{{ $q }}" placeholder="{{ __('buku_besar.search') }}">
            <select class="input" name="tahap">
                <option value="">— {{ __('buku_besar.tahap') }} —</option>
                @foreach (['INISIASI', 'PENGAJUAN', 'ANALISIS', 'PERSETUJUAN', 'REALISASI', 'PERUBAHAN'] as $opsi)
                    <option value="{{ $opsi }}" @selected($tahap === $opsi)>{{ __('buku_besar.'.$opsi) }}</option>
                @endforeach
            </select>
            <button class="btn" type="submit">{{ __('form.search') }}</button>
        </div>
    </form>

    <div class="card">
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('buku_besar.waktu') }}</th>
                        <th>{{ __('buku_besar.tahap') }}</th>
                        <th>{{ __('permohonan.no') }}</th>
                        <th>{{ __('permohonan.debitur') }}</th>
                        <th>{{ __('buku_besar.aktor') }}</th>
                        <th>{{ __('buku_besar.ringkasan') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entri as $row)
                        <tr>
                            <td class="nowrap">{{ $row->occurred_at?->format('d-m-Y H:i') }}</td>
                            <td>{{ __('buku_besar.'.$row->tahap) }}</td>
                            <td class="mono">{{ $row->permohonan?->no_permohonan }}</td>
                            <td>{{ $row->permohonan?->debitur?->nama_ktp }}</td>
                            <td>{{ $row->aktor_nama ?? '—' }}<div class="hint">{{ $row->aktor_role }}</div></td>
                            <td class="small">{{ $row->ringkasan }}</td>
                            <td>
                                @if ($row->permohonan)
                                    <a href="{{ route('buku-besar.show', $row->permohonan) }}">{{ __('buku_besar.detail') }}</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="muted">{{ __('buku_besar.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['paginator' => $entri])
    </div>
@endsection
