@extends('layouts.app', ['title' => $debitur->nama_ktp])

@section('content')
    <div class="page-head">
        <h1>{{ $debitur->nama_ktp }}</h1>
        <a class="btn" href="{{ route('debitur.index') }}">{{ __('form.back') }}</a>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h3>{{ __('debitur.identitas') }}</h3>
            <dl class="review-list">
                <dt>{{ __('debitur.no_ktp') }}</dt><dd class="mono">{{ $debitur->no_ktp }}</dd>
                <dt>{{ __('debitur.jenis_kelamin') }}</dt><dd>{{ __('enums.jenis_kelamin.'.$debitur->jenis_kelamin) }}</dd>
                <dt>{{ __('debitur.status_perkawinan') }}</dt><dd>{{ __('enums.status_perkawinan.'.$debitur->status_perkawinan) }}</dd>
                <dt>{{ __('debitur.tempat_lahir') }}</dt><dd>{{ $debitur->tempat_lahir }}, {{ $debitur->tanggal_lahir?->format('d-m-Y') }}</dd>
                <dt>{{ __('debitur.pekerjaan') }}</dt><dd>{{ $debitur->pekerjaan_utama ?? '—' }}</dd>
                <dt>{{ __('debitur.penghasilan') }}</dt><dd>Rp {{ number_format((float) $debitur->penghasilan_bulanan, 0, ',', '.') }}</dd>
            </dl>
        </div>

        <div class="card">
            <h3>{{ __('debitur.kontak_alamat') }}</h3>
            <dl class="review-list">
                <dt>{{ __('debitur.no_hp') }}</dt><dd>{{ $debitur->no_hp }}</dd>
                <dt>{{ __('debitur.email') }}</dt><dd>{{ $debitur->email ?? '—' }}</dd>
                <dt>{{ __('debitur.alamat_jalan') }}</dt>
                <dd>{{ $debitur->alamat_jalan }}, RT {{ $debitur->alamat_rt }}/RW {{ $debitur->alamat_rw }}, {{ $debitur->kelurahan }}, {{ $debitur->kecamatan }}, {{ $debitur->kabupaten }}, {{ $debitur->provinsi }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <h3>{{ __('debitur.permohonan') }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('permohonan.no') }}</th>
                    <th>{{ __('permohonan.tanggal') }}</th>
                    <th class="num">{{ __('permohonan.plafon') }}</th>
                    <th>{{ __('permohonan.status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($debitur->permohonan as $row)
                    <tr>
                        <td class="mono">{{ $row->no_permohonan }}</td>
                        <td>{{ $row->tanggal_permohonan?->format('d-m-Y') }}</td>
                        <td class="num">Rp {{ number_format((float) $row->plafon_diajukan, 0, ',', '.') }}</td>
                        <td><x-status-badge :status="$row->status" :label="__('status_permohonan.'.$row->status)" /></td>
                        <td><a href="{{ route('permohonan.show', $row) }}">{{ __('form.detail') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">{{ __('permohonan.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <a class="btn btn-sm" href="{{ route('permohonan.create', ['debitur' => $debitur->id]) }}">{{ __('permohonan.new') }}</a>
    </div>
@endsection
