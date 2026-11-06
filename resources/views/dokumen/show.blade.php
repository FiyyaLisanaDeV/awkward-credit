@extends('layouts.app', ['title' => $dokumen->nomor_dokumen])

@section('content')
    <div class="page-head">
        <h1>{{ $dokumen->nomor_dokumen }}</h1>
        <a class="btn" href="{{ route('dokumen.index') }}">{{ __('form.back') }}</a>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h3>{{ __('documents.title') }}</h3>
            <dl class="review-list">
                <dt>{{ __('documents.jenis') }}</dt><dd>{{ __('documents.'.$dokumen->jenis_dokumen.'.judul') }}</dd>
                <dt>{{ __('permohonan.debitur') }}</dt><dd>{{ $dokumen->permohonan?->debitur?->nama_ktp }}</dd>
                <dt>{{ __('documents.nomor') }}</dt><dd class="mono">{{ $dokumen->nomor_dokumen }}</dd>
                <dt>{{ __('documents.tanggal') }}</dt><dd>{{ $dokumen->tanggal_dokumen?->format('d-m-Y') }}</dd>
                <dt>{{ __('documents.status') }}</dt><dd><x-status-badge :status="$dokumen->status_ttd" :label="__('status_ttd.'.$dokumen->status_ttd)" /></dd>
            </dl>
        </div>

        <div class="card">
            <h3>{{ __('documents.riwayat_ttd') }}</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('documents.pihak') }}</th>
                        <th>{{ __('documents.nama') }}</th>
                        <th>{{ __('documents.jabatan') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dokumen->signatures as $signature)
                        <tr>
                            <td>{{ __('pihak_signature.'.$signature->pihak) }}</td>
                            <td>{{ $signature->nama }}</td>
                            <td>{{ $signature->jabatan ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">{{ __('documents.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>

            @can('dokumen.sign')
                <form method="POST" action="{{ route('dokumen.sign', $dokumen) }}" class="mt-2">
                    @csrf
                    <p class="hint">{{ __('documents.sign_hint') }}</p>
                    <div class="field">
                        <label for="pihak">{{ __('documents.pihak') }}</label>
                        <select class="input" id="pihak" name="pihak" required>
                            @foreach (\App\Enums\PihakSignature::cases() as $pihak)
                                <option value="{{ $pihak->value }}" @selected(old('pihak') === $pihak->value)>{{ __('pihak_signature.'.$pihak->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-input name="nama" label="{{ __('documents.nama') }}" required value="{{ old('nama') }}" />
                    <x-input name="jabatan" label="{{ __('documents.jabatan') }}" value="{{ old('jabatan') }}" />
                    <div class="mt-1">
                        <button type="submit" class="btn btn-primary">{{ __('documents.sign_button') }}</button>
                    </div>
                </form>
            @endcan
        </div>
    </div>
@endsection
