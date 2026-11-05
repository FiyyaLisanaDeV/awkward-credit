<div>
    <ol class="wizard-steps">
        @foreach ([1 => __('debitur.step_identitas'), 2 => __('debitur.step_alamat'), 3 => __('debitur.step_pekerjaan')] as $nomor => $judul)
            <li class="{{ $step === $nomor ? 'active' : ($step > $nomor ? 'done' : '') }}">
                <span class="step-no">{{ $nomor }}</span> {{ $judul }}
            </li>
        @endforeach
    </ol>

    <form wire:submit="save" class="card">
        @if ($step === 1)
            <div class="grid grid-2">
                <x-input name="nama_ktp" :label="__('debitur.nama_ktp')" wire:model="nama_ktp" required />
                <x-input name="nama_ibu_kandung" :label="__('debitur.nama_ibu_kandung')" wire:model="nama_ibu_kandung" />
                <x-input name="no_ktp" :label="__('debitur.no_ktp')" wire:model="no_ktp" required hint="{{ __('debitur.no_ktp_hint') }}" />
                <x-input name="no_kk" :label="__('debitur.no_kk')" wire:model="no_kk" />
                <x-input name="npwp" :label="__('debitur.npwp')" wire:model="npwp" />
                <x-select name="jenis_kelamin" :label="__('debitur.jenis_kelamin')" wire:model="jenis_kelamin" required
                    :options="collect($jenisKelamin)->mapWithKeys(fn ($e) => [$e->value => __('enums.jenis_kelamin.'.$e->value)])->all()" />
                <x-select name="status_perkawinan" :label="__('debitur.status_perkawinan')" wire:model="status_perkawinan" required
                    placeholder="{{ __('form.pilih') }}"
                    :options="collect($statusPerkawinan)->mapWithKeys(fn ($e) => [$e->value => __('enums.status_perkawinan.'.$e->value)])->all()" />
                <x-input name="agama" :label="__('debitur.agama')" wire:model="agama" />
                <x-input name="pendidikan" :label="__('debitur.pendidikan')" wire:model="pendidikan" />
                <x-input name="tempat_lahir" :label="__('debitur.tempat_lahir')" wire:model="tempat_lahir" required />
                <x-input name="tanggal_lahir" type="date" :label="__('debitur.tanggal_lahir')" wire:model="tanggal_lahir" required />
            </div>
        @elseif ($step === 2)
            <div class="grid grid-2">
                <x-input name="no_hp" :label="__('debitur.no_hp')" wire:model="no_hp" required />
                <x-input name="no_wa" :label="__('debitur.no_wa')" wire:model="no_wa" />
                <x-input name="email" type="email" :label="__('debitur.email')" wire:model="email" />
                <x-input name="alamat_jalan" :label="__('debitur.alamat_jalan')" wire:model="alamat_jalan" required />
                <x-input name="alamat_rt" :label="__('debitur.rt')" wire:model="alamat_rt" required />
                <x-input name="alamat_rw" :label="__('debitur.rw')" wire:model="alamat_rw" required />
                <x-input name="kelurahan" :label="__('debitur.kelurahan')" wire:model="kelurahan" required />
                <x-input name="kecamatan" :label="__('debitur.kecamatan')" wire:model="kecamatan" required />
                <x-input name="kabupaten" :label="__('debitur.kabupaten')" wire:model="kabupaten" required />
                <x-input name="provinsi" :label="__('debitur.provinsi')" wire:model="provinsi" required />
                <x-input name="kode_pos" :label="__('debitur.kode_pos')" wire:model="kode_pos" />
                <x-input name="koordinat_rumah" :label="__('debitur.koordinat')" wire:model="koordinat_rumah" />
                <x-input name="status_tempat_tinggal" :label="__('debitur.status_tempat_tinggal')" wire:model="status_tempat_tinggal" />
                <x-input name="lama_tinggal_tahun" type="number" :label="__('debitur.lama_tinggal')" wire:model="lama_tinggal_tahun" />
                <x-input name="jumlah_keluarga" type="number" :label="__('debitur.jumlah_keluarga')" wire:model="jumlah_keluarga" />
                <x-input name="jumlah_tanggungan" type="number" :label="__('debitur.jumlah_tanggungan')" wire:model="jumlah_tanggungan" />
            </div>
            <x-input name="alamat_tinggal" :label="__('debitur.alamat_tinggal')" wire:model="alamat_tinggal" />
        @else
            <div class="grid grid-2">
                <x-input name="pekerjaan_utama" :label="__('debitur.pekerjaan')" wire:model="pekerjaan_utama" />
                <x-input name="jabatan" :label="__('debitur.jabatan')" wire:model="jabatan" />
                <x-input name="nama_perusahaan" :label="__('debitur.nama_perusahaan')" wire:model="nama_perusahaan" />
                <x-input name="lama_bekerja_tahun" type="number" :label="__('debitur.lama_bekerja')" wire:model="lama_bekerja_tahun" />
                <x-money-input name="penghasilan_bulanan" :label="__('debitur.penghasilan')" wire:model="penghasilan_bulanan" />
            </div>

            <div class="review">
                <h4>{{ __('debitur.review') }}</h4>
                <dl class="review-list">
                    <dt>{{ __('debitur.nama_ktp') }}</dt><dd>{{ $nama_ktp }}</dd>
                    <dt>{{ __('debitur.no_ktp') }}</dt><dd>{{ $no_ktp }}</dd>
                    <dt>{{ __('debitur.no_hp') }}</dt><dd>{{ $no_hp }}</dd>
                    <dt>{{ __('debitur.alamat_jalan') }}</dt><dd>{{ $alamat_jalan }}, {{ $kelurahan }}, {{ $kecamatan }}</dd>
                </dl>
            </div>
        @endif

        <div class="wizard-actions">
            @if ($step > 1)
                <button type="button" class="btn" wire:click="previous">{{ __('form.back') }}</button>
            @endif
            @if ($step < 3)
                <button type="button" class="btn btn-primary" wire:click="next">{{ __('form.next') }}</button>
            @else
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">{{ __('debitur.save') }}</button>
            @endif
        </div>
    </form>
</div>
