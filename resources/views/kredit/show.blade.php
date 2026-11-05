@extends('layouts.app', ['title' => $kredit->no_rekening_kredit])

@section('content')
    <div class="page-head">
        <h1>{{ $kredit->no_rekening_kredit }}</h1>
        <a class="btn" href="{{ route('kredit.index') }}">{{ __('form.back') }}</a>
    </div>

    <div class="grid grid-3">
        <div class="card">
            <div class="hint">{{ __('kredit.plafon') }}</div>
            <h2 class="mb-0">Rp {{ number_format((float) $kredit->plafon, 0, ',', '.') }}</h2>
        </div>
        <div class="card">
            <div class="hint">{{ __('kredit.angsuran') }}</div>
            <h2 class="mb-0">Rp {{ number_format((float) $kredit->angsuran_bulanan, 0, ',', '.') }}</h2>
        </div>
        <div class="card">
            <div class="hint">{{ __('kredit.status') }}</div>
            <x-status-badge :status="$kredit->status" :label="__('status_kredit.'.$kredit->status)" />
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h3>{{ __('kredit.ringkasan') }}</h3>
            <dl class="review-list">
                <dt>{{ __('permohonan.debitur') }}</dt><dd>{{ $kredit->permohonan?->debitur?->nama_ktp }}</dd>
                <dt>{{ __('kredit.tenor') }}</dt><dd>{{ $kredit->tenor }} {{ __('form.bulan') }}</dd>
                <dt>{{ __('kredit.rate') }}</dt><dd>{{ number_format((float) $kredit->rate * 100, 2, ',', '.') }}%</dd>
                <dt>{{ __('kredit.rekening') }}</dt><dd class="mono">{{ $kredit->rekening_pencairan }}</dd>
                <dt>{{ __('kredit.tanggal_cair') }}</dt><dd>{{ $kredit->tanggal_cair?->format('d-m-Y') }}</dd>
            </dl>
        </div>

        <div class="card">
            <h3>{{ __('pembayaran.title') }}</h3>
            @livewire('pembayaran-form', ['kredit' => $kredit->id])
        </div>
    </div>

    <div class="card">
        <h3>{{ __('kredit.jadwal') }}</h3>
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('kredit.jatuh_tempo') }}</th>
                        <th class="num">{{ __('kredit.saldo_awal') }}</th>
                        <th class="num">{{ __('kredit.pokok') }}</th>
                        <th class="num">{{ __('kredit.bunga') }}</th>
                        <th class="num">{{ __('kredit.total') }}</th>
                        <th>{{ __('kredit.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kredit->jadwal as $baris)
                        <tr>
                            <td>{{ $baris->periode }}</td>
                            <td>{{ $baris->tanggal_jatuh_tempo?->format('d-m-Y') }}</td>
                            <td class="num">{{ number_format((float) $baris->saldo_awal, 0, ',', '.') }}</td>
                            <td class="num">{{ number_format((float) $baris->angsuran_pokok, 0, ',', '.') }}</td>
                            <td class="num">{{ number_format((float) $baris->bunga, 0, ',', '.') }}</td>
                            <td class="num">{{ number_format((float) $baris->total_angsuran, 0, ',', '.') }}</td>
                            <td><x-status-badge :status="$baris->status" :label="__('status_jadwal.'.$baris->status)" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h3>{{ __('pembayaran.riwayat') }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('pembayaran.periode') }}</th>
                    <th>{{ __('pembayaran.tanggal') }}</th>
                    <th class="num">{{ __('pembayaran.total') }}</th>
                    <th>{{ __('pembayaran.metode') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kredit->pembayaran as $bayar)
                    <tr>
                        <td>{{ $bayar->periode }}</td>
                        <td>{{ $bayar->tanggal_bayar?->format('d-m-Y') }}</td>
                        <td class="num">Rp {{ number_format((float) $bayar->total, 0, ',', '.') }}</td>
                        <td>{{ __('metode_pembayaran.'.$bayar->metode) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">{{ __('pembayaran.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
