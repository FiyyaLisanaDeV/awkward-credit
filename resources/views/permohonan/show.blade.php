@extends('layouts.app', ['title' => $permohonan->no_permohonan])

@section('content')
    <div class="page-head">
        <h1>{{ $permohonan->no_permohonan }}</h1>
        <div class="flex gap-1">
            <a class="btn" href="{{ route('permohonan.index') }}">{{ __('form.back') }}</a>
            <a class="btn" href="{{ route('simulasi.create', ['permohonan' => $permohonan->id]) }}">{{ __('simulasi.title') }}</a>
            @can('kredit.cairkan')
                @if (in_array($permohonan->status, ['APPROVED', 'SIGNED'], true))
                    <a class="btn btn-primary" href="{{ route('kredit.cairkan.create', $permohonan) }}">{{ __('kredit.cairkan') }}</a>
                @endif
            @endcan
        </div>
    </div>

    <div class="grid grid-3">
        <div class="card">
            <div class="hint">{{ __('permohonan.plafon') }}</div>
            <h2 class="mb-0">Rp {{ number_format((float) $permohonan->plafon_diajukan, 0, ',', '.') }}</h2>
        </div>
        <div class="card">
            <div class="hint">{{ __('permohonan.tenor') }}</div>
            <h2 class="mb-0">{{ $permohonan->tenor_diajukan }} {{ __('form.bulan') }}</h2>
        </div>
        <div class="card">
            <div class="hint">{{ __('permohonan.status') }}</div>
            <h2 class="mb-0"><x-status-badge :status="$permohonan->status" :label="__('status_permohonan.'.$permohonan->status)" /></h2>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h3>{{ __('permohonan.detail') }}</h3>
            <dl class="review-list">
                <dt>{{ __('permohonan.debitur') }}</dt><dd>{{ $permohonan->debitur?->nama_ktp }}</dd>
                <dt>{{ __('debitur.cif') }}</dt><dd class="mono">{{ $permohonan->debitur?->cif ?? '—' }}</dd>
                <dt>{{ __('permohonan.produk') }}</dt><dd>{{ $permohonan->produk?->nama }}</dd>
                <dt>{{ __('permohonan.suku_bunga') }}</dt><dd>{{ $permohonan->suku_bunga_tahunan }}</dd>
                <dt>{{ __('permohonan.sistem') }}</dt><dd>{{ $permohonan->sistem_suku_bunga }}</dd>
                <dt>{{ __('permohonan.tujuan') }}</dt><dd>{{ $permohonan->tujuan_penggunaan ?? '—' }}</dd>
                <dt>{{ __('permohonan.kegunaan') }}</dt><dd>{{ $permohonan->kegunaan_pinjaman }}</dd>
                <dt>{{ __('permohonan.tanggal') }}</dt><dd>{{ $permohonan->tanggal_permohonan?->format('d-m-Y') }}</dd>
            </dl>
            <x-kelengkapan-bar :persen="$permohonan->kelengkapan_persen" />
        </div>

        <div class="card">
            <h3>{{ __('permohonan.slik_title') }}</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('permohonan.lembaga') }}</th>
                        <th class="num">{{ __('permohonan.plafon') }}</th>
                        <th class="num">{{ __('permohonan.baki_debet') }}</th>
                        <th>{{ __('permohonan.kolektibilitas') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permohonan->slikDebitur as $slik)
                        <tr>
                            <td>{{ $slik->nama_lembaga }}</td>
                            <td class="num">{{ number_format((float) $slik->plafon, 0, ',', '.') }}</td>
                            <td class="num">{{ number_format((float) $slik->baki_debet, 0, ',', '.') }}</td>
                            <td>{{ $slik->kolektibilitas?->kode }} — {{ $slik->kolektibilitas?->nama }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">{{ __('permohonan.slik_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @can('dokumen.generate_draft')
        <div class="card">
            <h3>{{ __('documents.generate') }}</h3>
            <p class="hint">{{ __('documents.generate_hint') }}</p>
            <form method="POST" action="{{ route('dokumen.generate', $permohonan) }}" class="inline-form">
                @csrf
                <select class="input" name="jenis" required>
                    @foreach (\App\Enums\JenisDokumen::templateCases() as $jenis)
                        <option value="{{ $jenis->value }}">{{ $jenis->value }} — {{ __('documents.'.$jenis->value.'.judul') }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">{{ __('documents.generate') }}</button>
            </form>
        </div>
    @endcan

    @if ($permohonan->rekomendasi !== null)
        <div class="card">
            <h3>{{ __('permohonan.rekomendasi') }}</h3>
            <dl class="review-list">
                <dt>{{ __('permohonan.rekomendasi_sistem') }}</dt><dd><x-status-badge :status="$permohonan->rekomendasi->rekomendasi_sistem" :label="__('rekomendasi.'.$permohonan->rekomendasi->rekomendasi_sistem)" /></dd>
                <dt>{{ __('permohonan.plafon_rekomendasi') }}</dt><dd>Rp {{ number_format((float) $permohonan->rekomendasi->plafon_final, 0, ',', '.') }}</dd>
                <dt>{{ __('permohonan.tenor_rekomendasi') }}</dt><dd>{{ $permohonan->rekomendasi->tenor_final ?? '—' }}</dd>
            </dl>
        </div>
    @endif
@endsection
