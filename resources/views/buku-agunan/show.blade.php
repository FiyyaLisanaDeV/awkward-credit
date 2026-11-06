@extends('layouts.app', ['title' => __('buku_agunan.riwayat')])

@section('content')
    <div class="page-head">
        <h1>{{ __('buku_agunan.riwayat') }}</h1>
        <a class="btn" href="{{ route('buku-agunan.index') }}">{{ __('form.back') }}</a>
    </div>

    @if ($lintasCabang)
        <div class="alert alert-warning">{{ __('buku_agunan.warning') }}</div>
    @endif

    <div class="card">
        <div class="hint">{{ __('buku_agunan.cabang') }}: {{ $jumlahCabang }}</div>
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('buku_agunan.tanggal') }}</th>
                        <th>{{ __('buku_agunan.cabang') }}</th>
                        <th>{{ __('buku_agunan.permohonan') }}</th>
                        <th>{{ __('buku_agunan.kredit') }}</th>
                        <th>{{ __('buku_agunan.aktor') }}</th>
                        <th>{{ __('buku_agunan.snapshot') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $row)
                        <tr>
                            <td class="nowrap">{{ $row->occurred_at?->format('d-m-Y H:i') }}</td>
                            <td>{{ $row->cabang?->nama ?? '—' }}</td>
                            <td class="mono">{{ $row->permohonan?->no_permohonan ?? '—' }}</td>
                            <td class="mono">{{ $row->kredit?->no_rekening_kredit ?? $row->permohonan?->kredit?->no_rekening_kredit ?? '—' }}</td>
                            <td>{{ $row->aktor_nama ?? '—' }}</td>
                            <td>
                                <details class="small">
                                    <summary>{{ __('form.detail') }}</summary>
                                    <pre class="code">{{ json_encode($row->snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted">{{ __('buku_agunan.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
